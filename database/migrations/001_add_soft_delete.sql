-- Migration 001: Thêm cột soft delete cho leads và payments
-- Chạy câu lệnh này trong phpMyAdmin hoặc MySQL CLI nếu database đã tồn tại.
-- Nếu khởi tạo lại từ đầu (docker compose down -v && up), không cần chạy migration này vì
-- schema.sql đã có sẵn cột deleted_at.

USE training_center_crm;

ALTER TABLE leads
    ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL AFTER updated_at,
    ADD INDEX idx_leads_deleted_at (deleted_at);

ALTER TABLE payments
    ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL AFTER updated_at,
    ADD INDEX idx_payments_deleted_at (deleted_at);
