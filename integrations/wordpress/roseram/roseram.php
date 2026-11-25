<?php
/**
 * Plugin Name: Roseram
 * Plugin URI: https://roseram.com/integrations/wordpress
 * Description: Connect your Roseram account to request AI assistance from the WordPress administration area.
 * Version: 0.1.0
 * Requires at least: 6.5
 * Requires PHP: 8.0
 * Author: Roseram
 * Author URI: https://roseram.com/
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: roseram
 */
defined( 'ABSPATH' ) || exit;

final class Roseram_Connector {
    private const META = '_roseram_connector_key';
    private const BASE = 'https://roseram.com/api/v1';

    public static function register(): void {
        add_menu_page( 'Roseram', 'Roseram', 'manage_options', 'roseram', array( self::class, 'page' ), 'dashicons-admin-generic' );
    }

    private static function secret(): string {
        return hash( 'sha256', wp_salt( 'auth' ) . wp_salt( 'secure_auth' ), true );
    }

    private static function encrypt( string $key ): string {
        if ( ! function_exists( 'openssl_encrypt' ) ) { throw new RuntimeException( 'OpenSSL is required.' ); }
        $iv = random_bytes( 12 );
        $tag = '';
        $cipher = openssl_encrypt( $key, 'aes-256-gcm', self::secret(), OPENSSL_RAW_DATA, $iv, $tag );
        if ( false === $cipher ) { throw new RuntimeException( 'Could not secure the API key.' ); }
        return base64_encode( $iv . $tag . $cipher );
    }

    private static function key(): string {
        $saved = get_user_meta( get_current_user_id(), self::META, true );
        if ( ! is_string( $saved ) || ! $saved || ! function_exists( 'openssl_decrypt' ) ) { return ''; }
        $raw = base64_decode( $saved, true );
        if ( false === $raw || strlen( $raw ) < 29 ) { return ''; }
        $key = openssl_decrypt( substr( $raw, 28 ), 'aes-256-gcm', self::secret(), OPENSSL_RAW_DATA, substr( $raw, 0, 12 ), substr( $raw, 12, 16 ) );
        return is_string( $key ) ? $key : '';
    }

    private static function request( string $path, string $key, ?array $body = null ) {
        $args = array( 'timeout' => 55, 'redirection' => 0, 'sslverify' => true, 'limit_response_size' => 1048576,
            'headers' => array( 'Authorization' => 'Bearer ' . $key, 'Content-Type' => 'application/json' ) );
        if ( null !== $body ) { $args['method'] = 'POST'; $args['body'] = wp_json_encode( $body ); }
        return wp_safe_remote_request( self::BASE . $path, $args );
    }

    public static function page(): void {
        if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html__( 'You do not have permission.', 'roseram' ), '', array( 'response' => 403 ) ); }
        $notice = ''; $answer = ''; $error = false;
        if ( isset( $_POST['roseram_action'] ) && is_string( $_POST['roseram_action'] ) ) {
            check_admin_referer( 'roseram_connector', 'roseram_nonce' );
            $action = sanitize_key( wp_unslash( $_POST['roseram_action'] ) );
            try {
                if ( 'disconnect' === $action ) {
                    delete_user_meta( get_current_user_id(), self::META );
                    $notice = 'Disconnected from this WordPress account.';
                } elseif ( 'connect' === $action ) {
                    $candidate = isset( $_POST['roseram_key'] ) && is_string( $_POST['roseram_key'] ) ? trim( wp_unslash( $_POST['roseram_key'] ) ) : '';
                    if ( ! preg_match( '/\Arr_live_[A-Za-z0-9_-]{20,100}\z/', $candidate ) ) { throw new RuntimeException( 'Enter a valid Roseram API key.' ); }
                    $response = self::request( '/models', $candidate );
                    if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) { throw new RuntimeException( 'Connection failed. Check your API access and key in Roseram.' ); }
                    update_user_meta( get_current_user_id(), self::META, self::encrypt( $candidate ) );
                    $notice = 'Connected. Your model requests will use your Roseram account.';
                } elseif ( 'ask' === $action ) {
                    $key = self::key();
                    $prompt = isset( $_POST['roseram_prompt'] ) && is_string( $_POST['roseram_prompt'] ) ? sanitize_textarea_field( wp_unslash( $_POST['roseram_prompt'] ) ) : '';
                    if ( ! $key || ! trim( $prompt ) || strlen( $prompt ) > 30000 ) { throw new RuntimeException( 'Connect your account and enter a prompt of up to 30,000 bytes.' ); }
                    $response = self::request( '/chat/completions', $key, array( 'model' => 'roseram', 'messages' => array( array( 'role' => 'user', 'content' => $prompt ) ), 'max_tokens' => 4096, 'stream' => false ) );
                    if ( is_wp_error( $response ) ) { throw new RuntimeException( 'The connection to Roseram failed.' ); }
                    $data = json_decode( wp_remote_retrieve_body( $response ), true );
                    if ( 200 !== wp_remote_retrieve_response_code( $response ) ) { throw new RuntimeException( 'Roseram could not complete the request. Check your plan, credits and API status.' ); }
                    $answer = $data['choices'][0]['message']['content'] ?? '';
                    if ( ! is_string( $answer ) || '' === $answer ) { throw new RuntimeException( 'Roseram returned no text.' ); }
                }
            } catch ( Throwable $exception ) { $error = true; $notice = $exception instanceof RuntimeException ? $exception->getMessage() : 'The request could not be completed.'; }
        }
        $connected = '' !== self::key();
        ?>
        <div class="wrap">
            <h1>Roseram</h1>
            <p>Use your own Roseram membership and credits for AI assistance in WordPress.</p>
            <?php if ( $notice ) : ?><div class="notice <?php echo $error ? 'notice-error' : 'notice-success'; ?>"><p><?php echo esc_html( $notice ); ?></p></div><?php endif; ?>
            <h2>Account connection</h2>
            <p><?php echo $connected ? 'Connected to your Roseram account.' : 'Not connected.'; ?> <a href="https://roseram.com/developers" target="_blank" rel="noopener noreferrer">Manage API access</a>.</p>
            <form method="post">
                <?php wp_nonce_field( 'roseram_connector', 'roseram_nonce' ); ?>
                <?php if ( $connected ) : ?>
                    <input type="hidden" name="roseram_action" value="disconnect" /><?php submit_button( 'Disconnect', 'secondary' ); ?>
                <?php else : ?>
                    <input type="hidden" name="roseram_action" value="connect" />
                    <label for="roseram_key">Private Roseram API key</label><br /><input type="password" name="roseram_key" id="roseram_key" class="regular-text" autocomplete="new-password" required />
                    <p>Connecting sends the key to Roseram over HTTPS to check API access. It is stored encrypted for your WordPress user. <a href="https://roseram.com/privacy">Privacy</a> · <a href="https://roseram.com/terms">Terms</a></p>
                    <?php submit_button( 'Connect Roseram' ); ?>
                <?php endif; ?>
            </form>
            <?php if ( $connected ) : ?>
                <h2>Ask Roseram</h2><p>Only the prompt you submit is sent to Roseram. Model requests may consume prepaid API credits. Review generated text before using it.</p>
                <form method="post">
                    <?php wp_nonce_field( 'roseram_connector', 'roseram_nonce' ); ?>
                    <input type="hidden" name="roseram_action" value="ask" />
                    <label for="roseram_prompt">Your request</label><br /><textarea name="roseram_prompt" id="roseram_prompt" rows="8" class="large-text" required maxlength="30000"></textarea>
                    <?php submit_button( 'Send to Roseram' ); ?>
                </form>
                <?php if ( $answer ) : ?><h2>Response</h2><textarea readonly rows="12" class="large-text" aria-label="Roseram response"><?php echo esc_textarea( $answer ); ?></textarea><?php endif; ?>
            <?php endif; ?>
        </div>
        <?php
    }
}

add_action( 'admin_menu', array( Roseram_Connector::class, 'register' ) );

