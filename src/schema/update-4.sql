ALTER TABLE `pages` ADD `css` LONGTEXT NULL;
ALTER TABLE `sites` ADD `adminIP` varchar(200) NOT NULL AFTER `adminURI`;
UPDATE `options` SET `value`='4' WHERE `key`='schemaver';

/** CONVERT CHARSETS */
ALTER TABLE options CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE sessions CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE pages CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE options CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;

/** CONVERT ENGINES **/
ALTER TABLE options ENGINE=InnoDB;
ALTER TABLE sessions ENGINE=InnoDB;
ALTER TABLE pages ENGINE=InnoDB;
ALTER TABLE options ENGINE=InnoDB;