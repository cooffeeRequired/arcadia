INSERT INTO contacts (customer_id, type, subject, description, contact_date, status, created_at, updated_at) VALUES
(1, 'email', 'Nabídka produktů', 'Odeslána nabídka na CRM systém', NOW() - INTERVAL 2 DAY, 'completed', NOW(), NOW()),
(1, 'phone', 'Kontrola nabídky', 'Zákazník má zájem o demo', NOW() - INTERVAL 1 DAY, 'completed', NOW(), NOW()),
(2, 'meeting', 'Prezentace produktů', 'Schůzka v kanceláři', NOW() - INTERVAL 3 DAY, 'completed', NOW(), NOW()),
(3, 'email', 'Partnerská spolupráce', 'Návrh na spolupráci', NOW() - INTERVAL 5 DAY, 'completed', NOW(), NOW()),
(4, 'phone', 'První kontakt', 'Zájem o informace', NOW() - INTERVAL 1 DAY, 'completed', NOW(), NOW()),
(5, 'meeting', 'Technická konzultace', 'Diskuse o implementaci', NOW() + INTERVAL 2 DAY, 'scheduled', NOW(), NOW()); 