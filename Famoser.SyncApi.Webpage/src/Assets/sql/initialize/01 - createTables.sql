CREATE TABLE users (
  id        INTEGER PRIMARY KEY,
  user_id   TEXT,
  user_name TEXT
);

CREATE TABLE devices (
  id                          INTEGER PRIMARY KEY,
  user_id                     INTEGER,
  device_id                   TEXT,
  device_name                 TEXT,
  has_access                  TINYINT,
  last_modification_date_time INTEGER,
  last_request_date_time      INTEGER,
  authorization_date_time     INTEGER,
  access_revoked_reason       TEXT,
  access_revoked_by_device_id TEXT,
  access_revoked_date_time    TEXT
);

CREATE TABLE authorization_codes (
  id         INTEGER PRIMARY KEY,
  user_id    INTEGER,
  code       TEXT,
  content    TEXT,
  valid_till INTEGER
);

CREATE TABLE content (
  id                 INTEGER PRIMARY KEY,
  user_id            INTEGER,
  content_id         TEXT,
  collection_id      TEXT,
  version_id         TEXT,
  device_id          INTEGER,
  creation_date_time INTEGER
);