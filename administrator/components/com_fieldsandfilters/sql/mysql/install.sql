-- -----------------------------------------------------
-- Table `#__fieldsandfilters_fields`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__fieldsandfilters_fields` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NOT NULL,
  `alias` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_bin' NOT NULL,
  `type` VARCHAR(100) CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NOT NULL,
  `content_type_id` INT(11) UNSIGNED NOT NULL,
  `mode` TINYINT(20) NOT NULL DEFAULT 1,
  `description` MEDIUMTEXT CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NOT NULL DEFAULT '',
  `ordering` INT(11) NOT NULL DEFAULT 0,
  `state` TINYINT(2) NOT NULL DEFAULT 0,
  `required` TINYINT(1) NOT NULL DEFAULT 0,
  `access` INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `language` CHAR(7) CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NOT NULL,
  `params` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  INDEX `content_type_id` (`content_type_id` ASC),
  INDEX `ordering` (`ordering` ASC),
  INDEX `state` (`state` ASC),
  INDEX `language` (`language` ASC),
  INDEX `mode` (`mode` ASC),
  INDEX `access` (`access` ASC),
  INDEX `required` (`required` ASC))
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8
  COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `#__fieldsandfilters_static`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__fieldsandfilters_static` (
  `field_id` INT(11) UNSIGNED NOT NULL,
  `data` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NOT NULL DEFAULT '',
  UNIQUE INDEX `field_id` (`field_id` ASC))
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8
  COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `#__fieldsandfilters_data`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__fieldsandfilters_data` (
  `content_type_id` INT(11) UNSIGNED NOT NULL,
  `content_id` INT(11) UNSIGNED NOT NULL,
  `field_id` INT(11) UNSIGNED NOT NULL,
  `data` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NOT NULL DEFAULT '',
  `params` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NOT NULL DEFAULT '',
  INDEX `field_id` (`field_id` ASC),
  INDEX `content_type_id` (`content_type_id` ASC),
  UNIQUE INDEX `content_type_id_content_id_field_id` (`field_id` ASC, `content_type_id` ASC, `content_id` ASC),
  INDEX `content_id` (`content_id` ASC))
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8
  COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `#__fieldsandfilters_field_values`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__fieldsandfilters_field_values` (
  `field_value_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `field_id` INT(11) UNSIGNED NOT NULL,
  `value` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NOT NULL,
  `alias` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_bin' NOT NULL,
  `ordering` INT(11) NOT NULL,
  `state` TINYINT(1) NOT NULL,
  `params` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NOT NULL DEFAULT '',
  PRIMARY KEY (`field_value_id`),
  INDEX `field_id` (`field_id` ASC),
  INDEX `ordering` (`ordering` ASC),
  INDEX `state` (`state` ASC))
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8
  COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `#__fieldsandfilters_connections`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__fieldsandfilters_connections` (
  `content_type_id` INT(11) UNSIGNED NOT NULL,
  `field_id` INT(11) UNSIGNED NOT NULL,
  `field_value_id` INT(11) UNSIGNED NOT NULL,
  `content_id` INT(11) UNSIGNED NOT NULL,
  INDEX `field_id` (`field_id` ASC),
  INDEX `field_value_id` (`field_value_id` ASC),
  INDEX `content_type_id` (`content_type_id` ASC),
  INDEX `content_id` (`content_id` ASC),
  UNIQUE INDEX `content_type_id_content_id_field_value_id` (`content_type_id` ASC, `content_id` ASC, `field_value_id` ASC))
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8
  COLLATE = utf8_general_ci;