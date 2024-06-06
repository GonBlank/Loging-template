# Loging template

This project is a login and user management template with PHP and MySQL for websites.
We use gitflow to work on this project


## Database creation 

CREATE DATABASE IF NOT EXISTS main_db;
USE main_db;
CREATE TABLE IF NOT EXISTS users (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    role VARCHAR(10) DEFAULT 'USER',
    email VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(60) NOT NULL,
    validation_hash VARCHAR(24) DEFAULT NULL,
    hash_date DATETIME DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS contacts (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(50) DEFAULT NULL,
    valid_email TINYINT(1) DEFAULT 0,
    FOREIGN KEY (email) REFERENCES users(email) ON DELETE CASCADE ON UPDATE CASCADE
);
