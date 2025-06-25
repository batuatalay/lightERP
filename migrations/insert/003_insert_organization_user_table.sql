INSERT INTO organization_user (organization_id, user_id, role, updated_at) VALUES
-- TechCorp (org_id: 1)
(1, 1, 'admin', NOW()),  -- Ahmet admin
(1, 2, 'user', NOW()),   -- Elif user
(1, 3, 'user', NOW()),   -- Mehmet user

-- DataFlow (org_id: 2)
(2, 4, 'admin', NOW()),  -- Zeynep admin
(2, 5, 'user', NOW()),   -- Can user
(2, 6, 'user', NOW()),   -- Selin user

-- CloudBridge (org_id: 3)
(3, 7, 'admin', NOW()),  -- Burak admin
(3, 8, 'user', NOW()),   -- Ayşe user
(3, 9, 'user', NOW());   -- Emre user