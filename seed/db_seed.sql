CREATE TABLE user (id VARCHAR(255) NOT NULL, PRIMARY KEY (id));

CREATE TABLE project (name VARCHAR(255) NOT NULL, owner_id VARCHAR DEFAULT NULL, PRIMARY KEY (name), CONSTRAINT FK_2FB3D0EE7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE);

CREATE TABLE persisted_setting (scope VARCHAR(50) NOT NULL, path VARCHAR(140) NOT NULL, value VARCHAR(10000) NOT NULL, PRIMARY KEY (scope, path));

INSERT INTO "user" ("id") VALUES
    ('Alice'),
    ('Bob');

INSERT INTO "project" ("name", "owner_id") VALUES
    ('a-bar', 'Alice'),
    ('a-foo', 'Alice'),
    ('b-bar', 'Bob'),
    ('b-foo', 'Bob');