-- =====================================================================
-- The Unicorn Magazine – ALL database updates (safe to run more than once)
-- phpMyAdmin → click database "unicorn_magazine" → SQL tab → paste all → Go
-- Parts that already exist are skipped automatically, nothing is deleted.
-- =====================================================================

-- 1) Visitor counter (Dashboard → Website visitors)
CREATE TABLE IF NOT EXISTS `site_visits` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `visitor_id` varchar(40) NOT NULL,
  `path` varchar(191) NOT NULL,
  `referrer_host` varchar(191) DEFAULT NULL,
  `device` varchar(10) NOT NULL DEFAULT 'desktop',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `site_visits_created_at_index` (`created_at`),
  KEY `site_visits_created_at_visitor_id_index` (`created_at`,`visitor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `visitor_presence` (
  `visitor_id` varchar(40) NOT NULL,
  `path` varchar(191) DEFAULT NULL,
  `last_seen_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`visitor_id`),
  KEY `visitor_presence_last_seen_at_index` (`last_seen_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2) Profile & report pages
SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `profiles` ADD COLUMN `view_count` int(10) unsigned NOT NULL DEFAULT 0', 'SELECT 1') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'profiles' AND COLUMN_NAME = 'view_count');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `reports` ADD COLUMN `content` longtext NULL AFTER `description`', 'SELECT 1') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reports' AND COLUMN_NAME = 'content');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `reports` ADD COLUMN `view_count` int(10) unsigned NOT NULL DEFAULT 0', 'SELECT 1') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reports' AND COLUMN_NAME = 'view_count');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

UPDATE `profiles` SET `slug` = CONCAT('profile-', `id`) WHERE `slug` IS NULL OR `slug` = '';
UPDATE `reports`  SET `slug` = CONCAT('report-', `id`)  WHERE `slug` IS NULL OR `slug` = '';

-- "Stories & Profile" menu + Home "View all" → /profiles
UPDATE `menu_items` SET `url` = '/profiles' WHERE `url` = '/category/stories-profiles';
UPDATE `page_sections`
SET `settings` = REPLACE(REPLACE(`settings`, '"\\/category\\/stories-profiles"', '"\\/profiles"'), '"/category/stories-profiles"', '"/profiles"')
WHERE `section_name` = 'profiles';

-- 3) Advertisement click log (daily totals use the existing advertising_stats table)
CREATE TABLE IF NOT EXISTS `ad_clicks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `advertisement_id` bigint(20) unsigned NOT NULL,
  `visitor_id` varchar(40) DEFAULT NULL,
  `path` varchar(191) DEFAULT NULL,
  `device` varchar(10) NOT NULL DEFAULT 'desktop',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ad_clicks_advertisement_id_created_at_index` (`advertisement_id`,`created_at`),
  KEY `ad_clicks_created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `advertising_stats` ADD UNIQUE KEY `advertising_stats_advertisement_id_stat_date_unique` (`advertisement_id`,`stat_date`)', 'SELECT 1') FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'advertising_stats' AND INDEX_NAME = 'advertising_stats_advertisement_id_stat_date_unique');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- 4) Short links (/s/K7x2Qa) for articles, profiles and reports
SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `posts` ADD COLUMN `short_code` varchar(12) NULL', 'SELECT 1') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'posts' AND COLUMN_NAME = 'short_code');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `posts` ADD COLUMN `short_clicks` int(10) unsigned NOT NULL DEFAULT 0', 'SELECT 1') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'posts' AND COLUMN_NAME = 'short_clicks');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `posts` ADD UNIQUE KEY `posts_short_code_unique` (`short_code`)', 'SELECT 1') FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'posts' AND INDEX_NAME = 'posts_short_code_unique');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `profiles` ADD COLUMN `short_code` varchar(12) NULL', 'SELECT 1') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'profiles' AND COLUMN_NAME = 'short_code');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `profiles` ADD COLUMN `short_clicks` int(10) unsigned NOT NULL DEFAULT 0', 'SELECT 1') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'profiles' AND COLUMN_NAME = 'short_clicks');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `profiles` ADD UNIQUE KEY `profiles_short_code_unique` (`short_code`)', 'SELECT 1') FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'profiles' AND INDEX_NAME = 'profiles_short_code_unique');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `reports` ADD COLUMN `short_code` varchar(12) NULL', 'SELECT 1') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reports' AND COLUMN_NAME = 'short_code');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `reports` ADD COLUMN `short_clicks` int(10) unsigned NOT NULL DEFAULT 0', 'SELECT 1') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reports' AND COLUMN_NAME = 'short_clicks');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `reports` ADD UNIQUE KEY `reports_short_code_unique` (`short_code`)', 'SELECT 1') FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reports' AND INDEX_NAME = 'reports_short_code_unique');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- 5) Slot for the pop-up advertisement (Advertising → Pop-up Ad)
INSERT INTO `advertisement_placements` (`name`, `slug`, `description`, `dimensions`, `location`, `max_ads`, `is_active`, `status`, `created_at`, `updated_at`)
SELECT 'Pop-up Ad', 'popup-ad', 'Pop-up window (settings: Advertising → Pop-up Ad)', '600x500', 'popup', 1, 1, 1, NOW(), NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `advertisement_placements` WHERE `slug` = 'popup-ad');

-- 6) Tell Laravel these updates are done (so "php artisan migrate" won't repeat them)
INSERT INTO `migrations` (`migration`, `batch`)
SELECT m.name, (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`)
FROM (SELECT '2024_03_01_000001_create_visitor_tracking_tables' AS name
      UNION ALL SELECT '2024_03_01_000002_add_profile_and_report_pages'
      UNION ALL SELECT '2024_03_01_000003_add_ad_tracking_and_short_links') m
WHERE NOT EXISTS (SELECT 1 FROM `migrations` x WHERE x.`migration` = m.name);

SELECT 'Done – all Unicorn Magazine database updates are installed.' AS result;
