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