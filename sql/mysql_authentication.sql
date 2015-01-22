CREATE TABLE `authentication` (
  `AuthID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `AuthModule` varchar(16) CHARACTER SET utf8 COLLATE utf8_swedish_ci DEFAULT NULL,
  `ExternalReference` varchar(255) NOT NULL DEFAULT '',
  `ProfileID` int(10) unsigned NOT NULL,
  `Erased` datetime DEFAULT NULL,
  `Active` enum('Yes','No') CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL DEFAULT 'Yes',
  `Created` datetime DEFAULT NULL,
  `LastLogin` datetime DEFAULT NULL,
  `RequestID` char(36) CHARACTER SET utf8 COLLATE utf8_swedish_ci DEFAULT '',
  PRIMARY KEY (`AuthID`),
  UNIQUE KEY `ExtAuth` (`AuthModule`,`ExternalReference`,`Erased`),
  KEY `SearchByProfile` (`ProfileID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;