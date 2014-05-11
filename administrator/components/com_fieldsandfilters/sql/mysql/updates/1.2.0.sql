-- -----------------------------------------------------
-- extension_type_id to content_type_id
-- -----------------------------------------------------
ALTER TABLE `#__fieldsandfilters_elements` CHANGE `extension_type_id` `content_type_id` INT(11) UNSIGNED NOT NULL;
DROP INDEX `extension_type_id` ON `#__fieldsandfilters_elements`;
ALTER TABLE `#__fieldsandfilters_elements` ADD INDEX `content_type_id` (`content_type_id` ASC);

ALTER TABLE `#__fieldsandfilters_fields` CHANGE `extension_type_id` `content_type_id` INT(11) UNSIGNED NOT NULL;
DROP INDEX `extension_type_id` ON `#__fieldsandfilters_fields`;
ALTER TABLE `#__fieldsandfilters_fields` ADD INDEX `content_type_id` (`content_type_id` ASC);

ALTER TABLE `#__fieldsandfilters_data` CHANGE `extension_type_id` `content_type_id` INT(11) UNSIGNED NOT NULL;
DROP INDEX `extension_type_id` ON `#__fieldsandfilters_data`;
ALTER TABLE `#__fieldsandfilters_data` ADD INDEX `content_type_id` (`content_type_id` ASC);

ALTER TABLE `#__fieldsandfilters_connections` CHANGE `extension_type_id` `content_type_id` INT(11) UNSIGNED NOT NULL;
DROP INDEX `extension_type_id` ON `#__fieldsandfilters_connections`;
ALTER TABLE `#__fieldsandfilters_connections` ADD INDEX `content_type_id` (`content_type_id` ASC);

-- -----------------------------------------------------
-- Simple structure
-- -----------------------------------------------------
ALTER TABLE `#__fieldsandfilters_elements` CHANGE `element_id` `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `#__fieldsandfilters_fields` CHANGE `field_id` `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__fieldsandfilters_fields` CHANGE `field_name` `name` VARCHAR(255)
CHARACTER SET 'utf8'
COLLATE 'utf8_general_ci' NOT NULL DEFAULT '';
ALTER TABLE `#__fieldsandfilters_fields` CHANGE `field_alias` `alias` VARCHAR(255)
CHARACTER SET 'utf8'
COLLATE 'utf8_bin' NOT NULL DEFAULT '';
ALTER TABLE `#__fieldsandfilters_fields` CHANGE `field_type` `type` VARCHAR(100)
CHARACTER SET 'utf8'
COLLATE 'utf8_general_ci' NOT NULL DEFAULT '';

ALTER TABLE `#__fieldsandfilters_field_values` CHANGE `field_value_id` `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__fieldsandfilters_field_values` CHANGE `field_value` `value` VARCHAR(255)
CHARACTER SET 'utf8'
COLLATE 'utf8_general_ci' NOT NULL DEFAULT '';
ALTER TABLE `#__fieldsandfilters_field_values` CHANGE `field_value_alias` `alias` VARCHAR(255)
CHARACTER SET 'utf8'
COLLATE 'utf8_bin' NOT NULL DEFAULT '';

ALTER TABLE `#__fieldsandfilters_data` CHANGE `field_data` `data` TEXT
CHARACTER SET 'utf8'
COLLATE 'utf8_general_ci' NOT NULL;
-- -----------------------------------------------------
-- Add new/Remove old index
-- -----------------------------------------------------
DROP INDEX `element` ON `#__fieldsandfilters_elements`;

DROP INDEX `field` ON `#__fieldsandfilters_fields`;
ALTER TABLE `#__fieldsandfilters_fields` ADD INDEX `required` (`required` ASC);
