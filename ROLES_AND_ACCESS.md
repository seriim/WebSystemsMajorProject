# Roles and Access Control
## Church Management and Information System (CMIS)

---

## Overview

The system implements a comprehensive **Role-Based Access Control (RBAC)** system with 5 distinct roles, each with specific permissions and restrictions.

---

## Role Definitions

### 1. Administrator
**Full System Access**

**Permissions:**
- ✅ View, add, edit, and **delete** members
- ✅ View, add, edit, and **delete** events
- ✅ View, add, edit, and **delete** ministries
- ✅ Manage system users (add, edit, delete)
- ✅ Access all reports
- ✅ Track all attendance types
- ✅ Full dashboard access
- ✅ Export reports to PDF

**Restrictions:**
- None (full access)

**Use Case:** System administrator who manages all aspects of the church management system.

---

### 2. Pastor
**Spiritual Leader Access**

**Permissions:**
- ✅ View, add, and edit members (cannot delete)
- ✅ View, add, and edit events (cannot delete)
- ✅ View ministries (read-only)
- ✅ Access attendance tracking
- ✅ Access all reports
- ✅ Full dashboard access
- ✅ Export reports to PDF

**Restrictions:**
- ❌ Cannot delete members or events
- ❌ Cannot manage ministries (add/edit/delete)
- ❌ Cannot manage system users

**Use Case:** Church pastor who needs to view and manage member information and events but doesn't handle system administration.

---

### 3. Ministry Leader
**Ministry-Specific Access**

**Permissions:**
- ✅ View members (read-only)
- ✅ View events (read-only)
- ✅ View assigned ministry details
- ✅ Track attendance for their ministry
- ✅ Access limited reports
- ✅ View dashboard

**Restrictions:**
- ❌ Cannot add, edit, or delete members
- ❌ Cannot add, edit, or delete events
- ❌ Cannot manage ministries
- ❌ Cannot manage users
- ❌ Cannot access all reports
- ❌ Cannot export PDFs

**Use Case:** Ministry leader who needs to track attendance for their specific ministry and view relevant information.

---

### 4. Clerk
**Administrative Tasks Access**

**Permissions:**
- ✅ View, add, and edit members (cannot delete)
- ✅ View, add, and edit events (cannot delete)
- ✅ Track attendance
- ✅ Access reports
- ✅ View dashboard
- ✅ Export reports to PDF

**Restrictions:**
- ❌ Cannot delete members or events
- ❌ Cannot manage system users
- ❌ Cannot manage ministries

**Use Case:** Administrative staff who handle day-to-day data entry and record keeping.

---

### 5. Member
**Basic Read-Only Access**

**Permissions:**
- ✅ View own member profile only
- ✅ View dashboard (limited view)
- ✅ View events (read-only)
- ✅ View ministries (read-only)

**Restrictions:**
- ❌ Cannot view other members
- ❌ Cannot add, edit, or delete any records
- ❌ Cannot access reports
- ❌ Cannot manage attendance
- ❌ Cannot export PDFs
- ❌ Cannot access user management

**Use Case:** Regular church member who can only view their own information and general church information.

---

## Access Control Implementation

### How It Works

The system uses PHP functions to check user roles before allowing access:

```php
// Check if user has a specific role
hasRole('Administrator')

// Check if user has any of the required roles
hasAnyRole(['Administrator', 'Pastor', 'Clerk'])
```

### Implementation Points

1. **Page-Level Protection:**
   - Every page checks if user is logged in
   - Pages check for required roles before displaying content

2. **Action-Level Protection:**
   - Buttons/links are hidden if user doesn't have permission
   - Server-side validation prevents unauthorized actions

3. **Data-Level Protection:**
   - Members can only view their own profile
   - Queries filter data based on user role

---

## Access Control Matrix

| Feature | Administrator | Pastor | Ministry Leader | Clerk | Member |
|---------|--------------|--------|-----------------|-------|--------|
| **Members** |
| View All Members | ✅ | ✅ | ✅ | ✅ | ❌ |
| View Own Profile | ✅ | ✅ | ✅ | ✅ | ✅ |
| Add Member | ✅ | ✅ | ❌ | ✅ | ❌ |
| Edit Member | ✅ | ✅ | ❌ | ✅ | ❌ |
| Delete Member | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Events** |
| View Events | ✅ | ✅ | ✅ | ✅ | ✅ |
| Add Event | ✅ | ✅ | ❌ | ✅ | ❌ |
| Edit Event | ✅ | ✅ | ❌ | ✅ | ❌ |
| Delete Event | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Ministries** |
| View Ministries | ✅ | ✅ | ✅ | ✅ | ✅ |
| Add Ministry | ✅ | ❌ | ❌ | ❌ | ❌ |
| Edit Ministry | ✅ | ❌ | ❌ | ❌ | ❌ |
| Delete Ministry | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Attendance** |
| Record Attendance | ✅ | ✅ | ✅* | ✅ | ❌ |
| View Attendance | ✅ | ✅ | ✅* | ✅ | ❌ |
| **Reports** |
| View Reports | ✅ | ✅ | ✅ | ✅ | ❌ |
| Export PDF | ✅ | ✅ | ❌ | ✅ | ❌ |
| **Users** |
| Manage Users | ✅ | ❌ | ❌ | ❌ | ❌ |

*Ministry Leader can only manage attendance for their assigned ministry

---

## Security Features

### 1. Authentication
- Password hashing using bcrypt
- Session management
- Login validation
- Account status checking (Active/Inactive)

### 2. Authorization
- Role-based access control
- Function-level permission checks
- Page-level restrictions
- Action-level restrictions

### 3. Data Protection
- SQL injection prevention (prepared statements)
- XSS prevention (htmlspecialchars)
- Input sanitization
- Member privacy protection

---

## Code Examples

### Example 1: Checking User Role

```php
// In any PHP file
require_once __DIR__ . '/../config/config.php';
requireLogin(); // Ensures user is logged in

// Check if user is Administrator
if (hasRole('Administrator')) {
    // Show admin-only content
}

// Check if user has any of these roles
if (hasAnyRole(['Administrator', 'Pastor', 'Clerk'])) {
    // Show content for these roles
}
```

### Example 2: Restricting Member Access

```php
// In members/index.php
if (hasRole('Member')) {
    // Redirect members to their own profile
    header('Location: ' . BASE_URL . 'members/view.php?id=' . $member_id);
    exit();
}
```

### Example 3: Hiding UI Elements

```php
// In includes/header.php
<?php if (hasAnyRole(['Administrator', 'Pastor', 'Clerk'])): ?>
    <a href="members/index.php">Members</a>
<?php elseif (hasRole('Member')): ?>
    <a href="members/view.php?id=<?php echo $member_id; ?>">My Profile</a>
<?php endif; ?>
```

### Example 4: Action-Level Protection

```php
// In members/index.php
<?php if (hasAnyRole(['Administrator', 'Clerk'])): ?>
    <a href="?delete=<?php echo $member_id; ?>" class="btn-delete">
        Delete
    </a>
<?php endif; ?>
```

---

## Test Users

For testing role-based access, use these credentials:

| Username | Password | Role |
|----------|----------|------|
| `test_admin` | `test123` | Administrator |
| `test_pastor` | `test123` | Pastor |
| `test_leader` | `test123` | Ministry Leader |
| `test_clerk` | `test123` | Clerk |
| `test_member` | `test123` | Member |

**Note:** These test users are created in the database. See `database/test_users.sql` for details.

---

## Presentation Points

### Key Points to Highlight:

1. **5 Distinct Roles:** Each role has specific permissions tailored to their responsibilities
2. **Layered Security:** Authentication → Authorization → Data Protection
3. **Privacy Protection:** Members can only view their own information
4. **Flexible Access:** System supports different organizational needs
5. **Secure Implementation:** Prepared statements, input sanitization, role checks

### Demonstration Flow:

1. **Show Login:** Demonstrate logging in with different roles
2. **Show Dashboard:** Different views for different roles
3. **Show Members Page:** 
   - Admin/Pastor/Clerk: Full member list
   - Member: Redirected to own profile
4. **Show Actions:** 
   - Delete buttons only visible to Administrators
   - Edit buttons hidden for Ministry Leaders
5. **Show Reports:** 
   - All roles except Member can access
   - PDF export available to specific roles

---

## Technical Implementation

### Files Involved:

- `config/config.php` - Contains `hasRole()` and `hasAnyRole()` functions
- `includes/header.php` - Navigation based on roles
- `members/index.php` - Member list access control
- `members/view.php` - Profile view restrictions
- All module files - Action-level restrictions

### Database:

- `Roles` table - Defines available roles
- `Users` table - Links users to roles via `role` foreign key
- Session storage - Stores user role after login

---

**Last Updated:** December 2025

