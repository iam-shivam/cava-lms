-- Migration: Add Document Uploads
-- Date: 2026-06-26

ALTER TABLE `course_videos` ADD COLUMN `document_url` VARCHAR(255) DEFAULT NULL COMMENT 'Path to optional document file (PDF/Word)';
