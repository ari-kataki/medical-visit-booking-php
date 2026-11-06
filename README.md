# Medical Visit Booking (PHP)

A small web app to book doctor visits, written in PHP with MySQL while learning web development at ITS. The interface is in Italian.

## What it does
- Patients register, log in, book a visit with a doctor, see their bookings and cancel pending ones
- Doctors log in and confirm, refuse or mark visits as done
- One dashboard page that changes depending on the role (PAZIENTE or MEDICO)
- CSRF tokens on every form, passwords hashed with bcrypt

## Stack
PHP (PDO, sessions), MySQL, plain CSS.

## How to run
1. Import `prenotazione.sql` (creates the `prenotazione` database, the tables and two demo accounts).
2. Copy `config.example.php` to `config.php` and put your own MySQL user and password.
3. Put the folder in your web server (MAMP/XAMPP) and open `login.php`.

The SQL file also creates two demo accounts (a doctor and a patient), see `prenotazione.sql` for the usernames; the passwords are not written here. New registrations are always patients.

## What it shows about my learning
- A small `DbConnector` class and prepared statements
- Login, registration and role checks with sessions
- Basic CSRF protection

## Known issues
- Booking does not check that the chosen user is really a doctor, or that the date is in the future
- No `session_regenerate_id()` after login
- The database has no foreign keys, only plain indexes
