-- NewsMyJob CMS - Complete Database Schema
-- Import via phpMyAdmin or: mysql -u root < database/newsmyjob.sql

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `newsmyjob` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `newsmyjob`;

CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `role` enum('super_admin','admin','editor','author') NOT NULL DEFAULT 'author',
  `bio` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `remember_token` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `login_logs` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `status` enum('success','failed') NOT NULL DEFAULT 'failed',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `login_logs_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `activity_logs` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `module` varchar(50) NOT NULL,
  `record_id` int(11) UNSIGNED DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `activity_logs_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `categories` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `display_order` int(11) NOT NULL DEFAULT 0,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sub_categories` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` int(11) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `sub_categories_category_fk` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tags` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `slug` varchar(60) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `news` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(280) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `content` longtext NOT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `category_id` int(11) UNSIGNED DEFAULT NULL,
  `sub_category_id` int(11) UNSIGNED DEFAULT NULL,
  `author_id` int(11) UNSIGNED NOT NULL,
  `status` enum('draft','published','scheduled') NOT NULL DEFAULT 'draft',
  `published_at` datetime DEFAULT NULL,
  `scheduled_at` datetime DEFAULT NULL,
  `read_time` int(11) NOT NULL DEFAULT 3,
  `views` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `featured_order` int(11) NOT NULL DEFAULT 0,
  `is_trending` tinyint(1) NOT NULL DEFAULT 0,
  `is_editors_pick` tinyint(1) NOT NULL DEFAULT 0,
  `is_breaking` tinyint(1) NOT NULL DEFAULT 0,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `canonical_url` varchar(500) DEFAULT NULL,
  `og_image` varchar(255) DEFAULT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `video_type` enum('upload','youtube','none') NOT NULL DEFAULT 'none',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  KEY `author_id` (`author_id`),
  KEY `status` (`status`),
  FULLTEXT KEY `search_index` (`title`,`excerpt`,`content`),
  CONSTRAINT `news_category_fk` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `news_sub_category_fk` FOREIGN KEY (`sub_category_id`) REFERENCES `sub_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `news_author_fk` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `news_images` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `news_id` int(11) UNSIGNED NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `news_id` (`news_id`),
  CONSTRAINT `news_images_news_fk` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `news_tags` (
  `news_id` int(11) UNSIGNED NOT NULL,
  `tag_id` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`news_id`,`tag_id`),
  CONSTRAINT `news_tags_news_fk` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE,
  CONSTRAINT `news_tags_tag_fk` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `related_news` (
  `news_id` int(11) UNSIGNED NOT NULL,
  `related_id` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`news_id`,`related_id`),
  CONSTRAINT `related_news_news_fk` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE,
  CONSTRAINT `related_news_related_fk` FOREIGN KEY (`related_id`) REFERENCES `news` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `breaking_news` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `link` varchar(500) DEFAULT NULL,
  `news_id` int(11) UNSIGNED DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `news_id` (`news_id`),
  CONSTRAINT `breaking_news_news_fk` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `media` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_type` enum('image','document','pdf','video','youtube') NOT NULL DEFAULT 'image',
  `mime_type` varchar(100) DEFAULT NULL,
  `file_size` int(11) UNSIGNED DEFAULT NULL,
  `youtube_url` varchar(500) DEFAULT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `uploaded_by` int(11) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `uploaded_by` (`uploaded_by`),
  CONSTRAINT `media_user_fk` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `settings` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_group` varchar(50) DEFAULT 'general',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `menus` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `url` varchar(500) NOT NULL,
  `parent_id` int(11) UNSIGNED DEFAULT NULL,
  `menu_location` enum('header','footer') NOT NULL DEFAULT 'header',
  `target` enum('_self','_blank') NOT NULL DEFAULT '_self',
  `icon` varchar(50) DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `advertisements` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `position` enum('header','sidebar','footer','popup','sticky','content') NOT NULL DEFAULT 'sidebar',
  `ad_type` enum('image','html','adsense') NOT NULL DEFAULT 'html',
  `content` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `link` varchar(500) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `comments` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `news_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `comment` text NOT NULL,
  `status` enum('pending','approved','spam') NOT NULL DEFAULT 'pending',
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `news_id` (`news_id`),
  CONSTRAINT `comments_news_fk` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `visitors` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `referrer` varchar(500) DEFAULT NULL,
  `page_url` varchar(500) DEFAULT NULL,
  `visit_date` date NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `visit_date` (`visit_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `page_views` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `news_id` int(11) UNSIGNED DEFAULT NULL,
  `page_url` varchar(500) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `view_date` date NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `news_id` (`news_id`),
  CONSTRAINT `page_views_news_fk` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `newsletters` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `subscribed_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `contacts` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `status` enum('new','read','replied') NOT NULL DEFAULT 'new',
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `bookmarks` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `session_id` varchar(255) NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `news_id` int(11) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_news` (`session_id`,`news_id`),
  CONSTRAINT `bookmarks_news_fk` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default Super Admin: admin@newsmyjob.com / Admin@123
INSERT INTO `users` (`name`, `username`, `email`, `password`, `role`, `status`) VALUES
('Super Administrator', 'superadmin', 'admin@newsmyjob.com', '$2y$12$Wqnt0nnOkrzZBRvTb4uqSOVvk9zs1L5E7XEnF4wsm0ZBWHgdZDb2u', 'super_admin', 'active');

INSERT INTO `categories` (`name`, `slug`, `description`, `icon`, `status`, `display_order`) VALUES
('Politics', 'politics', 'Political news and updates', 'bi-bank', 'active', 1),
('Sports', 'sports', 'Sports news and scores', 'bi-trophy', 'active', 2),
('Technology', 'technology', 'Tech news and innovations', 'bi-cpu', 'active', 3),
('Entertainment', 'entertainment', 'Entertainment news', 'bi-film', 'active', 4),
('Business', 'business', 'Business and economy', 'bi-graph-up', 'active', 5),
('Health', 'health', 'Health and wellness', 'bi-heart-pulse', 'active', 6);

INSERT INTO `sub_categories` (`category_id`, `name`, `slug`, `status`, `display_order`) VALUES
(1, 'National Politics', 'national-politics', 'active', 1),
(1, 'International Politics', 'international-politics', 'active', 2),
(2, 'Cricket', 'cricket', 'active', 1),
(2, 'Football', 'football', 'active', 2),
(3, 'Gadgets', 'gadgets', 'active', 1),
(3, 'Software', 'software', 'active', 2);

INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_group`) VALUES
('site_name', 'NewsMyJob', 'general'),
('site_tagline', 'Your Trusted News Source', 'general'),
('site_logo', '', 'general'),
('site_favicon', '', 'general'),
('site_email', 'info@newsmyjob.com', 'contact'),
('site_phone', '+1 234 567 8900', 'contact'),
('site_address', '123 News Street, Media City', 'contact'),
('facebook_url', 'https://facebook.com/newsmyjob', 'social'),
('twitter_url', 'https://twitter.com/newsmyjob', 'social'),
('instagram_url', 'https://instagram.com/newsmyjob', 'social'),
('youtube_url', 'https://youtube.com/newsmyjob', 'social'),
('linkedin_url', 'https://linkedin.com/company/newsmyjob', 'social'),
('theme_color', '#dc3545', 'appearance'),
('dark_mode_enabled', '1', 'appearance'),
('maintenance_mode', '0', 'general'),
('maintenance_message', 'We are under maintenance. Please check back soon.', 'general'),
('header_code', '', 'custom'),
('footer_code', '', 'custom'),
('about_us', '<p>NewsMyJob is a leading news portal delivering latest news from around the world.</p>', 'pages'),
('privacy_policy', '<p>Your privacy is important to us.</p>', 'pages'),
('terms_conditions', '<p>By using this website you agree to our terms.</p>', 'pages'),
('contact_page_content', '<p>Send us a message and we will respond soon.</p>', 'pages'),
('homepage_hero_enabled', '1', 'homepage'),
('homepage_breaking_enabled', '1', 'homepage'),
('homepage_trending_enabled', '1', 'homepage'),
('homepage_featured_enabled', '1', 'homepage'),
('homepage_newsletter_enabled', '1', 'homepage'),
('comments_enabled', '1', 'general'),
('posts_per_page', '12', 'general'),
('meta_title', 'NewsMyJob - Latest News & Breaking Stories', 'seo'),
('meta_description', 'Get the latest news, breaking stories and trending topics on NewsMyJob.', 'seo'),
('meta_keywords', 'news, breaking news, latest news, trending', 'seo'),
('robots_txt', 'User-agent: *\nAllow: /\nSitemap: http://localhost/newsmyjob/sitemap.xml', 'seo'),
('google_analytics', '', 'seo');

INSERT INTO `menus` (`title`, `url`, `menu_location`, `display_order`, `status`) VALUES
('Home', '/', 'header', 1, 'active'),
('Politics', '/category/politics', 'header', 2, 'active'),
('Sports', '/category/sports', 'header', 3, 'active'),
('Technology', '/category/technology', 'header', 4, 'active'),
('Videos', '/videos', 'header', 5, 'active'),
('Gallery', '/gallery', 'header', 6, 'active'),
('About Us', '/about-us', 'header', 7, 'active'),
('Contact', '/contact-us', 'header', 8, 'active');

-- Sample published news
INSERT INTO `news` (`title`, `slug`, `excerpt`, `content`, `category_id`, `author_id`, `status`, `published_at`, `read_time`, `views`, `is_featured`, `featured_order`, `is_trending`, `is_editors_pick`) VALUES
('Government Announces New Economic Policy Reforms', 'government-announces-new-economic-policy-reforms', 'Major policy changes aimed at boosting economic growth announced today.', '<p>The government has unveiled a comprehensive set of economic reforms designed to stimulate growth and create jobs across multiple sectors.</p><p>Experts believe these changes could significantly impact the business landscape in the coming years.</p>', 1, 1, 'published', NOW(), 3, 1250, 1, 1, 1, 1),
('National Team Wins Championship in Thrilling Final', 'national-team-wins-championship', 'Historic victory marks the team''s first championship in decades.', '<p>In a nail-biting final match, the national team secured a historic victory that will be remembered for generations.</p>', 2, 1, 'published', NOW(), 2, 980, 1, 2, 1, 0),
('Revolutionary AI Technology Transforms Healthcare', 'revolutionary-ai-technology-transforms-healthcare', 'New AI system can diagnose diseases with unprecedented accuracy.', '<p>Researchers have developed an artificial intelligence system that can detect various diseases from medical scans with remarkable precision.</p>', 3, 1, 'published', NOW(), 4, 2100, 1, 3, 1, 1),
('Blockbuster Movie Breaks Box Office Records', 'blockbuster-movie-breaks-box-office-records', 'The latest release becomes the highest-grossing film of the year.', '<p>The highly anticipated film has shattered all expectations, earning record-breaking revenues in its opening weekend.</p>', 4, 1, 'published', NOW(), 2, 750, 0, 0, 0, 0),
('Stock Market Reaches All-Time High', 'stock-market-reaches-all-time-high', 'Major indices surge as investor confidence grows.', '<p>Financial markets celebrated today as major stock indices climbed to unprecedented levels amid positive economic indicators.</p>', 5, 1, 'published', NOW(), 3, 890, 0, 0, 1, 0),
('Breakthrough in Cancer Research Shows Promise', 'breakthrough-in-cancer-research', 'Scientists discover new treatment approach with promising results.', '<p>A team of researchers has identified a novel approach to cancer treatment that showed significant results in early clinical trials.</p>', 6, 1, 'published', NOW(), 5, 1560, 0, 0, 0, 1);

INSERT INTO `breaking_news` (`title`, `link`, `news_id`, `status`, `display_order`) VALUES
('Government Announces New Economic Policy Reforms', '/news/government-announces-new-economic-policy-reforms', 1, 'active', 1),
('Revolutionary AI Technology Transforms Healthcare', '/news/revolutionary-ai-technology-transforms-healthcare', 3, 'active', 2);
