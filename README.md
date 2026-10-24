# ReserveNU

A room reservation system for a university. Students submit booking requests, admins approve or reject them, and approved events sync to Google Calendar.

## Features

- Student sign up and login with a university ID
- Room availability search with checks for operating hours, booking length, and conflicts
- Reservation requests with file uploads
- Admin dashboard to review, approve, or reject requests
- Reservation history with filters
- Google Calendar sync for approved events

## Tech Stack

PHP, MySQL, JavaScript, HTML/CSS, Google Calendar API

## Setup

1. Install [XAMPP](https://www.apachefriends.org/) and start **Apache** and **MySQL**.
2. Clone this repo into the `htdocs` folder.
3. Open phpMyAdmin and create a database named `reservenu_db`.
4. Import `database/reservenu_db.sql` into that database.
5. Install dependencies:
   ```bash
   composer install
   ```
6. Create a Google Cloud project, enable the Calendar API, and save your OAuth credentials as `admin/php/credentials.json`.
7. Open `http://localhost/reserve-nu/user/html/index.html`.

## Notes

- The database settings in `config.php` use the XAMPP defaults (`root`, no password). If you deploy this, use a dedicated database user with a strong password and keep it out of version control.
- `credentials.json`, `token.json`, and the `uploads/` folder are ignored by Git.
