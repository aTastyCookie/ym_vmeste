Vmeste Saas
===========

1. Create application tables:

sudo php app/console doctrine:schema:update --force

2. Create repositories and setters and getters for entities

sudo php app/console doctrine:generate:entities VmesteSaasBundle

3. Insert default roles (SQL):

INSERT INTO `vmeste_db_v2`.`role` (`name`, `role`) VALUES ('Administrator', 'ROLE_ADMIN');
INSERT INTO `vmeste_db_v2`.`role` (`name`, `role`) VALUES ('User', 'ROLE_USER');

INSERT INTO `vmeste_db_v2`.`status` (`id`, `name`, `status`) VALUES ('1', 'Active', 'ACTIVE');
INSERT INTO `vmeste_db_v2`.`status` (`id`, `name`, `status`) VALUES ('2', 'Blocked', 'BLOCKED');
INSERT INTO `vmeste_db_v2`.`status` (`id`, `name`, `status`) VALUES ('3', 'Deleted', 'DELETED');
