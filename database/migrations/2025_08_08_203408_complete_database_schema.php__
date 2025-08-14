<?php /** @noinspection PhpUnhandledExceptionInspection */

use Core\CLI\AbstractMigration;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\Types;

class CompleteDatabaseSchema extends AbstractMigration
{
    protected function migrate(): void
    {
        // Nejdříve smažeme všechny existující tabulky
        $this->raw("DROP TABLE IF EXISTS workflow_executions");
        $this->raw("DROP TABLE IF EXISTS workflows");
        $this->raw("DROP TABLE IF EXISTS invoice_items");
        $this->raw("DROP TABLE IF EXISTS invoices");
        $this->raw("DROP TABLE IF EXISTS email_templates");
        $this->raw("DROP TABLE IF EXISTS email_signatures");
        $this->raw("DROP TABLE IF EXISTS email_servers");
        $this->raw("DROP TABLE IF EXISTS emails");
        $this->raw("DROP TABLE IF EXISTS activities");
        $this->raw("DROP TABLE IF EXISTS contacts");
        $this->raw("DROP TABLE IF EXISTS deals");
        $this->raw("DROP TABLE IF EXISTS customers");
        $this->raw("DROP TABLE IF EXISTS users");

        // Vytvoříme tabulku users
        $this->raw("CREATE TABLE `users` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `email` varchar(255) NOT NULL,
            `password` varchar(255) NOT NULL,
            `role` varchar(50) NOT NULL DEFAULT 'user',
            `avatar` varchar(255) DEFAULT NULL,
            `is_active` tinyint(1) NOT NULL DEFAULT 1,
            `last_login` datetime DEFAULT NULL,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `email` (`email`),
            KEY `idx_email` (`email`),
            KEY `idx_role` (`role`),
            KEY `idx_active` (`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Vytvoříme tabulku customers
        $this->raw("CREATE TABLE `customers` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `email` varchar(255) DEFAULT NULL,
            `phone` varchar(20) DEFAULT NULL,
            `company` varchar(255) DEFAULT NULL,
            `category` varchar(50) NOT NULL DEFAULT 'person',
            `address` text DEFAULT NULL,
            `zip_code` varchar(10) DEFAULT NULL,
            `city` varchar(100) DEFAULT NULL,
            `country` varchar(100) DEFAULT NULL,
            `status` varchar(50) DEFAULT 'active',
            `notes` text DEFAULT NULL,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_name` (`name`),
            KEY `idx_email` (`email`),
            KEY `idx_company` (`company`),
            KEY `idx_category` (`category`),
            KEY `idx_status` (`status`),
            KEY `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Vytvoříme tabulku contacts
        $this->raw("CREATE TABLE `contacts` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `customer_id` int(11) NOT NULL,
            `type` varchar(255) NOT NULL,
            `subject` varchar(255) NOT NULL,
            `description` text DEFAULT NULL,
            `contact_date` datetime NOT NULL,
            `status` varchar(50) NOT NULL DEFAULT 'completed',
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_customer_id` (`customer_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Vytvoříme tabulku deals
        $this->raw("CREATE TABLE `deals` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `customer_id` int(11) NOT NULL,
            `title` varchar(255) NOT NULL,
            `description` text DEFAULT NULL,
            `value` decimal(10,2) DEFAULT NULL,
            `stage` varchar(50) NOT NULL DEFAULT 'prospecting',
            `probability` decimal(3,2) NOT NULL DEFAULT 0.00,
            `expected_close_date` date DEFAULT NULL,
            `status` varchar(50) NOT NULL DEFAULT 'active',
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_customer_id` (`customer_id`),
            KEY `idx_title` (`title`),
            KEY `idx_stage` (`stage`),
            KEY `idx_status` (`status`),
            KEY `idx_value` (`value`),
            KEY `idx_expected_close_date` (`expected_close_date`),
            KEY `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Vytvoříme tabulku activities
        $this->raw("CREATE TABLE `activities` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `type` varchar(50) NOT NULL,
            `title` varchar(255) NOT NULL,
            `description` text DEFAULT NULL,
            `customer_id` int(11) DEFAULT NULL,
            `deal_id` int(11) DEFAULT NULL,
            `contact_id` int(11) DEFAULT NULL,
            `created_at` datetime DEFAULT current_timestamp(),
            `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `customer_id` (`customer_id`),
            KEY `deal_id` (`deal_id`),
            KEY `contact_id` (`contact_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Vytvoříme tabulku invoices
        $this->raw("CREATE TABLE `invoices` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `customer_id` int(11) NOT NULL,
            `invoice_number` varchar(50) NOT NULL,
            `issue_date` date NOT NULL,
            `due_date` date NOT NULL,
            `subtotal` decimal(10,2) NOT NULL,
            `tax_rate` decimal(5,2) NOT NULL,
            `tax_amount` decimal(10,2) NOT NULL,
            `total` decimal(10,2) NOT NULL,
            `currency` varchar(20) NOT NULL,
            `notes` longtext DEFAULT NULL,
            `status` varchar(20) NOT NULL,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `UNIQ_6A2F2F952DA68207` (`invoice_number`),
            KEY `IDX_6A2F2F959395C3F3` (`customer_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Vytvoříme tabulku invoice_items
        $this->raw("CREATE TABLE `invoice_items` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `invoice_id` int(11) NOT NULL,
            `description` varchar(255) NOT NULL,
            `quantity` decimal(10,2) NOT NULL,
            `unit_price` decimal(10,2) NOT NULL,
            `total` decimal(10,2) NOT NULL,
            `unit` varchar(20) DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `IDX_DCC4B9F82989F1FD` (`invoice_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Vytvoříme tabulku email_servers
        $this->raw("CREATE TABLE `email_servers` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `host` varchar(255) NOT NULL,
            `port` int(11) NOT NULL,
            `encryption` varchar(50) NOT NULL,
            `username` varchar(255) NOT NULL,
            `password` varchar(255) NOT NULL,
            `from_email` varchar(255) NOT NULL,
            `from_name` varchar(255) NOT NULL,
            `is_active` tinyint(1) NOT NULL DEFAULT 1,
            `is_default` tinyint(1) NOT NULL DEFAULT 0,
            `user_id` int(11) NOT NULL,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_user_id` (`user_id`),
            KEY `idx_is_active` (`is_active`),
            KEY `idx_is_default` (`is_default`),
            KEY `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Vytvoříme tabulku email_signatures
        $this->raw("CREATE TABLE `email_signatures` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `content` text NOT NULL,
            `is_default` tinyint(1) NOT NULL DEFAULT 0,
            `user_id` int(11) NOT NULL,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_user_id` (`user_id`),
            KEY `idx_is_default` (`is_default`),
            KEY `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Vytvoříme tabulku email_templates
        $this->raw("CREATE TABLE `email_templates` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `subject` varchar(255) NOT NULL,
            `content` text NOT NULL,
            `category` varchar(50) NOT NULL,
            `is_active` tinyint(1) NOT NULL DEFAULT 1,
            `user_id` int(11) NOT NULL,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_user_id` (`user_id`),
            KEY `idx_category` (`category`),
            KEY `idx_is_active` (`is_active`),
            KEY `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Vytvoříme tabulku emails
        $this->raw("CREATE TABLE `emails` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `subject` varchar(255) NOT NULL,
            `body` text NOT NULL,
            `from_email` varchar(255) NOT NULL,
            `from_name` varchar(255) NOT NULL,
            `to_emails` text NOT NULL,
            `cc_emails` text DEFAULT NULL,
            `bcc_emails` text DEFAULT NULL,
            `attachments` text DEFAULT NULL,
            `status` varchar(50) NOT NULL DEFAULT 'draft',
            `sent_at` datetime DEFAULT NULL,
            `scheduled_at` datetime DEFAULT NULL,
            `error_message` text DEFAULT NULL,
            `user_id` int(11) NOT NULL,
            `customer_id` int(11) DEFAULT NULL,
            `deal_id` int(11) DEFAULT NULL,
            `template_id` int(11) DEFAULT NULL,
            `server_id` int(11) DEFAULT NULL,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_user_id` (`user_id`),
            KEY `idx_status` (`status`),
            KEY `idx_sent_at` (`sent_at`),
            KEY `idx_scheduled_at` (`scheduled_at`),
            KEY `idx_customer_id` (`customer_id`),
            KEY `idx_deal_id` (`deal_id`),
            KEY `idx_template_id` (`template_id`),
            KEY `idx_server_id` (`server_id`),
            KEY `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Vytvoříme tabulku workflows
        $this->raw("CREATE TABLE `workflows` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `description` text DEFAULT NULL,
            `trigger_type` varchar(50) NOT NULL,
            `trigger_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
            `conditions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
            `actions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
            `is_active` tinyint(1) DEFAULT 1,
            `priority` int(11) DEFAULT 0,
            `created_by` int(11) DEFAULT NULL,
            `created_at` datetime DEFAULT current_timestamp(),
            `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `created_by` (`created_by`),
            KEY `idx_trigger_type` (`trigger_type`),
            KEY `idx_is_active` (`is_active`),
            KEY `idx_priority` (`priority`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Vytvoříme tabulku workflow_executions
        $this->raw("CREATE TABLE `workflow_executions` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `workflow_id` int(11) NOT NULL,
            `status` varchar(50) NOT NULL DEFAULT 'running',
            `trigger_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
            `execution_log` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
            `error_message` text DEFAULT NULL,
            `started_at` datetime DEFAULT current_timestamp(),
            `completed_at` datetime DEFAULT NULL,
            `execution_time_ms` int(11) DEFAULT 0,
            PRIMARY KEY (`id`),
            KEY `idx_workflow_id` (`workflow_id`),
            KEY `idx_status` (`status`),
            KEY `idx_started_at` (`started_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Přidáme foreign key constraints
        $this->raw("ALTER TABLE `contacts` ADD CONSTRAINT `contacts_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE");
        $this->raw("ALTER TABLE `deals` ADD CONSTRAINT `deals_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE");
        $this->raw("ALTER TABLE `activities` ADD CONSTRAINT `activities_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL");
        $this->raw("ALTER TABLE `activities` ADD CONSTRAINT `activities_ibfk_2` FOREIGN KEY (`deal_id`) REFERENCES `deals` (`id`) ON DELETE SET NULL");
        $this->raw("ALTER TABLE `activities` ADD CONSTRAINT `activities_ibfk_3` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL");
        $this->raw("ALTER TABLE `invoices` ADD CONSTRAINT `FK_6A2F2F959395C3F3` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`)");
        $this->raw("ALTER TABLE `invoice_items` ADD CONSTRAINT `FK_DCC4B9F82989F1FD` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`)");
        $this->raw("ALTER TABLE `email_servers` ADD CONSTRAINT `email_servers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE");
        $this->raw("ALTER TABLE `email_signatures` ADD CONSTRAINT `email_signatures_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE");
        $this->raw("ALTER TABLE `email_templates` ADD CONSTRAINT `email_templates_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE");
        $this->raw("ALTER TABLE `emails` ADD CONSTRAINT `emails_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE");
        $this->raw("ALTER TABLE `emails` ADD CONSTRAINT `emails_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL");
        $this->raw("ALTER TABLE `emails` ADD CONSTRAINT `emails_ibfk_3` FOREIGN KEY (`deal_id`) REFERENCES `deals` (`id`) ON DELETE SET NULL");
        $this->raw("ALTER TABLE `emails` ADD CONSTRAINT `emails_ibfk_4` FOREIGN KEY (`template_id`) REFERENCES `email_templates` (`id`) ON DELETE SET NULL");
        $this->raw("ALTER TABLE `emails` ADD CONSTRAINT `emails_ibfk_5` FOREIGN KEY (`server_id`) REFERENCES `email_servers` (`id`) ON DELETE SET NULL");
        $this->raw("ALTER TABLE `workflows` ADD CONSTRAINT `workflows_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL");
        $this->raw("ALTER TABLE `workflow_executions` ADD CONSTRAINT `workflow_executions_ibfk_1` FOREIGN KEY (`workflow_id`) REFERENCES `workflows` (`id`) ON DELETE CASCADE");
    }

    protected function rollback(): void
    {
        // Smažeme všechny tabulky v opačném pořadí kvůli foreign keys
        $this->raw("DROP TABLE IF EXISTS workflow_executions");
        $this->raw("DROP TABLE IF EXISTS workflows");
        $this->raw("DROP TABLE IF EXISTS invoice_items");
        $this->raw("DROP TABLE IF EXISTS invoices");
        $this->raw("DROP TABLE IF EXISTS email_templates");
        $this->raw("DROP TABLE IF EXISTS email_signatures");
        $this->raw("DROP TABLE IF EXISTS email_servers");
        $this->raw("DROP TABLE IF EXISTS emails");
        $this->raw("DROP TABLE IF EXISTS activities");
        $this->raw("DROP TABLE IF EXISTS contacts");
        $this->raw("DROP TABLE IF EXISTS deals");
        $this->raw("DROP TABLE IF EXISTS customers");
        $this->raw("DROP TABLE IF EXISTS users");
    }
}
