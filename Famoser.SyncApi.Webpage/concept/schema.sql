CREATE TABLE 'applications' (
  'id'                INTEGER DEFAULT NULL PRIMARY KEY AUTOINCREMENT,
  'admin_id'          INTEGER DEFAULT NULL REFERENCES 'frontend_users' ('id'),
  'name'              TEXT    DEFAULT NULL,
  'description'       TEXT    DEFAULT NULL,
  'application_id'    TEST    DEFAULT NULL,
  'application_seed'  INT     DEFAULT NULL,
  'release_date_time' TEXT    DEFAULT NULL
);

CREATE TABLE 'users' (
  'id'             INTEGER DEFAULT NULL PRIMARY KEY AUTOINCREMENT,
  'application_id' INTEGER DEFAULT NULL REFERENCES 'applications' ('id'),
  'identifier'     TEXT    DEFAULT NULL,
  'guid'           TEXT    DEFAULT NULL,
  'personal_seed'  TEXT    DEFAULT NULL
);

CREATE TABLE 'devices' (
  'id'         INTEGER DEFAULT NULL PRIMARY KEY AUTOINCREMENT,
  'user_guid'  TEXT    DEFAULT NULL,
  'identifier' TEXT    DEFAULT NULL,
  'guid'       TEXT    DEFAULT NULL
);

CREATE TABLE 'collections' (
  'id'          INTEGER DEFAULT NULL PRIMARY KEY AUTOINCREMENT,
  'user_guid'   TEXT    DEFAULT NULL,
  'device_guid' TEXT    DEFAULT NULL,
  'identifier'  TEXT    DEFAULT NULL,
  'guid'        TEXT    DEFAULT NULL
);

CREATE TABLE 'user_collections' (
  'id'               INTEGER DEFAULT NULL PRIMARY KEY AUTOINCREMENT,
  'user_guid'        TEXT    DEFAULT NULL,
  'collection_guid'  TEXT    DEFAULT NULL,
  'create_date_time' TEXT    DEFAULT NULL
);

CREATE TABLE 'entities' (
  'id'              INTEGER DEFAULT NULL PRIMARY KEY AUTOINCREMENT,
  'user_guid'       TEXT    DEFAULT NULL,
  'device_guid'     TEXT    DEFAULT NULL,
  'collection_guid' TEXT    DEFAULT NULL,
  'identifier'      TEXT    DEFAULT NULL,
  'guid'            TEXT    DEFAULT NULL
);

CREATE TABLE 'content_versions' (
  'id'               INTEGER DEFAULT NULL PRIMARY KEY AUTOINCREMENT,
  'content_type'     INTEGER DEFAULT NULL,
  'entity_guid'      TEXT    DEFAULT NULL,
  'version_guid'     TEXT    DEFAULT NULL,
  'content'          TEXT    DEFAULT NULL,
  'create_date_time' TEXT    DEFAULT NULL
);

CREATE TABLE 'frontend_users' (
  'id'       INTEGER DEFAULT NULL PRIMARY KEY AUTOINCREMENT,
  'email'    TEXT    DEFAULT NULL,
  'username' TEXT    DEFAULT NULL,
  'password' TEXT    DEFAULT NULL
);

CREATE TABLE 'authorization_code' (
  'id'                   INTEGER DEFAULT NULL PRIMARY KEY AUTOINCREMENT,
  'user_guid'            TEXT    DEFAULT NULL,
  'code'                 TEXT    DEFAULT NULL,
  'valid_till_date_time' TEXT    DEFAULT NULL
);

CREATE TABLE 'application_settings' (
  'id'             INTEGER DEFAULT NULL PRIMARY KEY AUTOINCREMENT,
  'application_id' INTEGER DEFAULT NULL REFERENCES 'applications' ('id'),
  'key'            TEXT    DEFAULT NULL,
  'val'            TEXT    DEFAULT NULL
);