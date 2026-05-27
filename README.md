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

- Public site: `http://localhost:8080/`
- Admin login: `http://localhost:8080/admin.php`
- HTTPS admin login: `https://localhost:8443/admin.php`
- phpMyAdmin: `http://localhost:8081`

When Docker HTTPS certificates are set up, the public page's Login links upgrade from `http://<computer-ip>:8080/` to `https://<computer-ip>:8443/admin.php` automatically.

Docker database credentials:

- Host: `db`
- Database: `parenting_seminar`
- User: `app`
- Password: `app_password`

The MySQL container imports `database/schema.sql` automatically the first time the `db_data` volume is created. If the volume already exists, the schema import will not run again unless you recreate the volume.
If you already have a Docker database volume, recreate it or update the existing `admins` rows manually so the default account emails below are applied.

Default accounts:

- Admin: `admin@admin.com` / `password`
- Staff: `staff@admin.com` / `password`

Local debugging is enabled in `config/app.php`. API errors are also written to `storage/logs/app.log`.

## CSV Format

Use this header:

```csv
student_no,first_name,last_name,batch_num,section,parent_name,parent_phone
```

## SMS Gateway

SMS delivery is logged as `simulated` until `SMS_API_URL` and `SMS_API_KEY` are configured. The gateway request belongs in `app/Services/SmsService.php`.

## Testing QR Scanner on Phone

Phone browsers require HTTPS for camera access. The Docker app exposes HTTPS on port `8443`. If `docker/certs/local-cert.pem` and `docker/certs/local-key.pem` exist, Docker uses them. If they do not exist, Docker automatically creates a self-signed certificate when the app container starts.

For phone scanning, `mkcert` is still recommended because it can create a trusted certificate for each PC's LAN IP address. Every computer has its own LAN IP address, so run the HTTPS setup script on each PC where you use this project.

1. Install `mkcert` on your computer.
2. Connect your phone and computer to the same WiFi network.
3. Generate a trusted Docker certificate for this PC:

```bash
powershell -ExecutionPolicy Bypass -File scripts/setup-https.ps1
```

If the script chooses the wrong IP address, rerun it with the IP your phone can reach:

```bash
powershell -ExecutionPolicy Bypass -File scripts/setup-https.ps1 -IpAddress <computer-ip>
```

Manual fallback:

```bash
mkcert -install
mkcert -cert-file docker/certs/local-cert.pem -key-file docker/certs/local-key.pem localhost 127.0.0.1 <computer-ip>
```

4. Install and trust the mkcert root CA on your phone. Find the CA location on your computer with:

```bash
mkcert -CAROOT
```

5. Rebuild and start Docker:

```bash
docker compose up -d --build
```

If your Docker installation uses the legacy Compose command, run `docker-compose up -d --build` instead.

6. Open the public page on your phone and tap Login:

```text
http://<computer-ip>:8080/
```

The Login link should move to HTTPS automatically:

```text
https://<computer-ip>:8443/admin.php
```

You can also open the HTTPS admin URL printed by the setup script directly. Do not use `localhost` on your phone because that points to the phone itself, not your computer. If the browser blocks the camera, confirm the mkcert CA is trusted on the phone, reload the HTTPS page, and allow camera permission.

Self-signed fallback: if you skip `mkcert`, Docker still serves HTTPS at `https://localhost:8443/admin.php`. Your browser will show a certificate warning because the certificate is not trusted. This is useful for quick desktop testing, but `mkcert` is better for phone camera scanning.

The scanner uses the browser `BarcodeDetector` API when available and falls back to the local `public/assets/js/vendor/jsqr.min.js` decoder for browsers that do not support it, including many iPhone/Safari versions.
