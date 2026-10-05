-- Optional: shows what is installed. Paste in phpMyAdmin → SQL → Go.
SELECT 'site_visits table'   AS item, IF(COUNT(*) > 0, 'OK', 'missing') AS status FROM information_schema.TABLES  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'site_visits'
UNION ALL SELECT 'visitor_presence table', IF(COUNT(*) > 0, 'OK', 'missing') FROM information_schema.TABLES  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'visitor_presence'
UNION ALL SELECT 'ad_clicks table',        IF(COUNT(*) > 0, 'OK', 'missing') FROM information_schema.TABLES  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ad_clicks'
UNION ALL SELECT 'profiles.view_count',    IF(COUNT(*) > 0, 'OK', 'missing') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'profiles' AND COLUMN_NAME = 'view_count'
UNION ALL SELECT 'reports.content',        IF(COUNT(*) > 0, 'OK', 'missing') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reports'  AND COLUMN_NAME = 'content'
UNION ALL SELECT 'reports.view_count',     IF(COUNT(*) > 0, 'OK', 'missing') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reports'  AND COLUMN_NAME = 'view_count'
UNION ALL SELECT 'posts.short_code',       IF(COUNT(*) > 0, 'OK', 'missing') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'posts'    AND COLUMN_NAME = 'short_code'
UNION ALL SELECT 'profiles.short_code',    IF(COUNT(*) > 0, 'OK', 'missing') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'profiles' AND COLUMN_NAME = 'short_code'
UNION ALL SELECT 'reports.short_code',     IF(COUNT(*) > 0, 'OK', 'missing') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reports'  AND COLUMN_NAME = 'short_code'
UNION ALL SELECT 'Pop-up Ad placement',    IF(COUNT(*) > 0, 'OK', 'missing') FROM `advertisement_placements` WHERE `slug` = 'popup-ad';
