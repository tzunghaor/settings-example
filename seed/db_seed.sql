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

INSERT INTO "persisted_setting" ("scope", "path", "value") VALUES
    ('afternoon', 'ContentSettings.messages', '["info|The afternoon is uneventful."]'),
    ('afternoon', 'DisplaySettings.borders', '["bottom","top"]'),
    ('day', 'ContentSettings.title', 'Happy Day'),
    ('day', 'DisplaySettings.borders', '["left","right"]'),
    ('day', 'DisplaySettings.padding', '30'),
    ('morning', 'ContentSettings.messages', '["success|Successfully woke up!","error|It is too early!"]'),
    ('morning', 'DisplaySettings.backgroundColor', '#f5f2bc'),
    ('morning', 'DisplaySettings.textColor', '#921601'),
    ('night', 'ContentSettings.messages', '["success|Was in a great party.","error|Very tired.","info|ZZZZZZ"]'),
    ('night', 'ContentSettings.title', 'Good night'),
    ('night', 'DisplaySettings.backgroundColor', '#011e3d'),
    ('night', 'DisplaySettings.padding', '10'),
    ('night', 'DisplaySettings.textColor', '#f2fcff');