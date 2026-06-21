-- Migration 003: Add 'pending' status to users table ENUM.
-- Run this to update existing database.

USE training_center_crm;

ALTER TABLE users
MODIFY COLUMN status ENUM('active','inactive','pending') NOT NULL DEFAULT 'pending';
