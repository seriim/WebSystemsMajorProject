# Quick Setup Guide - Church Management System

## Fast Setup (5 Minutes)

### Step 1: Start XAMPP
1. Open XAMPP Control Panel
2. Start **Apache** and **MySQL**

### Step 2: Create Database
Open phpMyAdmin: `http://localhost/phpmyadmin`

**Option A: Using SQL (Quick)**
1. Click "SQL" tab
2. Paste and run:
```sql
CREATE DATABASE IF NOT EXISTS church_system_database;
USE church_system_database;
```
3. Click "Import" tab
4. Select `database/schema.sql`
5. Click "Go"

**Option B: Manual**
1. Click "New" → Name: `church_system_database` → Create
2. Select database → "Import" → Choose `database/schema.sql` → Go

### Step 3: Add Dummy Data (Optional)
1. In phpMyAdmin, select `church_system_database`
2. Click "Import"
3. Choose `database/add_attendance_records.sql`
4. Click "Go"

### Step 4: Access Application
Open browser: `http://localhost/labs/MajorProjectFinal/login.php`

**Login:**
- Username: `admin`
- Password: `admin123`

### Step 5: Verify
- ✓ Dashboard loads
- ✓ Can view members
- ✓ Can add events
- ✓ Reports work

---

## Troubleshooting

**Can't connect to database?**
- Check MySQL is running in XAMPP
- Verify database name: `church_system_database`
- Check `config/database.php` settings

**Blank page?**
- Check PHP error logs
- Verify all files are in correct location
- Check file permissions

**Can't login?**
- Default: `admin` / `admin123`
- Check `Users` table has admin record

---

## Database Sync Check

Run in phpMyAdmin SQL tab:
```sql
USE church_system_database;
SHOW TABLES;
```

Should show 10 tables.

---

For detailed instructions, see `SETUP_GUIDE.md`

