# Church Management System - Setup Guide

## Complete Installation and Setup Instructions

This guide will walk you through setting up the Church Management System from scratch, including database setup, configuration, and verification.

---

## Prerequisites

Before you begin, ensure you have the following installed:

- **XAMPP** (PHP 8.0+ and MySQL/MariaDB)
  - Download from: https://www.apachefriends.org/
  - Install XAMPP in the default location or note your installation path

---

## Step 1: Install XAMPP

1. Download XAMPP from https://www.apachefriends.org/
2. Install XAMPP (default location: `/Applications/XAMPP/` on Mac, `C:\xampp\` on Windows)
3. Start XAMPP Control Panel
4. Start **Apache** and **MySQL** services

**Verify Installation:**
- Open browser and go to: `http://localhost`
- You should see the XAMPP welcome page

---

## Step 2: Place Project Files

### Option A: Using Git (Recommended)

```bash
# Navigate to your XAMPP htdocs directory
cd /Applications/XAMPP/xamppfiles/htdocs  # Mac
# OR
cd C:\xampp\htdocs  # Windows

# Clone the repository
git clone <your-repository-url> MajorProjectFinal

# Navigate to project
cd MajorProjectFinal
```

### Option B: Manual Copy

1. Copy the entire project folder to:
   - **Mac:** `/Applications/XAMPP/xamppfiles/htdocs/MajorProjectFinal`
   - **Windows:** `C:\xampp\htdocs\MajorProjectFinal`

---

## Step 3: Database Setup

### 3.1 Create the Database

**Method 1: Using phpMyAdmin (Easiest)**

1. Open browser and go to: `http://localhost/phpmyadmin`
2. Click on "New" in the left sidebar
3. Enter database name: `church_system_database`
4. Select collation: `utf8mb4_general_ci`
5. Click "Create"

**Method 2: Using MySQL Command Line**

```bash
# Mac
/Applications/XAMPP/xamppfiles/bin/mysql -u root -p

# Windows
C:\xampp\mysql\bin\mysql.exe -u root -p
```

Then run:
```sql
CREATE DATABASE church_system_database;
EXIT;
```

### 3.2 Import the Database Schema

**Method 1: Using phpMyAdmin**

1. Go to: `http://localhost/phpmyadmin`
2. Select `church_system_database` from the left sidebar
3. Click on "Import" tab
4. Click "Choose File" and select: `database/schema.sql`
5. Click "Go" at the bottom
6. Wait for "Import has been successfully finished" message

**Method 2: Using MySQL Command Line**

```bash
# Mac
/Applications/XAMPP/xamppfiles/bin/mysql -u root -p church_system_database < database/schema.sql

# Windows
C:\xampp\mysql\bin\mysql.exe -u root -p church_system_database < database\schema.sql
```

**Verify Database Creation:**
- Go to phpMyAdmin
- Select `church_system_database`
- You should see 10 tables:
  - `Roles`
  - `Users`
  - `Members`
  - `Ministries`
  - `Ministry_Members`
  - `Events`
  - `Attendance`
  - `sunday_school_attendance`
  - `vestry_hours`
  - `church_service_attendance`

---

## Step 4: Configure Database Connection

### 4.1 Check Database Configuration

Open `config/database.php` and verify the settings:

```php
// For Mac (default XAMPP path)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Usually empty for XAMPP
define('DB_NAME', 'church_system_database');
define('DB_SOCKET', '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock');
```

**For Windows, change the socket path:**
```php
define('DB_SOCKET', null);  // Windows doesn't use socket files
```

### 4.2 Test Database Connection

Create a test file `test_connection.php` in the project root:

```php
<?php
require_once 'config/database.php';

$conn = getDBConnection();

if ($conn) {
    echo "✓ Database connection successful!<br>";
    echo "Database: " . DB_NAME . "<br>";
    
    // Check tables
    $result = $conn->query("SHOW TABLES");
    $tables = $result->fetch_all(MYSQLI_NUM);
    echo "Tables found: " . count($tables) . "<br>";
    
    foreach ($tables as $table) {
        echo "- " . $table[0] . "<br>";
    }
    
    closeDBConnection($conn);
} else {
    echo "✗ Database connection failed!";
}
?>
```

**Run the test:**
- Open browser: `http://localhost/labs/MajorProjectFinal/test_connection.php`
- You should see a list of tables

**If connection fails:**
- Check MySQL is running in XAMPP Control Panel
- Verify database name in `config/database.php`
- Check socket path (Mac) or remove socket (Windows)
- Verify username/password (usually `root` with no password for XAMPP)

---

## Step 5: Add Dummy Data (Optional but Recommended)

### 5.1 Add Attendance Records

```bash
# Mac
/Applications/XAMPP/xamppfiles/bin/mysql -u root -p church_system_database < database/add_attendance_records.sql

# Windows
C:\xampp\mysql\bin\mysql.exe -u root -p church_system_database < database\add_attendance_records.sql
```

**Or using phpMyAdmin:**
1. Select `church_system_database`
2. Click "Import"
3. Choose `database/add_attendance_records.sql`
4. Click "Go"

### 5.2 Verify Data

Go to phpMyAdmin and check:
- `Members` table should have records
- `Attendance` table should have records
- `Events` table should have records
- `Ministries` table should have records

---

## Step 6: Access the Application

### 6.1 Open the Login Page

Open your browser and navigate to:
```
http://localhost/labs/MajorProjectFinal/login.php
```

**Note:** Adjust the path based on where you placed the project:
- If in `htdocs/MajorProjectFinal/` → `http://localhost/MajorProjectFinal/login.php`
- If in `htdocs/labs/MajorProjectFinal/` → `http://localhost/labs/MajorProjectFinal/login.php`

### 6.2 Default Login Credentials

**Administrator:**
- Username: `admin`
- Password: `admin123`

**Test Users (if created):**
- `test_admin` / `test123` (Administrator)
- `test_pastor` / `test123` (Pastor)
- `test_leader` / `test123` (Ministry Leader)
- `test_clerk` / `test123` (Clerk)
- `test_member` / `test123` (Member)

---

## Step 7: Verify Everything Works

### 7.1 Test Login

1. Go to login page
2. Enter admin credentials
3. You should be redirected to the dashboard

### 7.2 Check Dashboard

After logging in, verify:
- ✓ Dashboard loads without errors
- ✓ Stat cards show numbers (not zeros or errors)
- ✓ Attendance chart displays (if data exists)
- ✓ Navigation menu works

### 7.3 Test Key Features

**Members:**
1. Click "Members" in sidebar
2. Should see list of members (or empty if no data)
3. Try "Add Member" button
4. Fill form and save
5. Verify member appears in list

**Events:**
1. Click "Events" in sidebar
2. Should see list of events
3. Try adding a new event
4. Verify it appears in the list

**Reports:**
1. Click "Reports" in sidebar
2. Navigate through tabs (Membership, Attendance, Events, Milestones)
3. Charts should display (if data exists)
4. Try "Export PDF" button

**Attendance:**
1. Click "Attendance" in sidebar
2. Should see attendance options
3. Try adding attendance record
4. Verify it saves

---

## Step 8: Troubleshooting Common Issues

### Issue: "Database connection error"

**Solutions:**
1. Check MySQL is running in XAMPP Control Panel
2. Verify database name in `config/database.php` matches created database
3. Check socket path (Mac) or remove socket line (Windows)
4. Verify username/password (usually `root` with empty password)

### Issue: "Table doesn't exist"

**Solutions:**
1. Verify schema was imported correctly
2. Check database name matches
3. Re-import `database/schema.sql`

### Issue: "Cannot login"

**Solutions:**
1. Verify admin user exists in `Users` table
2. Check password hash in database
3. Default admin password: `admin123`
4. Try resetting password in database

### Issue: "Blank page or 500 error"

**Solutions:**
1. Check PHP error logs: `XAMPP/xamppfiles/logs/php_error_log`
2. Enable error display in `config/config.php`:
   ```php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```
3. Check file permissions
4. Verify all files are in correct location

### Issue: "Charts not displaying"

**Solutions:**
1. Check browser console for JavaScript errors (F12)
2. Verify Chart.js is loading (check Network tab)
3. Check if data exists in database
4. Verify `footer.php` includes Chart.js script

### Issue: "Icons not showing"

**Solutions:**
1. Check Font Awesome is loading
2. Verify internet connection (CDN)
3. Check browser console for 404 errors
4. Verify `includes/header.php` includes Font Awesome link

---

## Step 9: Database Sync Verification

### 9.1 Verify All Tables Exist

Run this query in phpMyAdmin or MySQL:

```sql
USE church_system_database;
SHOW TABLES;
```

Should show 10 tables.

### 9.2 Check Table Structures

Verify each table has correct columns:

```sql
DESCRIBE Members;
DESCRIBE Users;
DESCRIBE Events;
DESCRIBE Attendance;
```

### 9.3 Test Data Integrity

```sql
-- Check foreign key relationships
SELECT COUNT(*) FROM Members;
SELECT COUNT(*) FROM Users;
SELECT COUNT(*) FROM Events WHERE member_id IN (SELECT mem_id FROM Members);
```

### 9.4 Verify Default Data

```sql
-- Check roles
SELECT * FROM Roles;

-- Check admin user
SELECT * FROM Users WHERE username = 'admin';

-- Check ministries
SELECT * FROM Ministries;
```

---

## Step 10: Final Checklist

Before considering setup complete, verify:

- [ ] XAMPP Apache and MySQL are running
- [ ] Database `church_system_database` exists
- [ ] All 10 tables are created
- [ ] Can login with admin credentials
- [ ] Dashboard loads without errors
- [ ] Navigation menu works
- [ ] Can add/view members
- [ ] Can add/view events
- [ ] Reports pages load
- [ ] Charts display (if data exists)
- [ ] No PHP errors in browser or logs
- [ ] No JavaScript errors in console

---

## Additional Configuration

### Enable Error Reporting (Development Only)

In `config/config.php`, add:

```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
```

**⚠️ Remove this before production!**

### Set Timezone

In `config/config.php`, add:

```php
date_default_timezone_set('America/Jamaica'); // Change to your timezone
```

### Configure Base URL

If your project is in a subdirectory, update `config/config.php`:

```php
define('BASE_URL', '/labs/MajorProjectFinal/'); // Adjust path as needed
```

---

## Quick Reference

### Database Connection Test
```bash
# Mac
/Applications/XAMPP/xamppfiles/bin/mysql -u root -p -e "USE church_system_database; SHOW TABLES;"

# Windows
C:\xampp\mysql\bin\mysql.exe -u root -p -e "USE church_system_database; SHOW TABLES;"
```

### Re-import Schema
```bash
# Mac
/Applications/XAMPP/xamppfiles/bin/mysql -u root -p church_system_database < database/schema.sql

# Windows
C:\xampp\mysql\bin\mysql.exe -u root -p church_system_database < database\schema.sql
```

### Check PHP Version
```bash
# Mac
/Applications/XAMPP/xamppfiles/bin/php -v

# Windows
C:\xampp\php\php.exe -v
```

### Access phpMyAdmin
```
http://localhost/phpmyadmin
```

---

## Support

If you encounter issues:

1. Check the troubleshooting section above
2. Review PHP error logs: `XAMPP/xamppfiles/logs/php_error_log`
3. Check MySQL error logs: `XAMPP/xamppfiles/logs/mysql_error_log`
4. Verify all prerequisites are met
5. Ensure XAMPP services are running

---

## Next Steps

After setup is complete:

1. **Add Real Data:** Replace dummy data with actual church member information
2. **Create Users:** Add users for different roles (Pastor, Clerk, etc.)
3. **Configure Ministries:** Set up your church ministries
4. **Customize:** Adjust settings, colors, and branding as needed
5. **Backup:** Set up regular database backups

---

**Last Updated:** December 2025

