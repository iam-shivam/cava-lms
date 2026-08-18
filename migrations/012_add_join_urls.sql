-- Migration to add optional Join URLs for Events and Webinars
ALTER TABLE events ADD COLUMN join_url VARCHAR(255) NULL;
ALTER TABLE webinars ADD COLUMN join_url VARCHAR(255) NULL;
