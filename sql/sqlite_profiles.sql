CREATE TABLE "profiles" (
  "profileId" int(10) NOT NULL,
  "active" char(1) NOT NULL DEFAULT 'Y',
  "online" char(1) NOT NULL DEFAULT 'N',
  "regDate" datetime DEFAULT NULL,
  "lastLogin" datetime NOT NULL,
  "numberLogins" int(11) NOT NULL DEFAULT '0',
  "tasks" varchar(255) NOT NULL DEFAULT '',
  "firstname" varchar(255) NOT NULL DEFAULT '',
  "surname" varchar(255) NOT NULL DEFAULT '',
  "email" varchar(255) NOT NULL DEFAULT '',
  "status" float NOT NULL DEFAULT '0',
  "credits" tinyint(1) NOT NULL DEFAULT '0',
  "notisen" varchar(150) DEFAULT NULL,
  "erased" datetime DEFAULT NULL,
  PRIMARY KEY ("profileId")
);

create index "profiles_Search" on "profiles" ("active");
create index "profiles_Online" on "profiles" ("online");
