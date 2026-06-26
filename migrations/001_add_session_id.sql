-- Migration: Add session_id to users and admins tables for Single-Session Enforcement
-- Date: 2026-06-26

ALTER TABLE `users` ADD COLUMN `session_id` VARCHAR(255) DEFAULT NULL;
ALTER TABLE `admins` ADD COLUMN `session_id` VARCHAR(255) DEFAULT NULL;
