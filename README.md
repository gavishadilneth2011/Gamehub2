# Game Hub Authentication Starter

A local PHP + MySQL login/register starter designed to be integrated with an existing HTML/CSS/JavaScript Game Hub. The old NOVA project is not modified.

## Features
- Login, registration, logout
- MySQL with PDO prepared statements
- Password hashing with `password_hash()` and verification with `password_verify()`
- PHP session checks on the dashboard
- CSRF tokens on POST forms
- Responsive dark gaming UI

## Requirements
- XAMPP with Apache, PHP and MySQL/MariaDB
- PHP PDO MySQL extension enabled

## Setup on Windows/XAMPP
1. Extract `gamehub-auth.zip` into `C:\xampp\htdocs\gamehub-auth\`.
2. Start **Apache** and **MySQL** in XAMPP Control Panel.
3. Open `http://localhost/phpmyadmin/`.
4. Choose **Import**, select the included `database.sql`, then import it. This creates the `gamehub_auth` database and `users` table.
5. If your local MySQL uses a password or a non-default account, update `config/database.php`. Typical fresh XAMPP installs use `root` with an empty password; your setup may differ.
6. Open `http://localhost/gamehub-auth/`.
7. Create an account from the registration link, then log in.

## Add your existing games without losing them
1. Keep the original Game Hub folder as a backup.
2. Copy (do not move) the original fighting-game folder into `games/fighting-game/`.
3. Copy the original zombie-game folder into `games/zombie-game/`.
4. Keep the game's own HTML, CSS, JS, images, sounds and other assets together. Do not rename assets unless you also update paths inside the game.
5. If a game uses a different entry file or folder name, update the corresponding link in `dashboard.php`.
6. Test each game from the dashboard. This starter protects the PHP dashboard, but **static HTML game files placed in `games/` can still be opened directly by URL**. To truly restrict every game to logged-in users, the games must be served through a PHP access-control approach or have their static assets relocated/served behind server-side checks. Ask for the next integration step before assuming direct URL access is blocked.

## Security notes
- Use only for local development until configured and reviewed for deployment.
- Keep PHP errors in server logs, not on public pages.
- Use HTTPS and secure deployment settings before publishing online.
- Do not upload real passwords or database credentials to GitHub.
