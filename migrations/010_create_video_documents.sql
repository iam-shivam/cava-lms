-- Migration: Create Video Documents Table
-- Date: 2026-07-05

CREATE TABLE IF NOT EXISTS `video_documents` (
    `id` CHAR(36) PRIMARY KEY,
    `video_id` CHAR(36) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(255) NOT NULL,
    `file_type` VARCHAR(50) NOT NULL,
    `file_size` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`video_id`) REFERENCES `course_videos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
