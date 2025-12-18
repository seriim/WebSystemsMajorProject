/*
===========================================================
 Church Management and Information System
 Database Reports and Quesries

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
WHERE MONTH(a.date) = 1   -- defult to 1 aka January, replace with desired month
  AND YEAR(a.date) = 2025 -- defult to 2025, replace with desired year
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
WHERE MONTH(dob) = 7;  -- defult to 7 aka July, replace with desired month

-- By Week
SELECT first_name, last_name, dob
FROM Members
WHERE WEEK(dob, 1) = 30;  -- defult to 30, replace with desired week number



------------------------------------------------------------
-- 3. Membership Growth Report
-- Inputs: month, quarter, year
-- Output: number of new members joined
------------------------------------------------------------
-- This will output the churches growth by month, showing number of new members joined 
-- Growth by Month

SELECT MONTH(date_joined) AS month, COUNT(*) AS new_members
FROM Members
WHERE YEAR(date_joined) = 2025
GROUP BY MONTH(date_joined)
ORDER BY month;

-- This will output the churches growth by quarter, showing number of new members joined
-- Growth by Quarter
SELECT QUARTER(date_joined) AS quarter, COUNT(*) AS new_members
FROM Members
WHERE YEAR(date_joined) = 2025 
GROUP BY QUARTER(date_joined)
ORDER BY quarter;

-- This will output the churches growth by year, showing number of new members joined
-- Growth by Year
SELECT YEAR(date_joined) AS year, COUNT(*) AS new_members
FROM Members
GROUP BY YEAR(date_joined)
ORDER BY year;


------------------------------------------------------------
-- 4. Ministry Participation Report
-- Inputs: ministry, date range
-- Output: attendance + member list
------------------------------------------------------------

-- This will output the attendance and member list for a specific ministry within a date range

SELECT m.name AS ministry, mem.first_name, mem.last_name, a.date, a.count
FROM Ministries m
JOIN Ministry_Members mm ON m.id = mm.ministry_id
JOIN Members mem ON mm.member_id = mem.mem_id
LEFT JOIN Attendance a ON m.id = a.ministry_id
WHERE m.id = 1  -- defult to the id 1, replace with desired ministry id
  AND a.date BETWEEN '2023-01-01' AND '2023-12-31'
ORDER BY a.date;


------------------------------------------------------------
-- 5. Search Members by Last Name (pattern match)
-- Input: partial last name
------------------------------------------------------------
SELECT mem_id, first_name, last_name, email
FROM Members
WHERE last_name LIKE 'Sm%'; -- outputting last names starting with 'Sm'


------------------------------------------------------------
-- 6. Search Members by Email Domain
-- Input: domain (for example: '%@gmail.com')
------------------------------------------------------------
SELECT first_name, last_name, email
FROM Members
WHERE email LIKE '%@gmail.com';


------------------------------------------------------------
-- 7. Ministry Role Search
-- Input: role keyword (for example: '%Leader%')
------------------------------------------------------------
SELECT m.name AS ministry, mem.first_name, mem.last_name, mm.role
FROM Ministries m
JOIN Ministry_Members mm ON m.id = mm.ministry_id
JOIN Members mem ON mm.member_id = mem.mem_id
WHERE mm.role LIKE '%Leader%'; -- outputting roles containing 'Leader'


------------------------------------------------------------
-- 8. Event Notes Keyword Search
-- Input: keyword in notes ( for example:'%birthday%')
------------------------------------------------------------
SELECT e.event_type, e.date, mem.first_name, mem.last_name, e.notes
FROM Events e
JOIN Members mem ON e.member_id = mem.mem_id
WHERE e.notes LIKE '%birthday%'; -- outputting notes containing 'birthday'


------------------------------------------------------------
-- 9. Attendance Recorded By User Search
-- Input: username pattern
------------------------------------------------------------
SELECT a.date, m.name AS ministry, a.count, u.username
FROM Attendance a
JOIN Ministries m ON a.ministry_id = m.id
JOIN Users u ON a.recorded_by = u.id
WHERE u.username LIKE 'pastor%'; -- outputting usernames starting with 'pastor'

