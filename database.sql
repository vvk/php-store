CREATE DATABASE store CHARACTER SET 'utf8';
USE store;

CREATE TABLE items (
  id          SERIAL PRIMARY KEY,
  name        VARCHAR(200) NOT NULL,
  description TEXT,
  price       DECIMAL(12, 2) NOT NULL,
  image       VARCHAR(50),
  INDEX price_index(price)
);