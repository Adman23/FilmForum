CREATE TABLE IF NOT EXISTS `user` (
  `username` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('superuser','manager','moderator','registered') NOT NULL DEFAULT 'registered',
  `email` varchar(100) NOT NULL,
  `age` int NOT NULL,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `film` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `fecha` year NOT NULL,
  `genre` varchar(100) NOT NULL,
  `directors` varchar(255) NOT NULL,
  `actors` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `hashtags` varchar(255) NOT NULL DEFAULT '',
  `released` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `image` (
  `id` int NOT NULL AUTO_INCREMENT,
  `film_id` int UNSIGNED DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `type` varchar(30) NOT NULL,
  `content` longblob NOT NULL,
  PRIMARY KEY (`id`),
  KEY `image_film_id_idx` (`film_id`),
  CONSTRAINT `image_film_id_fk` FOREIGN KEY (`film_id`) REFERENCES `film` (`id`)
    ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `comment` (
  `id` int NOT NULL AUTO_INCREMENT,
  `film_id` int UNSIGNED NOT NULL,
  `author` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL DEFAULT '',
  `date` datetime NOT NULL,
  `text` text NOT NULL,
  `edited` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `comment_film_id_idx` (`film_id`),
  CONSTRAINT `comment_film_id_fk` FOREIGN KEY (`film_id`) REFERENCES `film` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `banned_words` (
  `id` int NOT NULL AUTO_INCREMENT,
  `word` varchar(15) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
