/*
===========================================================
 Church Management and Information System
 Dummy Data for Testing
===========================================================
*/

USE church_system_database;

-- Insert additional users
INSERT IGNORE INTO Users (username, password, role, status) VALUES
('pastor_john', '$2y$12$5LLWx8mu9hFO99ksddMeAeq8VXhXgGhCHML.7XtZGwWGEXaTUObfm', 2, 'Active'),
('clerk_mary', '$2y$12$5LLWx8mu9hFO99ksddMeAeq8VXhXgGhCHML.7XtZGwWGEXaTUObfm', 4, 'Active'),
('leader_sarah', '$2y$12$5LLWx8mu9hFO99ksddMeAeq8VXhXgGhCHML.7XtZGwWGEXaTUObfm', 3, 'Active');

-- Insert Ministries (if not already inserted)
INSERT IGNORE INTO Ministries (id, name, description) VALUES
(1, 'Youth Ministry', 'Youth activities and programs'),
(2, 'Women\'s Ministry', 'Women\'s fellowship and activities'),
(3, 'Men\'s Ministry', 'Men\'s fellowship and activities'),
(4, 'Music Ministry', 'Choir and music programs'),
(5, 'Sunday School', 'Children\'s education programs'),
(6, 'Outreach Ministry', 'Community outreach and evangelism'),
(7, 'Prayer Ministry', 'Intercessory prayer and spiritual support'),
(8, 'Ushers Ministry', 'Greeting and ushering services');

-- Insert Members
INSERT IGNORE INTO Members (mem_id, first_name, middle_initials, last_name, dob, gender, home_address1, home_address2, town, parish, contact_home, contact_work, email, next_of_kin_name, next_of_kin_address, next_of_kin_relation, next_of_kin_contact, next_of_kin_email, status, date_joined, min_id) VALUES
(1, 'Serena', 'M', 'Morris', '1990-05-15', 'Female', '123 Church Street', 'Apt 4B', 'Kingston', 'St. Andrew', '876-555-0101', '876-555-0102', 'serena.morris@email.com', 'John Morris', '123 Church Street, Apt 4B', 'Father', '876-555-0103', 'john.morris@email.com', 'Member', '2018-03-10', 'YM001'),
(2, 'Joshane', 'A', 'Beecher', '1988-08-22', 'Male', '456 Main Road', NULL, 'Montego Bay', 'St. James', '876-555-0201', '876-555-0202', 'joshane.beecher@email.com', 'Jane Beecher', '456 Main Road', 'Wife', '876-555-0203', 'jane.beecher@email.com', 'Member', '2019-06-15', 'MM001'),
(3, 'Abbygayle', 'L', 'Higgins', '1992-11-30', 'Female', '789 Hope Avenue', NULL, 'Spanish Town', 'St. Catherine', '876-555-0301', NULL, 'abbygayle.higgins@email.com', 'Robert Higgins', '789 Hope Avenue', 'Brother', '876-555-0302', 'robert.higgins@email.com', 'Member', '2020-01-20', 'WM001'),
(4, 'Jahzeal', 'D', 'Simms', '1995-02-14', 'Male', '321 Faith Lane', 'Unit 12', 'Portmore', 'St. Catherine', '876-555-0401', '876-555-0402', 'jahzeal.simms@email.com', 'Maria Simms', '321 Faith Lane, Unit 12', 'Mother', '876-555-0403', 'maria.simms@email.com', 'Member', '2021-04-05', 'MM002'),
(5, 'Michael', 'J', 'Johnson', '1985-07-18', 'Male', '654 Grace Street', NULL, 'Mandeville', 'Manchester', '876-555-0501', '876-555-0502', 'michael.johnson@email.com', 'Patricia Johnson', '654 Grace Street', 'Wife', '876-555-0503', 'patricia.johnson@email.com', 'Member', '2017-09-12', 'MM003'),
(6, 'Sarah', 'K', 'Williams', '1993-09-25', 'Female', '987 Love Boulevard', 'Suite 5', 'Ocho Rios', 'St. Ann', '876-555-0601', NULL, 'sarah.williams@email.com', 'David Williams', '987 Love Boulevard, Suite 5', 'Husband', '876-555-0602', 'david.williams@email.com', 'Member', '2019-11-08', 'WM002'),
(7, 'David', 'R', 'Brown', '1991-12-05', 'Male', '147 Peace Road', NULL, 'Negril', 'Westmoreland', '876-555-0701', '876-555-0702', 'david.brown@email.com', 'Lisa Brown', '147 Peace Road', 'Sister', '876-555-0703', 'lisa.brown@email.com', 'Adherent', '2022-02-14', 'YM002'),
(8, 'Emily', 'S', 'Davis', '1994-04-20', 'Female', '258 Joy Circle', NULL, 'Falmouth', 'Trelawny', '876-555-0801', NULL, 'emily.davis@email.com', 'James Davis', '258 Joy Circle', 'Father', '876-555-0802', 'james.davis@email.com', 'Member', '2020-07-22', 'SS001'),
(9, 'Robert', 'T', 'Miller', '1987-10-11', 'Male', '369 Hope Drive', 'Apt 8', 'May Pen', 'Clarendon', '876-555-0901', '876-555-0902', 'robert.miller@email.com', 'Susan Miller', '369 Hope Drive, Apt 8', 'Wife', '876-555-0903', 'susan.miller@email.com', 'Member', '2018-05-30', 'MM004'),
(10, 'Lisa', 'A', 'Wilson', '1996-01-28', 'Female', '741 Faith Way', NULL, 'Savanna-la-Mar', 'Westmoreland', '876-555-1001', NULL, 'lisa.wilson@email.com', 'Mark Wilson', '741 Faith Way', 'Brother', '876-555-1002', 'mark.wilson@email.com', 'Adherent', '2021-08-15', 'YM003'),
(11, 'James', 'B', 'Moore', '1989-06-07', 'Male', '852 Grace Avenue', NULL, 'St. Ann\'s Bay', 'St. Ann', '876-555-1101', '876-555-1102', 'james.moore@email.com', 'Karen Moore', '852 Grace Avenue', 'Wife', '876-555-1103', 'karen.moore@email.com', 'Member', '2019-03-18', 'MM005'),
(12, 'Patricia', 'C', 'Taylor', '1992-03-16', 'Female', '963 Love Street', 'Unit 3', 'Port Antonio', 'Portland', '876-555-1201', NULL, 'patricia.taylor@email.com', 'Richard Taylor', '963 Love Street, Unit 3', 'Husband', '876-555-1202', 'richard.taylor@email.com', 'Member', '2020-10-05', 'WM003'),
(13, 'Christopher', 'D', 'Anderson', '1990-08-09', 'Male', '159 Peace Lane', NULL, 'Black River', 'St. Elizabeth', '876-555-1301', '876-555-1302', 'christopher.anderson@email.com', 'Jennifer Anderson', '159 Peace Lane', 'Sister', '876-555-1303', 'jennifer.anderson@email.com', 'Visitor', '2023-01-10', NULL),
(14, 'Jennifer', 'E', 'Thomas', '1993-11-22', 'Female', '357 Joy Road', NULL, 'Lucea', 'Hanover', '876-555-1401', NULL, 'jennifer.thomas@email.com', 'William Thomas', '357 Joy Road', 'Father', '876-555-1402', 'william.thomas@email.com', 'Adherent', '2022-06-20', 'SS002'),
(15, 'William', 'F', 'Jackson', '1986-05-03', 'Male', '468 Faith Boulevard', 'Apt 15', 'Morant Bay', 'St. Thomas', '876-555-1501', '876-555-1502', 'william.jackson@email.com', 'Nancy Jackson', '468 Faith Boulevard, Apt 15', 'Wife', '876-555-1503', 'nancy.jackson@email.com', 'Member', '2017-12-01', 'MM006');

-- Insert Ministry_Members (linking members to ministries)
INSERT IGNORE INTO Ministry_Members (member_id, ministry_id, role) VALUES
(1, 1, 'Leader'),           -- Serena Morris - Youth Ministry Leader
(1, 4, 'Member'),           -- Serena Morris - Music Ministry
(2, 3, 'Member'),           -- Joshane Beecher - Men's Ministry
(2, 6, 'Coordinator'),     -- Joshane Beecher - Outreach Ministry
(3, 2, 'Leader'),          -- Abbygayle Higgins - Women's Ministry Leader
(3, 7, 'Member'),          -- Abbygayle Higgins - Prayer Ministry
(4, 1, 'Member'),          -- Jahzeal Simms - Youth Ministry
(4, 3, 'Member'),          -- Jahzeal Simms - Men's Ministry
(5, 3, 'Leader'),          -- Michael Johnson - Men's Ministry Leader
(5, 6, 'Member'),          -- Michael Johnson - Outreach Ministry
(6, 2, 'Member'),          -- Sarah Williams - Women's Ministry
(6, 4, 'Leader'),          -- Sarah Williams - Music Ministry Leader
(7, 1, 'Member'),          -- David Brown - Youth Ministry
(8, 5, 'Teacher'),         -- Emily Davis - Sunday School Teacher
(8, 2, 'Member'),          -- Emily Davis - Women's Ministry
(9, 3, 'Member'),          -- Robert Miller - Men's Ministry
(9, 8, 'Leader'),          -- Robert Miller - Ushers Ministry Leader
(10, 1, 'Member'),         -- Lisa Wilson - Youth Ministry
(11, 3, 'Member'),         -- James Moore - Men's Ministry
(11, 6, 'Member'),         -- James Moore - Outreach Ministry
(12, 2, 'Member'),         -- Patricia Taylor - Women's Ministry
(12, 7, 'Coordinator'),    -- Patricia Taylor - Prayer Ministry Coordinator
(13, 1, 'Member'),         -- Christopher Anderson - Youth Ministry (Visitor)
(14, 5, 'Assistant'),      -- Jennifer Thomas - Sunday School Assistant
(15, 3, 'Member'),         -- William Jackson - Men's Ministry
(15, 8, 'Member');         -- William Jackson - Ushers Ministry

-- Insert Events
INSERT IGNORE INTO Events (event_type, date, member_id, notes) VALUES
('Birthday', '2024-12-20', 1, 'Serena Morris birthday celebration'),
('Wedding', '2024-12-25', 2, 'Joshane Beecher wedding ceremony'),
('Anniversary', '2024-12-28', 5, 'Michael and Patricia Johnson 10th anniversary'),
('Baptism', '2025-01-05', 7, 'David Brown baptism ceremony'),
('Birthday', '2025-01-10', 3, 'Abbygayle Higgins birthday'),
('Anniversary', '2025-01-15', 6, 'Sarah and David Williams 5th anniversary'),
('Birthday', '2025-01-20', 4, 'Jahzeal Simms birthday celebration'),
('Wedding', '2025-01-25', 10, 'Lisa Wilson wedding ceremony'),
('Baptism', '2025-02-01', 13, 'Christopher Anderson baptism'),
('Anniversary', '2025-02-05', 9, 'Robert and Susan Miller 8th anniversary'),
('Birthday', '2025-02-10', 8, 'Emily Davis birthday'),
('Death', '2024-11-15', 15, 'William Jackson memorial service'),
('Birthday', '2025-02-15', 11, 'James Moore birthday'),
('Anniversary', '2025-02-20', 12, 'Patricia and Richard Taylor 3rd anniversary'),
('Baptism', '2025-02-25', 14, 'Jennifer Thomas baptism');

-- Insert Attendance records (last 4 weeks)
INSERT IGNORE INTO Attendance (date, ministry_id, count, recorded_by) VALUES
-- Week 1 (4 weeks ago)
('2024-11-18', 1, 25, 1),  -- Youth Ministry
('2024-11-18', 2, 18, 1),  -- Women's Ministry
('2024-11-18', 3, 22, 1),  -- Men's Ministry
('2024-11-18', 4, 15, 1),  -- Music Ministry
('2024-11-18', 5, 30, 1),  -- Sunday School
('2024-11-18', 6, 12, 1),  -- Outreach Ministry
-- Week 2 (3 weeks ago)
('2024-11-25', 1, 28, 1),
('2024-11-25', 2, 20, 1),
('2024-11-25', 3, 24, 1),
('2024-11-25', 4, 16, 1),
('2024-11-25', 5, 32, 1),
('2024-11-25', 6, 14, 1),
-- Week 3 (2 weeks ago)
('2024-12-02', 1, 30, 1),
('2024-12-02', 2, 22, 1),
('2024-12-02', 3, 26, 1),
('2024-12-02', 4, 18, 1),
('2024-12-02', 5, 35, 1),
('2024-12-02', 6, 15, 1),
-- Week 4 (1 week ago)
('2024-12-09', 1, 32, 1),
('2024-12-09', 2, 24, 1),
('2024-12-09', 3, 28, 1),
('2024-12-09', 4, 20, 1),
('2024-12-09', 5, 38, 1),
('2024-12-09', 6, 16, 1),
-- This week
('2024-12-16', 1, 35, 1),
('2024-12-16', 2, 26, 1),
('2024-12-16', 3, 30, 1),
('2024-12-16', 4, 22, 1),
('2024-12-16', 5, 40, 1),
('2024-12-16', 6, 18, 1);

