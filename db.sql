CREATE DATABASE IF NOT EXISTS cloud_captcha;
CREATE TABLE cloud_captcha.captcha(
    id VARCHAR(36) PRIMARY KEY,
    createdAt timestamp DEFAULT now()
);