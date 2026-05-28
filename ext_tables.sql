CREATE TABLE tx_maiaccount_interest (
    uid         int(11) NOT NULL auto_increment,
    pid         int(11) NOT NULL DEFAULT 0,
    sorting     int(11) NOT NULL DEFAULT 0,
    hidden      tinyint(4) NOT NULL DEFAULT 0,
    deleted     tinyint(4) NOT NULL DEFAULT 0,
    tstamp      int(11) NOT NULL DEFAULT 0,
    crdate      int(11) NOT NULL DEFAULT 0,

    sys_language_uid   int(11) NOT NULL DEFAULT 0,
    l10n_parent        int(11) NOT NULL DEFAULT 0,
    l10n_diffsource    mediumblob,

    title       varchar(255) NOT NULL DEFAULT '',
    identifier  varchar(255) NOT NULL DEFAULT '',

    PRIMARY KEY (uid),
    KEY pid (pid),
    KEY language (l10n_parent, sys_language_uid)
);

CREATE TABLE tx_maiaccount_reminder (
    uid         int(11) NOT NULL auto_increment,
    pid         int(11) NOT NULL DEFAULT 0,
    hidden      tinyint(4) NOT NULL DEFAULT 0,
    deleted     tinyint(4) NOT NULL DEFAULT 0,
    tstamp      int(11) NOT NULL DEFAULT 0,
    crdate      int(11) NOT NULL DEFAULT 0,

    fe_user     int(11) NOT NULL DEFAULT 0,
    title       varchar(255) NOT NULL DEFAULT '',
    remind_at   int(11) NOT NULL DEFAULT 0,
    sent        tinyint(4) NOT NULL DEFAULT 0,

    PRIMARY KEY (uid),
    KEY pid (pid),
    KEY fe_user (fe_user),
    KEY remind_at (remind_at, sent)
);

CREATE TABLE tx_maiaccount_story (
    uid            int(11) NOT NULL auto_increment,
    pid            int(11) NOT NULL DEFAULT 0,
    hidden         tinyint(4) NOT NULL DEFAULT 0,
    deleted        tinyint(4) NOT NULL DEFAULT 0,
    tstamp         int(11) NOT NULL DEFAULT 0,
    crdate         int(11) NOT NULL DEFAULT 0,

    sys_language_uid   int(11) NOT NULL DEFAULT 0,
    l10n_parent        int(11) NOT NULL DEFAULT 0,
    l10n_diffsource    mediumblob,

    title          varchar(255) NOT NULL DEFAULT '',
    content        mediumtext,
    media          int(11) NOT NULL DEFAULT 0,
    fe_user        int(11) NOT NULL DEFAULT 0,
    status         varchar(32) NOT NULL DEFAULT 'submitted',
    submitted_at   int(11) NOT NULL DEFAULT 0,
    published_at   int(11) NOT NULL DEFAULT 0,

    PRIMARY KEY (uid),
    KEY pid (pid),
    KEY fe_user (fe_user),
    KEY status (status),
    KEY submitted_at (submitted_at),
    KEY language (l10n_parent, sys_language_uid)
);

CREATE TABLE fe_users (
    tx_maiaccount_mfa_secret        varchar(255) NOT NULL DEFAULT '',
    tx_maiaccount_mfa_enabled       tinyint(4) NOT NULL DEFAULT 0,
    tx_maiaccount_interests         int(11) NOT NULL DEFAULT 0,
    tx_maiaccount_newsletter_optin  tinyint(4) NOT NULL DEFAULT 0,
    tx_maiaccount_member_uid        int(11) NOT NULL DEFAULT 0,
    tx_maiaccount_confirm_token     varchar(128) NOT NULL DEFAULT '',
    tx_maiaccount_confirm_expires   int(11) NOT NULL DEFAULT 0,

    KEY tx_maiaccount_confirm_token (tx_maiaccount_confirm_token)
);

CREATE TABLE tx_maiaccount_password_reset_log (
    uid         int(11) NOT NULL auto_increment,
    pid         int(11) NOT NULL DEFAULT 0,
    crdate      int(11) NOT NULL DEFAULT 0,
    tstamp      int(11) NOT NULL DEFAULT 0,

    email       varchar(255) NOT NULL DEFAULT '',
    ip_address  varchar(45) NOT NULL DEFAULT '',
    fe_user     int(11) NOT NULL DEFAULT 0,
    status      varchar(32) NOT NULL DEFAULT 'requested',

    PRIMARY KEY (uid),
    KEY pid (pid),
    KEY email (email),
    KEY fe_user (fe_user),
    KEY status_crdate (status, crdate)
);

CREATE TABLE tx_maiaccount_feuser_interest_mm (
    uid_local       int(11) unsigned NOT NULL DEFAULT 0,
    uid_foreign     int(11) unsigned NOT NULL DEFAULT 0,
    sorting         int(11) unsigned NOT NULL DEFAULT 0,
    sorting_foreign int(11) unsigned NOT NULL DEFAULT 0,

    KEY uid_local (uid_local),
    KEY uid_foreign (uid_foreign)
);
