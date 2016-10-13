<?php

namespace Migration;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161013121550 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql(
            'CREATE TABLE `user_project` ('
            . ' `id` INT UNSIGNED AUTO_INCREMENT NOT NULL,'
            . ' `user_id` INT UNSIGNED NOT NULL,'
            . ' `project_id` INT UNSIGNED NOT NULL,'
            . ' PRIMARY KEY (`id`),'
            . ' UNIQUE KEY `uniq_user_project` (`user_id`, `project_id`)'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8;'
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
