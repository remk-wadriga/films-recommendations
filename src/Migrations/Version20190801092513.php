<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190801092513 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE actor (id INT AUTO_INCREMENT NOT NULL, country_id INT NOT NULL, name VARCHAR(255) NOT NULL, age INT NOT NULL, sex VARCHAR(8) NOT NULL, INDEX IDX_447556F9F92F3E70 (country_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE company (id INT AUTO_INCREMENT NOT NULL, country_id INT NOT NULL, name VARCHAR(255) NOT NULL, staff INT NOT NULL, INDEX IDX_4FBF094FF92F3E70 (country_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE country (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(4) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE director (id INT AUTO_INCREMENT NOT NULL, country_id INT NOT NULL, name VARCHAR(255) NOT NULL, age INT NOT NULL, sex VARCHAR(8) NOT NULL, INDEX IDX_1E90D3F0F92F3E70 (country_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE film (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, poster VARCHAR(255) NOT NULL, budget INT NOT NULL, sales INT NOT NULL, languages LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', date DATE NOT NULL, duration INT NOT NULL, INDEX IDX_8244BE22A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE film_genre (film_id INT NOT NULL, genre_id INT NOT NULL, INDEX IDX_1A3CCDA8567F5183 (film_id), INDEX IDX_1A3CCDA84296D31F (genre_id), PRIMARY KEY(film_id, genre_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE film_company (film_id INT NOT NULL, company_id INT NOT NULL, INDEX IDX_AF01126C567F5183 (film_id), INDEX IDX_AF01126C979B1AD6 (company_id), PRIMARY KEY(film_id, company_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE film_director (film_id INT NOT NULL, director_id INT NOT NULL, INDEX IDX_BC171C99567F5183 (film_id), INDEX IDX_BC171C99899FB366 (director_id), PRIMARY KEY(film_id, director_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE film_actor (film_id INT NOT NULL, actor_id INT NOT NULL, INDEX IDX_DD19A8A9567F5183 (film_id), INDEX IDX_DD19A8A910DAF24A (actor_id), PRIMARY KEY(film_id, actor_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE film_producer (film_id INT NOT NULL, producer_id INT NOT NULL, INDEX IDX_35E386B5567F5183 (film_id), INDEX IDX_35E386B589B658FE (producer_id), PRIMARY KEY(film_id, producer_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE film_writer (film_id INT NOT NULL, writer_id INT NOT NULL, INDEX IDX_FC52E588567F5183 (film_id), INDEX IDX_FC52E5881BC7E6B6 (writer_id), PRIMARY KEY(film_id, writer_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE film_premium (film_id INT NOT NULL, premium_id INT NOT NULL, INDEX IDX_69830FA6567F5183 (film_id), INDEX IDX_69830FA6F7798796 (premium_id), PRIMARY KEY(film_id, premium_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE genre (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE premium (id INT AUTO_INCREMENT NOT NULL, country_id INT NOT NULL, name VARCHAR(255) NOT NULL, prize INT DEFAULT NULL, INDEX IDX_893D1485F92F3E70 (country_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE producer (id INT AUTO_INCREMENT NOT NULL, company_id INT NOT NULL, name VARCHAR(255) NOT NULL, age INT NOT NULL, sex VARCHAR(8) NOT NULL, INDEX IDX_976449DC979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, sex VARCHAR(8) NOT NULL, age INT NOT NULL, about_me LONGTEXT DEFAULT NULL, password VARCHAR(120) NOT NULL, salt VARCHAR(64) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', access_token VARCHAR(64) NOT NULL, renew_token VARCHAR(64) NOT NULL, access_token_expired_at DATETIME NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE writer (id INT AUTO_INCREMENT NOT NULL, country_id INT NOT NULL, name VARCHAR(255) NOT NULL, age INT NOT NULL, sex VARCHAR(8) NOT NULL, INDEX IDX_97A0D882F92F3E70 (country_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE actor ADD CONSTRAINT FK_447556F9F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id)');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094FF92F3E70 FOREIGN KEY (country_id) REFERENCES country (id)');
        $this->addSql('ALTER TABLE director ADD CONSTRAINT FK_1E90D3F0F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id)');
        $this->addSql('ALTER TABLE film ADD CONSTRAINT FK_8244BE22A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE film_genre ADD CONSTRAINT FK_1A3CCDA8567F5183 FOREIGN KEY (film_id) REFERENCES film (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE film_genre ADD CONSTRAINT FK_1A3CCDA84296D31F FOREIGN KEY (genre_id) REFERENCES genre (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE film_company ADD CONSTRAINT FK_AF01126C567F5183 FOREIGN KEY (film_id) REFERENCES film (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE film_company ADD CONSTRAINT FK_AF01126C979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE film_director ADD CONSTRAINT FK_BC171C99567F5183 FOREIGN KEY (film_id) REFERENCES film (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE film_director ADD CONSTRAINT FK_BC171C99899FB366 FOREIGN KEY (director_id) REFERENCES director (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE film_actor ADD CONSTRAINT FK_DD19A8A9567F5183 FOREIGN KEY (film_id) REFERENCES film (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE film_actor ADD CONSTRAINT FK_DD19A8A910DAF24A FOREIGN KEY (actor_id) REFERENCES actor (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE film_producer ADD CONSTRAINT FK_35E386B5567F5183 FOREIGN KEY (film_id) REFERENCES film (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE film_producer ADD CONSTRAINT FK_35E386B589B658FE FOREIGN KEY (producer_id) REFERENCES producer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE film_writer ADD CONSTRAINT FK_FC52E588567F5183 FOREIGN KEY (film_id) REFERENCES film (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE film_writer ADD CONSTRAINT FK_FC52E5881BC7E6B6 FOREIGN KEY (writer_id) REFERENCES writer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE film_premium ADD CONSTRAINT FK_69830FA6567F5183 FOREIGN KEY (film_id) REFERENCES film (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE film_premium ADD CONSTRAINT FK_69830FA6F7798796 FOREIGN KEY (premium_id) REFERENCES premium (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE premium ADD CONSTRAINT FK_893D1485F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id)');
        $this->addSql('ALTER TABLE producer ADD CONSTRAINT FK_976449DC979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE writer ADD CONSTRAINT FK_97A0D882F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE film_actor DROP FOREIGN KEY FK_DD19A8A910DAF24A');
        $this->addSql('ALTER TABLE film_company DROP FOREIGN KEY FK_AF01126C979B1AD6');
        $this->addSql('ALTER TABLE producer DROP FOREIGN KEY FK_976449DC979B1AD6');
        $this->addSql('ALTER TABLE actor DROP FOREIGN KEY FK_447556F9F92F3E70');
        $this->addSql('ALTER TABLE company DROP FOREIGN KEY FK_4FBF094FF92F3E70');
        $this->addSql('ALTER TABLE director DROP FOREIGN KEY FK_1E90D3F0F92F3E70');
        $this->addSql('ALTER TABLE premium DROP FOREIGN KEY FK_893D1485F92F3E70');
        $this->addSql('ALTER TABLE writer DROP FOREIGN KEY FK_97A0D882F92F3E70');
        $this->addSql('ALTER TABLE film_director DROP FOREIGN KEY FK_BC171C99899FB366');
        $this->addSql('ALTER TABLE film_genre DROP FOREIGN KEY FK_1A3CCDA8567F5183');
        $this->addSql('ALTER TABLE film_company DROP FOREIGN KEY FK_AF01126C567F5183');
        $this->addSql('ALTER TABLE film_director DROP FOREIGN KEY FK_BC171C99567F5183');
        $this->addSql('ALTER TABLE film_actor DROP FOREIGN KEY FK_DD19A8A9567F5183');
        $this->addSql('ALTER TABLE film_producer DROP FOREIGN KEY FK_35E386B5567F5183');
        $this->addSql('ALTER TABLE film_writer DROP FOREIGN KEY FK_FC52E588567F5183');
        $this->addSql('ALTER TABLE film_premium DROP FOREIGN KEY FK_69830FA6567F5183');
        $this->addSql('ALTER TABLE film_genre DROP FOREIGN KEY FK_1A3CCDA84296D31F');
        $this->addSql('ALTER TABLE film_premium DROP FOREIGN KEY FK_69830FA6F7798796');
        $this->addSql('ALTER TABLE film_producer DROP FOREIGN KEY FK_35E386B589B658FE');
        $this->addSql('ALTER TABLE film DROP FOREIGN KEY FK_8244BE22A76ED395');
        $this->addSql('ALTER TABLE film_writer DROP FOREIGN KEY FK_FC52E5881BC7E6B6');
        $this->addSql('DROP TABLE actor');
        $this->addSql('DROP TABLE company');
        $this->addSql('DROP TABLE country');
        $this->addSql('DROP TABLE director');
        $this->addSql('DROP TABLE film');
        $this->addSql('DROP TABLE film_genre');
        $this->addSql('DROP TABLE film_company');
        $this->addSql('DROP TABLE film_director');
        $this->addSql('DROP TABLE film_actor');
        $this->addSql('DROP TABLE film_producer');
        $this->addSql('DROP TABLE film_writer');
        $this->addSql('DROP TABLE film_premium');
        $this->addSql('DROP TABLE genre');
        $this->addSql('DROP TABLE premium');
        $this->addSql('DROP TABLE producer');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE writer');
    }
}
