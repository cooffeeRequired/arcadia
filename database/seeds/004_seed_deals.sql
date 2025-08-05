INSERT INTO deals (customer_id, title, description, value, stage, probability, expected_close_date, status, created_at, updated_at) VALUES
(1, 'CRM implementace', 'Implementace CRM systému pro Novák s.r.o.', 150000.00, 'negotiation', 0.75, '2024-03-15', 'active', NOW(), NOW()),
(2, 'Produktová licence', 'Roční licence na CRM systém', 25000.00, 'proposal', 0.50, '2024-02-28', 'active', NOW(), NOW()),
(3, 'Partnerská smlouva', 'Dlouhodobá partnerská spolupráce', 50000.00, 'qualification', 0.30, '2024-04-30', 'active', NOW(), NOW()),
(5, 'Technická podpora', 'Roční technická podpora', 35000.00, 'prospecting', 0.20, '2024-05-15', 'active', NOW(), NOW()),
(1, 'Rozšíření systému', 'Rozšíření CRM o moduly', 75000.00, 'closed_won', 1.00, '2024-01-15', 'won', NOW(), NOW()); 