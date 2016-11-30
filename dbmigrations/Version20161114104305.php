<?php

namespace Migration;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161114104305 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql(
            'CREATE TABLE project_notification ('
            . ' `id` INT UNSIGNED AUTO_INCREMENT NOT NULL,'
            . ' `project_id` INT UNSIGNED NOT NULL,'
            . ' `name` VARCHAR(100) NOT NULL,'
            . ' `type` VARCHAR(20) NOT NULL,'
            . ' `data` TEXT DEFAULT NULL,'
            . ' PRIMARY KEY (`id`)'
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
