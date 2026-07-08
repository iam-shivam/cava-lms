-- Add bunny stream integration fields to course_videos
ALTER TABLE course_videos ADD COLUMN video_provider VARCHAR(20) DEFAULT 'local';
ALTER TABLE course_videos ADD COLUMN bunny_video_id VARCHAR(255) NULL;
ALTER TABLE course_videos MODIFY COLUMN video_url VARCHAR(255) NULL;
