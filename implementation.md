# Implementation Plan: Password Expiration Start Date (Issue #7)

## Overview
The plugin currently calculates expiration based on `last_reset_timestamp + X days`. Users want the ability to either:
1. Set a "start date" from which password age is calculated (forcing expiration sooner)
2. Manually force password reset for users via an admin button

## Recommended Approach

### 1. Start Date Behavior
Apply to users who have *never* reset their password (or have old reset timestamps), with a checkbox to apply to all users.

### 2. Force Reset Behavior
Immediately expire (force reset on next login).

### 3. Email Notification
Yes, send notification when admin force-resets passwords.

## File Changes

### 1. expire-user-passwords.php
Add new methods:
- `get_effective_reset_date($user)` - Returns either the start date or user's last reset timestamp
- `force_password_reset($user_id)` - Forces immediate password reset for a user
- Update `get_expiration()` to use `get_effective_reset_date()`

### 2. includes/class-settings.php
Add new settings:
- Date picker field for start date
- Force reset checkbox/buttons (global and per-user)
- Update sanitize_settings() to handle new fields

### 3. includes/class-list-table.php
Add per-user force reset action link in the Users table.

### 4. includes/class-login-screen.php
Update messages to reflect force reset vs normal expiration.

## Database Storage
Store new settings in `user_expass_settings` option:
- `start_date` (string, Y-m-d format)
- `apply_to_all` (boolean, whether to apply start date to all users)

## Workflow

### When start_date is set:
1. For users without reset timestamp: Use start_date as their reset date
2. For users with reset timestamp: 
   - If "apply to all" is checked: Use start_date
   - Else: Use their actual reset timestamp
3. Calculate expiration as `reset_date + X days`

### When force reset is triggered:
1. Update user's `user_expass_password_reset` to current timestamp
2. Destroy user sessions (force re-login)
3. Send notification email if enabled
4. Clear the global force reset flag after processing

## UI Elements
- Settings page: Date picker for "Start enforcing from date"
- Settings page: Checkbox "Apply this date to all users"
- Settings page: Button "Force Password Reset Now"
- Users table: "Force Reset" link per user
- Admin notices: Warning if start date is in the past

## Edge Cases Handled
- Invalid dates in settings
- Users who reset password after start date is set
- Role changes for users (maintaining expiration logic)
- Empty/null start date (fallback to current behavior)

## Testing Checklist
1. Verify normal expiration still works when no start date is set
2. Verify start date affects only users without recent reset (by default)
3. Verify "apply to all" option works correctly
4. Verify force reset button works for individual users
5. Verify bulk force reset works
6. Verify session destruction works
7. Verify email notifications are sent appropriately
8. Verify backward compatibility (updating from older versions)
