CREATE TABLE `cms_content_images` 
(
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`parent_id` INT(10) UNSIGNED NULL DEFAULT NULL,
	`title` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`alt` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`file_md5` VARCHAR(255) NOT NULL COLLATE 'utf8_unicode_ci',
	`filename` VARCHAR(255) NOT NULL COLLATE 'utf8_unicode_ci',
	`file_extension` CHAR(4) NOT NULL COLLATE 'utf8_unicode_ci',
	`filepath` VARCHAR(255) NOT NULL COLLATE 'utf8_unicode_ci',
	`width` INT(10) UNSIGNED NOT NULL,
	`height` INT(10) UNSIGNED NOT NULL,
	`crop_x` INT(10) UNSIGNED NULL DEFAULT NULL,
	`crop_y` INT(10) UNSIGNED NULL DEFAULT NULL,
	`crop_x2` INT(10) UNSIGNED NULL DEFAULT NULL,
	`crop_y2` INT(10) UNSIGNED NULL DEFAULT NULL,
	`date_creation` DATETIME NOT NULL,
	PRIMARY KEY (`id`)	
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB;

