-- Migration: Add User Video Progress Tracking Table
-- Date: 2026-07-02

CREATE TABLE IF NOT EXISTS `user_video_progress` (
  `id` CHAR(36) PRIMARY KEY,
  `user_id` CHAR(36) NOT NULL,
  `video_id` CHAR(36) NOT NULL,
  `course_id` CHAR(36) NOT NULL,
  `status` ENUM('started', 'completed') DEFAULT 'completed',
  `watched_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `user_video` (`user_id`, `video_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`video_id`) REFERENCES `course_videos` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
