ALTER TABLE  `ym_action` ADD  `group` BOOLEAN NOT NULL DEFAULT  '0';

--
-- Структура таблицы `ym_usersaction`
--

CREATE TABLE IF NOT EXISTS `ym_usersaction` (
  `user_id` varchar(25) character set utf8 NOT NULL,
  `action_id` int(10) unsigned NOT NULL,
  `summ` int(11) NOT NULL,
  UNIQUE KEY `action_user_id` (`user_id`,`action_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Users and Actions relations (for group actions)';

ALTER TABLE  `ym_usersaction` ADD  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;

ALTER TABLE  `ym_action` ADD  `dupl` INT UNSIGNED NOT NULL DEFAULT  '0';
ALTER TABLE  `ym_action` ADD  `checked` BOOLEAN NOT NULL DEFAULT  '0';