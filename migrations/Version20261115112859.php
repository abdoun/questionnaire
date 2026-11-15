<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261115112859 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insert the abdoun user.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            "INSERT INTO users (username, password, email, active, level) VALUES ('abdoun', 'e10adc3949ba59abbe56e057f20f883e', 'abdoun79@gmail.com', 1, 'user')"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            "DELETE FROM users WHERE username = 'abdoun' AND password = 'e10adc3949ba59abbe56e057f20f883e' AND email = 'abdoun@example.com' AND active = 1 AND level = 'user'"
        );
    }
}
