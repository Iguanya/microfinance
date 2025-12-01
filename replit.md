# mangoO Microfinance Management - Replit Setup

## Overview
mangoO is a lightweight, yet powerful software solution for small microfinance institutions. This is a PHP web application originally designed for MySQL, now converted to run on Replit using SQLite.

**Original Project:** GitHub import  
**Technology Stack:** PHP 8.2, SQLite, JavaScript (jQuery), CSS  
**Current State:** Fully functional and running on Replit

## Recent Changes (December 2025)

### Dashboard UI Overhaul & Modern Design
- **Bootstrap 5 Integration:** Complete responsive design overhaul
- **Interactive Charts:** Chart.js integration with:
  - Loan status distribution (doughnut chart)
  - Top customers by borrowing amount (bar chart)
- **Key Statistics Dashboard:** Real-time metrics display
  - Total customers, active loans, total savings, total shares
- **Improved Navigation:**
  - Modern navbar with dropdown menus
  - Responsive sidebar with quick actions
  - Professional color scheme and styling
- **Data Tables:** Enhanced with Bootstrap styling
  - Hover effects, better readability
  - Professional appearance

## Recent Changes (Earlier - December 2025)

### Database Migration (MySQL → SQLite)
- **Original:** MySQL/MariaDB database
- **Current:** SQLite database (mangoo.db)
- **Reason:** Replit environment doesn't support MySQL due to permission restrictions
- **Impact:** Fully compatible, all 26 tables migrated successfully with test data

### Code Refactoring
- Created PDO wrapper functions (db_query, db_fetch_assoc, db_fetch_array, db_error, db_escape)
- Replaced all mysqli_* function calls throughout the codebase
- Updated functions.php to use PDO with SQLite
- Modified login.php to eliminate session warnings

### PHP 8 Compatibility Fixes (December 2025)
- Removed deprecated `get_magic_quotes_gpc()` function call (removed in PHP 8.0)
- Fixed 18 instances of `checkSQL()` function calls with incorrect arguments
- Improved error handling in db_query with error logging
- Added proper PHPDoc annotations to database wrapper functions

### Configuration
- Created config/config.php with SQLite DSN
- Configured PHP built-in server on port 5000 (0.0.0.0)
- Set up deployment for autoscale target

## Project Architecture

### Database
- **File:** `mangoo.db` (SQLite database)
- **Tables:** 26 tables including customer, employee, loans, savings, shares, etc.
- **Test Data:** Includes 5 users and sample customer/loan data
- **Schema Source:** Converted from `database/mangoo_test.sql`

### Key Files
- `functions.php` - Core database functions and PDO wrappers
- `config/config.php` - Database configuration (SQLite DSN)
- `config/pepper.php` - Password pepper for security
- `login.php` - User authentication
- `start.php` - Main application dashboard
- `mangoo.db` - SQLite database file

### Features
- Customer management with photo support
- Share account management
- Savings account management  
- Loan management with interest calculation
- Employee management
- Financial reporting and accounting
- User access control with role-based permissions

## Usage

### Default Login
- **Username:** admin
- **Password:** password

### Setup & Migration
Before first use or after deployment, access the setup panel:
- **URL:** `/setup/`
- **Features:** Check database status and run migrations

### Running Locally
The application runs automatically via the "Start Application" workflow:
```bash
php -S 0.0.0.0:5000
```

### Database Access
Access the SQLite database using PHP:
```php
$pdo = new PDO('sqlite:mangoo.db');
$users = $pdo->query("SELECT * FROM user")->fetchAll();
```

## User Preferences
None documented yet.

## Development Notes

### PHP Extensions Used
- PDO
- PDO_SQLite
- session
- password hashing (password_verify, password_hash)

### Important Considerations
1. The database file (mangoo.db) should be backed up regularly
2. config/pepper.php should be changed for production use
3. Session security uses fingerprinting based on IP and user agent
4. All user input is sanitized using db_escape()

### Future Improvements
- Migrate to prepared statements for better SQL injection protection
- Add CSRF token protection
- Implement proper error handling with user-friendly messages
- Update to modern PHP framework (optional)

## Deployment

### Production Considerations
The SQLite database file (mangoo.db) persists in production. To ensure all test data is available on first deployment:

**Manual Migration (One-time):**
1. Access `/setup/` after deployment
2. Click "Run Migration Now" to check for and insert missing data
3. Verify all tables show record counts

**CLI Migration:**
```bash
php setup/db_migrate.php
```

The application is configured for Replit autoscale deployment and will run on port 5000 when deployed.

## License
GNU General Public License 3.0 (see LICENCE file)

## Credits
- Originally developed for Luweero Diocese SACCO, Uganda
- Funded by Christian Services International (Stuttgart, Germany)
- Adapted for Replit by Replit Agent (December 2025)