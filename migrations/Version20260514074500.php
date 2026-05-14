<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260514074500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add zapatilla_imagen table for multiple images per shoe';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE zapatilla_imagen (
            id SERIAL PRIMARY KEY,
            zapatilla_id INT NOT NULL,
            ruta VARCHAR(255) NOT NULL,
            fecha_creacion TIMESTAMP NOT NULL,
            orden INT NOT NULL DEFAULT 0,
            FOREIGN KEY (zapatilla_id) REFERENCES zapatilla(id) ON DELETE CASCADE
        )');
        
        $this->addSql('CREATE INDEX idx_zapatilla_imagen_zapatilla_id ON zapatilla_imagen(zapatilla_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE zapatilla_imagen');
    }
}
