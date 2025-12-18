# Role-Based Access Control Documentation

## How Roles Differentiate Views

The system uses role-based access control (RBAC) to show/hide features based on user roles. Here's how it works:

### Role Hierarchy
1. **Administrator** - Full system access
2. **Pastor** - Spiritual leader access
3. **Ministry Leader** - Ministry-specific access
4. **Clerk** - Administrative tasks
5. **Member** - Basic read-only access

### Access Control Functions

Located in `config/config.php`:

```php
// Check if user has a specific role
hasRole('Administrator')

// Check if user has any of the required roles
hasAnyRole(['Administrator', 'Clerk', 'Pastor'])

// Require login (redirects if not logged in)
requireLogin()

// Require specific role (redirects if doesn't have role)
requireRole('Administrator')
```

### How Views Differ by Role

#### 1. Navigation Menu (`includes/header.php`)
- **All Users**: Dashboard, Members, Attendance, Events, Ministries, Reports
- **Administrator Only**: Users management link (line 52-57)

#### 2. Members Module

**View Members (`members/index.php`):**
- **All Users**: Can view member list
- **Administrator/Clerk/Pastor**: Can add members (line 54-59)
- **Administrator/Clerk**: Can delete members (line 11)

**View Member Details (`members/view.php`):**
- **All Users**: Can view member details
- **Administrator/Clerk/Pastor**: Can edit member (line 40-45)

**Add/Edit Members (`members/add.php`, `members/edit.php`):**
- **Administrator/Clerk/Pastor Only**: Access restricted (line 5-8)

#### 3. Events Module

**View Events (`events/index.php`):**
- **All Users**: Can view events
- **Administrator/Clerk**: Can add, edit, delete events (line 82-87, 124-129)

**Add/Edit Events (`events/add.php`, `events/edit.php`):**
- **Administrator/Clerk/Pastor Only**: Access restricted

#### 4. Users Module (`users/index.php`)
- **Administrator Only**: Full access to user management

#### 5. Ministries Module
- **All Users**: Can view ministries
- **Administrator Only**: Can add/edit/delete ministries

### Example: Member vs Admin View

**Member Role:**
- ✅ Can view dashboard
- ✅ Can view member list
- ✅ Can view member details
- ❌ Cannot add/edit/delete members
- ❌ Cannot access Users page
- ❌ Cannot manage ministries

**Administrator Role:**
- ✅ Full access to all features
- ✅ Can manage users
- ✅ Can add/edit/delete everything
- ✅ Can access all reports

### Implementation Pattern

The code uses conditional rendering:

```php
<?php if (hasAnyRole(['Administrator', 'Clerk', 'Pastor'])): ?>
    <!-- Admin-only content -->
    <a href="add.php">Add Member</a>
<?php endif; ?>
```

This pattern is used throughout the application to show/hide features based on roles.

