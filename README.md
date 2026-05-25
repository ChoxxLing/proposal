# Web-Based Parenting Seminar Attendance Management System

Vanilla HTML, CSS, JavaScript, PHP, and MySQL implementation using an MVC-style backend.

## Setup

1. Import `database/schema.sql` in phpMyAdmin or MySQL.
2. Confirm `config/db.php` uses your local MySQL credentials.
3. Open `http://localhost/<foldername>/public/`.

## Docker Setup

Run the app locally with PHP, Apache, MySQL, and phpMyAdmin:

```bash
docker compose up -d --build
```

If your Docker installation uses the legacy Compose command, run `docker-compose up -d --build` instead.

Open the app:

- Public site: `http://localhost:8080/project/public/`
- Admin login: `http://localhost:8080/project/public/admin.php`
- phpMyAdmin: `http://localhost:8081`

Docker database credentials:

- Host: `db`
- Database: `parenting_seminar`
- User: `app`
- Password: `app_password`

The MySQL container imports `database/schema.sql` automatically the first time the `db_data` volume is created. If the volume already exists, the schema import will not run again unless you recreate the volume.

Default accounts:

- Admin: `admin@example.com` / `password`
- Staff: `staff@example.com` / `password`

Local debugging is enabled in `config/app.php`. API errors are also written to `storage/logs/app.log`.

## CSV Format

Use this header:

```csv
student_no,first_name,last_name,batch_num,section,parent_name,parent_phone
```

## SMS Gateway

SMS delivery is logged as `simulated` until `SMS_API_URL` and `SMS_API_KEY` are configured. The gateway request belongs in `app/Services/SmsService.php`.

## Testing QR Scanner on Phone

1. Connect your phone and computer to the same WiFi network.
2. Find your computer's local IP address, then open the admin page on your phone:

```text
https://<computer-ip>/<foldername>/public/admin.php
```

Do not use `localhost` on your phone because that points to the phone itself, not your computer.

Most phone browsers block camera access on normal `http://<computer-ip>` pages. Use HTTPS for phone testing. If the browser blocks the camera, the QR Attendance page will show a warning and you can still paste the QR token manually.

The scanner uses the browser `BarcodeDetector` API when available and falls back to the local `public/assets/js/vendor/jsqr.min.js` decoder for browsers that do not support it, including many iPhone/Safari versions.
