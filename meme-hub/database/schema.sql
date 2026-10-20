-- Meme Hub database schema (MySQL 8.0+ / MariaDB 10.5+)
CREATE DATABASE IF NOT EXISTS meme_hub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE meme_hub;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS memes;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  display_name VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL,
  avatar_url VARCHAR(500) NULL,
  role VARCHAR(30) NOT NULL DEFAULT 'member',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categories (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(80) NOT NULL,
  slug VARCHAR(100) NOT NULL,
  description VARCHAR(255) NULL,
  color_hex CHAR(7) NOT NULL DEFAULT '#7C5CFC',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_categories_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE memes (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(150) NOT NULL,
  caption TEXT NOT NULL,
  image_url VARCHAR(1000) NOT NULL,
  alt_text VARCHAR(255) NULL,
  category_id INT UNSIGNED NOT NULL,
  created_by INT UNSIGNED NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'published',
  view_count INT UNSIGNED NOT NULL DEFAULT 0,
  like_count INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_memes_status_date (status, created_at),
  KEY idx_memes_category (category_id),
  KEY idx_memes_creator (created_by),
  CONSTRAINT fk_memes_category FOREIGN KEY (category_id) REFERENCES categories(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_memes_creator FOREIGN KEY (created_by) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (display_name, email, avatar_url, role) VALUES
('Meme Admin', 'admin@memehub.local', NULL, 'admin'),
('Nong Lol', 'nonglol@memehub.local', NULL, 'member'),
('KaoJai', 'kaojai@memehub.local', NULL, 'member');

INSERT INTO categories (name, slug, description, color_hex) VALUES
('ชีวิตออฟฟิศ', 'office-life', 'มีมที่คนทำงานเข้าใจดี', '#F5A623'),
('ชีวิตประจำวัน', 'daily-life', 'เรื่องเล็ก ๆ ที่เกิดขึ้นทุกวัน', '#7C5CFC'),
('เกมเมอร์', 'gamer', 'มีมจากโลกแห่งเกม', '#20B8A6'),
('สัตว์เลี้ยง', 'pets', 'ความป่วนของเจ้านายสี่ขา', '#F06B9A');

INSERT INTO memes (title, caption, image_url, alt_text, category_id, created_by, status, view_count, like_count) VALUES
('เมื่อหัวหน้าบอกว่ามีงานด่วน', 'POV: กำลังจะกลับบ้านแล้ว แต่ได้ยินคำว่า "ขออีกนิดเดียว"', 'assets/memes/meme-office.svg', 'ภาพการ์ตูนพนักงานออฟฟิศกับงานกองโต', 1, 2, 'published', 2480, 326),
('ตั้งใจจะนอนเร็วคืนนี้', 'ตีหนึ่งแล้ว แต่สมองเพิ่งเปิดโหมดไอเดียสร้างสรรค์', 'assets/memes/meme-sleep.svg', 'ภาพคนพยายามนอนแต่ยังตาสว่าง', 2, 3, 'published', 1920, 281),
('แพ้เกมเพราะทีมไม่ช่วย', 'ฝีมือเราไม่เคยมีปัญหา ปัญหาคือจักรวาลไม่เข้าข้าง', 'assets/memes/meme-gamer.svg', 'ภาพเกมเมอร์หัวร้อนหน้าคอมพิวเตอร์', 3, 2, 'published', 1510, 204),
('บอกว่าจะกินแค่คำเดียว', 'อีกห้านาทีค่อยเริ่มไดเอตก็ได้มั้ง', 'assets/memes/meme-food.svg', 'ภาพคนเห็นของกินแล้วตื่นเต้น', 2, 3, 'published', 980, 157),
('แมวที่บ้านเป็นเจ้าของบ้าน', 'เราเป็นแค่พนักงานบริการที่จ่ายค่าเช่า', 'assets/memes/meme-cat.svg', 'ภาพแมวนั่งอย่างราชาบนโซฟา', 4, 1, 'published', 845, 122),
('กำลังใจวันจันทร์', 'ยิ้มไว้ก่อน ส่วนเรื่องอื่นค่อยว่ากัน', 'assets/memes/meme-default.svg', 'การ์ดข้อความมีมสีม่วง', 1, 1, 'draft', 0, 0);
