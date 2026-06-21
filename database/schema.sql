-- ============================================================
-- Lab05 - Mini Training Center CRM DB App
-- Schema: users + leads (tư vấn) + payments (thanh toán học phí)
-- Charset utf8mb4 để hỗ trợ tiếng Việt / Unicode.
-- ============================================================

SET NAMES utf8mb4;
SET character_set_client = utf8mb4;

CREATE DATABASE IF NOT EXISTS training_center_crm
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE training_center_crm;

DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS leads;
DROP TABLE IF EXISTS users;

-- Bảng users (tài khoản nhân sự trung tâm)
CREATE TABLE users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('admin','staff') NOT NULL DEFAULT 'staff',
    status        ENUM('active','inactive','pending') NOT NULL DEFAULT 'pending',
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_email (email)
);

-- Module A: leads (lead tư vấn) - email không trùng; deleted_at cho soft delete
CREATE TABLE leads (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    full_name       VARCHAR(100) NOT NULL,
    email           VARCHAR(150) NOT NULL,
    phone           VARCHAR(30),
    course_interest VARCHAR(50)  NOT NULL DEFAULT 'web',
    care_status     VARCHAR(30)  NOT NULL DEFAULT 'new',
    note            TEXT,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      DATETIME NULL DEFAULT NULL,
    UNIQUE KEY unique_lead_email (email),
    INDEX idx_leads_created_at (created_at),
    INDEX idx_leads_care_status_created_at (care_status, created_at),
    INDEX idx_leads_phone (phone),
    INDEX idx_leads_deleted_at (deleted_at)
);

-- Module B: payments (đơn thanh toán học phí) - payment_code không trùng; deleted_at cho soft delete
CREATE TABLE payments (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    payment_code  VARCHAR(50)  NOT NULL,
    student_name  VARCHAR(100) NOT NULL,
    student_email VARCHAR(150),
    course_name   VARCHAR(100) NOT NULL,
    amount        DECIMAL(12,2) NOT NULL DEFAULT 0,
    status        VARCHAR(30)  NOT NULL DEFAULT 'pending',
    note          TEXT,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at    DATETIME NULL DEFAULT NULL,
    UNIQUE KEY unique_payment_code (payment_code),
    INDEX idx_payments_created_at (created_at),
    INDEX idx_payments_status_created_at (status, created_at),
    INDEX idx_payments_student_email (student_email),
    INDEX idx_payments_deleted_at (deleted_at)
);
