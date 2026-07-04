-- Migration v4: Update students table schema
USE `clearance_system`;

-- Since the user said to start fresh, we can truncate the students table.
-- To do this with foreign keys, we disable foreign key checks temporarily.
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `students`;
SET FOREIGN_KEY_CHECKS = 1;

-- Now alter the columns
ALTER TABLE `students` DROP COLUMN `full_name`;
ALTER TABLE `students` ADD COLUMN `last_name` VARCHAR(150) NOT NULL AFTER `student_id`;
ALTER TABLE `students` ADD COLUMN `first_name` VARCHAR(150) NOT NULL AFTER `last_name`;
ALTER TABLE `students` ADD COLUMN `college` VARCHAR(50) NOT NULL AFTER `email`;
