# Church Management and Information System (CMIS)

A comprehensive web-based church management system built with PHP, MySQL, JavaScript, HTML5, CSS3, and Bootstrap 5.

## Features

- **Membership Management**: Complete member registration, editing, and tracking
- **Attendance Management**: Track attendance across different ministries
- **Event & Milestone Tracking**: Manage birthdays, weddings, anniversaries, baptisms, and deaths
- **Ministry Group Management**: Organize members into different ministries
- **User Roles and Access Control**: Role-based access (Administrator, Pastor, Ministry Leader, Clerk, Member)
- **Reports**: Generate various reports including membership, attendance, and events
- **Dashboard**: Overview with statistics and trends

## Technology Stack

- **Backend**: PHP 8+
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Icons**: Font Awesome 6.4.0
- **Charts**: Chart.js

## Installation

1. **Prerequisites**:
   - XAMPP (or similar PHP/MySQL environment)
   - PHP 8.0 or higher
   - MySQL/MariaDB

2. **Database Setup**:
   ```bash
   mysql -u root < database/schema.sql
   mysql -u root < database/dummy_data.sql
   mysql -u root < database/dashboard_data.sql
   ```

3. **Configuration**:
   - Update `config/database.php` if needed (default uses XAMPP settings)

4. **Access**:
   - Navigate to `http://localhost/labs/MajorProjectFinal/`
   - Login with one of the demo credentials below

## Demo Login Credentials

### Administrator Accounts
- **Username:** `admin` | **Password:** `admin123`
- **Username:** `test_admin` | **Password:** `test123`

### Pastor Account
- **Username:** `test_pastor` | **Password:** `test123`

### Ministry Leader Account
- **Username:** `test_leader` | **Password:** `test123`

### Clerk Account
- **Username:** `test_clerk` | **Password:** `test123`

### Member Account
- **Username:** `test_member` | **Password:** `test123`

**Note:** Test users (`test_*`) are created when you import the database schema. Use these accounts to test different role-based access levels.



## Role-Based Access Control

The system implements role-based access control with the following roles:

- **Administrator**: Full system access
- **Pastor**: Spiritual leader access
- **Ministry Leader**: Ministry-specific access
- **Clerk**: Administrative tasks
- **Member**: Basic read-only access

For detailed access control information, see the User Manual.

## Database Schema

The database includes the following tables:
- `Roles` - User roles
- `Users` - System users
- `Members` - Church members
- `Ministries` - Ministry groups
- `Ministry_Members` - Member-ministry relationships
- `Events` - Church events and milestones
- `Attendance` - Attendance records (ministry meetings)
- `sunday_school_attendance` - Children's Sunday School attendance
- `vestry_hours` - Minister's vestry appointments
- `church_service_attendance` - Church service attendance


## Authors

- Joshane Beecher (2304845)
- Abbygayle Higgins (2106327)
- Serena Morris (2208659)
- Jahzeal Simms (2202446)

