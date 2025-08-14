<?php /** @noinspection PhpUnhandledExceptionInspection */

use Core\CLI\AbstractMigration;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;

class CreateTablesFromEntities extends AbstractMigration
{
    protected function migrate(): void
    {
        $this->setDescription('Aktualizace schématu databáze podle entit: Activity, Contact, Customer, Deal, Email, EmailServer, EmailSignature, EmailTemplate, Invoice, InvoiceItem, Project, ProjectEvent, ProjectFile, ProjectTimeEntry, User, Workflow, WorkflowExecution');

        $this->raw("CREATE TABLE activities (id INT AUTO_INCREMENT NOT NULL, customer_id INT DEFAULT NULL, deal_id INT DEFAULT NULL, contact_id INT DEFAULT NULL, type VARCHAR(50) NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_B5F1AFE59395C3F3 (customer_id), INDEX IDX_B5F1AFE5F60E2305 (deal_id), INDEX IDX_B5F1AFE5E7A1254A (contact_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->raw("CREATE TABLE contacts (id INT AUTO_INCREMENT NOT NULL, customer_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, subject VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, contact_date DATETIME NOT NULL, status VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_334015739395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->raw("CREATE TABLE customers (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, company VARCHAR(255) DEFAULT NULL, category VARCHAR(50) NOT NULL, address LONGTEXT DEFAULT NULL, zip_code VARCHAR(10) DEFAULT NULL, city VARCHAR(100) DEFAULT NULL, country VARCHAR(100) DEFAULT NULL, status VARCHAR(50) DEFAULT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->raw("CREATE TABLE deals (id INT AUTO_INCREMENT NOT NULL, customer_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, value NUMERIC(10, 2) DEFAULT NULL, stage VARCHAR(50) NOT NULL, probability NUMERIC(3, 2) NOT NULL, expected_close_date DATE DEFAULT NULL, status VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_EF39849B9395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->raw("CREATE TABLE emails (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, customer_id INT DEFAULT NULL, deal_id INT DEFAULT NULL, template_id INT DEFAULT NULL, server_id INT DEFAULT NULL, subject VARCHAR(255) NOT NULL, body LONGTEXT NOT NULL, from_email VARCHAR(255) NOT NULL, from_name VARCHAR(255) NOT NULL, to_emails LONGTEXT NOT NULL, cc_emails LONGTEXT DEFAULT NULL, bcc_emails LONGTEXT DEFAULT NULL, attachments LONGTEXT DEFAULT NULL, status VARCHAR(50) NOT NULL, sent_at DATETIME DEFAULT NULL, scheduled_at DATETIME DEFAULT NULL, error_message LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_4C81E852A76ED395 (user_id), INDEX IDX_4C81E8529395C3F3 (customer_id), INDEX IDX_4C81E852F60E2305 (deal_id), INDEX IDX_4C81E8525DA0FB8 (template_id), INDEX IDX_4C81E8521844E6B7 (server_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->raw("CREATE TABLE email_servers (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, host VARCHAR(255) NOT NULL, port INT NOT NULL, encryption VARCHAR(50) NOT NULL, username VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, from_email VARCHAR(255) NOT NULL, from_name VARCHAR(255) NOT NULL, is_active TINYINT(1) NOT NULL, is_default TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_F24A9D38A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->raw("CREATE TABLE email_signatures (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, is_default TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_B6C8C4D3A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->raw("CREATE TABLE email_templates (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, subject VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, category VARCHAR(50) NOT NULL, is_active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_6023E2A5A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->raw("CREATE TABLE invoices (id INT AUTO_INCREMENT NOT NULL, customer_id INT NOT NULL, invoice_number VARCHAR(50) NOT NULL, issue_date DATE NOT NULL, due_date DATE NOT NULL, subtotal NUMERIC(10, 2) NOT NULL, tax_rate NUMERIC(5, 2) NOT NULL, tax_amount NUMERIC(10, 2) NOT NULL, total NUMERIC(10, 2) NOT NULL, currency VARCHAR(20) NOT NULL, notes LONGTEXT DEFAULT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_6A2F2F952DA68207 (invoice_number), INDEX IDX_6A2F2F959395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->raw("CREATE TABLE invoice_items (id INT AUTO_INCREMENT NOT NULL, invoice_id INT NOT NULL, description VARCHAR(255) NOT NULL, quantity NUMERIC(10, 2) NOT NULL, unit_price NUMERIC(10, 2) NOT NULL, total NUMERIC(10, 2) NOT NULL, unit VARCHAR(20) DEFAULT NULL, INDEX IDX_DCC4B9F82989F1FD (invoice_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->raw("CREATE TABLE projects (id INT AUTO_INCREMENT NOT NULL, customer_id INT NOT NULL, manager_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, status VARCHAR(50) NOT NULL, priority VARCHAR(20) NOT NULL, startDate DATETIME DEFAULT NULL, endDate DATETIME DEFAULT NULL, budget NUMERIC(10, 2) DEFAULT NULL, actualCost NUMERIC(10, 2) DEFAULT NULL, estimatedHours INT DEFAULT NULL, actualHours INT DEFAULT NULL, progress NUMERIC(5, 2) DEFAULT NULL, miniPageSlug VARCHAR(255) DEFAULT NULL, miniPageContent LONGTEXT DEFAULT NULL, settings LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json)', createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, INDEX IDX_5C93B3A49395C3F3 (customer_id), INDEX IDX_5C93B3A4783E3463 (manager_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->raw("CREATE TABLE project_events (id INT AUTO_INCREMENT NOT NULL, project_id INT NOT NULL, created_by INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, type VARCHAR(50) NOT NULL, status VARCHAR(50) NOT NULL, eventDate DATETIME NOT NULL, completedAt DATETIME DEFAULT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, INDEX IDX_4423BC00166D1F9C (project_id), INDEX IDX_4423BC00DE12AB56 (created_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->raw("CREATE TABLE project_files (id INT AUTO_INCREMENT NOT NULL, project_id INT NOT NULL, uploaded_by INT DEFAULT NULL, filename VARCHAR(255) NOT NULL, originalFilename VARCHAR(255) NOT NULL, mimeType VARCHAR(100) NOT NULL, fileSize INT NOT NULL, filePath VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, category VARCHAR(50) NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, INDEX IDX_156049C7166D1F9C (project_id), INDEX IDX_156049C7E3E73126 (uploaded_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->raw("CREATE TABLE project_time_entries (id INT AUTO_INCREMENT NOT NULL, project_id INT NOT NULL, user_id INT DEFAULT NULL, description LONGTEXT NOT NULL, startTime DATETIME NOT NULL, endTime DATETIME DEFAULT NULL, durationMinutes INT DEFAULT NULL, hourlyRate NUMERIC(10, 2) DEFAULT NULL, totalCost NUMERIC(10, 2) DEFAULT NULL, status VARCHAR(50) NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, INDEX IDX_53D8F6C0166D1F9C (project_id), INDEX IDX_53D8F6C0A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->raw("CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, role VARCHAR(50) NOT NULL, avatar VARCHAR(255) DEFAULT NULL, is_active TINYINT(1) NOT NULL, last_login DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->raw("CREATE TABLE workflows (id INT AUTO_INCREMENT NOT NULL, created_by INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, trigger_type VARCHAR(50) NOT NULL, trigger_config LONGTEXT NOT NULL COMMENT '(DC2Type:json)', conditions LONGTEXT NOT NULL COMMENT '(DC2Type:json)', actions LONGTEXT NOT NULL COMMENT '(DC2Type:json)', is_active TINYINT(1) NOT NULL, priority INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_EFBFBFC2DE12AB56 (created_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->raw("CREATE TABLE workflow_executions (id INT AUTO_INCREMENT NOT NULL, workflow_id INT DEFAULT NULL, status VARCHAR(50) NOT NULL, trigger_data LONGTEXT NOT NULL COMMENT '(DC2Type:json)', execution_log LONGTEXT NOT NULL COMMENT '(DC2Type:json)', error_message LONGTEXT DEFAULT NULL, started_at DATETIME NOT NULL, completed_at DATETIME DEFAULT NULL, execution_time_ms INT NOT NULL, INDEX IDX_402F685B2C7C2CBA (workflow_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->raw("ALTER TABLE activities ADD CONSTRAINT FK_B5F1AFE59395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE SET NULL");
        $this->raw("ALTER TABLE activities ADD CONSTRAINT FK_B5F1AFE5F60E2305 FOREIGN KEY (deal_id) REFERENCES deals (id) ON DELETE SET NULL");
        $this->raw("ALTER TABLE activities ADD CONSTRAINT FK_B5F1AFE5E7A1254A FOREIGN KEY (contact_id) REFERENCES contacts (id) ON DELETE SET NULL");
        $this->raw("ALTER TABLE contacts ADD CONSTRAINT FK_334015739395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id)");
        $this->raw("ALTER TABLE deals ADD CONSTRAINT FK_EF39849B9395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id)");
        $this->raw("ALTER TABLE emails ADD CONSTRAINT FK_4C81E852A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)");
        $this->raw("ALTER TABLE emails ADD CONSTRAINT FK_4C81E8529395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id)");
        $this->raw("ALTER TABLE emails ADD CONSTRAINT FK_4C81E852F60E2305 FOREIGN KEY (deal_id) REFERENCES deals (id)");
        $this->raw("ALTER TABLE emails ADD CONSTRAINT FK_4C81E8525DA0FB8 FOREIGN KEY (template_id) REFERENCES email_templates (id)");
        $this->raw("ALTER TABLE emails ADD CONSTRAINT FK_4C81E8521844E6B7 FOREIGN KEY (server_id) REFERENCES email_servers (id)");
        $this->raw("ALTER TABLE email_servers ADD CONSTRAINT FK_F24A9D38A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)");
        $this->raw("ALTER TABLE email_signatures ADD CONSTRAINT FK_B6C8C4D3A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)");
        $this->raw("ALTER TABLE email_templates ADD CONSTRAINT FK_6023E2A5A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)");
        $this->raw("ALTER TABLE invoices ADD CONSTRAINT FK_6A2F2F959395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id)");
        $this->raw("ALTER TABLE invoice_items ADD CONSTRAINT FK_DCC4B9F82989F1FD FOREIGN KEY (invoice_id) REFERENCES invoices (id)");
        $this->raw("ALTER TABLE projects ADD CONSTRAINT FK_5C93B3A49395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id)");
        $this->raw("ALTER TABLE projects ADD CONSTRAINT FK_5C93B3A4783E3463 FOREIGN KEY (manager_id) REFERENCES users (id)");
        $this->raw("ALTER TABLE project_events ADD CONSTRAINT FK_4423BC00166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id)");
        $this->raw("ALTER TABLE project_events ADD CONSTRAINT FK_4423BC00DE12AB56 FOREIGN KEY (created_by) REFERENCES users (id)");
        $this->raw("ALTER TABLE project_files ADD CONSTRAINT FK_156049C7166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id)");
        $this->raw("ALTER TABLE project_files ADD CONSTRAINT FK_156049C7E3E73126 FOREIGN KEY (uploaded_by) REFERENCES users (id)");
        $this->raw("ALTER TABLE project_time_entries ADD CONSTRAINT FK_53D8F6C0166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id)");
        $this->raw("ALTER TABLE project_time_entries ADD CONSTRAINT FK_53D8F6C0A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)");
        $this->raw("ALTER TABLE workflows ADD CONSTRAINT FK_EFBFBFC2DE12AB56 FOREIGN KEY (created_by) REFERENCES users (id)");
        $this->raw("ALTER TABLE workflow_executions ADD CONSTRAINT FK_402F685B2C7C2CBA FOREIGN KEY (workflow_id) REFERENCES workflows (id)");

    }

    protected function rollback(): void
    {
        $this->raw("DROP TABLE IF EXISTS `workflow_executions`");
        $this->raw("DROP TABLE IF EXISTS `workflows`");
        $this->raw("DROP TABLE IF EXISTS `users`");
        $this->raw("DROP TABLE IF EXISTS `project_time_entries`");
        $this->raw("DROP TABLE IF EXISTS `project_files`");
        $this->raw("DROP TABLE IF EXISTS `project_events`");
        $this->raw("DROP TABLE IF EXISTS `projects`");
        $this->raw("DROP TABLE IF EXISTS `invoice_items`");
        $this->raw("DROP TABLE IF EXISTS `invoices`");
        $this->raw("DROP TABLE IF EXISTS `email_templates`");
        $this->raw("DROP TABLE IF EXISTS `email_signatures`");
        $this->raw("DROP TABLE IF EXISTS `email_servers`");
        $this->raw("DROP TABLE IF EXISTS `emails`");
        $this->raw("DROP TABLE IF EXISTS `deals`");
        $this->raw("DROP TABLE IF EXISTS `customers`");
        $this->raw("DROP TABLE IF EXISTS `contacts`");
        $this->raw("DROP TABLE IF EXISTS `activities`");
    }
}
