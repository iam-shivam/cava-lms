-- Migration: Add description to course_videos
-- Date: 2026-06-26

ALTER TABLE `course_videos` ADD COLUMN IF NOT EXISTS `description` TEXT DEFAULT NULL COMMENT 'Video description text';
