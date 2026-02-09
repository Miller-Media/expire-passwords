# Expire User Passwords

## Overview
Forces users with specific roles to reset their passwords after a configurable number of days. Supports both email-based and inline password reset flows.

## Architecture

```
expire-user-passwords.php        # Entry point, singleton, core logic
includes/
├── class-settings.php           # Expire_User_Passwords_Settings - admin settings
├── class-login-screen.php       # Expire_User_Passwords_Login_Screen - login enforcement
└── class-list-table.php         # Expire_User_Passwords_List_Table - users table column
languages/                       # i18n translations
```

**Namespace:** `MillerMedia\ExpireUserPasswords`

## Key Classes

### Expire_User_Passwords (expire-user-passwords.php)
Singleton. Core password expiration logic.

- `instance()` - Singleton accessor
- `save_user_meta($user)` - Stores current timestamp in `user_expass_password_reset` user meta
- `get_user_meta($user)` - Retrieves password reset timestamp
- `get_limit()` - Returns password age limit in days (default 90, max 365)
- `get_roles()` - Returns array of roles with expiring passwords
- `get_expiration($user, $format)` - Calculates expiration date
- `has_expirable_role($user)` - Checks if user has an expirable role
- `is_expired($user)` - Returns true if password has exceeded age limit

### Expire_User_Passwords_Login_Screen (class-login-screen.php)
Enforces password reset on login.

- `wp_login($user_login, $user)` - On login, destroys sessions + redirects if expired
- `validate_password_reset($errors, $user)` - Prevents reusing old password
- `lost_password_message($message)` - Custom message on reset screen
- `should_send_email()` - Determines email vs inline reset flow

### Expire_User_Passwords_Settings (class-settings.php)
- Admin submenu under Users
- Fields: limit (days), roles (checkboxes), email preference (radio)

## Settings (wp_options)
- `user_expass_settings` - `limit`, `roles` array, `send_email` flag

## User Meta
- `user_expass_password_reset` - Unix timestamp of last password reset

## Hooks
- `user_register` - Saves initial meta
- `password_reset` - Updates meta on password change
- `wp_login` - Checks expiration and forces reset

## Testing
Tests are in `../tests/unit/expire-passwords/`. Run with:
```bash
make test-plugin PLUGIN=expire-passwords
```

## Common Issues
- Default limit is 90 days if not configured
- Administrator role excluded by default when no roles are configured
- Hard cap of 365 days on the limit setting
