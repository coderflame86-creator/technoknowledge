# Technoknowledge Backend

This directory houses the backend system for **Technoknowledge (SMC-Pvt) Ltd.**, handling form submissions from the frontend website, persisting data into the database, protecting against spam/bots, sending email alerts, and providing a comprehensive Admin Management Portal.

---

## 1. Architecture Overview

```
backend/
├── api/
│   └── submit.php          # Public POST endpoint for frontend form submissions
├── admin/
│   ├── index.php           # Overview dashboard & KPI lead metrics
│   ├── submissions.php     # Full lead management, search, filters & status updater
│   ├── export.php          # 1-click CSV export with Excel compatibility (UTF-8 BOM)
│   ├── settings.php        # Password management, diagnostics & email test
│   ├── login.php           # Secure authentication with brute-force protection
│   ├── logout.php          # Session termination
│   ├── api.php             # Internal admin AJAX handler
│   ├── assets/             # CSS and JavaScript for the admin dashboard
│   └── includes/           # Auth guard, headers, and footer components
├── config/
│   ├── config.php          # Central configuration & environment loader
│   └── database.php        # PDO Database connection (auto-supports SQLite & MySQL)
├── data/
│   ├── .htaccess           # Direct web request denial for database files
│   └── technoknowledge.sqlite # SQLite database (zero-setup default)
├── includes/
│   ├── validator.php       # Form validation, honeypot & rate limiter
│   └── mailer.php          # Formatted HTML email alert with WhatsApp reply button
└── .env.example            # Environment variables template
```

---

## 2. Admin Management Portal ("Backend Access")

The admin dashboard provides complete control over website leads without needing access to raw database files.

- **URL (Local XAMPP):** `http://localhost/TK/backend/admin/`
- **URL (Live Production):** `https://thetechnoknowledge.com/backend/admin/`
- **Default Username:** `admin`
- **Default Password:** `TechnoAdmin2026!`
*(You can change your password and profile anytime in the Settings tab).*

### Key Capabilities:
1. **Overview Dashboard:** Total submissions, pending "New" leads count, and breakdown across all four forms (Camp Registrations, School Proposals, AITEP Starter, Contact).
2. **Lead Pipeline & Status:** Update lead lifecycle (`New`, `Contacted`, `Demo / meeting booked`, `Proposal sent`, `Enrolled / signed`, `Not interested`).
3. **One-Tap WhatsApp Reply:** Direct link on every lead (`https://wa.me/...`) with pre-composed greeting message tailored to the visitor's name and inquiry.
4. **Internal Admin Notes:** Keep a running log of phone calls, demo schedules, and follow-ups.
5. **CSV Export:** Download all or filtered leads into Excel/Google Sheets compatible `.csv`.
6. **Search & Filter:** Search across names, phone numbers, schools, and cities.

---

## 3. Form Submission API (`api/submit.php`)

All frontend forms post through `frontend/js/api.js` to `backend/api/submit.php`.

### Forms Supported:
- `Camp Registrations` (`campRegistration`)
- `Proposal Requests` (`proposalRequest`)
- `AITEP Starter Bookings` (`aitepStarter`)
- `Contact Messages` (`contact`)

### Built-in Protection & Security:
- **Honeypot Trap:** Rejects automated bot submissions filling hidden fields (`hp-field`, `hp`).
- **Speed Check:** Rejects submissions completed faster than 2,500ms (`elapsed` check).
- **Rate Limiting:** Maximum 5 submissions per 10 minutes per IP and phone number.
- **SQL Injection Prevention:** 100% PDO prepared statements.
- **XSS Sanitization:** All text inputs stripped of harmful tags before database entry.

---

## 4. Database Setup (SQLite & MySQL)

The backend features dual-driver support:

### Option A: SQLite (Default, Zero-Setup)
- Works immediately without installing or configuring MySQL.
- Database file is stored safely at `backend/data/technoknowledge.sqlite`.
- Protected by `.htaccess` to prevent public downloading.

### Option B: MySQL (XAMPP / Hostinger / cPanel)
To connect to MySQL:
1. Copy `backend/.env.example` to `backend/.env`.
2. Set:
   ```env
   DB_DRIVER=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_NAME=technoknowledge
   DB_USER=root
   DB_PASS=your_password
   ```
3. The database tables (`submissions`, `admins`, `rate_limits`) and default admin user are **automatically created** on the first run.
