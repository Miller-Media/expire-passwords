=== Expire User Passwords ===
Contributors:      fjarrett, millermedianow, millermediadev
Tags:              passwords, security, login, users, membership
Requires at least: 4.0
Tested up to:      6.9
Stable tag: 1.4.2
Requires PHP:      8.1
License:           GPL-2.0-or-later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Require certain users to change their passwords on a regular basis.

== Description ==

Note: This is a forked version of the now unsupported [Expire Passwords](https://wordpress.org/plugins/expire-passwords/) plugin. The notes below are copied over from the original plugin and will be updated as relevant updates become available. Please help by contributing to the GitHub repository [Expire Passwords](https://github.com/Miller-Media/expire-passwords) on GitHub

**Did you find this plugin helpful? Please consider [leaving a 5-star review](https://wordpress.org/support/view/plugin-reviews/expire-user-passwords).**

Harden the security of your site by preventing unauthorized access to stale user accounts.

This plugin is also ideal for sites needing to meet certain industry security compliances - such as government, banking or healthcare.

In the plugin settings you can set the maximum number of days users are allowed to use the same password (90 days by default), as well as which user roles will be required to reset their passwords regularly (non-Administrators by default).

**Languages supported:**

* Albanian (Shqip)
* Arabic (العربية)
* Armenian (Հայերեն)
* Basque (Euskara)
* Bengali (বাংলা)
* Bulgarian (Български)
* Catalan (Català)
* Chinese Simplified (简体中文)
* Croatian (Hrvatski)
* Czech (Čeština)
* Danish (Dansk)
* Dutch (Nederlands)
* Estonian (Eesti)
* Finnish (Suomi)
* French (Français)
* Galician (Galego)
* Georgian (ქართული)
* German (Deutsch)
* Greek (Ελληνικά)
* Hebrew (עברית)
* Hindi (हिन्दी)
* Hungarian (Magyar)
* Indonesian (Bahasa Indonesia)
* Irish (Gaeilge)
* Italian (Italiano)
* Japanese (日本語)
* Korean (한국어)
* Latvian (Latviešu)
* Lithuanian (Lietuvių)
* Macedonian (Македонски)
* Norwegian (Norsk)
* Persian (فارسی)
* Persian - Afghanistan (دری)
* Polish (Polski)
* Portuguese - Brazil (Português do Brasil)
* Portuguese - Portugal (Português)
* Romanian (Română)
* Russian (Русский)
* Serbian (Српски)
* Slovak (Slovenčina)
* Slovenian (Slovenščina)
* Spanish (Español)
* Swedish (Svenska)
* Tamil (தமிழ்)
* Thai (ไทย)
* Turkish (Türkçe)
* Ukrainian (Українська)
* Urdu (اردو)
* Vietnamese (Tiếng Việt)
* Welsh (Cymraeg)

**Development of this plugin is done [on GitHub](https://github.com/Miller-Media/expire-passwords). Pull requests welcome. Please see [issues reported](https://github.com/Miller-Media/expire-passwords/issues) there before going to the plugin forum.**

== Frequently Asked Questions ==

= What happens to existing users when I first install the plugin? =

Existing users are **not** immediately expired. The plugin tracks each user's last password reset date. Users who have never reset their password have no recorded date, so they are treated as not expired. The expiration clock only starts once a user registers or resets their password after the plugin is active.

= How can I hide the settings page from certain roles? =

The plugin provides a `eup_submenu_access` filter to control which capability is required to see the settings page. By default it requires `manage_options`. To restrict it to only administrators, add this to your theme's `functions.php`:

`add_filter( 'eup_submenu_access', function() { return 'manage_network'; } );`

Or use any capability that only your desired role has. This is useful when custom roles have `manage_options` for other purposes but should not access the Expire User Passwords settings.

= What is the default password expiration period? =

The default is 90 days. You can change this in Settings > Expire Passwords to any value between 1 and 365 days.

= Which user roles are affected by default? =

By default, all non-Administrator roles are required to reset their passwords. You can customize which roles are affected in the plugin settings.

= Can users reuse their old password? =

No. When a user's password expires and they are prompted to reset it, they cannot use the same password they had before.

= What PHP and WordPress versions are supported? =

The plugin requires PHP 8.1 or higher and has been tested up to WordPress 6.9.1. It requires WordPress 4.0 at minimum.

= What languages are supported? =

The plugin is available in 50 languages!

== Screenshots ==

1. Configure which user roles should be required to regularly reset their passwords and how often.
2. Users with expired passwords are redirected to the password reset screen upon sign in.
3. Users are not permitted to use the same password two times in a row during reset.

== Changelog ==

= 1.4.2 =
* Added sanitization callback to register_setting()
* Added translators comment and fixed singular placeholder in _n() call
* Removed .gitmodules and .travis.yml from plugin directory
* Updated "Tested up to" to 6.9

= 1.4.1 =
* Added GPL license declaration to plugin header
* Updated Author URI
* Added direct file access protection to all PHP files
* Improved output escaping and input sanitization
* Removed non-production files from plugin directory

= 1.4.0 =
* Added translations for 50 languages
* Added POT translation template file
* Added opt-in data cleanup on plugin deletion
* Updated FAQ section
* Updated readme with complete language list


= 1.3.9 =
* Added translations for Russian, Polish, Dutch, Turkish, and Swedish
* Updated localization section in readme

= 1.3.8 =
* Added Chinese Simplified (zh_CN) translation

= 1.3.7 =
* Added Japanese (ja) translation

= 1.3.6 =
* Tested up to WordPress 6.9.1

= 1.3.5 =
* Added dismissible review prompt notice after 14 days of usage
* Removed non-dismissible footer review prompt from settings page

= 1.3.4 =
* Added translations for Spanish, French, German, Portuguese (Brazilian), and Italian

= 1.3.3 =
* Added FAQ section documenting first-install behavior and settings page access control

= 1.3.2 =
* Compatibility updates for WordPress 6.9 and PHP 8.1+
* Replaced deprecated FILTER_SANITIZE_STRING usage

= 1.3.1 - September 30, 2022 =

* Support for PHP 7.2 and older

= 1.3 - September 27, 2022 =

* Added ability for users to reset their expired password from the login screen
* Added Dutch translation

Props [@janwoostendorp](https://github.com/janwoostendorp)

= 1.2 - May 4, 2022 =

* Added 'eup_submenu_access' filter for greater control to access of plugin settings

= 1.1.0 - February 15, 2020 =

* Fix: Updated namespace issues that caused critical errors when installing
* Tweak: Tested support for WordPress 5.3.2

= 1.0.0 - November 7, 2019 =

* New: Indicate support for WordPress 5.3

Props [@Miller-Media](https://github.com/Miller-Media)

= 0.6.0 - January 5, 2017 =

* Fix: Expiration not updating when resetting a password via email confirmation link.

Props [@fjarrett](https://github.com/fjarrett)

= 0.5.0 - December 23, 2016 =

* Tweak: Indicate support for WordPress 4.7 and require at least 4.0.
* Fix: Selected user roles in plugin settings not always being honored.
* Fix: Destroy all sessions after login with an expired password.

Props [@fjarrett](https://github.com/fjarrett)

= 0.4.0 - April 13, 2016 =

* New: Indicate support for WordPress 4.5.
* Tweak: Bring back PHP 5.2 compatibility.

Props [@fjarrett](https://github.com/fjarrett)

= 0.3.0 - July 9, 2015 =

* New: Language support for Czech
* Tweak: Optimizations requiring PHP 5.3 or higher
* Fix: User role array error before options exist

Props [@fjarrett](https://github.com/fjarrett), [@dero](https://github.com/dero)

= 0.2.2 - July 2, 2015 =

* New: Language support for Español

Props [@fjarrett](https://github.com/fjarrett)

= 0.2.1 - July 2, 2015 =

* Fix: Fatal undefined function error occuring in some cases ([#3](https://github.com/fjarrett/expire-passwords/issues/3))

Props [@fjarrett](https://github.com/fjarrett)

= 0.2.0 - April 29, 2015 =

* New: Disallow using the same password as before on reset ([#1](https://github.com/fjarrett/expire-passwords/issues/1))
* Tweak: Use default if limit is set to greater than 365 days

Props [@fjarrett](https://github.com/fjarrett)

= 0.1.0 - April 28, 2015 =

* Initial release

Props [@fjarrett](https://github.com/fjarrett)
