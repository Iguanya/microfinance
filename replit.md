# mangoO Microfinance Management - Replit Setup

## Overview
mangoO is a lightweight, yet powerful software solution for small microfinance institutions. This is a PHP web application originally designed for MySQL, now converted to run on Replit using SQLite.

**Original Project:** GitHub import  
**Technology Stack:** PHP 8.2, SQLite, JavaScript (jQuery), Bootstrap 5, CSS  
**Current State:** Fully functional with modern Bootstrap UI and running on Replit

## Recent Changes (December 2025)

### Bug Fixes - Account & Customer Operations (December 02, 2025)

**1. Fixed savbalance NULL constraint violation (ALL savings operations):**
- Updated `updateSavingsBalance()` function to use COALESCE() for NULL handling
- Fixed `updateSavingsBalanceAll()` function with proper NULL defaults
- All acc_sav_depos.php, acc_sav_withd.php operations now work correctly
- Savings deposits, withdrawals, and balance calculations fully functional

**2. Fixed cust_new.php incomes.inc_text NOT NULL constraint:**
- Added `inc_text` column to income INSERT statements with descriptions:
  - 'Entrance Fee' for entrance fee income (inctype_id=1)
  - 'Stationary' for stationery income (inctype_id=6)
- Added `savbal_fixed='0'` to savbalance INSERT initialization

**Status Update:**
- ✅ All 4 acc_* pages (buy/sell shares, deposit/withdraw savings) tested and verified
- ✅ Database persistence confirmed for all account operations
- ✅ Share transactions (buy, sell, transfer) functional with proper value calculations
- ✅ Savings transactions (deposit, withdrawal) functional with fee handling
- ✅ Customer registration (cust_new.php) now works without constraint errors

## Recent Changes (Earlier - December 2025)

### Complete Bootstrap 5 Modernization - ALL Pages Complete ✅
**All 20+ Pages Now Bootstrap Ready:**
- **Report Pages (6 pages):** rep_annual.php, rep_capital.php, rep_expenses.php, rep_loans.php, rep_monthly.php, rep_incomes.php
- **Settings Pages (8 pages):** set_basic.php, set_dbbackup.php, set_fees.php, set_loans.php, set_logrec.php, set_ugroup.php, set_ugroup_del.php, set_user.php
- **Accounting Pages (3 pages):** books_expense.php, books_income.php, books_annual.php
- **Employee Pages (3 pages):** empl_curr.php, empl_past.php, empl_new.php
- **Other Pages (2 pages):** loans_securities.php, set_basic.php

**Bootstrap 5 Features Implemented:**
- Modern responsive cards, tabs, and tables across all pages
- Bootstrap navbar with responsive design
- Form controls with proper Bootstrap styling
- Alert boxes and badges for status indicators
- Grid layout (rows/columns) for responsive design
- Proper button styling with icons
- Bootstrap tables with striped and hover effects

**All Pages Follow Standard Pattern:**
- PHP code at top (before HTML output)
- checkLogin() validation with commented permission checks
- Include bootstrap_header.php and bootstrap_header_nav.php
- Bootstrap-styled content in container-fluid with rows/columns
- Include bootstrap_footer.php at bottom
- Tab navigation between related settings/reports pages

**Key Fixes Implemented:**
- Fixed rep_annual.php typo: `$db_lib` → `$db_link` (line 140)
- All permission checks commented out for test environment
- All "headers already sent" errors resolved
- Variable initialization fixed (set_ugroup.php, set_user.php)

**Report Calculations Verified CORRECT:**
- Shares: `share_value` (positive=buys, negative=sales)
- Savings: `sav_amount` with savtype_id (1=deposits, 2=withdrawals)
- Loan Recoveries: `ltrans_principal + ltrans_interest` ✓
- Loan Due: `ltrans_principaldue + ltrans_interestdue` ✓
- Recovery Rate: `total_recovered / total_due * 100%` ✓

### Database Migration (MySQL → SQLite)
- **Original:** MySQL/MariaDB database
- **Current:** SQLite database (mangoo.db)
- **Reason:** Replit environment doesn't support MySQL due to permission restrictions
- **Impact:** Fully compatible, all 26 tables migrated successfully with test data

### Code Refactoring & PHP 8 Compatibility
- Created PDO wrapper functions (db_query, db_fetch_assoc, db_fetch_array, db_error, db_escape)
- Replaced all mysqli_* function calls throughout the codebase
- Updated functions.php to use PDO with SQLite
- Removed deprecated `get_magic_quotes_gpc()` function call
- Fixed 18 instances of `checkSQL()` function calls with incorrect arguments

## Project Architecture

### Database
- **File:** `mangoo.db` (SQLite database)
- **Tables:** 26 tables including customer, employee, loans, savings, shares, etc.
- **Test Data:** Includes 5 users and sample customer/loan data

### Key Files
- `functions.php` - Core database functions and PDO wrappers
- `config/config.php` - Database configuration (SQLite DSN)
- `login.php` - User authentication
- **Bootstrap Templates:**
  - `includes/bootstrap_header.php` - HTML5 doctype and Bootstrap CSS
  - `includes/bootstrap_header_nav.php` - Navigation bar and menus
  - `includes/bootstrap_footer.php` - Bootstrap JS and dependencies
- `css/bootstrap-dashboard.css` - Custom Bootstrap styles
- `mangoo.db` - SQLite database file

### All Settings Pages
- `set_basic.php` - General system settings (currency, minimum balances, dashboard widgets)
- `set_loans.php` - Loan configuration (interest rates, limits, guarantees)
- `set_fees.php` - Fees and charges (shares, entrance, withdrawal, loan fees)
- `set_user.php` - User management (create/edit users, assign groups)
- `set_ugroup.php` - User group permissions (admin, delete, reports)
- `set_ugroup_del.php` - User group deletion with dependency checking
- `set_logrec.php` - Login/logoff records display
- `set_dbbackup.php` - Database backup functionality

### Account Management Pages (Bootstrap 5 Modernized)
- `customer.php` - Customer account dashboard with financial summary, tabbed interface (personal details, savings, loans, shares), and quick action buttons
- `acc_sav_depos.php` - Record savings deposits with Bootstrap form and account statement
- `acc_sav_withd.php` - Record savings withdrawals with Bootstrap form and account statement
- `acc_share_buy.php` - Purchase shares with Bootstrap form, share statement, and transfer option
- `acc_share_sale.php` - Sell shares with Bootstrap form and share statement
- `acc_sav_list.php` - Responsive savings transaction table with Bootstrap styling
- `acc_share_list.php` - Responsive share transaction table with Bootstrap styling

### Loan Management Pages (Bootstrap 5 Modernized)
- `loan.php` - Loan details dashboard with Bootstrap cards and responsive 2-column layout (loan info + payment schedule)
- `loan_new.php` - New loan application with Bootstrap 5 form, **inline guarantor creation feature** with collapsible section
- `cust_new.php` - New customer registration with responsive Bootstrap form grid layout
- `loans_result.php` - Loan search results with Bootstrap table and badges for status
- `security.php` - Loan security management with Bootstrap cards and image display

### All Report Pages
- `rep_incomes.php` - Income by type analysis
- `rep_expenses.php` - Detailed and summarized expense reports
- `rep_loans.php` - Due payments, recoveries, and loans out
- `rep_capital.php` - Capital additions/deductions analysis
- `rep_monthly.php` - Monthly consolidated summary
- `rep_annual.php` - Annual consolidated report

### Features
- Customer management with search and list views
- Share account management
- Savings account management  
- Loan management with interest calculation
- Employee management (current, former, and new hires)
- Financial reporting and accounting (income, expenses, annual accounts)
- Expense tracking and management (detailed and summarized formats)
- Loan reporting (due payments, recoveries, loans out, recovery rates)
- User access control with role-based permissions
- System settings and configuration
- User management and authentication
- Login/logoff audit trail
- Database backup functionality

## Usage

### Default Login
- **Username:** admin
- **Password:** password

### Accessing Pages
All pages are now fully functional and accessible with Bootstrap 5 styling:
- **Settings Tab:** `/set_basic.php` → Full settings navigation with 8 settings pages
- **Dashboard:** `/start.php` - Statistics and key metrics
- **Customers:** `/cust_search.php`, `/cust_act.php`, `/cust_inact.php`
- **Loans:** `/loans_search.php`, `/loans_act.php`, `/loans_pend.php`, `/loans_securities.php`
- **Reports:** All 6 report pages accessible via tab navigation
- **Accounting:** `/books_expense.php`, `/books_income.php`, `/books_annual.php`
- **Employees:** `/empl_new.php`, `/empl_curr.php`, `/empl_past.php`

### Running Locally
The application runs automatically via the "Start Application" workflow:
```bash
php -S 0.0.0.0:5000
```

## User Preferences
- Modern Bootstrap 5 design for all pages
- Using SQLite instead of MySQL for Replit compatibility
- Orange (#FF8C00) as primary brand color
- All PHP code BEFORE HTML output (fixes headers issues)
- Permission checks disabled in test environment for accessibility
- All financial calculations verified for accuracy
- Responsive grid layout with cards and tabs

## Development Notes

### Important Considerations
1. All PHP code MUST come BEFORE any HTML output to prevent "headers already sent" errors
2. All new pages should use the Bootstrap header/footer templates for consistency
3. The database file (mangoo.db) should be backed up regularly
4. config/pepper.php should be changed for production use
5. All user input is sanitized using db_escape()
6. All report calculations have been verified and are accurate

### Complete Settings Navigation
All 8 settings pages are interconnected with tab navigation for easy access:
- Basic Settings
- Loan Settings
- Fees & Charges
- Users
- Usergroups
- Log Records
- Database Backup (Linux only)

### Future Improvements
- Re-enable permission checks when user roles are properly configured
- Apply Bootstrap to remaining pages (dashboard modules, loan.php, customer.php, etc.)
- Add form validation using Bootstrap and JavaScript
- Implement Bootstrap alerts for success/error messages
- Add modal dialogs for confirmations
- Responsive design improvements for mobile
- Consider adding charts/graphs to dashboard and reports

### Database Table Reference
- `customer` - Customer records
- `loans` - Loan records
- `shares` - Share account records
- `savings` - Savings records
- `employee` - Employee records
- `user` - User accounts
- `ugroup` - User group permissions
- `logrec` - Login/logoff audit trail

## Deployment

The application is configured for Replit autoscale deployment and will run on port 5000 when deployed.

## License
GNU General Public License 3.0 (see LICENCE file)

## Credits
- Originally developed for Luweero Diocese SACCO, Uganda
- Funded by Christian Services International (Stuttgart, Germany)
- Adapted for Replit by Replit Agent (December 2025)
