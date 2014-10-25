ALTER TABLE `pages` ADD `text` TEXT NOT NULL AFTER `title` ;
ALTER TABLE `pages` CHANGE `status` `status` INT( 11 ) NOT NULL DEFAULT '1';
ALTER TABLE `pages` ADD `email` VARCHAR( 255 ) NOT NULL AFTER `photo` ;
ALTER TABLE `pages` ADD `hash` VARCHAR( 255 ) NOT NULL ,
ADD UNIQUE (
`hash`
);
ALTER TABLE `pages` CHANGE `field_name` `field_name` TINYINT( 2 ) NOT NULL DEFAULT '0';
ALTER TABLE `pages` CHANGE `field_phone` `field_phone` TINYINT( 2 ) NOT NULL DEFAULT '0';
