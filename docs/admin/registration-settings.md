# Registration Settings

Control how new users register and activate their accounts.

## Settings Overview

| Setting | Description |
|---------|-------------|
| **Registration Open** | Allow new users to register |
| **Require Approval** | New registrations require admin approval before activation |
| **Require Email Verification** | Users must verify their email address before activation |

## How User Activation Works

When a user registers, their initial account status depends on your settings:

| Require Approval | Require Email Verification | Initial Status | How They Activate |
|------------------|---------------------------|----------------|-------------------|
| No | No | Active | Immediate access |
| No | Yes | Pending | Verify email |
| Yes | No | Pending | Admin approval |
| Yes | Yes | Pending | Verify email + Admin approval |

## Changing Settings with Existing Users

When you change registration settings, existing pending users are affected:

### Making Settings Less Restrictive

If you disable a requirement, pending users who now meet the criteria will be **automatically activated on their next login attempt**.

**Examples:**
- User registered when email verification was required, but hasn't verified yet
- You disable email verification
- User can now log in and will be automatically activated

### Making Settings More Restrictive

Enabling stricter requirements **does not affect existing active users**. Only new registrations are affected.

**Examples:**
- User is already active
- You enable "Require Approval"
- User remains active (they were already approved by registering when approval wasn't required)

## Email Address Changes

When a user's email address is changed (by an admin or the user themselves):

1. **Verification status is always reset** - The user becomes unverified regardless of settings
2. **Verification email sent if required** - If "Require Email Verification" is enabled, a new verification email is sent automatically
3. **User status is NOT changed** - Active users remain active; they've already been approved

This ensures:
- Users always have a verified, working email on file (when verification is required)
- Changed emails are confirmed to belong to the user
- Established users aren't locked out just for updating their email

## Best Practices

1. **Start restrictive, relax later** - It's easier to remove requirements than to add them retroactively

2. **Use email verification for public VAs** - Prevents spam accounts and ensures valid contact info

3. **Use approval for invite-only VAs** - Review each applicant before granting access

4. **Check pending users before changing settings** - Review the user list in the admin panel to understand who will be affected
