--
-- Extend fe_users table with maispace account fields
--

CREATE TABLE fe_users (
    -- Member reference (e.g. membership number from maispace/member)
    tx_account_member_ref varchar(64) DEFAULT '' NOT NULL,

    -- Comma-separated list of interest identifiers
    tx_account_interests text,

    -- Newsletter opt-in flag
    tx_account_newsletter_optin tinyint(1) unsigned DEFAULT 0 NOT NULL,

    -- Timestamp when newsletter opt-in was confirmed
    tx_account_newsletter_optin_date int(11) unsigned DEFAULT 0 NOT NULL,

    -- Event reminder opt-in flag
    tx_account_reminders_optin tinyint(1) unsigned DEFAULT 0 NOT NULL,

    -- Email confirmation token for registration
    tx_account_confirmation_token varchar(128) DEFAULT '' NOT NULL,

    -- Timestamp when confirmation token expires
    tx_account_confirmation_token_expires int(11) unsigned DEFAULT 0 NOT NULL,

    -- Whether email has been confirmed
    tx_account_email_confirmed tinyint(1) unsigned DEFAULT 0 NOT NULL,

    -- Password reset token
    tx_account_password_reset_token varchar(128) DEFAULT '' NOT NULL,

    -- Timestamp when password reset token expires
    tx_account_password_reset_token_expires int(11) unsigned DEFAULT 0 NOT NULL,

    -- MFA/TOTP secret (encrypted)
    tx_account_mfa_secret varchar(256) DEFAULT '' NOT NULL,

    -- Whether MFA is enabled
    tx_account_mfa_enabled tinyint(1) unsigned DEFAULT 0 NOT NULL,

    -- JSON-encoded array of hashed backup codes
    tx_account_mfa_backup_codes text
);

--
-- Table for pending event reminders (populated by ReminderService)
--

CREATE TABLE tx_account_reminder_queue (
    uid int(11) unsigned NOT NULL auto_increment,
    pid int(11) unsigned DEFAULT 0 NOT NULL,
    tstamp int(11) unsigned DEFAULT 0 NOT NULL,
    crdate int(11) unsigned DEFAULT 0 NOT NULL,
    deleted tinyint(1) unsigned DEFAULT 0 NOT NULL,

    fe_user_uid int(11) unsigned DEFAULT 0 NOT NULL,
    event_uid int(11) unsigned DEFAULT 0 NOT NULL,
    event_title varchar(255) DEFAULT '' NOT NULL,
    event_date int(11) unsigned DEFAULT 0 NOT NULL,
    event_location varchar(255) DEFAULT '' NOT NULL,
    remind_at int(11) unsigned DEFAULT 0 NOT NULL,
    sent tinyint(1) unsigned DEFAULT 0 NOT NULL,

    PRIMARY KEY (uid),
    KEY fe_user_uid (fe_user_uid),
    KEY remind_at (remind_at),
    KEY sent (sent)
);
