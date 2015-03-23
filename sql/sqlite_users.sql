CREATE TABLE "users" (
  "UserID" smallint(5) NOT NULL,
  "Username" varchar(128) NOT NULL DEFAULT '',
  "Password" char(40) NOT NULL DEFAULT '5baa61e4c9b93f3f0682250b6cf8331b7ee68fd8',
  "Type" varchar(32) NOT NULL DEFAULT 'native',
  "Mail" varchar(80) DEFAULT NULL,
  "Language" char(5) NOT NULL DEFAULT 'xx_XX',
  "Active" char(3) NOT NULL DEFAULT 'Yes',
  "LastLogin" datetime DEFAULT NULL,
  "LastIP" varchar(45) DEFAULT '0.0.0.0',
  "Firstname" varchar(32) DEFAULT NULL,
  "Surname" varchar(32) DEFAULT NULL,
  "Created" datetime DEFAULT NULL,
  "Modified" timestamp NOT NULL,
  "Erased" datetime DEFAULT NULL,
  "ExternalReference" varchar(255) NOT NULL DEFAULT '',
  "ProfileID" char(32) NOT NULL,
  PRIMARY KEY ("UserID")
);

create index "users_Username" on "users" ("Username","ExternalReference","Erased");
create index "users_Login" on "users" ("Active","Username","Password","Erased");
create index "users_External" on "users" ("ExternalReference","Type");
