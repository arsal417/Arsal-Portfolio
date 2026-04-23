-- ============================================================
--  ARSALAN ABBAS PORTFOLIO — DATABASE SETUP
--  Run this once on your web server MySQL/phpMyAdmin
-- ============================================================

CREATE DATABASE IF NOT EXISTS arsalan_portfolio
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE arsalan_portfolio;

-- ---- CONTACT SUBMISSIONS ----
CREATE TABLE IF NOT EXISTS contacts (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(255)    NOT NULL,
  email       VARCHAR(255)    NOT NULL,
  service     VARCHAR(255)    DEFAULT NULL,
  budget      VARCHAR(100)    DEFAULT NULL,
  message     TEXT            NOT NULL,
  status      ENUM('new','read','replied') DEFAULT 'new',
  ip_address  VARCHAR(45)     DEFAULT NULL,
  created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---- REPLIES SENT TO CLIENTS ----
CREATE TABLE IF NOT EXISTS replies (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  contact_id  INT             NOT NULL,
  reply_text  TEXT            NOT NULL,
  sent_at     TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---- CLIENT REVIEWS / TESTIMONIALS ----
CREATE TABLE IF NOT EXISTS reviews (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(255)    NOT NULL,
  email       VARCHAR(255)    NOT NULL,
  role        VARCHAR(255)    DEFAULT NULL,
  rating      TINYINT         DEFAULT 5,
  review_text TEXT            NOT NULL,
  status      ENUM('pending','approved','rejected') DEFAULT 'pending',
  created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---- ADMIN USERS ----
CREATE TABLE IF NOT EXISTS admins (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  username    VARCHAR(100)    NOT NULL UNIQUE,
  password    VARCHAR(255)    NOT NULL,
  created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---- DEFAULT ADMIN (username: arsalan | password: Admin@2026) ----
-- You can change the password hash by running: php -r "echo password_hash('YourNewPassword', PASSWORD_DEFAULT);"
INSERT INTO admins (username, password) VALUES
('arsalan', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
-- NOTE: The above hash is for password "password" — change it immediately after setup!

-- ---- INDEXES ----
CREATE INDEX idx_contacts_status    ON contacts(status);
CREATE INDEX idx_contacts_email     ON contacts(email);
CREATE INDEX idx_reviews_status     ON reviews(status);
