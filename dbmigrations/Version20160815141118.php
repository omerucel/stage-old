<?php

namespace Migration;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160815141118 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql(
            'CREATE TABLE users ('
            . ' `id` INT UNSIGNED AUTO_INCREMENT NOT NULL,'
            . ' `email` VARCHAR(100) NOT NULL,'
            . ' `password` VARCHAR(255) NOT NULL,'
            . ' `name` VARCHAR(50) NOT NULL,'
            . ' `surname` VARCHAR(50) NOT NULL,'
            . ' `status` SMALLINT DEFAULT 1,'
            . ' `role` VARCHAR(10),'
            . ' PRIMARY KEY (`id`),'
            . ' UNIQUE KEY `uniq_email` (`email`)'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8;'
        );
        $password = password_hash('admin', PASSWORD_BCRYPT);
        $this->addSql(
            'INSERT INTO `users` (`email`, `password`, `name`, `surname`, `status`, `role`) VALUES ('
            . '"admin@admin.com", "' . $password . '", "admin", "admin", 1, "admin")'
        );
        $this->addSql(
            'CREATE TABLE `user_permission` ('
            . ' `id` INT UNSIGNED AUTO_INCREMENT NOT NULL,'
            . ' `user_id` INT UNSIGNED NOT NULL,'
            . ' `permission_id` INT UNSIGNED NOT NULL,'
            . ' PRIMARY KEY (`id`),'
            . ' UNIQUE KEY `uniq_perm` (`user_id`, `permission_id`)'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8;'
        );
        $this->addSql(
            'CREATE TABLE `permission` ('
            . ' `id` INT UNSIGNED AUTO_INCREMENT NOT NULL,'
            . ' `name` VARCHAR(255) NOT NULL,'
            . ' PRIMARY KEY (`id`),'
            . ' UNIQUE KEY `uniq_name` (`name`)'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8;'
        );
        $this->addSql(
            'INSERT INTO `permission` (`name`) VALUES '
            . '("users.save"),'
            . '("users.list"),'
            . '("users.activities"),'
            . '("project.save"),'
            . '("project.list"),'
            . '("project.server"),'
            . '("project.server.setup"),'
            . '("project.server.start"),'
            . '("project.server.restart"),'
            . '("project.server.stop"),'
            . '("project.server.inspect"),'
            . '("project.server.vhost.get"),'
            . '("project.server.vhost.save")'
        );
        $this->addSql(
            'INSERT INTO `user_permission` (`user_id`, `permission_id`) VALUES '
            . '(1, 1),'
            . '(1, 2),'
            . '(1, 3),'
            . '(1, 4),'
            . '(1, 5),'
            . '(1, 6),'
            . '(1, 7),'
            . '(1, 8),'
            . '(1, 9),'
            . '(1, 10),'
            . '(1, 11),'
            . '(1, 12),'
            . '(1, 13)'
        );
        $this->addSql(
            'CREATE TABLE `user_activity` ('
            . ' `id` INT UNSIGNED AUTO_INCREMENT NOT NULL,'
            . ' `user_id` INT UNSIGNED NOT NULL,'
            . ' `activity` VARCHAR(100),'
            . ' `data` TEXT DEFAULT NULL,'
            . ' `created_at` DATETIME NOT NULL,'
            . ' PRIMARY KEY (`id`)'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8;'
        );
        $this->addSql(
            'CREATE TABLE `project` ('
            . ' `id` INT UNSIGNED AUTO_INCREMENT NOT NULL,'
            . ' `name` VARCHAR(50) NOT NULL,'
            . ' PRIMARY KEY (`id`),'
            . ' UNIQUE KEY `uniq_name` (`name`)'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8;'
        );
        $this->addSql(
            'CREATE TABLE `project_file` ('
            . ' `id` INT UNSIGNED AUTO_INCREMENT NOT NULL,'
            . ' `project_id` INT UNSIGNED NOT NULL,'
            . ' `name` VARCHAR(50) NOT NULL,'
            . ' `content` TEXT DEFAULT NULL,'
            . ' PRIMARY KEY (`id`),'
            . ' KEY `idx_project` (`project_id`),'
            . ' UNIQUE KEY `idx_name` (`project_id`, `name`)'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8;'
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
