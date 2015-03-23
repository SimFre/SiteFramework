CREATE TABLE "files_categories" (
  "CatID" int(10) NOT NULL,
  "Name" varchar(128) NOT NULL DEFAULT '',
  "Language" char(5) NOT NULL DEFAULT '',
  "Folder" varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY ("CatID")
);
create index "files_categories_Uniq" on "files_categories" ("Language","Name");

INSERT INTO "files_categories" ("CatID", "Name", "Language", "Folder")
VALUES
 (100,'default','en_US','Default');
