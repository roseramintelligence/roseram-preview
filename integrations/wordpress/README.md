# Roseram WordPress connector

Install the public connector source on a WordPress staging site. Requires WordPress 6.5+, PHP 8.0+, OpenSSL, outbound HTTPS and eligible Roseram developer API access.

## Install from source

1. Download this repository using Code → Download ZIP.
2. Copy the `integrations/wordpress/roseram` folder into `wp-content/plugins/`.
3. Activate Roseram in WordPress and open its administration menu.
4. Create a private key in [Roseram Developer API settings](https://roseram.com/developers), then connect it in the plugin.
5. Enter a prompt and review the returned text. Requests use the connected account's API credits.

The connector provides administrator-only text assistance. It does not execute generated code or publish responses automatically. Every administrator connects their own account key. The preview has not completed WordPress runtime acceptance or WordPress.org review.

## Service and privacy

See [readme.txt](roseram/readme.txt) for service transmission details. The key is stored encrypted using WordPress salts and OpenSSL AES-GCM; rotating salts invalidates stored credentials. Hosting administrators with database and server configuration access may recover credentials. Disconnect to remove the locally stored key, and revoke it on Roseram when appropriate.

[API access](https://roseram.com/developers) · [Plans](https://roseram.com/plans) · [Privacy](https://roseram.com/privacy) · [Terms](https://roseram.com/terms)

## License

This subdirectory is licensed GPL-2.0-or-later, as declared in the plugin header and readme. It is an exception to the parent repository's MIT license. License terms: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
