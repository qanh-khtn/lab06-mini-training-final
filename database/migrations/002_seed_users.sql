-- Migration 002: Seed two demo users into the users table.
-- Run this if you already have the schema from Lab05 but no user rows yet.
-- (INSERT IGNORE is safe to run multiple times.)

USE training_center_crm;

INSERT IGNORE INTO users (name, email, password_hash, role, status) VALUES
  ('Quản trị viên', 'admin@center.edu.vn',
   '$2y$12$gfs7SXl9uJ4TC6Hq2YlQdeYWQ6Vld.lZi6azaPa4zvGDGFzN06AYW',
   'admin', 'active'),
  ('Tư vấn viên', 'staff@center.edu.vn',
   '$2y$12$09c/N4HwxhTg8bLqAzCdGOltyVZ8egG75eqowGX05qYImnz71tIi6',
   'staff', 'active');
