-- -----------------------------------------------------
-- Table `#__fieldsandfilters_elements`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__fieldsandfilters_elements` (
  `element_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `extension_type_id` INT(11) UNSIGNED NOT NULL ,
  `item_id` INT(11) UNSIGNED NOT NULL ,
  `ordering` INT(11) NOT NULL DEFAULT 0 ,
  `state` SMALLINT(6) NOT NULL DEFAULT 0 ,
  PRIMARY KEY (`element_id`) ,
  INDEX `extension_type_id` (`extension_type_id` ASC) ,
  INDEX `item_id` (`item_id` ASC) ,
  INDEX `ordering` (`ordering` ASC) ,
  INDEX `state` (`state` ASC) ,
  INDEX `element` (`ordering` ASC, `state` ASC)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `#__fieldsandfilters_fields`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__fieldsandfilters_fields` (
  `field_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `field_name` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NOT NULL ,
  `field_alias` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_bin' NOT NULL ,
  `field_type` VARCHAR(100) CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NOT NULL ,
  `extension_type_id` INT(11) UNSIGNED NOT NULL DEFAULT 0 ,
  `mode` TINYINT(20) NOT NULL DEFAULT 1 ,
  `description` MEDIUMTEXT CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NOT NULL DEFAULT '' ,
  `ordering` INT(11) NOT NULL DEFAULT 0 ,
  `state` TINYINT(2) NOT NULL DEFAULT 0 ,
  `required` TINYINT(1) NOT NULL DEFAULT 0 ,
  `access` INT(10) UNSIGNED NOT NULL DEFAULT 0 ,
  `language` CHAR(7) CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NOT NULL ,
  `params` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NOT NULL DEFAULT '' ,
  PRIMARY KEY (`field_id`) ,
  INDEX `extension_type_id` (`extension_type_id` ASC) ,
  INDEX `ordering` (`ordering` ASC) ,
  INDEX `state` (`state` ASC) ,
  INDEX `language` (`language` ASC) ,
  INDEX `field` (`state` ASC, `access` ASC) ,
  INDEX `mode` (`mode` ASC) ,
  INDEX `access` (`access` ASC)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

-- -----------------------------------------------------
-- Table `#__fieldsandfilters_field_values`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__fieldsandfilters_field_values` (
  `field_value_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `field_id` INT(11) UNSIGNED NOT NULL DEFAULT 0 ,
  `field_value` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NOT NULL ,
  `field_value_alias` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_bin' NOT NULL ,
  `ordering` INT(11) NOT NULL DEFAULT 0 ,
  `state` TINYINT(1) NOT NULL DEFAULT 0 ,
  PRIMARY KEY (`field_value_id`) ,
  INDEX `field_id` (`field_id` ASC) ,
  INDEX `ordering` (`ordering` ASC) ,
  INDEX `state` (`state` ASC)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `#__fieldsandfilters_data`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__fieldsandfilters_data` (
  `element_id` INT(11) UNSIGNED NOT NULL ,
  `extension_type_id` INT(11) UNSIGNED NOT NULL DEFAULT 0 ,
  `field_id` INT(11) UNSIGNED NOT NULL DEFAULT 0 ,
  `field_data` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NOT NULL DEFAULT '' ,
  INDEX `field_id` (`field_id` ASC) ,
  INDEX `extension_type_id` (`extension_type_id` ASC) ,
  INDEX `element_id` (`element_id` ASC) ,
  UNIQUE INDEX `element_id_field_id` (`element_id` ASC, `field_id` ASC)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `#__fieldsandfilters_connections`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__fieldsandfilters_connections` (
  `element_id` INT(11) UNSIGNED NOT NULL ,
  `extension_type_id` INT(11) UNSIGNED NOT NULL DEFAULT 0 ,
  `field_id` INT(11) UNSIGNED NOT NULL DEFAULT 0 ,
  `field_value_id` INT(11) UNSIGNED NOT NULL DEFAULT 0 ,
  INDEX `field_id` (`field_id` ASC) ,
  INDEX `field_value_id` (`field_value_id` ASC) ,
  UNIQUE INDEX `element_id_field_value_id` (`field_value_id` ASC, `element_id` ASC) ,
  INDEX `extension_type_id` (`extension_type_id` ASC) ,
  INDEX `element_id` (`element_id` ASC)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `#__content_types`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__content_types` (
  `type_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type_title` varchar(255) NOT NULL DEFAULT '',
  `type_alias` varchar(255) NOT NULL DEFAULT '',
  `table` varchar(255) NOT NULL DEFAULT '',
  `rules` text NOT NULL,
  `field_mappings` text NOT NULL,
  `router` varchar(255) NOT NULL  DEFAULT '',
  `content_history_options` varchar(5120) COMMENT 'JSON string for com_contenthistory options',
  PRIMARY KEY (`type_id`),
  KEY `idx_alias` (`type_alias`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COLLATE = utf8_general_ci;
AUTO_INCREMENT=10000;

--
-- Dumping data for table `#__content_types` to idzie do script.php
--
INSERT INTO `#__content_types` (`type_id`, `type_title`, `type_alias`, `table`, `rules`, `field_mappings`,`router`, `content_history_options`) VALUES
(NULL, 'Fieldsandfilters Field', 'com_fieldsandfilters.field', '{"special":{"dbtable":"vpkos_fieldsandfilters_fields","key":"field_id","type":"Field","prefix":"FieldsandfiltersTable","config":"array()"},"common":[]}', '', '{"common":{"core_content_item_id":"field_id","core_title":"field_name","core_state":"state","core_alias":"field_alias","core_created_time":"null","core_modified_time":"null","core_body":"description","core_hits":"null","core_publish_up":"null","core_publish_down":"null","core_access":"access","core_params":"params","core_featured":"null","core_metadata":"null","core_language":"language","core_images":"null","core_urls":"null","core_version":"null","core_ordering":"ordering","core_metakey":"null","core_metadesc":"null","core_catid":"null","core_xreference":"null","asset_id":"null"},"special":{"field_type":"field_type","content_type_id":"content_type_id","mode":"mode","required":"required"}}', '', '{"formFile":"administrator\/components\/com_fieldsandfilters\/models\/forms\/field.xml","hideFields":["mode"],"ignoreChanges":[],"convertToInt":["content_type_id","mode","ordering","state","required"],"displayLookup":[{"sourceColumn":"content_type_id","targetTable":"vpkos_content_types","targetColumn":"type_id","displayColumn":"type_title"}]}' )
ON DUPLICATE KEY UPDATE `type_title` != 'com_fieldsandfilters.field';