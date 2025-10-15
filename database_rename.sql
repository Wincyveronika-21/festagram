-- SQL Script to rename database from zephyr to festagram
-- Run these commands in MySQL/phpMyAdmin

-- 1. Create new database
CREATE DATABASE IF NOT EXISTS festagram;

-- 2. Export zephyr database and import to festagram
-- (You can do this via phpMyAdmin Export/Import or command line)

-- Command line approach:
-- mysqldump -u myuser -p zephyr > zephyr_backup.sql
-- mysql -u myuser -p festagram < zephyr_backup.sql

-- 3. After importing, you can optionally drop the old database
-- DROP DATABASE zephyr;

-- Note: Make sure to update linc.php to use 'festagram' instead of 'zephyr'