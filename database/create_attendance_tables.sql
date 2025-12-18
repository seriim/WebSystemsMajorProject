-- Create Vestry Hours table
USE church_system_database;

CREATE TABLE IF NOT EXISTS vestry_hours (
  id INT AUTO_INCREMENT PRIMARY KEY,
  date DATE NOT NULL,
  visitor_lastname VARCHAR(100),
  visitor_name VARCHAR(100),
  time_of_visit TIME,
  nature_of_visit VARCHAR(300),
  telephone_type VARCHAR(20),
  telephone VARCHAR(20),
  minister_comment TEXT,
  recorded_by INT NULL,
  FOREIGN KEY (recorded_by) REFERENCES Users(id)
);

-- Create Church Service Attendance table
CREATE TABLE IF NOT EXISTS church_service_attendance (
  id INT AUTO_INCREMENT PRIMARY KEY,
  date DATE NOT NULL,
  service_type VARCHAR(100),
  number_attended INT DEFAULT 0,
  notes VARCHAR(300),
  recorded_by INT NULL,
  FOREIGN KEY (recorded_by) REFERENCES Users(id)
);

