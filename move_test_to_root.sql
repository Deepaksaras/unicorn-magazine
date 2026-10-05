-- =====================================================================
-- The Unicorn Magazine: site moved from  /test  to the main domain
-- Run ONCE in phpMyAdmin (database → SQL tab → paste → Go)
-- AFTER the files are moved. Take a database backup (Export) first.
-- It only changes links that still contain  theunicornmagazine.com/test
-- Safe to run again (the second time it changes nothing).
-- =====================================================================

-- Articles, profiles, reports (pictures / links placed with the editor)
UPDATE posts    SET content   = REPLACE(content,   'theunicornmagazine.com/test/', 'theunicornmagazine.com/') WHERE content   LIKE '%theunicornmagazine.com/test/%';
UPDATE posts    SET featured_image = REPLACE(featured_image, 'theunicornmagazine.com/test/', 'theunicornmagazine.com/') WHERE featured_image LIKE '%theunicornmagazine.com/test/%';
UPDATE profiles SET biography = REPLACE(biography, 'theunicornmagazine.com/test/', 'theunicornmagazine.com/') WHERE biography LIKE '%theunicornmagazine.com/test/%';
UPDATE profiles SET profile_image = REPLACE(profile_image, 'theunicornmagazine.com/test/', 'theunicornmagazine.com/') WHERE profile_image LIKE '%theunicornmagazine.com/test/%';
UPDATE reports  SET content   = REPLACE(content,   'theunicornmagazine.com/test/', 'theunicornmagazine.com/') WHERE content   LIKE '%theunicornmagazine.com/test/%';
UPDATE reports  SET cover_image = REPLACE(cover_image, 'theunicornmagazine.com/test/', 'theunicornmagazine.com/') WHERE cover_image LIKE '%theunicornmagazine.com/test/%';
UPDATE reports  SET external_url = REPLACE(external_url, 'theunicornmagazine.com/test/', 'theunicornmagazine.com/') WHERE external_url LIKE '%theunicornmagazine.com/test/%';

-- Menus, breaking news bar, advertisements
UPDATE menu_items     SET url = REPLACE(url, 'theunicornmagazine.com/test/', 'theunicornmagazine.com/') WHERE url LIKE '%theunicornmagazine.com/test/%';
UPDATE menu_items     SET url = REPLACE(url, 'theunicornmagazine.com/test',  'theunicornmagazine.com/') WHERE url LIKE '%theunicornmagazine.com/test';
UPDATE menu_items     SET url = SUBSTRING(url, 6) WHERE url LIKE '/test/%';
UPDATE menu_items     SET url = '/' WHERE url = '/test';
UPDATE breaking_news  SET url = REPLACE(url, 'theunicornmagazine.com/test/', 'theunicornmagazine.com/') WHERE url LIKE '%theunicornmagazine.com/test/%';
UPDATE breaking_news  SET url = SUBSTRING(url, 6) WHERE url LIKE '/test/%';
UPDATE advertisements SET url   = REPLACE(url,   'theunicornmagazine.com/test/', 'theunicornmagazine.com/') WHERE url   LIKE '%theunicornmagazine.com/test/%';
UPDATE advertisements SET image = REPLACE(image, 'theunicornmagazine.com/test/', 'theunicornmagazine.com/') WHERE image LIKE '%theunicornmagazine.com/test/%';
UPDATE advertisements SET code  = REPLACE(code,  'theunicornmagazine.com/test/', 'theunicornmagazine.com/') WHERE code  LIKE '%theunicornmagazine.com/test/%';

-- Settings and SEO boxes
UPDATE settings SET value = REPLACE(value, 'theunicornmagazine.com/test/', 'theunicornmagazine.com/') WHERE value LIKE '%theunicornmagazine.com/test/%';
UPDATE seo_meta SET canonical_url = REPLACE(canonical_url, 'theunicornmagazine.com/test/', 'theunicornmagazine.com/') WHERE canonical_url LIKE '%theunicornmagazine.com/test/%';
UPDATE seo_meta SET canonical_url = REPLACE(canonical_url, 'theunicornmagazine.com/test',  'theunicornmagazine.com')  WHERE canonical_url LIKE '%theunicornmagazine.com/test';
UPDATE seo_meta SET og_image = REPLACE(og_image, 'theunicornmagazine.com/test/', 'theunicornmagazine.com/') WHERE og_image LIKE '%theunicornmagazine.com/test/%';

-- Page texts (Admin → Pages). Links are stored two ways: normal and with \/
UPDATE page_sections SET settings = REPLACE(settings, 'theunicornmagazine.com/test/', 'theunicornmagazine.com/') WHERE settings LIKE '%theunicornmagazine.com/test/%';
UPDATE page_sections SET settings = REPLACE(settings, 'theunicornmagazine.com\\/test\\/', 'theunicornmagazine.com\\/') WHERE settings LIKE '%theunicornmagazine.com\\\\/test\\\\/%';
UPDATE page_sections SET settings = REPLACE(settings, '"\\/test\\/', '"\\/') WHERE settings LIKE '%"\\\\/test\\\\/%';
UPDATE page_sections SET content  = REPLACE(content,  'theunicornmagazine.com/test/', 'theunicornmagazine.com/') WHERE content  LIKE '%theunicornmagazine.com/test/%';
