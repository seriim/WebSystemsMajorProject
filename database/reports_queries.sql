/*
===========================================================
 Church Management and Information System
 Database Reports and Queries

 Authors:
   - Joshane Beecher   - 2304845
   - Abbygayle Higgins - 2106327
   - Serena Morris     - 2208659
   - Jahzeal Simms     - 2202446
===========================================================
*/

USE church_system_database;

------------------------------------------------------------
-- 1. Monthly Attendance Summary Report
-- Inputs: month, year
-- Output: attendance totals per ministry
------------------------------------------------------------
SELECT m.name AS ministry, SUM(a.count) AS total_attendance
FROM Attendance a
JOIN Ministries m ON a.ministry_id = m.id
WHERE MONTH(a.date) = 1   -- default to 1 aka January, replace with desired month
  AND YEAR(a.date) = 2025 -- default to 2025, replace with desired year
GROUP BY m.name
ORDER BY m.name;


------------------------------------------------------------
-- 2. Birthday List
-- Inputs: month or week
-- Output: members with upcoming birthdays
------------------------------------------------------------
-- By Month
SELECT first_name, last_name, dob
FROM Members
WHERE MONTH(dob) = 7;  -- default to 7 aka July, replace with desired month

-- By Week
SELECT first_name, last_name, dob
FROM Members
WHERE WEEK(dob, 1) = 30;  -- default to 30, replace with desired week number



------------------------------------------------------------
-- 3. Membership Growth Report
-- Inputs: month, quarter, year
-- Output: new members joined in specified period
------------------------------------------------------------
-- By Month
SELECT COUNT(*) AS new_members, MONTH(date_joined) AS month
FROM Members
WHERE YEAR(date_joined) = 2025  -- replace with desired year
GROUP BY MONTH(date_joined)
ORDER BY month;

-- By Quarter
SELECT COUNT(*) AS new_members, QUARTER(date_joined) AS quarter
FROM Members
WHERE YEAR(date_joined) = 2025  -- replace with desired year
GROUP BY QUARTER(date_joined)
ORDER BY quarter;

-- By Year
SELECT COUNT(*) AS new_members, YEAR(date_joined) AS year
FROM Members
GROUP BY YEAR(date_joined)
ORDER BY year;


------------------------------------------------------------
-- 4. Ministry Member Count
-- Output: number of members per ministry
------------------------------------------------------------
SELECT m.name AS ministry, COUNT(mm.member_id) AS member_count
FROM Ministries m
LEFT JOIN Ministry_Members mm ON m.id = mm.ministry_id
GROUP BY m.id, m.name
ORDER BY member_count DESC;


------------------------------------------------------------
-- 5. Upcoming Events Report
-- Inputs: number of days ahead
-- Output: events in the next N days
------------------------------------------------------------
SELECT e.event_type, e.date, m.first_name, m.last_name, e.notes
FROM Events e
JOIN Members m ON e.member_id = m.mem_id
WHERE e.date >= CURDATE()
  AND e.date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)  -- next 30 days, adjust as needed
ORDER BY e.date;


------------------------------------------------------------
-- 6. Member Contact List
-- Output: all members with contact information
------------------------------------------------------------
SELECT 
    CONCAT(first_name, ' ', COALESCE(middle_initials, ''), ' ', last_name) AS full_name,
    email,
    contact_home,
    contact_work,
    status
FROM Members
WHERE status IN ('Member', 'Adherent')
ORDER BY last_name, first_name;


------------------------------------------------------------
-- 7. Attendance Trend Analysis
-- Inputs: ministry_id, start_date, end_date
-- Output: attendance trend over time
------------------------------------------------------------
SELECT 
    DATE_FORMAT(a.date, '%Y-%m') AS month,
    m.name AS ministry,
    AVG(a.count) AS avg_attendance,
    MAX(a.count) AS max_attendance,
    MIN(a.count) AS min_attendance
FROM Attendance a
JOIN Ministries m ON a.ministry_id = m.id
WHERE a.date >= '2024-01-01'  -- replace with desired start date
  AND a.date <= '2024-12-31'  -- replace with desired end date
  AND a.ministry_id = 1  -- replace with desired ministry_id, or remove for all ministries
GROUP BY DATE_FORMAT(a.date, '%Y-%m'), m.id, m.name
ORDER BY month, m.name;


------------------------------------------------------------
-- 8. Member Status Distribution
-- Output: count of members by status
------------------------------------------------------------
SELECT status, COUNT(*) AS count
FROM Members
GROUP BY status
ORDER BY count DESC;


------------------------------------------------------------
-- 9. Event Type Summary
-- Output: count of events by type
------------------------------------------------------------
SELECT event_type, COUNT(*) AS count
FROM Events
GROUP BY event_type
ORDER BY count DESC;


------------------------------------------------------------
-- 10. Active Members by Ministry
-- Output: active members grouped by their ministries
------------------------------------------------------------
SELECT 
    mi.name AS ministry,
    COUNT(DISTINCT m.mem_id) AS active_members
FROM Ministries mi
LEFT JOIN Ministry_Members mm ON mi.id = mm.ministry_id
LEFT JOIN Members m ON mm.member_id = m.mem_id AND m.status = 'Member'
GROUP BY mi.id, mi.name
HAVING active_members > 0
ORDER BY active_members DESC;

