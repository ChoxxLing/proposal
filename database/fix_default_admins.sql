USE parenting_seminar;

UPDATE admins
SET password_hash = '$2y$10$ds76IjA/qdI7YINZm/ZF7uZw.R9DTdbXFra4.uNjQwXLrsgb0zBOu',
    is_active = 1
WHERE email IN ('admin@example.com', 'staff@example.com');

SELECT id, name, email, role, is_active, LEFT(password_hash, 7) AS hash_prefix
FROM admins
WHERE email IN ('admin@example.com', 'staff@example.com');
