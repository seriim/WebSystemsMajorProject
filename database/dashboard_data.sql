/*
===========================================================
 Church Management and Information System
 Dashboard-Ready Dummy Data
 Updated for current date display
===========================================================
*/

USE church_system_database;

-- Insert Events for next 2 weeks (so they show up on dashboard)
INSERT IGNORE INTO Events (event_type, date, member_id, notes) VALUES
('Birthday', DATE_ADD(CURRENT_DATE, INTERVAL 2 DAY), 1, 'Serena Morris birthday celebration'),
('Wedding', DATE_ADD(CURRENT_DATE, INTERVAL 5 DAY), 2, 'Joshane Beecher wedding ceremony'),
('Anniversary', DATE_ADD(CURRENT_DATE, INTERVAL 8 DAY), 5, 'Michael and Patricia Johnson 10th anniversary'),
('Baptism', DATE_ADD(CURRENT_DATE, INTERVAL 10 DAY), 7, 'David Brown baptism ceremony'),
('Birthday', DATE_ADD(CURRENT_DATE, INTERVAL 12 DAY), 3, 'Abbygayle Higgins birthday'),
('Anniversary', DATE_ADD(CURRENT_DATE, INTERVAL 14 DAY), 6, 'Sarah and David Williams 5th anniversary'),
('Birthday', DATE_ADD(CURRENT_DATE, INTERVAL 1 DAY), 4, 'Jahzeal Simms birthday celebration'),
('Wedding', DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY), 10, 'Lisa Wilson wedding ceremony'),
('Baptism', DATE_ADD(CURRENT_DATE, INTERVAL 9 DAY), 13, 'Christopher Anderson baptism'),
('Anniversary', DATE_ADD(CURRENT_DATE, INTERVAL 11 DAY), 9, 'Robert and Susan Miller 8th anniversary');

-- Insert Attendance records for current month (December 2025)
-- This will make "This Month" show data
INSERT IGNORE INTO Attendance (date, ministry_id, count, recorded_by) VALUES
-- December 1, 2025 (Sunday)
('2025-12-01', 1, 28, 1),  -- Youth Ministry
('2025-12-01', 2, 20, 1),  -- Women's Ministry
('2025-12-01', 3, 24, 1),  -- Men's Ministry
('2025-12-01', 4, 16, 1),  -- Music Ministry
('2025-12-01', 5, 32, 1),  -- Sunday School
('2025-12-01', 6, 14, 1),  -- Outreach Ministry
-- December 8, 2025 (Sunday)
('2025-12-08', 1, 30, 1),
('2025-12-08', 2, 22, 1),
('2025-12-08', 3, 26, 1),
('2025-12-08', 4, 18, 1),
('2025-12-08', 5, 35, 1),
('2025-12-08', 6, 15, 1),
-- December 15, 2025 (Sunday - last Monday's week)
('2025-12-15', 1, 32, 1),
('2025-12-15', 2, 24, 1),
('2025-12-15', 3, 28, 1),
('2025-12-15', 4, 20, 1),
('2025-12-15', 5, 38, 1),
('2025-12-15', 6, 16, 1),
-- December 22, 2025 (Sunday - this week)
('2025-12-22', 1, 35, 1),
('2025-12-22', 2, 26, 1),
('2025-12-22', 3, 30, 1),
('2025-12-22', 4, 22, 1),
('2025-12-22', 5, 40, 1),
('2025-12-22', 6, 18, 1);

-- Insert Attendance records for last 4 Mondays (for attendance trends chart)
-- Calculate the last 4 Mondays dynamically
INSERT IGNORE INTO Attendance (date, ministry_id, count, recorded_by) VALUES
-- 4 weeks ago Monday (Nov 24, 2025)
('2025-11-24', 1, 25, 1),
('2025-11-24', 2, 18, 1),
('2025-11-24', 3, 22, 1),
('2025-11-24', 4, 15, 1),
('2025-11-24', 5, 30, 1),
('2025-11-24', 6, 12, 1),
-- 3 weeks ago Monday (Dec 1, 2025)
('2025-12-01', 1, 28, 1),
('2025-12-01', 2, 20, 1),
('2025-12-01', 3, 24, 1),
('2025-12-01', 4, 16, 1),
('2025-12-01', 5, 32, 1),
('2025-12-01', 6, 14, 1),
-- 2 weeks ago Monday (Dec 8, 2025)
('2025-12-08', 1, 30, 1),
('2025-12-08', 2, 22, 1),
('2025-12-08', 3, 26, 1),
('2025-12-08', 4, 18, 1),
('2025-12-08', 5, 35, 1),
('2025-12-08', 6, 15, 1),
-- 1 week ago Monday (Dec 15, 2025)
('2025-12-15', 1, 32, 1),
('2025-12-15', 2, 24, 1),
('2025-12-15', 3, 28, 1),
('2025-12-15', 4, 20, 1),
('2025-12-15', 5, 38, 1),
('2025-12-15', 6, 16, 1);

