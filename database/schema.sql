/*
===========================================================
 Church Management and Information System
 Database Entities & Relationships

 Authors:
   - Joshane Beecher   - 2304845
   - Abbygayle Higgins - 2106327
   - Serena Morris     - 2208659
   - Jahzeal Simms     - 2202446
===========================================================
*/

CREATE DATABASE IF NOT EXISTS church_system_database;
USE church_system_database;

-- Creating the Roles table to define different user roles within the church system
CREATE TABLE IF NOT EXISTS Roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  role_name ENUM('Administrator','Pastor','Ministry Leader','Clerk','Member') NOT NULL,
  description VARCHAR(300)
);

-- Creating the Users table to keep track of all the login credentials (depends on Roles)
CREATE TABLE IF NOT EXISTS Users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(300) NOT NULL,
  role INT NOT NULL,
  status ENUM('Active','Inactive') DEFAULT 'Active',
  FOREIGN KEY (role) REFERENCES Roles(id)
);

-- Creating the Members table to store information about church members
CREATE TABLE IF NOT EXISTS Members (
  mem_id INT AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(50) NOT NULL,
  middle_initials VARCHAR(10),
  last_name VARCHAR(50) NOT NULL,
  dob DATE,
  gender ENUM('Male','Female','Other'),
  home_address1 VARCHAR(100),
  home_address2 VARCHAR(100),
  town VARCHAR(50),
  parish VARCHAR(50),
  contact_home VARCHAR(20),
  contact_work VARCHAR(20),
  email VARCHAR(100) UNIQUE,
  next_of_kin_name VARCHAR(100),
  next_of_kin_address VARCHAR(150),
  next_of_kin_relation VARCHAR(50),
  next_of_kin_contact VARCHAR(20),
  next_of_kin_email VARCHAR(100),
  status ENUM('Member','Adherent','Visitor') DEFAULT 'Visitor',
  date_joined DATE,
  min_id VARCHAR(5), -- ministry ID, 5 characters max
  passing_date DATE
);

-- Creating the Ministries table to store different ministries within the church
CREATE TABLE IF NOT EXISTS Ministries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description VARCHAR(300)
);

-- Creating the Ministry_Members table to link the members of the church to a ministry (junction table: depends on Members + Ministries)
CREATE TABLE IF NOT EXISTS Ministry_Members (
  id INT AUTO_INCREMENT PRIMARY KEY,
  member_id INT NOT NULL,
  ministry_id INT NOT NULL,
  role VARCHAR(50),
  FOREIGN KEY (member_id) REFERENCES Members(mem_id),
  FOREIGN KEY (ministry_id) REFERENCES Ministries(id)
);

-- Creating the Events table to store events that happen at the church (depends on Members)
CREATE TABLE IF NOT EXISTS Events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_type ENUM('Wedding','Birthday','Anniversary','Baptism','Death'),
  date DATE NOT NULL,
  member_id INT NOT NULL,
  notes VARCHAR(300),
  FOREIGN KEY (member_id) REFERENCES Members(mem_id)
);

-- Creating the Attendance table to store attendance records (depends on Ministries + Users)
CREATE TABLE IF NOT EXISTS Attendance (
  id INT AUTO_INCREMENT PRIMARY KEY,
  date DATE NOT NULL,
  ministry_id INT NOT NULL,
  count INT DEFAULT 0,
  recorded_by INT NULL,
  FOREIGN KEY (ministry_id) REFERENCES Ministries(id),
  FOREIGN KEY (recorded_by) REFERENCES Users(id)
);

-- Creating the Sunday School Attendance table to track individual children's attendance
CREATE TABLE IF NOT EXISTS sunday_school_attendance (
  id INT AUTO_INCREMENT PRIMARY KEY,
  date DATE NOT NULL,
  category ENUM('ages_3_under', 'ages_9_11', 'ages_12_above') NOT NULL,
  name VARCHAR(100),
  dob DATE,
  next_of_kin_name VARCHAR(100),
  next_of_kin_contact VARCHAR(20),
  attended TINYINT(1) DEFAULT 1,
  recorded_by INT NULL,
  FOREIGN KEY (recorded_by) REFERENCES Users(id)
);

-- Insert default roles (INSERT IGNORE prevents duplicate key errors)
INSERT IGNORE INTO Roles (id, role_name, description) VALUES
(1, 'Administrator', 'Full system access'),
(2, 'Pastor', 'Spiritual leader of the church'),
(3, 'Ministry Leader', 'Leads a specific ministry'),
(4, 'Clerk', 'Handles administrative tasks'),
(5, 'Member', 'Regular church member');

-- Insert default admin user (password: admin123) - INSERT IGNORE prevents duplicate username errors
INSERT IGNORE INTO Users (username, password, role, status) VALUES
('admin', '$2y$12$5LLWx8mu9hFO99ksddMeAeq8VXhXgGhCHML.7XtZGwWGEXaTUObfm', 1, 'Active');
