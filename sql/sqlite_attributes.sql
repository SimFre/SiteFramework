CREATE TABLE "attributes" (
  "AttributeName" varchar(64) NOT NULL DEFAULT '',
  "Language" char(5) NOT NULL DEFAULT 'xx_XX',
  "AttributeValue" text,
  "PageID" varchar(64) NOT NULL DEFAULT '',
  "ContentType" varchar(32) DEFAULT 'text/plain',
  "Hits" int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY ("AttributeName","Language","PageID")
);

INSERT INTO "attributes" ("AttributeName", "Language", "AttributeValue", "PageID", "ContentType", "Hits")
VALUES
 ('LOREM','en_US','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.','','text/plain',0),
 ('WELCOME','en_US','Welcome to SiteFramework!','','text/plain',0);
