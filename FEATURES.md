## Authentication

* Login / Logout — extends `cms-felogin` with custom actions and MFA support
* TOTP MFA — time-based one-time password via `spomky-labs/otphp`
* User registration — self-registration with email confirmation dispatched via `mai_mail`

## Profile Management

* Profile management — edit personal data, password change
* Interests — tag-based interest management per user
* Reminders — configurable reminder scheduling via TYPO3 Scheduler
* Member reference — link frontend users to `mai_member` records (requires `mai_member`)

## Newsletter Integration

* Newsletter opt-in — double opt-in UI; writes into `mai_newsletter` subscriber table (requires `mai_newsletter`)

## Community Content (Story Wall)

* Story submission — authenticated frontend users submit personal stories via the account area
* Moderation queue — submitted stories enter editorial review before publication
* Rich media support — photos, videos, and text content in story entries
