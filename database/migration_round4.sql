-- Run once: mysql -u root mamaove < database/migration_round4.sql

-- --- Email-based 2FA (alongside the existing TOTP option) ---
ALTER TABLE `users`
    ADD COLUMN `two_factor_method` ENUM('totp','email') DEFAULT NULL AFTER `two_factor_enabled`,
    ADD COLUMN `email_otp_code` VARCHAR(10) DEFAULT NULL AFTER `two_factor_method`,
    ADD COLUMN `email_otp_expires` DATETIME DEFAULT NULL AFTER `email_otp_code`;

-- --- Session tracking (for the admin "active sessions" view + revocation) ---
CREATE TABLE IF NOT EXISTS `user_sessions` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,
    `session_id_hash` VARCHAR(64) NOT NULL,
    `role` VARCHAR(20) DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `last_activity_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `revoked_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_session_hash` (`session_id_hash`),
    KEY `idx_sessions_user` (`user_id`),
    KEY `idx_sessions_activity` (`last_activity_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --- Delivery zones: curated Kampala Metro + real towns from your order
-- history, not a raw import of all 146 Ugandan districts (a bakery isn't
-- delivering to Karamoja). All seeded INACTIVE with fee=0 — nothing shows
-- on the client checkout until you open admin/delivery_locations.php,
-- assign a real fee, and activate it.
ALTER TABLE `delivery_locations` ADD UNIQUE KEY `idx_delivery_name` (`name`);

INSERT IGNORE INTO `delivery_locations` (`name`, `fee`, `is_active`) VALUES
('Kampala Central', 0, 0),
('Nakasero', 0, 0),
('Old Kampala', 0, 0),
('Kamwokya', 0, 0),
('Kololo', 0, 0),
('Bukoto', 0, 0),
('Naguru', 0, 0),
('Ntinda', 0, 0),
('Kisaasi', 0, 0),
('Kyanja', 0, 0),
('Najjera', 0, 0),
('Kiwatule', 0, 0),
('Naalya', 0, 0),
('Bweyogerere', 0, 0),
('Kireka', 0, 0),
('Namugongo', 0, 0),
('Kyaliwajjala', 0, 0),
('Kira Town', 0, 0),
('Nakawa', 0, 0),
('Luzira', 0, 0),
('Butabika', 0, 0),
('Bugolobi', 0, 0),
('Mbuya', 0, 0),
('Banda', 0, 0),
('Kinawataka', 0, 0),
('Makindye', 0, 0),
('Nsambya', 0, 0),
('Kabalagala', 0, 0),
('Muyenga', 0, 0),
('Ggaba', 0, 0),
('Bunga', 0, 0),
('Kansanga', 0, 0),
('Najjanankumbi', 0, 0),
('Salaama', 0, 0),
('Kawempe', 0, 0),
('Bwaise', 0, 0),
('Kalerwe', 0, 0),
('Mpererwe', 0, 0),
('Kyebando', 0, 0),
('Kazo', 0, 0),
('Komamboga', 0, 0),
('Rubaga', 0, 0),
('Mengo', 0, 0),
('Namirembe', 0, 0),
('Nateete', 0, 0),
('Lubaga', 0, 0),
('Busega', 0, 0),
('Ndeeba', 0, 0),
('Kasubi', 0, 0),
('Wandegeya', 0, 0),
('Makerere', 0, 0),
('Mulago', 0, 0),
('Entebbe Road / Kajjansi', 0, 0),
('Entebbe Town', 0, 0),
('Najjera - Wakiso', 0, 0),
('Nansana', 0, 0),
('Namasuba', 0, 0),
('Ssabagabo', 0, 0),
('Gayaza', 0, 0),
('Matugga', 0, 0),
('Wakiso Town', 0, 0),
('Mukono Town', 0, 0),
('Seeta', 0, 0),
('Namanve', 0, 0),
('Jinja Town', 0, 0);
