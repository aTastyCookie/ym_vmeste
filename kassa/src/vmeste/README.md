Vmeste Saas
===========

1. Create application tables:

sudo php app/console doctrine:schema:update --force

2. Create repositories and setters and getters for entities

sudo php app/console doctrine:generate:entities VmesteSaasBundle

3. Insert default roles and statuses (SQL):

INSERT INTO `role` (`name`, `role`) VALUES ('Administrator', 'ROLE_ADMIN');
INSERT INTO `role` (`name`, `role`) VALUES ('User', 'ROLE_USER');

INSERT INTO `status` (`id`, `name`, `status`) VALUES ('1', 'Active', 'ACTIVE');
INSERT INTO `status` (`id`, `name`, `status`) VALUES ('2', 'Blocked', 'BLOCKED');
INSERT INTO `status` (`id`, `name`, `status`) VALUES ('3', 'Deleted', 'DELETED');
INSERT INTO `status` (`id`, `name`, `status`) VALUES ('4', 'On moderation', 'ON_MODERATION');
INSERT INTO `status` (`id`, `name`, `status`) VALUES ('7', 'Pending', 'PENDING');

4. Insert admin account:
// Insert admin
INSERT INTO `user` (`id`, `email`, `username`, `password`, `created`, `changed`, `created_by`) 
VALUES ('1', 'admin@localhost', 'admin', SHA2('password', 512), UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), '1');
INSERT INTO `user_role` (`user_id`, `role_id`) VALUES ('1', '1');
INSERT INTO `user_status` (`user_id`, `status_id`) VALUES ('1', '1');
