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
   - Default admin credentials:
     - Username: `admin`
     - Password: `admin123`

4. **Access**:
   - Navigate to `http://localhost/labs/MajorProjectFinal/`
   - Login with admin credentials

## Project Structure

```
MajorProjectFinal/
├── assets/
│   ├── css/          # Stylesheets
│   ├── js/           # JavaScript files
│   └── images/       # Images and icons
├── attendance/       # Attendance management pages
├── config/           # Configuration files
├── database/         # SQL schema and data files
├── events/           # Event management pages
├── includes/         # Reusable components (header, footer)
├── members/          # Member management pages
├── ministries/       # Ministry management pages
├── reports/          # Report generation pages
├── users/            # User management pages
├── login.php         # Login page
├── index.php         # Dashboard
└── README.md         # This file
```

## Role-Based Access Control

The system implements role-based access control with the following roles:

- **Administrator**: Full system access
- **Pastor**: Spiritual leader access
- **Ministry Leader**: Ministry-specific access
- **Clerk**: Administrative tasks
- **Member**: Basic read-only access

See `ROLE_BASED_ACCESS.md` for detailed access control documentation.

## Database Schema

The database includes the following tables:
- `Roles` - User roles
- `Users` - System users
- `Members` - Church members
- `Ministries` - Ministry groups
- `Ministry_Members` - Member-ministry relationships
- `Events` - Church events and milestones
- `Attendance` - Attendance records

See `database/schema.sql` for complete schema.

## Security Features

- Password hashing using PHP `password_hash()`
- SQL injection prevention using prepared statements
- XSS prevention using `htmlspecialchars()`
- Session management
- Role-based access control
- Input sanitization

## Development

### Adding New Features

1. Follow the existing code structure
2. Use prepared statements for database queries
3. Implement role-based access control where needed
4. Sanitize all user inputs
5. Use the reusable header/footer components

### Code Style

- Use meaningful variable names
- Comment complex logic
- Follow PSR-12 coding standards
- Use consistent indentation (4 spaces)

## Authors

- Joshane Beecher (2304845)
- Abbygayle Higgins (2106327)
- Serena Morris (2208659)
- Jahzeal Simms (2202446)

## License

This project is developed for educational purposes.

## Version Control

This project uses Git for version control. To contribute:

```bash
git add .
git commit -m "Your commit message"
git push
```
