-- Migration: Course Features (Duration, Partial Payments, OTP)
-- Date: 2026-06-26

ALTER TABLE `courses` ADD COLUMN IF NOT EXISTS `course_duration` INT DEFAULT 0 COMMENT 'Duration in months, 0 for unlimited';
ALTER TABLE `courses` ADD COLUMN IF NOT EXISTS `allow_partial_payment` TINYINT(1) DEFAULT 0;
ALTER TABLE `courses` ADD COLUMN IF NOT EXISTS `min_installment` DECIMAL(10, 2) DEFAULT 0.00;

ALTER TABLE `course_videos` ADD COLUMN IF NOT EXISTS `video_access_duration` INT DEFAULT 0 COMMENT 'Duration in minutes, 0 for unlimited';

ALTER TABLE `payments` ADD COLUMN IF NOT EXISTS `payment_type` ENUM('Partial', 'Full') DEFAULT 'Full';

ALTER TABLE `enrollments` ADD COLUMN IF NOT EXISTS `expiry_date` DATETIME DEFAULT NULL;
ALTER TABLE `enrollments` ADD COLUMN IF NOT EXISTS `status` ENUM('Pending', 'Active', 'Expired') DEFAULT 'Pending';

CREATE TABLE IF NOT EXISTS `video_otp_sessions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `video_id` INT NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`video_id`) REFERENCES `course_videos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
