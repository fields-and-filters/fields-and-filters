-- -----------------------------------------------------
-- Table `#__fieldsandfilters_elements`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__fieldsandfilters_elements` (
	`id`              INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`content_type_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
	`item_id`         INT(11) UNSIGNED NOT NULL DEFAULT 0,
	`ordering`        INT(11)          NOT NULL DEFAULT 0,
	`state`           SMALLINT(6)      NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`),
	INDEX `item_id` (`item_id` ASC),
	INDEX `ordering` (`ordering` ASC),
	INDEX `content_type_id` (`content_type_id` ASC),
	INDEX `state` (`state` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `#__fieldsandfilters_fields`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__fieldsandfilters_fields` (
	`id`              INT(11) UNSIGNED          NOT NULL AUTO_INCREMENT,
	`name`            VARCHAR(255)
										CHARACTER SET 'utf8'
										COLLATE 'utf8_general_ci' NOT NULL DEFAULT '',
	`alias`           VARCHAR(255)
										CHARACTER SET 'utf8'
										COLLATE 'utf8_bin'        NOT NULL DEFAULT '',
	`type`            VARCHAR(100)
										CHARACTER SET 'utf8'
										COLLATE 'utf8_general_ci' NOT NULL DEFAULT '',
	`content_type_id` INT(11) UNSIGNED          NOT NULL DEFAULT 0,
	`mode`            TINYINT(20)               NOT NULL DEFAULT 1,
	`description`     MEDIUMTEXT
										CHARACTER SET 'utf8'
										COLLATE 'utf8_general_ci' NOT NULL DEFAULT '',
	`ordering`        INT(11)                   NOT NULL DEFAULT 0,
	`state`           TINYINT(2)                NOT NULL DEFAULT 0,
	`required`        TINYINT(1)                NOT NULL DEFAULT 0,
	`access`          INT(10) UNSIGNED          NOT NULL DEFAULT 0,
	`language`        CHAR(7)
										CHARACTER SET 'utf8'
										COLLATE 'utf8_general_ci' NOT NULL DEFAULT '',
	`params`          TEXT
										CHARACTER SET 'utf8'
										COLLATE 'utf8_general_ci' NOT NULL DEFAULT '',
	PRIMARY KEY (`id`),
	INDEX `ordering` (`ordering` ASC),
	INDEX `state` (`state` ASC),
	INDEX `content_type_id` (`content_type_id` ASC),
	INDEX `mode` (`mode` ASC),
	INDEX `access` (`access` ASC),
	INDEX `language` (`language` ASC),
	INDEX `required` (`required` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `#__fieldsandfilters_data`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__fieldsandfilters_data` (
	`element_id`      INT(11) UNSIGNED          NOT NULL DEFAULT 0,
	`content_type_id` INT(11) UNSIGNED          NOT NULL DEFAULT 0,
	`field_id`        INT(11) UNSIGNED          NOT NULL DEFAULT 0,
	`data`            TEXT
										CHARACTER SET 'utf8'
										COLLATE 'utf8_general_ci' NOT NULL DEFAULT '',
	INDEX `field_id` (`field_id` ASC),
	INDEX `element_id` (`element_id` ASC),
	UNIQUE INDEX `element_id_field_id` (`element_id` ASC, `field_id` ASC),
	INDEX `content_type_id` (`content_type_id` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `#__fieldsandfilters_field_values`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__fieldsandfilters_field_values` (
	`id`       INT(11) UNSIGNED          NOT NULL AUTO_INCREMENT,
	`field_id` INT(11) UNSIGNED          NOT NULL DEFAULT 0,
	`value`    VARCHAR(255)
						 CHARACTER SET 'utf8'
						 COLLATE 'utf8_general_ci' NOT NULL DEFAULT '',
	`alias`    VARCHAR(255)
						 CHARACTER SET 'utf8'
						 COLLATE 'utf8_bin'        NOT NULL DEFAULT '',
	`ordering` INT(11)                   NOT NULL DEFAULT 0,
	`state`    TINYINT(1)                NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`),
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
	`element_id`      INT(11) UNSIGNED NOT NULL DEFAULT 0,
	`content_type_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
	`field_id`        INT(11) UNSIGNED NOT NULL DEFAULT 0,
	`field_value_id`  INT(11) UNSIGNED NOT NULL DEFAULT 0,
	INDEX `field_id` (`field_id` ASC),
	INDEX `field_value_id` (`field_value_id` ASC),
	UNIQUE INDEX `element_id_field_value_id` (`field_value_id` ASC, `element_id` ASC),
	INDEX `element_id` (`element_id` ASC),
	INDEX `content_type_id` (`content_type_id` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `#__content_types`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__content_types` (
	`type_id`                 INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`type_title`              VARCHAR(255)     NOT NULL DEFAULT '',
	`type_alias`              VARCHAR(255)     NOT NULL DEFAULT '',
	`table`                   VARCHAR(255)     NOT NULL DEFAULT '',
	`rules`                   TEXT             NOT NULL,
	`field_mappings`          TEXT             NOT NULL,
	`router`                  VARCHAR(255)     NOT NULL  DEFAULT '',
	`content_history_options` VARCHAR(5120) COMMENT 'JSON string for com_contenthistory options',
	PRIMARY KEY (`type_id`),
	KEY `idx_alias` (`type_alias`)
)
	ENGINE =InnoDB
	DEFAULT CHARSET =utf8
	COLLATE = utf8_general_ci
	AUTO_INCREMENT=10000;