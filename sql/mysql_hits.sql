CREATE TABLE `hits` (
  `HitID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `RequestID` char(36) NOT NULL DEFAULT 'Undefined',
  `LoadTime` float DEFAULT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LoadStart` double DEFAULT NULL,
  `LoadStop` double DEFAULT NULL,
  `IP` varchar(45) DEFAULT '0.0.0.0',
  `Hostname` tinyint(3) unsigned DEFAULT NULL,
  `SessionID` varchar(40) DEFAULT NULL,
  `URL` text,
  `RequestURI` text,
  `RawHead` text,
  `Language` char(5) DEFAULT 'xx_XX',
  `ServerHost` varchar(32) DEFAULT NULL,
  `PageID` varchar(64) DEFAULT NULL,
  `Method` varchar(8) DEFAULT NULL,
  `Cookie` text,
  `Cookie_Serial` text,
  `Session` text,
  `Session_Serial` text,
  `POST` text,
  `POST_Serial` text,
  `GET` text,
  `GET_Serial` text,
  `SERVER` text,
  `SERVER_Serial` text,
  PRIMARY KEY (`HitID`),
  KEY `Search` (`Language`,`Timestamp`,`PageID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `hits`
	ADD INDEX `RequestID` (`RequestID`),
	ADD INDEX `Timestamp` (`Timestamp`),
	ADD INDEX `PageID` (`PageID`),
	DROP INDEX `Search`;