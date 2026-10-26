<h1 align="center">Expire User Passwords</h1>

<p align="center">
<pre align="center">
 _____  _____ ___ ___ ___
| __\ \/ / _ \_ _| _ \ __|
| _| &gt;  &lt;|  _/| ||   / _|
|___/_/\_\_| |___|_|_\___|
&nbsp;
    [ user passwords ]
</pre>
</p>

<p align="center">
  <strong>Require certain WordPress users to change their passwords on a regular basis.</strong>
</p>

<p align="center">
  <a href="https://wordpress.org/plugins/expire-user-passwords/"><img src="https://img.shields.io/wordpress/plugin/v/expire-user-passwords?style=flat-square" alt="WordPress.org Plugin Version"></a>
  <a href="https://wordpress.org/plugins/expire-user-passwords/"><img src="https://img.shields.io/wordpress/plugin/tested/expire-user-passwords?style=flat-square" alt="Tested up to"></a>
  <a href="https://wordpress.org/plugins/expire-user-passwords/"><img src="https://img.shields.io/wordpress/plugin/installs/expire-user-passwords?style=flat-square" alt="Active Installs"></a>
  <a href="https://www.gnu.org/licenses/gpl-2.0.html"><img src="https://img.shields.io/badge/license-GPLv2%2B-blue?style=flat-square" alt="License"></a>
  <img src="https://img.shields.io/wordpress/plugin/required-php/expire-user-passwords?style=flat-square&logo=php&logoColor=white" alt="PHP Version">
  <a href="https://github.com/Miller-Media/expire-passwords/actions/workflows/ci.yml"><img src="https://img.shields.io/github/actions/workflow/status/Miller-Media/expire-passwords/ci.yml?label=tests&style=flat-square" alt="Tests"></a>
</p>

---

Harden the security of your site by preventing unauthorized access to stale user accounts. It is also a good fit for sites that need to meet security compliance requirements, such as government, banking or healthcare.

This is a fork of the now unsupported [Expire Passwords](https://wordpress.org/plugins/expire-passwords/) plugin.

Available for free on [WordPress.org](https://wordpress.org/plugins/expire-user-passwords/).

## Features

- Set the maximum number of days users may keep the same password (90 days by default, from 1 to 365)
- Choose which user roles must reset their passwords regularly (all roles except Administrator by default)
- Users with an expired password are redirected to the password reset screen when they sign in, and can reset it from the login screen
- A user cannot reuse the same password when resetting
- Existing users are not expired on install: the clock starts when a user registers or next resets their password
- The `eup_submenu_access` filter controls which capability can see the settings page (`manage_options` by default)
- Translated into 50 languages

## Screenshots

**Configure which user roles must regularly reset their passwords, and how often**

![Configure which user roles must regularly reset their passwords, and how often](assets/screenshot-1.png)

**Users with expired passwords are redirected to the password reset screen on sign in**

![Users with expired passwords are redirected to the password reset screen on sign in](assets/screenshot-2.png)

**Users cannot use the same password twice in a row when resetting**

![Users cannot use the same password twice in a row when resetting](assets/screenshot-3.png)

## Documentation and changelog

The full FAQ, requirements and changelog are in [readme.txt](readme.txt).

## Contributing

Pull requests are welcome. Please check the [reported issues](https://github.com/Miller-Media/expire-passwords/issues) first.

## Support Development

[![Buy me a coffee](https://www.buymeacoffee.com/assets/img/custom_images/orange_img.png)](https://www.buymeacoffee.com/yOd1JU9MQe)
