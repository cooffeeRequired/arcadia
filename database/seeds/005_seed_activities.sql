-- Vložení ukázkových aktivit pro kontakty
INSERT INTO activities (type, title, description, customer_id, contact_id)
SELECT
    'contact',
    CONCAT('Nový kontakt: ', c.subject),
    CONCAT('Kontakt typu: ', c.type),
    c.customer_id,
    c.id
FROM contacts c
INNER JOIN customers cu ON c.customer_id = cu.id
WHERE c.customer_id IS NOT NULL
LIMIT 10;

-- Vložení ukázkových aktivit pro obchody
INSERT INTO activities (type, title, description, customer_id, deal_id)
SELECT
    'deal',
    CONCAT('Nový obchod: ', d.title),
    CONCAT('Hodnota: ', FORMAT(d.value, 0), ' Kč - Pravděpodobnost: ', d.probability * 100, '%'),
    d.customer_id,
    d.id
FROM deals d
INNER JOIN customers cu ON d.customer_id = cu.id
WHERE d.customer_id IS NOT NULL
LIMIT 10;

-- Vložení ukázkových aktivit pro zákazníky
INSERT INTO activities (type, title, description, customer_id)
SELECT
    'customer',
    CONCAT('Nový zákazník: ', c.name),
    CASE
        WHEN c.company IS NOT NULL THEN CONCAT('Společnost: ', c.company)
        ELSE 'Soukromá osoba'
    END,
    c.id
FROM customers c
ORDER BY c.id ASC
LIMIT 10;
