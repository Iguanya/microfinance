# mangoO Microfinance Management - Replit Setup

## Overview
mangoO is a lightweight, yet powerful software solution for small microfinance institutions. This is a PHP web application originally designed for MySQL, now converted to run on Replit using SQLite.

**Original Project:** GitHub import  
**Technology Stack:** PHP 8.2, SQLite, JavaScript (jQuery), Bootstrap 5, CSS  
**Current State:** Fully functional with modern Bootstrap UI and running on Replit

## Recent Changes (December 2025)

### Bootstrap Styling Phase 1 - Navigation Pages Updated
- **Created Reusable Bootstrap Template:**
  - `includes/bootstrap_header.php` - Common header with navigation
  - `includes/bootstrap_footer.php` - Footer with scripts
- **Updated Navigation Pages:**
  - `cust_search.php` - Customer search with Bootstrap forms
  - `loans_search.php` - Loan search with Bootstrap interface
  - Responsive navbar with all main navigation links
  - Sidebar with quick action buttons
- **Database Query Fixes:**
  - Fixed `start.php` to use correct SQLite table names
  - Changed `sharebal` table reference to `shares` table
  - Changed `loam_amount` to `loan_principal`
  - Added error handling for all database queries

### Dashboard UI Overhaul & Modern Design
- **Bootstrap 5 Integration:** Complete responsive design overhaul on main dashboard
- **Interactive Charts:** Chart.js integration with:
  - Loan status distribution (doughnut chart)
  - Top customers by borrowing amount (bar chart)
- **Key Statistics Dashboard:** Real-time metrics display
  - Total customers, active loans, total savings, total shares
- **Improved Navigation:**
  - Modern navbar with dropdown menus
  - Responsive sidebar with quick actions
  - Professional color scheme (orange theme matching mangoO branding)
- **Data Tables:** Enhanced with Bootstrap styling
  - Hover effects, better readability
  - Professional appearance

### Database Migration (MySQL → SQLite)
- **Original:** MySQL/MariaDB database
- **Current:** SQLite database (mangoo.db)
- **Reason:** Replit environment doesn't support MySQL due to permission restrictions
- **Impact:** Fully compatible, all 26 tables migrated successfully with test data

### Code Refactoring & PHP 8 Compatibility
- Created PDO wrapper functions (db_query, db_fetch_assoc, db_fetch_array, db_error, db_escape)
- Replaced all mysqli_* function calls throughout the codebase
- Updated functions.php to use PDO with SQLite
- Removed deprecated `get_magic_quotes_gpc()` function call (removed in PHP 8.0)
- Fixed 18 instances of `checkSQL()` function calls with incorrect arguments
- Improved error handling in db_query with error logging

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
- `start.php` - Main application dashboard (Bootstrap)
- `cust_search.php` - Customer search (Bootstrap)
- `loans_search.php` - Loan search (Bootstrap)
- `includes/bootstrap_header.php` - Reusable Bootstrap header template
- `includes/bootstrap_footer.php` - Reusable Bootstrap footer template
- `css/bootstrap-dashboard.css` - Custom Bootstrap styles
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
- Modern Bootstrap design for all pages
- Using SQLite instead of MySQL for Replit compatibility
- Orange (#FF8C00) as primary brand color

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
5. All new pages should use the Bootstrap header/footer templates for consistency

### Future Improvements - Bootstrap Rollout
- Apply Bootstrap to remaining pages (employee.php, books_expense.php, reports, settings, etc.)
- Convert old includeHead() and includeMenu() function calls to use new Bootstrap template
- Update all data tables to use Bootstrap table styling
- Add form validation using Bootstrap and JavaScript
- Implement Bootstrap alerts for success/error messages

### Database Table Reference
Common table names (for query corrections):
- `customer` - Customer records
- `loans` - Loan records
- `shares` - Share account records (NOT `sharebal`)
- `savings` - Savings records (NOT `savbalance`)
- `savbalance` - Savings balance records
- `employee` - Employee records
- `user` - User accounts
- Correct column names:
  - `loans.loan_principal` (NOT `loam_amount`)
  - `shares.share_amount` (NOT `sharebal_balance`)

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
