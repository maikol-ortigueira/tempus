CREATE TABLE IF NOT EXISTS `#__tempus_songs` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`ordering` INT(11)  NOT NULL ,
`state` TINYINT(1)  NOT NULL DEFAULT 1,
`checked_out` INT(11)  NOT NULL ,
`checked_out_time` DATETIME NOT NULL DEFAULT "0000-00-00 00:00:00",
`created_by` INT(11)  NOT NULL ,
`modified_by` INT(11)  NOT NULL ,
`title` VARCHAR(255) NOT NULL ,
`alias` VARCHAR(255) NOT NULL ,
`author` VARCHAR(255) NOT NULL ,
`song_note` VARCHAR(255) NOT NULL ,
`catid` TEXT NOT NULL ,
`tags` TEXT NOT NULL ,
`documents` TEXT NOT NULL ,
`note` VARCHAR(120) NOT NULL ,
/*###tempus_songs###*/
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE UNIQUE INDEX `aliasindex` ON `#__tempus_songs` (`alias`);


CREATE TABLE IF NOT EXISTS `#__tempus_singers` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`ordering` INT(11)  NOT NULL ,
`state` TINYINT(1)  NOT NULL DEFAULT 1,
`checked_out` INT(11)  NOT NULL ,
`checked_out_time` DATETIME NOT NULL DEFAULT "0000-00-00 00:00:00",
`created_by` INT(11)  NOT NULL ,
`modified_by` INT(11)  NOT NULL ,
`name` VARCHAR(255) NOT NULL ,
`surname` VARCHAR(255) NOT NULL ,
`alias` VARCHAR(255) NOT NULL ,
`user_id` INT(11)  NOT NULL ,
`nickname` VARCHAR(255) NOT NULL ,
`username` TEXT NOT NULL ,
`range` INT(3) NOT NULL ,
`email` VARCHAR(120) NOT NULL ,
`note` VARCHAR(120) NOT NULL ,
/*###tempus_singers###*/
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE UNIQUE INDEX `aliasindex` ON `#__tempus_singers` (`alias`);

INSERT INTO `#__content_types` (`type_title`, `type_alias`, `table`, `content_history_options`)
SELECT * FROM ( SELECT 'Song','com_tempus.song','{"special":{"dbtable":"#__tempus_songs","key":"id","type":"Song","prefix":"TempusTable"}}', '{"formFile":"administrator\/components\/com_tempus\/models\/forms\/song.xml", "hideFields":["checked_out","checked_out_time","params","language"], "ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time"], "convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"catid","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"group_id","targetTable":"#__usergroups","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"created_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"}]}') AS tmp
WHERE NOT EXISTS (
	SELECT type_alias FROM `#__content_types` WHERE (`type_alias` = 'com_tempus.song')
) LIMIT 1;
-- New table `#__tempus_concerts`

CREATE TABLE IF NOT EXISTS `#__tempus_concerts` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`ordering` INT(11)  NOT NULL ,
`state` TINYINT(1)  NOT NULL DEFAULT 1,
`checked_out` INT(11)  NOT NULL ,
`checked_out_time` DATETIME NOT NULL DEFAULT "0000-00-00 00:00:00",
`created_by` INT(11)  NOT NULL ,
`modified_by` INT(11)  NOT NULL ,
`title` VARCHAR(100) NOT NULL ,
`alias` VARCHAR(100) NOT NULL ,
`rehearsal_id` INT(11) NOT NULL ,
`note` VARCHAR(120) NOT NULL ,
`songs_id` VARCHAR (255) NOT NULL ,
`extended_note` text NOT NULL ,
`concert_location` text NOT NULL ,
`concert_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' ,
/*###tempus_concerts###*/
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8mb4_unicode_ci;

INSERT INTO `#__content_types` (`type_title`, `type_alias`, `table`, `content_history_options`)
SELECT * FROM ( SELECT 'Concert','com_tempus.concert','{"special":{"dbtable":"#__tempus_concerts","key":"id","type":"Concert","prefix":"TempusTable"}}', '{"formFile":"administrator\/components\/com_tempus\/models\/forms\/concert.xml", "hideFields":["checked_out","checked_out_time","params","language"], "ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time"], "convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"catid","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"group_id","targetTable":"#__usergroups","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"created_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"}]}') AS tmp
WHERE NOT EXISTS (
	SELECT type_alias FROM `#__content_types` WHERE (`type_alias` = 'com_tempus.concert')
) LIMIT 1;

-- New table `#__tempus_rehearsals`

CREATE TABLE IF NOT EXISTS `#__tempus_rehearsals` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`ordering` INT(11)  NOT NULL ,
`state` TINYINT(1)  NOT NULL DEFAULT 1,
`checked_out` INT(11)  NOT NULL ,
`checked_out_time` DATETIME NOT NULL DEFAULT "0000-00-00 00:00:00",
`created_by` INT(11)  NOT NULL ,
`modified_by` INT(11)  NOT NULL ,
`title` VARCHAR(100) NOT NULL ,
`alias` VARCHAR(100) NOT NULL ,
`note` VARCHAR(120) NOT NULL ,
`start_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' ,
`end_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' ,
`extended_note` text NOT NULL ,
`songs_id` VARCHAR(255) NOT NULL ,
`concert_id` INT(11)  NOT NULL ,
`convocation` text NOT NULL ,
/*###tempus_rehearsals###*/
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8mb4_unicode_ci;

INSERT INTO `#__content_types` (`type_title`, `type_alias`, `table`, `content_history_options`)
SELECT * FROM ( SELECT 'Rehearsal','com_tempus.rehearsal','{"special":{"dbtable":"#__tempus_rehearsals","key":"id","type":"Rehearsal","prefix":"TempusTable"}}', '{"formFile":"administrator\/components\/com_tempus\/models\/forms\/rehearsal.xml", "hideFields":["checked_out","checked_out_time","params","language"], "ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time"], "convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"catid","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"group_id","targetTable":"#__usergroups","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"created_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"}]}') AS tmp
WHERE NOT EXISTS (
	SELECT type_alias FROM `#__content_types` WHERE (`type_alias` = 'com_tempus.rehearsal')
) LIMIT 1;

