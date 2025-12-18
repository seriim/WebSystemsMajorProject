-- Create Sunday School Attendance table
USE church_system_database;

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

