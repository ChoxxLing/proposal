CREATE DATABASE IF NOT EXISTS parenting_seminar
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE parenting_seminar;

CREATE TABLE IF NOT EXISTS admins (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin', 'staff') NOT NULL DEFAULT 'staff',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS students (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_no VARCHAR(60) NOT NULL UNIQUE,
  first_name VARCHAR(120) NOT NULL,
  last_name VARCHAR(120) NOT NULL,
  grade_level VARCHAR(40) NULL,
  section VARCHAR(80) NULL,
  parent_name VARCHAR(160) NOT NULL,
  parent_phone VARCHAR(40) NOT NULL,
  qr_token VARCHAR(64) NOT NULL UNIQUE,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_by INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_students_created_by
    FOREIGN KEY (created_by) REFERENCES admins(id)
    ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS seminars (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(180) NOT NULL,
  description TEXT NULL,
  seminar_date DATE NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  venue VARCHAR(180) NOT NULL,
  status ENUM('scheduled', 'completed', 'archived') NOT NULL DEFAULT 'scheduled',
  created_by INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_seminars_created_by
    FOREIGN KEY (created_by) REFERENCES admins(id)
    ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS attendance (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  seminar_id INT UNSIGNED NOT NULL,
  student_id INT UNSIGNED NOT NULL,
  status ENUM('present', 'absent') NOT NULL,
  check_in_method ENUM('qr', 'manual', 'auto_absent') NOT NULL,
  checked_in_by INT UNSIGNED NULL,
  checked_in_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_attendance_student_seminar (seminar_id, student_id),
  KEY idx_attendance_status (seminar_id, status),
  CONSTRAINT fk_attendance_seminar
    FOREIGN KEY (seminar_id) REFERENCES seminars(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_attendance_student
    FOREIGN KEY (student_id) REFERENCES students(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_attendance_admin
    FOREIGN KEY (checked_in_by) REFERENCES admins(id)
    ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS sms_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  seminar_id INT UNSIGNED NOT NULL,
  student_id INT UNSIGNED NOT NULL,
  phone VARCHAR(40) NOT NULL,
  message TEXT NOT NULL,
  status ENUM('queued', 'sent', 'failed', 'simulated') NOT NULL DEFAULT 'queued',
  provider_response TEXT NULL,
  sent_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_sms_logs_seminar
    FOREIGN KEY (seminar_id) REFERENCES seminars(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_sms_logs_student
    FOREIGN KEY (student_id) REFERENCES students(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO admins (name, email, password_hash, role)
VALUES
  ('System Administrator', 'admin@example.com', '$2y$10$ds76IjA/qdI7YINZm/ZF7uZw.R9DTdbXFra4.uNjQwXLrsgb0zBOu', 'admin'),
  ('Attendance Staff', 'staff@example.com', '$2y$10$ds76IjA/qdI7YINZm/ZF7uZw.R9DTdbXFra4.uNjQwXLrsgb0zBOu', 'staff')
ON DUPLICATE KEY UPDATE
  name = VALUES(name),
  password_hash = VALUES(password_hash),
  role = VALUES(role),
  is_active = 1;
