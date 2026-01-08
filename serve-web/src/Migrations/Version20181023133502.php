<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181023133502 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE client_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE deputy_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE document_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE dc_order_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE dc_user_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE client (id INT NOT NULL, case_number VARCHAR(8) NOT NULL, client_name VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C74404553F7E58FD ON client (case_number)');
        $this->addSql('CREATE TABLE deputy (id INT NOT NULL, deputy_type VARCHAR(255) NOT NULL, forename VARCHAR(255) NOT NULL, surname VARCHAR(255) NOT NULL, dob DATE DEFAULT NULL, email_address VARCHAR(255) DEFAULT NULL, daytime_contact_number VARCHAR(255) DEFAULT NULL, evening_contact_number VARCHAR(255) DEFAULT NULL, mobile_contact_number VARCHAR(255) DEFAULT NULL, address_line_1 VARCHAR(255) DEFAULT NULL, address_line_2 VARCHAR(255) DEFAULT NULL, address_line_3 VARCHAR(255) DEFAULT NULL, address_town VARCHAR(255) DEFAULT NULL, address_county VARCHAR(255) DEFAULT NULL, address_postcode VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE document (id INT NOT NULL, order_id INT DEFAULT NULL, type VARCHAR(100) NOT NULL, fileName VARCHAR(255) DEFAULT NULL, storageReference VARCHAR(255) NOT NULL, remoteStorageReference VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D8698A768D9F6D38 ON document (order_id)');
        $this->addSql('CREATE TABLE dc_order (id INT NOT NULL, client_id INT DEFAULT NULL, sub_type VARCHAR(50) DEFAULT NULL, has_assets_above_threshold VARCHAR(50) DEFAULT NULL, appointment_type VARCHAR(50) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, made_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT \'01-01-2017\' NOT NULL, payload_served JSON DEFAULT NULL, api_response JSON DEFAULT NULL, issued_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, served_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_450EF9F719EB6921 ON dc_order (client_id)');
        $this->addSql('COMMENT ON COLUMN dc_order.payload_served IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN dc_order.api_response IS \'(DC2Type:json_array)\'');
        $this->addSql('CREATE TABLE ordertype_deputy (deputy_id INT NOT NULL, order_type_id INT NOT NULL, PRIMARY KEY(deputy_id, order_type_id))');
        $this->addSql('CREATE INDEX IDX_DBBC0D8A4B6F93BB ON ordertype_deputy (deputy_id)');
        $this->addSql('CREATE INDEX IDX_DBBC0D8A333625D8 ON ordertype_deputy (order_type_id)');
        $this->addSql('CREATE TABLE dc_user (id INT NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7A619B33E7927C74 ON dc_user (email)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A768D9F6D38 FOREIGN KEY (order_id) REFERENCES dc_order (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE dc_order ADD CONSTRAINT FK_450EF9F719EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ordertype_deputy ADD CONSTRAINT FK_DBBC0D8A4B6F93BB FOREIGN KEY (deputy_id) REFERENCES dc_order (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ordertype_deputy ADD CONSTRAINT FK_DBBC0D8A333625D8 FOREIGN KEY (order_type_id) REFERENCES deputy (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE dc_order DROP CONSTRAINT FK_450EF9F719EB6921');
        $this->addSql('ALTER TABLE ordertype_deputy DROP CONSTRAINT FK_DBBC0D8A333625D8');
        $this->addSql('ALTER TABLE document DROP CONSTRAINT FK_D8698A768D9F6D38');
        $this->addSql('ALTER TABLE ordertype_deputy DROP CONSTRAINT FK_DBBC0D8A4B6F93BB');
        $this->addSql('DROP SEQUENCE client_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE deputy_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE document_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE dc_order_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE dc_user_id_seq CASCADE');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE deputy');
        $this->addSql('DROP TABLE document');
        $this->addSql('DROP TABLE dc_order');
        $this->addSql('DROP TABLE ordertype_deputy');
        $this->addSql('DROP TABLE dc_user');
    }
}
