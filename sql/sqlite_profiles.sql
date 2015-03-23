CREATE TABLE "profiles" (
  "profileId" int(10) NOT NULL,
  "active" char(1) NOT NULL DEFAULT 'Y',
  "online" char(1) NOT NULL DEFAULT 'N',
  "regDate" datetime DEFAULT NULL,
  "lastLogin" datetime NOT NULL,
  "number_logins" int(11) NOT NULL DEFAULT '0',
  "tasks" varchar(255) NOT NULL DEFAULT '',
  "firstname" varchar(255) NOT NULL DEFAULT '',
  "surname" varchar(255) NOT NULL DEFAULT '',
  "email" varchar(255) NOT NULL DEFAULT '',
  "msn" varchar(255) DEFAULT NULL,
  "icq" varchar(14) DEFAULT NULL,
  "birthdate" date NOT NULL DEFAULT '0000-00-00',
  "sex" char(1) NOT NULL DEFAULT 'F',
  "country" varchar(10) DEFAULT NULL,
  "city" varchar(64) NOT NULL DEFAULT '',
  "occupy" varchar(255) DEFAULT NULL,
  "homepage_url" varchar(255) DEFAULT NULL,
  "signature" text,
  "avatar" varchar(32) DEFAULT NULL,
  "presentation" text,
  "status" float NOT NULL DEFAULT '0',
  "credits" tinyint(1) NOT NULL DEFAULT '0',
  "emoticons" tinyint(1) NOT NULL DEFAULT '1',
  "mus" varchar(10) NOT NULL DEFAULT '0',
  "notisen" varchar(150) DEFAULT NULL,
  "fbid" varchar(200) DEFAULT NULL,
  "erased" datetime DEFAULT NULL,
  PRIMARY KEY ("profileId")
);

create index "profiles_Search" on "profiles" ("active","birthdate");
create index "profiles_Online" on "profiles" ("online");
