CREATE TABLE fe_users (
    tx_account_interests int(11) unsigned DEFAULT '0' NOT NULL,
    tx_account_newsletter_optin tinyint(1) unsigned DEFAULT '0' NOT NULL,
    tx_account_reminder_enabled tinyint(1) unsigned DEFAULT '0' NOT NULL,
    tx_account_member_reference varchar(255) DEFAULT '' NOT NULL,
    tx_account_mfa_secret varchar(255) DEFAULT '' NOT NULL,
    tx_account_mfa_backup_codes text,
    tx_account_mfa_enabled tinyint(1) unsigned DEFAULT '0' NOT NULL,
    tx_account_confirmation_token varchar(255) DEFAULT '' NOT NULL,
    tx_account_confirmed tinyint(1) unsigned DEFAULT '0' NOT NULL
);

CREATE TABLE tx_account_interest (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) unsigned DEFAULT '0' NOT NULL,
    crdate int(11) unsigned DEFAULT '0' NOT NULL,
    deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
    hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
    title varchar(255) DEFAULT '' NOT NULL,
    PRIMARY KEY (uid),
    KEY parent (pid)
);

CREATE TABLE tx_account_fe_users_interest_mm (
    uid_local int(11) unsigned DEFAULT '0' NOT NULL,
    uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
    sorting int(11) unsigned DEFAULT '0' NOT NULL,
    sorting_foreign int(11) unsigned DEFAULT '0' NOT NULL,
    KEY uid_local (uid_local),
    KEY uid_foreign (uid_foreign)
);

CREATE TABLE tx_account_reminder (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) unsigned DEFAULT '0' NOT NULL,
    crdate int(11) unsigned DEFAULT '0' NOT NULL,
    deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
    fe_user int(11) unsigned DEFAULT '0' NOT NULL,
    event_uid varchar(255) DEFAULT '' NOT NULL,
    event_title varchar(255) DEFAULT '' NOT NULL,
    event_date int(11) unsigned DEFAULT '0' NOT NULL,
    sent tinyint(1) unsigned DEFAULT '0' NOT NULL,
    PRIMARY KEY (uid),
    KEY parent (pid)
);
