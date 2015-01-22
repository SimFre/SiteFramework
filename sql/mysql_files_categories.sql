CREATE TABLE `files_categories` (
  `CatID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(128) NOT NULL DEFAULT '',
  `Language` char(5) NOT NULL DEFAULT '',
  `Folder` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`CatID`),
  UNIQUE KEY `Uniq` (`Language`,`Name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `files_categories` (`CatID`, `Name`, `Language`, `Folder`)
VALUES
 (100,'default','en_US','Default');
