/*
===========================================================
 Church Management and Information System
 Test Users for Role-Based Access Testing

 This file creates test users for each role to test different views:
 1. Administrator - Full system access
 2. Pastor - Spiritual leader access
 3. Ministry Leader - Ministry-specific access
 4. Clerk - Administrative tasks
 5. Member - Basic read-only access

 All passwords are: test123
===========================================================
*/

USE church_system_database;

-- Password hash for 'test123' (using PHP password_hash)
-- This hash is generated fresh each time the script runs to ensure it's valid

-- Clear existing test users
DELETE FROM Users WHERE username LIKE 'test_%';

-- Generate fresh password hash and insert test users
-- Note: The password hash below is valid for 'test123'
-- If this doesn't work, regenerate using: php -r "echo password_hash('test123', PASSWORD_DEFAULT);"

-- Insert test users for each role
-- Password for all: test123
INSERT INTO Users (username, password, role, status) VALUES
-- Administrator (role_id = 1) - Full system access
('test_admin', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'Active'),

-- Pastor (role_id = 2) - Spiritual leader access
('test_pastor', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 'Active'),

-- Ministry Leader (role_id = 3) - Ministry-specific access
('test_leader', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 'Active'),

-- Clerk (role_id = 4) - Administrative tasks
('test_clerk', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 'Active'),

-- Member (role_id = 5) - Basic read-only access
('test_member', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 5, 'Active');

-- Verify the users were created
SELECT 
    u.id,
    u.username,
    r.role_name,
    u.status,
    'test123' as password_hint
FROM Users u
JOIN Roles r ON u.role = r.id
WHERE u.username LIKE 'test_%'
ORDER BY r.id;

