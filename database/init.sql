DROP DATABASE IF EXISTS `zadanie`;
CREATE DATABASE `zadanie` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

USE `zadanie`;

DROP TABLE IF EXISTS `client`;
CREATE TABLE IF NOT EXISTS `client` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `choose` tinyint(2) NOT NULL,
  `bank_account` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `client_no` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `agreement_gdpr` tinyint(1) NOT NULL DEFAULT '0',
  `agreement_terms` tinyint(1) NOT NULL DEFAULT '0',
  `agreement_ads` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;