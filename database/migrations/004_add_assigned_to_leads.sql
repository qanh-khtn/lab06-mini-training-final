-- Migration 004: Add assigned_to column to leads table
ALTER TABLE leads ADD COLUMN assigned_to INT NULL;
ALTER TABLE leads ADD CONSTRAINT fk_leads_assigned_to FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL;
ALTER TABLE leads ADD INDEX idx_leads_assigned_to (assigned_to);
