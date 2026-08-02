-- Add thumbnail column to webinars table
ALTER TABLE webinars ADD COLUMN thumbnail VARCHAR(255) DEFAULT NULL;
