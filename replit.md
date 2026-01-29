# mangoO Microfinance Management

## Overview
mangoO is a lightweight, yet powerful PHP web application designed for small microfinance institutions. It provides comprehensive tools for managing customer accounts, loans, savings, and financial reporting. The project has been adapted to run on Replit using MySQL/MariaDB and features a modern Bootstrap 5 user interface.

**Key Capabilities:**
- Customer, Loan, and Savings account management
- Stakeholder/Shareholder management (separate from customers)
- Share capital accounting and reporting
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
- **Stakeholders/Shareholders separated from Customers** - Share ownership is managed independently

## System Architecture

**UI/UX Decisions:**
- All pages are modernized with Bootstrap 5, featuring responsive cards, tabs, tables, and form controls.
- Consistent navigation is provided via a central `bootstrap_header_nav.php` file.
- Custom `bootstrap-dashboard.css` is used for brand-specific styling.
- Navigation includes dropdown menus for Accounting section

**Technical Implementations:**
- **Environment Variables:** Supports `.env` files for local development via `config/load_env.php`.
- **Database:** MySQL/MariaDB with 29 tables, using PDO for database interactions.
- **PHP 8.2 Compatibility:** Refactored to use PDO for database interactions, replacing `mysqli_*` functions.
- **Core Files:**
    - `functions.php`: Contains core database functions and PDO wrappers.
    - `config/config.php`: Stores MySQL/MariaDB configuration.
    - `login.php`: Handles user authentication.
    - `includes/bootstrap_header.php`, `includes/bootstrap_header_nav.php`, `includes/bootstrap_footer.php`: Provide consistent Bootstrap templating.

**Feature Specifications:**
- **Customer Management:**
    - `customer.php`: Customer dashboard with financial summary and tabbed interface (Personal Details, Savings, Loans).
    - `acc_sav_depos.php`, `acc_sav_withd.php`: Savings deposit and withdrawal.
    - `cust_new.php`, `cust_search.php`: Customer registration and search.
- **Stakeholder/Share Capital Management (NEW):**
    - `stakeholder.php`, `stakeholder_new.php`, `stakeholder_search.php`: Full stakeholder CRUD operations.
    - `share_buy.php`, `share_sell.php`, `share_transfer.php`: Share transactions for stakeholders.
    - `books_shares.php`: Share capital accounting with period filtering.
    - `rep_shares.php`: Share register report with ownership percentages.
    - **Database Tables:** `stakeholder`, `stakeholder_shares`, `dividends`
    - **Key Design Decision:** Stakeholders are separate from customers. A stakeholder may optionally be linked to a customer record.
- **Loan Management:**
    - `loan.php`: Loan details, payment schedule, and guarantor verification status.
    - `loan_new.php`: New loan application with customer search/selection and inline guarantor creation.
    - `loan_verify_guarantors.php`: Guarantor verification workflow - verify/reject guarantors before loan approval.
    - `loans_search.php`, `loans_result.php`: Loan search functionality.
    - **Database Tables:** `loan_guarantor_verification` (tracks verification status for each guarantor)
    - **Key Design Decision:** All guarantors must be verified before a loan can be approved. Verification includes status (pending/verified/rejected), notes, and audit trail.
- **Reporting & Accounting:**
    - `rep_incomes.php`, `rep_expenses.php`, `rep_loans.php`, `rep_capital.php`, `rep_monthly.php`, `rep_annual.php`: Comprehensive financial reports.
    - `books_expense.php`, `books_income.php`, `books_annual.php`: General accounting for institutional finances.
    - `books_shares.php`, `rep_shares.php`: Share capital accounting and shareholder register.

### **Accounting Workflow & Interaction**
The accounting system in mangoO follows a modular flow where operational transactions feed into centralized reporting:

1.  **Transaction Entry (The Source):**
    *   **Savings:** `acc_sav_depos.php` records cash inflows, `acc_sav_withd.php` records withdrawals.
    *   **Share Capital:** `share_buy.php`, `share_sell.php`, `share_transfer.php` record stakeholder share transactions.
    *   **Loans:** `loan_pay.php` (repayments) generates income, while `loan_new.php` (disbursements) records outflows.
    *   **Institutional:** `books_income.php` and `books_expense.php` allow manual entry of non-client transactions.

2.  **Data Processing:**
    *   All transactions are logged in the appropriate tables via `functions.php`.
    *   The `db_query` function handles the persistence to the remote MySQL host.

3.  **Reporting & Reconciliation:**
    *   **Share Capital:** `books_shares.php` shows period-based transaction history, `rep_shares.php` shows the shareholder register.
    *   **Categorized Reports:** `rep_incomes.php` and `rep_expenses.php` filter logs by transaction type.
    *   **Final Accounts:** `rep_annual.php` interacts with `books_annual.php` to generate financial statements.

4.  **Verification:**
    *   `rep_capital.php` provides a birds-eye view of the institution's health.
    *   `rep_shares.php` shows ownership distribution and share capital totals.

- **Settings:**
    - `set_basic.php`, `set_loans.php`, `set_fees.php`, `set_user.php`, `set_ugroup.php`, `set_ugroup_del.php`, `set_logrec.php`, `set_dbbackup.php`: System configuration, user management, and audit trails.

**System Design Choices:**
- All PHP logic is executed before HTML output to prevent header errors.
- Modular design with shared Bootstrap templates for consistency.
- Responsive grid layouts, cards, and tabs are used extensively for improved user experience.
- Guarantors are managed separately from customers with their own IDs (G-0001, G-0002, etc.).
- Stakeholders are managed separately from customers to properly track share capital ownership.
- Customers can be optionally linked to guarantor and stakeholder records for cross-referencing.

## Database Schema (Key Tables)

### Guarantor Module (New)
- `guarantor`: Stores guarantor information (guarantor_id, guarantor_no, name, phone, ID number, address, employer, occupation, optional customer link)
- `loan_guarantor_verification`: Tracks verification status for each guarantor on a loan (loan_id, guarantor_id, status, verified_by, verified_date, notes)
- **Key Design Decision:** Guarantors are separate from customers. A guarantor may optionally be linked to a customer record.

### Stakeholder Module
- `stakeholder`: Stores shareholder information (id, name, type, contact, bank details, optional customer link)
- `stakeholder_shares`: Tracks all share transactions (buy, sell, transfer_in, transfer_out, dividend)
- `dividends`: Records dividend declarations

## External Dependencies

- **Database:** MySQL/MariaDB (remote host)
- **Frontend Framework:** Bootstrap 5
- **JavaScript Library:** jQuery
