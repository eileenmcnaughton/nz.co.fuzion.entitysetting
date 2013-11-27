DROP TABLE IF EXISTS `civicrm_entity_setting`;

-- /*******************************************************
-- *
-- * civicrm_hrjob
-- *
-- * Job positions.
-- *
-- *******************************************************/
CREATE TABLE `civicrm_entity_setting` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique Entity Setting ID',
     `entity_id` int unsigned,
     `entity_type` varchar(127)    COMMENT 'Entity Type',
     `setting_data` Text    COMMENT 'json stored data'
   ,
    PRIMARY KEY ( `id` )

    ,INDEX `index_entity`(
       entity_id, entity_type
    )
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;
