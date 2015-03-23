CREATE TABLE "hits" (
  "HitID" int(10) NOT NULL,
  "RequestID" char(36) NOT NULL DEFAULT 'Undefined',
  "LoadTime" float DEFAULT NULL,
  "Timestamp" timestamp NOT NULL,
  "LoadStart" double DEFAULT NULL,
  "LoadStop" double DEFAULT NULL,
  "IP" varchar(45) DEFAULT '0.0.0.0',
  "Hostname" tinyint(3) DEFAULT NULL,
  "SessionID" varchar(40) DEFAULT NULL,
  "URL" text,
  "RequestURI" text,
  "RawHead" text,
  "Language" char(5) DEFAULT 'xx_XX',
  "ServerHost" varchar(32) DEFAULT NULL,
  "PageID" varchar(64) DEFAULT NULL,
  "Method" varchar(8) DEFAULT NULL,
  "Cookie" text,
  "Cookie_Serial" text,
  "Session" text,
  "Session_Serial" text,
  "POST" text,
  "POST_Serial" text,
  "GET" text,
  "GET_Serial" text,
  "SERVER" text,
  "SERVER_Serial" text,
  PRIMARY KEY ("HitID")
);

create index "hits_RequestID" on "hits" ("RequestID");
create index "hits_Timestamp" on "hits" ("Timestamp");
create index "hits_PageID" on "hits" ("PageID");
