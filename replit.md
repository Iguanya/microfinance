# mangoO Microfinance Management

## Overview
mangoO is a lightweight, yet powerful PHP web application designed for small microfinance institutions. It provides comprehensive tools for managing customer accounts, loans, savings, shares, and financial reporting. The project has been adapted to run on Replit using SQLite and features a modern Bootstrap 5 user interface.

**Key Capabilities:**
- Customer, Loan, Savings, and Shares account management
- Employee management
- Financial reporting (income, expenses, annual accounts)
- User access control with role-based permissions
- System settings and configuration
- Database backup functionality

**Technology Stack:** PHP 8.2, MySQL/MariaDB, JavaScript (jQuery), Bootstrap 5, CSS

## User Preferences
- Modern Bootstrap 5 design for all pages
- Using MySQL/MariaDB for database persistence
- Orange (#FF8C00) as primary brand color
- All PHP code BEFORE HTML output (fixes headers issues)
- Permission checks disabled in test environment for accessibility
- All financial calculations verified for accuracy
- Responsive grid layout with cards and tabs

## System Architecture

**UI/UX Decisions:**
- All pages are modernized with Bootstrap 5, featuring responsive cards, tabs, tables, and form controls.
- Consistent navigation is provided via a central `bootstrap_header_nav.php` file.
- Custom `bootstrap-dashboard.css` is used for brand-specific styling.

**Technical Implementations:**
- **Database:** MySQL/MariaDB with 26 tables, using PDO for database interactions.
- **PHP 8.2 Compatibility:** Refactored to use PDO for database interactions, replacing `mysqli_*` functions.
- **Core Files:**
    - `functions.php`: Contains core database functions and PDO wrappers.
    - `config/config.php`: Stores MySQL/MariaDB configuration.
    - `login.php`: Handles user authentication.
    - `includes/bootstrap_header.php`, `includes/bootstrap_header_nav.php`, `includes/bootstrap_footer.php`: Provide consistent Bootstrap templating.

**Feature Specifications:**
- **Account Management:**
    - `customer.php`: Customer dashboard with financial summary and tabbed interface.
    - `acc_sav_depos.php`, `acc_sav_withd.php`: Savings deposit and withdrawal.
    - `acc_share_buy.php`, `acc_share_sale.php`: Share purchase and sale.
- **Loan Management:**
    - `loan.php`: Loan details and payment schedule.
    - `loan_new.php`: New loan application with inline guarantor creation.
    - `cust_new.php`: New customer registration.
    - `loans_result.php`, `security.php`: Loan search and security management.
- **Reporting & Accounting:**
    - `rep_incomes.php`, `rep_expenses.php`, `rep_loans.php`, `rep_capital.php`, `rep_monthly.php`, `rep_annual.php`: Comprehensive financial reports.
    - `books_expense.php`, `books_income.php`, `books_annual.php`: General accounting for institutional finances.
- **Settings:**
    - `set_basic.php`, `set_loans.php`, `set_fees.php`, `set_user.php`, `set_ugroup.php`, `set_ugroup_del.php`, `set_logrec.php`, `set_dbbackup.php`: System configuration, user management, and audit trails.

**System Design Choices:**
- All PHP logic is executed before HTML output to prevent header errors.
- Modular design with shared Bootstrap templates for consistency.
- Responsive grid layouts, cards, and tabs are used extensively for improved user experience.
- Loan search and selection are integrated directly into `loan_new.php` for a seamless workflow.

## External Dependencies

- **Database:** SQLite (`mangoo.db`)
- **Frontend Framework:** Bootstrap 5
- **JavaScript Library:** jQuery