CREATE TABLE `groups` (
  `GroupName` varchar(32) NOT NULL DEFAULT '',
  `Member` smallint(5) unsigned NOT NULL DEFAULT '65534',
  `Language` char(5) NOT NULL DEFAULT 'xx_XX',
  PRIMARY KEY (`GroupName`,`Member`,`Language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;