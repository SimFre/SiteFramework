CREATE TABLE "authentication" (
  "AuthID" int(10) NOT NULL,
  "AuthModule" varchar(16) DEFAULT NULL,
  "ExternalReference" varchar(255) NOT NULL DEFAULT '',
  "ProfileID" int(10) NOT NULL,
  "Erased" datetime DEFAULT NULL,
  "Active" varchar(3) NOT NULL DEFAULT 'Yes',
  "Created" datetime DEFAULT NULL,
  "LastLogin" datetime DEFAULT NULL,
  "RequestID" char(36) DEFAULT '',
  PRIMARY KEY ("AuthID")
);

CREATE INDEX "authentication_ExtAuth" on "authentication" ("AuthModule","ExternalReference","Erased");
CREATE INDEX "authentication_SearchByProfile" on "authentication" ("ProfileID");
