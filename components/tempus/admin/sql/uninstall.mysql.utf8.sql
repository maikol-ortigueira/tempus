DELETE FROM `#__ucm_history` WHERE ucm_type_id IN
	(SELECT type_id FROM `#__content_types` WHERE (type_alias LIKE 'com_tempus.%'));

DELETE FROM `#__content_types` WHERE (type_alias LIKE 'com_tempus.%');

DROP TABLE IF EXISTS `#__tempus_songs`;
DROP TABLE IF EXISTS `#__tempus_singers`;
DROP TABLE IF EXISTS `#__tempus_concerts`;
DROP TABLE IF EXISTS `#__tempus_rehearsals`;
