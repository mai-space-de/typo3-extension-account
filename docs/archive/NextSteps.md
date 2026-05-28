# Next Steps — EXT:mai_account

Last audited: 2026-04-19.

---

## Scope boundary

- **Newsletter subscriber records** live in `mai_newsletter`. `mai_account` only calls `SubscriberService::optIn()` / `ConfirmationMailer::send()` via a soft dependency check — no duplication.
- **Mail transport** goes through `mai_mail::MailService`. Feature code renders HTML via `StandaloneView` and enqueues.

---

## Done

- MM join table `tx_maiaccount_feuser_interest_mm` + Layout + Site Set (first pass).
- Logout via `FrontendUserAuthentication::logoff()`; change-password action; interests save; reminders save.
- Scheduler task registration for `ReminderTask`.
- **Registration email confirmation**: `tx_maiaccount_confirm_token` + `_expires` columns on `fe_users`; `RegistrationService::register()` sets `disable=1`, stores signed token, returns token; `registerAction` handles POST + dispatches email via new `AccountMailer`; `confirmAction(token)` activates the account.
- **`AccountMailer` service** renders Fluid email templates (`Email/Confirm.html`, `Email/Reminder.html`) and enqueues via `MailService`.
- **Reminder dispatch**: `ReminderTask` now uses `AccountMailer::sendReminderNotification()` and sends a formatted email body. `ReminderTask` switched to `GeneralUtility::makeInstance()` (scheduler tasks cannot have DI constructor args).
- **Newsletter opt-in wiring**: `newsletterOptInAction` POST toggles `tx_maiaccount_newsletter_optin`; if `mai_newsletter` is loaded it calls `SubscriberService::optIn()` and dispatches the confirmation email via `ConfirmationMailer` through a `mai_newsletter` code path — no subscriber table in `mai_account`.
- TypoScript settings: added `registerStoragePid`, `newsletterSubscriberStoragePid`, `newsletterConfirmPid`.

---

## 1. MFA integration with login

Still open. The MFA verify step must be triggered **during** login. Two paths:

- **Middleware approach (preferred)**: register a PSR-15 middleware after felogin that checks `tx_maiaccount_mfa_enabled`, sets a `pending_mfa` session flag, and redirects to the MFA verify page.
- Auth-service approach: register an `AuthenticationService` in `ext_localconf.php` and gate login completion on MFA.

The session-flag path is lighter and easier to unit-test.

---

## 2. Story — moderation backend module

Stories enter with `status = submitted`. Add:

1. `Configuration/Backend/Modules.php` registering a `StoryBackendController`.
2. `StoryRepository::findByStatus(string $status)`.
3. Backend list with Approve / Reject actions: on approval, set `status = published` + `published_at = now()`; optionally enqueue a "your story was published" email via `AccountMailer`.

Mirror the pattern used by `mai_member::MemberBackendController` (now implemented).

---

## 3. Member reference (`mai_member`)

`FrontendUser::$txMaiaccountMemberUid` is still an `int`. Once the `mai_member` side is stable:

1. Convert to `Maispace\MaiMember\Domain\Model\Member` property (or `ObjectStorage`).
2. The TCA group field already points at `tx_maimember_member`.
3. Render / link the member card from `Profile.html`.

---

## 4. QA

```bash
composer lint:check
composer check:phpstan
composer test:unit
```

Priority targets for unit tests:

- `RegistrationService::register()` / `confirm()` — happy path, wrong token, expired token.
- `AccountController::changePasswordAction()` — wrong current password, mismatched confirm.
- `MfaService::generateSecret()` / `verifyCode()` — entropy, valid / expired / invalid.
