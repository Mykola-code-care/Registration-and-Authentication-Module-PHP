# Registration and Authentication Module

Email/password registration and login, email confirmation, password recovery, terms of use acceptance, and basic profile management.

## Stack

- **PHP** 7.4+ (8.x recommended)
- **SQLite** (database is created automatically in `data/auth.db`)
- **HTML/CSS/JS** — frontend without frameworks

## How to run the project

```bash
# 1. Clone the repo
git clone <your-repo-url>
cd testAIPHP

# 2. Install dependencies (PHPMailer)
composer install

# 3. Optional: copy local config for email (Mailtrap etc.)
cp config.local.php.example config.local.php
# Edit config.local.php and set MAIL_USER, MAIL_PASS

# 4. Set APP_URL in config.php if needed (default: http://localhost:8080)

# 5. Start PHP built-in server
php -S localhost:8080 -t .
```

Open in browser: **http://localhost:8080**

The SQLite database and `data/` folder are created automatically on first request.

## What is not in git

- **vendor/** — install with `composer install` after clone
- **.env** / **config.local.php** — local env and mail credentials (use `config.local.php.example` as template)

## Installation (details)

1. Clone or copy the project files.

2. Run `composer install` to get PHPMailer. To send real emails: copy `config.local.php.example` to `config.local.php` and add your SMTP credentials (e.g. from [Mailtrap](https://mailtrap.io) → Inbox → SMTP Settings).

3. In `config.php` set **APP_URL** (e.g. `http://localhost:8080`) if you use another host/port.

4. Start the server: `php -S localhost:8080 -t .` and open http://localhost:8080

If emails don’t arrive, after sign-up the login page will show a “Confirm email now” link when SMTP is not configured.

## Features

- **Sign up** — email, password, name, required terms of use acceptance.
- **Log in / Log out** — session, CSRF token.
- **Email confirmation** — link in email (valid 24 hours).
- **Password reset** — email with link to set new password (valid 1 hour).
- **Profile** — view data, change name and password.

## Structure

```
├── config.php          # Config (URL, DB, mail)
├── db.php              # PDO + SQLite, schema
├── auth.php            # Register, login, confirm, reset
├── mailer.php          # Mail (mail() or PHPMailer SMTP)
├── layout.php          # Page layout
├── index.php           # Home (guest / user)
├── login.php           # Log in
├── register.php        # Sign up
├── logout.php          # Log out
├── forgot-password.php # Request reset link
├── reset-password.php  # Set new password by token
├── confirm.php         # Confirm email by token
├── profile.php         # User profile
├── data/               # SQLite DB (auto-created)
├── assets/
│   ├── css/style.css   # Styles
│   └── js/app.js       # Password validation, agreement modal
└── composer.json       # Dependencies (PHPMailer)
```

## Email

Uses **PHPMailer** (installed via Composer). If SMTP is not configured or sending fails, the confirmation link is shown on screen (see `SHOW_CONFIRM_LINK_WHEN_MAIL_FAILS` in `config.php`).

## Security

- Passwords stored as bcrypt hashes.
- Email confirmation and password reset tokens are random with limited lifetime.
- Forms protected with CSRF token.
- Prepared statements (PDO) to prevent SQL injection.
