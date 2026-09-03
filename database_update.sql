-- =====================================================
-- Incremental update: adds a photo column to farm_records
-- Run this in phpMyAdmin's Import tab — it does NOT delete
-- your existing data, it just adds one new column.
-- =====================================================

USE farm_system;

ALTER TABLE farm_records ADD COLUMN photo_path VARCHAR(255) NULL AFTER unit;
