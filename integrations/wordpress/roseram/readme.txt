=== Roseram ===
Tags: ai, assistant, api, productivity
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 8.0
Stable tag: 0.1.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Connect your Roseram account to WordPress and request AI assistance using your existing API credits.

== Description ==

Roseram provides an administrator-only text assistant for WordPress. Connect an eligible Roseram developer account, enter a request, and review the returned text in your dashboard. Responses are never executed or automatically published. Each WordPress administrator connects their own API key.

This plugin relies on the external Roseram service at https://roseram.com/. A Roseram account, eligible membership, enabled developer API and sufficient prepaid credits are required. Connecting sends an API key to https://roseram.com/api/v1/models over HTTPS. Sending a request transmits the entered prompt and key to https://roseram.com/api/v1/chat/completions for processing and usage accounting. No background requests, visitor information, or site content are transmitted automatically. Do not include sensitive information in your prompts.

Service: https://roseram.com/
API access: https://roseram.com/developers
Pricing: https://roseram.com/plans
Privacy policy: https://roseram.com/privacy
Terms: https://roseram.com/terms

== Installation ==

1. Upload the plugin ZIP via Plugins > Add New > Upload Plugin.
2. Activate Roseram and open its dashboard menu as an administrator.
3. Create a private key in Roseram Developer API settings.
4. Enter the key and select Connect Roseram. Your hosting environment must permit outbound HTTPS and provide PHP OpenSSL.
5. Enter a prompt and select Send to Roseram. Disconnect at any time to remove the locally stored key.

== Frequently Asked Questions ==

= Is the plugin free? =

The plugin code is free and GPL licensed. The external Roseram API requires an eligible account and sufficient credits. The plugin does not sell or activate a membership.

= Does this change my WordPress site automatically? =

No. It returns text for review. No content changes or code execution are automatic.

= Can I use a shared hosting provider? =

Any compatible WordPress installation with PHP 8.0+, OpenSSL and outbound HTTPS may install the plugin. Host-specific restrictions can apply.

== Changelog ==

= 0.1.0 =

Initial preview: per-user API connection, encrypted key storage and explicit metered text requests.

