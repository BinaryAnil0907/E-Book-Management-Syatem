CREATE DATABASE ebook_project2;

USE ebook_project;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);


CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    author VARCHAR(100) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    img VARCHAR(255) NOT NULL
);

-- Example data
INSERT INTO books (title, author, file_path, img) VALUES
('PHP Basics', 'John Smith', 'ebooks/php_basics.pdf', 'img/php.jpg'),
('MySQL Guide', 'Jane Doe', 'ebooks/mysql_guide.pdf', 'img/mysql.jpg'),
('Web Development Handbook', 'Mark Lee', 'ebooks/web_dev_handbook.pdf', 'img/webdev.jpg');


CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Insert one admin (password is "admin123")
INSERT INTO admins (username, password) 
VALUES ('admin', '$2y$10$9WgHKQoqzLcsF5cuJsmxv.ZcplSOO6KcLQlmhfSvz0Yy8/6Gx80ji');

