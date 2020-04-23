DROP TABLE IF EXISTS `#__tempus_songs`;
DELETE FROM `#__ucm_history` WHERE ucm_type_id IN
	(SELECT type_id FROM `#__content_types` WHERE (type_alias LIKE 'com_tempus.%'));
DELETE FROM `#__content_types` WHERE (type_alias LIKE 'com_tempus.%');