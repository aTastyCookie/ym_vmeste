Andrey Glyatsevich
Выполнить команды из директории src/vmeste/ (они описаны в README):
php app/console doctrine:schema:update --force
php app/console doctrine:generate:entities VmesteSaasBundle
Эти команды обновляют структуру БД. Практически после каждого обновления кода, нужно выполнять эти команды, так как мы часто что-то изменяем в БД.
2014-09-26
 Andrey Glyatsevich
Вставить исходные данные в БД:
INSERT INTO `vmeste`.`role` (`name`, `role`) VALUES ('Administrator', 'ROLE_ADMIN');

INSERT INTO `vmeste`.`role` (`name`, `role`) VALUES ('User', 'ROLE_USER');

INSERT INTO `vmeste`.`status` (`id`, `name`, `status`) VALUES ('1', 'Active', 'ACTIVE');

INSERT INTO `vmeste`.`status` (`id`, `name`, `status`) VALUES ('2', 'Blocked', 'BLOCKED');

INSERT INTO `vmeste`.`status` (`id`, `name`, `status`) VALUES ('3', 'Deleted', 'DELETED');

INSERT INTO `vmeste`.`status` (`id`, `name`, `status`) VALUES ('4', 'On moderation', 'ON_MODERATION');

INSERT INTO `vmeste`.`status` (`id`, `name`, `status`) VALUES ('7', 'Pending', 'PENDING');
2014-09-26
 Andrey Glyatsevich
attached Dump20140930.sql
2014-09-30
 Andrey Glyatsevich
Команды для cron:
app/console vmeste:recurrent (1 раз в день (желательно, в 12:00))
app/console vmeste:cleantokens
3 days ago
 Andrey Glyatsevich
В конфиге vmeste\src\vmeste\app\config\config.yml нужно правильно прописать настройки в секции "parameters:"
3 days ago
 Andrey Glyatsevich
Задания для CRON:
0 12 * * * /usr/bin/php /home/admin/www/vmeste/src/vmeste/app/console vmeste:recurrent
0 * * /usr/bin/php /home/admin/www/vmeste/src/vmeste/app/console vmeste:cleantokens
Пути к php и приложению, возможно, придется исправить в соответствии с локальными настройками сервера
yesterday
 Andrey Glyatsevich
Первоначальную настройку приложения можно совершить через веб-интерфейс /config.php
