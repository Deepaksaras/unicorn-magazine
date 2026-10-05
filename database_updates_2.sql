-- =====================================================================
-- The Unicorn Magazine – update 2: ad click tracking + short links + pop-up ad
-- phpMyAdmin → database unicorn_magazine → SQL tab → paste → Go (run ONCE).
-- If a line says "Duplicate column" or "Duplicate key", that part is already done:
-- delete that line and run the rest again.
-- =====================================================================

-- 1) One row per advertisement click (time, page, device)
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

-- 2) Short links (/s/K7x2Qa) for articles, profiles and reports
ALTER TABLE `posts`    ADD COLUMN `short_code` varchar(12) NULL, ADD COLUMN `short_clicks` int(10) unsigned NOT NULL DEFAULT 0, ADD UNIQUE KEY `posts_short_code_unique` (`short_code`);
ALTER TABLE `profiles` ADD COLUMN `short_code` varchar(12) NULL, ADD COLUMN `short_clicks` int(10) unsigned NOT NULL DEFAULT 0, ADD UNIQUE KEY `profiles_short_code_unique` (`short_code`);
ALTER TABLE `reports`  ADD COLUMN `short_code` varchar(12) NULL, ADD COLUMN `short_clicks` int(10) unsigned NOT NULL DEFAULT 0, ADD UNIQUE KEY `reports_short_code_unique` (`short_code`);

-- 3) Slot for the pop-up advertisement (Admin → Advertising → Pop-up Ad)
INSERT INTO `advertisement_placements` (`name`, `slug`, `description`, `dimensions`, `location`, `max_ads`, `is_active`, `status`, `created_at`, `updated_at`)
SELECT 'Pop-up Ad', 'popup-ad', 'Pop-up window (settings: Advertising → Pop-up Ad)', '600x500', 'popup', 1, 1, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `advertisement_placements` WHERE `slug` = 'popup-ad');

-- 4) Tell Laravel this update is done
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2024_03_01_000003_add_ad_tracking_and_short_links', COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2024_03_01_000003_add_ad_tracking_and_short_links');
