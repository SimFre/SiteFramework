CREATE TABLE `users` (
  `UserID` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `Username` varchar(128) NOT NULL DEFAULT '',
  `Password` char(40) NOT NULL DEFAULT '5baa61e4c9b93f3f0682250b6cf8331b7ee68fd8' COMMENT 'Default: "password"',
  `Type` varchar(32) NOT NULL DEFAULT 'native',
  `Mail` varchar(80) DEFAULT NULL,
  `Language` char(5) NOT NULL DEFAULT 'xx_XX',
  `Active` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `LastLogin` datetime DEFAULT NULL,
  `LastIP` varchar(45) DEFAULT '0.0.0.0',
  `Firstname` varchar(32) DEFAULT NULL,
  `Surname` varchar(32) DEFAULT NULL,
  `Created` datetime DEFAULT NULL,
  `Modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Erased` datetime DEFAULT NULL,
  `ExternalReference` varchar(255) NOT NULL DEFAULT '',
  `ProfileID` char(32) NOT NULL,
  PRIMARY KEY (`UserID`),
  UNIQUE KEY `Username` (`Username`,`ExternalReference`,`Erased`),
  KEY `Login` (`Active`,`Username`,`Password`,`Erased`),
  KEY `External` (`ExternalReference`,`Type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;