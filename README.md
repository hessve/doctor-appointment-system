# Doctor Appointment System

A lightweight doctor appointment management system built with PHP, HTML, and JavaScript.

## Features

- Patient registration and login
- Doctor registration and login
- Appointment booking
- Doctor schedule management
- Admin dashboard
- SQLite database storage
- Responsive HTML/CSS interface

## Quick start

1. Make sure PHP is installed.
2. Start a local PHP server:

```bash
php -S localhost:8000
```

3. Open http://localhost:8000 in the browser.
4. Login with the default admin account:
   - Email: admin@clinic.local
   - Password: admin123

## Default accounts

- Admin: admin@clinic.local / admin123
- Patients and doctors can register from the registration page.

## Project structure

- `index.php` — landing page
- `login.php` — login form
- `register.php` — account creation
- `dashboard.php` — user dashboard
- `appointments.php` — booking page
- `doctor_schedule.php` — doctor availability setup
- `admin.php` — admin panel
- `includes/db.php` — SQLite database setup
- `assets/css/style.css` — styling
- `assets/js/app.js` — simple frontend JS

## Notes

This project uses SQLite so no external database server is required. The database file is created automatically in `data/app.db` when the app runs.
