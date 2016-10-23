<?php

namespace Migration;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161023101234 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql(
            'CREATE TABLE project_task ('
            . ' `id` INT UNSIGNED AUTO_INCREMENT NOT NULL,'
            . ' `project_id` INT UNSIGNED NOT NULL,'
            . ' `name` VARCHAR(100) NOT NULL,'
            . ' `data` TEXT DEFAULT NULL,'
            . ' `status` SMALLINT DEFAULT 0,'
            . ' `output` TEXT DEFAULT NULL,'
            . ' `created_at` DATETIME NOT NULL,'
            . ' `updated_at` DATETIME DEFAULT NULL,'
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
