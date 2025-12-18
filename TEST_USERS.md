# Test Users for Role-Based Access Testing

This document lists all test users created for testing different role-based views in the Church Management System.

## Test User Credentials

**All test users have the same password:** `test123`

## Test Users by Role

### 1. Administrator
- **Username:** `test_admin`
- **Password:** `test123`
- **Role:** Administrator
- **Access Level:** Full system access
- **Can Access:**
  - All pages and features
  - User management
  - All member operations (add, edit, delete)
  - All event operations
  - All ministry operations
  - All reports
  - System settings

### 2. Pastor
- **Username:** `test_pastor`
- **Password:** `test123`
- **Role:** Pastor
- **Access Level:** Spiritual leader access
- **Can Access:**
  - Dashboard
  - Member management (view, add, edit)
  - Event management (view, add, edit)
  - Ministry management (view)
  - Attendance tracking
  - Reports
  - **Cannot Access:**
    - User management
    - Delete operations

### 3. Ministry Leader
- **Username:** `test_leader`
- **Password:** `test123`
- **Role:** Ministry Leader
- **Access Level:** Ministry-specific access
- **Can Access:**
  - Dashboard
  - Member viewing
  - Event viewing
  - Their assigned ministry details
  - Attendance tracking for their ministry
  - Limited reports
  - **Cannot Access:**
    - User management
    - Member editing/deletion
    - Event editing/deletion
    - Ministry management

### 4. Clerk
- **Username:** `test_clerk`
- **Password:** `test123`
- **Role:** Clerk
- **Access Level:** Administrative tasks
- **Can Access:**
  - Dashboard
  - Member management (view, add, edit)
  - Event management (view, add, edit)
  - Attendance tracking
  - Reports
  - **Cannot Access:**
    - User management
    - Delete operations (members, events)

### 5. Member
- **Username:** `test_member`
- **Password:** `test123`
- **Role:** Member
- **Access Level:** Basic read-only access
- **Can Access:**
  - Dashboard (limited view)
  - View own member profile
  - View events (read-only)
  - View ministries (read-only)
  - **Cannot Access:**
    - User management
    - Member management (add/edit/delete)
    - Event management (add/edit/delete)
    - Ministry management
    - Attendance tracking
    - Reports

## How to Use

1. **Import the test users:**
   ```bash
   mysql -u root < database/test_users.sql
   ```

2. **Login with any test user:**
   - Navigate to the login page
   - Enter the username (e.g., `test_admin`)
   - Enter the password: `test123`
   - Click login

3. **Test different views:**
   - Log in as each role
   - Navigate through the system
   - Verify that the correct pages and features are accessible
   - Verify that restricted features are not accessible

## Default Admin User

The default admin user is still available:
- **Username:** `admin`
- **Password:** `admin123`

## Notes

- All test users are set to 'Active' status
- Test users can be safely deleted and recreated using `database/test_users.sql`
- The password hash is generated using PHP's `password_hash()` function
- To regenerate test users, simply run the SQL file again (it uses `DELETE` then `INSERT`)

