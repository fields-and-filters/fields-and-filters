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
	AUTO_INCREMENT =10000;