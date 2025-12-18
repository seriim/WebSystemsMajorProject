/*
===========================================================
 Church Management and Information System
 Additional Attendance Records
===========================================================
*/

USE church_system_database;

-- Add Ministry Meeting Attendance Records
INSERT IGNORE INTO Attendance (date, ministry_id, count, recorded_by) VALUES
-- December 2025 - Youth Ministry
('2025-12-01', 1, 28, 1),
('2025-12-08', 1, 30, 1),
('2025-12-15', 1, 32, 1),
('2025-12-22', 1, 29, 1),
-- December 2025 - Women's Ministry
('2025-12-01', 2, 20, 1),
('2025-12-08', 2, 22, 1),
('2025-12-15', 2, 24, 1),
('2025-12-22', 2, 21, 1),
-- December 2025 - Men's Ministry
('2025-12-01', 3, 24, 1),
('2025-12-08', 3, 26, 1),
('2025-12-15', 3, 25, 1),
('2025-12-22', 3, 27, 1),
-- December 2025 - Music Ministry
('2025-12-01', 4, 16, 1),
('2025-12-08', 4, 18, 1),
('2025-12-15', 4, 17, 1),
('2025-12-22', 4, 19, 1),
-- December 2025 - Sunday School
('2025-12-01', 5, 32, 1),
('2025-12-08', 5, 35, 1),
('2025-12-15', 5, 33, 1),
('2025-12-22', 5, 34, 1),
-- December 2025 - Outreach Ministry
('2025-12-01', 6, 14, 1),
('2025-12-08', 6, 15, 1),
('2025-12-15', 6, 16, 1),
('2025-12-22', 6, 15, 1),
-- November 2025 - Various Ministries
('2025-11-24', 1, 27, 1),
('2025-11-24', 2, 19, 1),
('2025-11-24', 3, 23, 1),
('2025-11-24', 4, 15, 1),
('2025-11-24', 5, 31, 1),
('2025-11-24', 6, 13, 1),
('2025-11-17', 1, 26, 1),
('2025-11-17', 2, 18, 1),
('2025-11-17', 3, 22, 1),
('2025-11-17', 4, 14, 1),
('2025-11-17', 5, 30, 1),
('2025-11-17', 6, 12, 1);

-- Add Sunday School Attendance Records
INSERT IGNORE INTO sunday_school_attendance (date, category, name, dob, next_of_kin_name, next_of_kin_contact, attended, recorded_by) VALUES
-- Ages 3 and Under
('2025-12-01', 'ages_3_under', 'Emma Johnson', '2023-05-15', 'Sarah Johnson', '876-555-1001', 1, 1),
('2025-12-01', 'ages_3_under', 'Lucas Brown', '2022-11-20', 'David Brown', '876-555-1002', 1, 1),
('2025-12-08', 'ages_3_under', 'Emma Johnson', '2023-05-15', 'Sarah Johnson', '876-555-1001', 1, 1),
('2025-12-08', 'ages_3_under', 'Lucas Brown', '2022-11-20', 'David Brown', '876-555-1002', 1, 1),
('2025-12-15', 'ages_3_under', 'Emma Johnson', '2023-05-15', 'Sarah Johnson', '876-555-1001', 1, 1),
('2025-12-15', 'ages_3_under', 'Lucas Brown', '2022-11-20', 'David Brown', '876-555-1002', 0, 1),
-- Ages 9-11
('2025-12-01', 'ages_9_11', 'Sophia Williams', '2015-08-10', 'Michael Williams', '876-555-2001', 1, 1),
('2025-12-01', 'ages_9_11', 'James Davis', '2014-12-05', 'Lisa Davis', '876-555-2002', 1, 1),
('2025-12-01', 'ages_9_11', 'Olivia Miller', '2015-03-22', 'Robert Miller', '876-555-2003', 1, 1),
('2025-12-08', 'ages_9_11', 'Sophia Williams', '2015-08-10', 'Michael Williams', '876-555-2001', 1, 1),
('2025-12-08', 'ages_9_11', 'James Davis', '2014-12-05', 'Lisa Davis', '876-555-2002', 1, 1),
('2025-12-08', 'ages_9_11', 'Olivia Miller', '2015-03-22', 'Robert Miller', '876-555-2003', 0, 1),
('2025-12-15', 'ages_9_11', 'Sophia Williams', '2015-08-10', 'Michael Williams', '876-555-2001', 1, 1),
('2025-12-15', 'ages_9_11', 'James Davis', '2014-12-05', 'Lisa Davis', '876-555-2002', 1, 1),
-- Ages 12 and Above
('2025-12-01', 'ages_12_above', 'Noah Wilson', '2012-07-18', 'Jennifer Wilson', '876-555-3001', 1, 1),
('2025-12-01', 'ages_12_above', 'Ava Martinez', '2011-09-30', 'Carlos Martinez', '876-555-3002', 1, 1),
('2025-12-01', 'ages_12_above', 'Ethan Anderson', '2010-04-12', 'Patricia Anderson', '876-555-3003', 1, 1),
('2025-12-08', 'ages_12_above', 'Noah Wilson', '2012-07-18', 'Jennifer Wilson', '876-555-3001', 1, 1),
('2025-12-08', 'ages_12_above', 'Ava Martinez', '2011-09-30', 'Carlos Martinez', '876-555-3002', 1, 1),
('2025-12-08', 'ages_12_above', 'Ethan Anderson', '2010-04-12', 'Patricia Anderson', '876-555-3003', 0, 1),
('2025-12-15', 'ages_12_above', 'Noah Wilson', '2012-07-18', 'Jennifer Wilson', '876-555-3001', 1, 1),
('2025-12-15', 'ages_12_above', 'Ava Martinez', '2011-09-30', 'Carlos Martinez', '876-555-3002', 1, 1);

-- Add Vestry Hours Records
INSERT IGNORE INTO vestry_hours (date, visitor_lastname, visitor_name, time_of_visit, nature_of_visit, telephone_type, telephone, minister_comment, recorded_by) VALUES
('2025-12-02', 'Smith', 'John', '10:00:00', 'Pastoral counseling', 'Cell', '876-555-4001', 'Discussed family matters and prayer requests', 1),
('2025-12-03', 'Johnson', 'Mary', '14:30:00', 'Marriage counseling', 'Home', '876-555-4002', 'Scheduled follow-up session', 1),
('2025-12-05', 'Williams', 'Robert', '11:15:00', 'Spiritual guidance', 'Work', '876-555-4003', 'Seeking direction for life decisions', 1),
('2025-12-09', 'Brown', 'Patricia', '09:30:00', 'Prayer request', 'Cell', '876-555-4004', 'Prayer for health concerns', 1),
('2025-12-10', 'Davis', 'Michael', '15:00:00', 'Baptism inquiry', 'Home', '876-555-4005', 'Interested in baptism for child', 1),
('2025-12-12', 'Miller', 'Sarah', '10:45:00', 'Pastoral counseling', 'Cell', '876-555-4006', 'Grief counseling session', 1),
('2025-12-16', 'Wilson', 'David', '13:00:00', 'Spiritual guidance', 'Work', '876-555-4007', 'Career and faith discussion', 1),
('2025-12-17', 'Moore', 'Jennifer', '11:30:00', 'Marriage counseling', 'Home', '876-555-4008', 'Relationship support needed', 1),
('2025-12-19', 'Taylor', 'Christopher', '09:00:00', 'Prayer request', 'Cell', '876-555-4009', 'Prayer for job situation', 1),
('2025-12-20', 'Anderson', 'Lisa', '14:00:00', 'Baptism inquiry', 'Home', '876-555-4010', 'Adult baptism inquiry', 1);

-- Add Church Service Attendance Records
INSERT IGNORE INTO church_service_attendance (date, service_type, number_attended, notes, recorded_by) VALUES
('2025-12-01', 'Sunday Morning Service', 145, 'Regular Sunday service', 1),
('2025-12-01', 'Sunday Evening Service', 98, 'Evening worship', 1),
('2025-12-08', 'Sunday Morning Service', 152, 'Regular Sunday service', 1),
('2025-12-08', 'Sunday Evening Service', 105, 'Evening worship', 1),
('2025-12-15', 'Sunday Morning Service', 148, 'Regular Sunday service', 1),
('2025-12-15', 'Sunday Evening Service', 102, 'Evening worship', 1),
('2025-12-22', 'Sunday Morning Service', 158, 'Regular Sunday service - Christmas week', 1),
('2025-12-22', 'Sunday Evening Service', 112, 'Evening worship', 1),
('2025-12-24', 'Christmas Eve Service', 185, 'Special Christmas Eve service', 1),
('2025-12-25', 'Christmas Day Service', 165, 'Christmas Day celebration', 1),
('2025-11-24', 'Sunday Morning Service', 142, 'Regular Sunday service', 1),
('2025-11-24', 'Sunday Evening Service', 95, 'Evening worship', 1),
('2025-11-17', 'Sunday Morning Service', 138, 'Regular Sunday service', 1),
('2025-11-17', 'Sunday Evening Service', 92, 'Evening worship', 1);

