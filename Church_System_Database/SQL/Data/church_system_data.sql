/*
===========================================================
 Church Management and Information System
 Database Data

 Authors:
   - Joshane Beecher   - 2304845
   - Abbygayle Higgins - 2106327
   - Serena Morris     - 2208659
   - Jahzeal Simms     - 2202446
===========================================================
*/

/*
===========================================================
 Church Management and Information System
 Seed Data Inserts
===========================================================
*/

USE church_system_database;

-- 1. Roles
INSERT INTO Roles (role_name, description) VALUES
('Administrator', 'Full system access'),
('Pastor', 'Spiritual leader of the church'),
('Ministry Leader', 'Leads a specific ministry'),
('Clerk', 'Handles administrative tasks'),
('Member', 'Regular church member');

-- 2. Users
INSERT INTO Users (username, password, role, status) VALUES
('admin1', 'adminpass', 1, 'Active'),
('pastor_john', 'pastorpass', 2, 'Active'),
('leader_mary', 'leaderpass', 3, 'Active'),
('clerk_sam', 'clerkpass', 4, 'Active');

-- 3. Members
INSERT INTO Members (
  first_name, middle_initials, last_name, dob, gender,
  home_address1, home_address2, town, parish,
  contact_home, contact_work, email,
  next_of_kin_name, next_of_kin_address, next_of_kin_relation, next_of_kin_contact, next_of_kin_email,
  status, date_joined, min_id, passing_date
) VALUES
('John', 'A', 'Doe', '1980-05-12', 'Male',
 '123 Main St', 'Apt 4', 'Kingston', 'St. Andrew',
 '555-1234', '555-5678', 'john.doe@email.com',
 'Jane Doe', '456 Elm St, Kingston', 'Spouse', '555-9999', 'jane.doe@email.com',
 'Member', '2010-01-15', 'M001', NULL),

('Mary', 'B', 'Smith', '1990-07-22', 'Female',
 '789 Oak Rd', NULL, 'Montego Bay', 'St. James',
 '555-2222', '555-3333', 'mary.smith@email.com',
 'Paul Smith', '101 Pine St, Montego Bay', 'Brother', '555-8888', 'paul.smith@email.com',
 'Member', '2015-03-10', 'M002', NULL),

('Sam', 'C', 'Brown', '2000-11-05', 'Male',
 '55 Palm Ave', NULL, 'Ocho Rios', 'St. Ann',
 '555-4444', '555-5555', 'sam.brown@email.com',
 'Lisa Brown', '77 Cedar St, Ocho Rios', 'Mother', '555-7777', 'lisa.brown@email.com',
 'Visitor', '2022-09-01', 'M003', NULL);

-- 4. Ministries
INSERT INTO Ministries (name, description) VALUES
('Youth Ministry', 'Focuses on youth activities'),
('Music Ministry', 'Handles worship and music'),
('Outreach Ministry', 'Community outreach and evangelism');

-- 5. Ministry_Members
INSERT INTO Ministry_Members (member_id, ministry_id, role) VALUES
(1, 1, 'Youth Mentor'),
(2, 2, 'Choir Leader'),
(3, 3, 'Volunteer');

-- 6. Events
INSERT INTO Events (event_type, date, member_id, notes) VALUES
('Wedding', '2020-06-15', 1, 'John married Jane'),
('Birthday', '2021-07-22', 2, 'Mary’s 31st birthday'),
('Baptism', '2022-11-05', 3, 'Sam baptized into the church');

-- 7. Attendance
INSERT INTO Attendance (date, ministry_id, count, recorded_by) VALUES
('2023-01-01', 1, 25, 2),
('2023-01-08', 2, 15, 3),
('2023-01-15', 3, 30, 4);