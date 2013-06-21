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
  INDEX `element` (`ordering` ASC, `state` ASC) )
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
  INDEX `access` (`access` ASC) )
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
  INDEX `state` (`state` ASC) )
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
  UNIQUE INDEX `element_id_field_id` (`element_id` ASC, `field_id` ASC) )
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
  INDEX `element_id` (`element_id` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `#__fieldsandfilters_extensions_type`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__fieldsandfilters_extensions_type` (
  `extension_type_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `extension_name` VARCHAR(100) CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NOT NULL ,
  PRIMARY KEY (`extension_type_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

INSERT INTO `#__fieldsandfilters_extensions_type` (`extension_type_id`, `extension_name`) VALUES (NULL, 'allextensions');