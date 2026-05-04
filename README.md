# Web-Based Parenting Seminar Attendance Management System

Vanilla HTML, CSS, JavaScript, PHP, and MySQL implementation using an MVC-style backend.

## Setup

1. Import `database/schema.sql` in phpMyAdmin or MySQL.
2. Confirm `config/db.php` uses your local MySQL credentials.
3. Open `http://localhost/crudajax/public/`.

Default accounts:

- Admin: `admin@example.com` / `password`
- Staff: `staff@example.com` / `password`

If login still says invalid credentials after importing an older schema, run this SQL:

```sql
UPDATE admins
SET password_hash = '$2y$10$ds76IjA/qdI7YINZm/ZF7uZw.R9DTdbXFra4.uNjQwXLrsgb0zBOu',
    is_active = 1
WHERE email IN ('admin@example.com', 'staff@example.com');
```

The same repair script is available at `database/fix_default_admins.sql`.

Local debugging is enabled in `config/app.php`. API errors are also written to `storage/logs/app.log`.

## CSV Format

Use this header:

```csv
student_no,first_name,last_name,grade_level,section,parent_name,parent_phone
```

## SMS Gateway

SMS delivery is logged as `simulated` until `SMS_API_URL` and `SMS_API_KEY` are configured. The gateway request belongs in `app/Services/SmsService.php`.
