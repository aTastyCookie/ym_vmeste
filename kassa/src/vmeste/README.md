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
INSERT INTO `vmeste_db_v2`.`status` (`id`, `name`, `status`) VALUES ('4', 'On moderation', 'ON_MODERATION');
INSERT INTO `vmeste_db_v2`.`status` (`id`, `name`, `status`) VALUES ('7', 'Pending', 'PENDING');

// Insert donor
INSERT INTO `vmeste_db_v2`.`donor` (`status_id`, `campaign_id`, `name`, `email`, `amount`, `currency`, `details`, `created`, `updated`) VALUES ('1', '1', 'John', 'Doe', '30', 'RUB', 'Comment', '0', '0');
INSERT INTO `vmeste_db_v2`.`donor` (`status_id`, `campaign_id`, `name`, `email`, `amount`, `currency`, `details`, `created`, `updated`) VALUES ('1', '1', 'Fred', 'Bred', '300', 'RUB', 'Comment', '0', '0');

// Insert transaction
INSERT INTO `vmeste_db_v2`.`transaction` (`campaign_id`, `donor_id`, `invoiceId`, `gross`, `currency`, `paymentStatus`, `transactionType`, `txnId`, `details`, `created`, `changed`) VALUES ('1', '1', '121243242', '30', 'RUB', 'ok', 'YKassa', 'sd', 'sd', '0', '0');
INSERT INTO `vmeste_db_v2`.`transaction` (`campaign_id`, `donor_id`, `invoiceId`, `gross`, `currency`, `paymentStatus`, `transactionType`, `txnId`, `details`, `created`, `changed`) VALUES ('1', '1', '3234234', '400', 'RUB', 'ok', 'YKassa', 'sd', 'sd', '0', '0');
