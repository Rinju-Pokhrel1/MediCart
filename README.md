# MediCart – Pharmacy E-Commerce System

**Students:** Rinju Pokhrel (Roll No. 54) and Harina Kumari Khati (Roll No. 49)  
**Course:** CSC 381 – E-Commerce  
**Project:** MediCart

## Requirements
- PHP 8.1+ with PDO SQLite extension
- A browser
- OpenSSL (for the HTTPS lab)

## Run locally
1. Open a terminal in the `medicart` directory.
2. Initialize the database: `php database/seed.php`
3. Start the server: `php -S localhost:8000`
4. Visit `http://localhost:8000`
5. Demo admin: `admin@medicart.local` / `Admin@12345`

The seed script creates 5 users (1 admin + 4 customers), 5 categories, 10 products and 5 sample orders. Demo admin: admin@medicart.local / Admin@12345. Demo customers use password Demo@12345. Passwords are stored using PHP `password_hash()`.

## Lab 6 – local HTTPS demonstration
Create a self-signed certificate:
```bash
openssl req -x509 -newkey rsa:2048 -keyout key.pem -out cert.pem -days 365 -nodes -subj "/CN=localhost"
```
For a local TLS reverse proxy, install `socat` and run the PHP server on port 8000, then:
```bash
socat OPENSSL-LISTEN:8443,cert=cert.pem,key=key.pem,verify=0,fork TCP:127.0.0.1:8000
```
Open `https://localhost:8443`. Your browser will show a warning because this is a self-signed development certificate. Do not use it for a public production site. PHP's built-in server itself is HTTP; the proxy provides TLS for this local lab.

## Lab 7 – email
Newsletter signups are stored in SQLite and a safe welcome-email preview is appended to `storage/mail.log`. This avoids sending real emails during a lab demonstration. For actual delivery, configure PHPMailer with a Mailtrap SMTP sandbox; never commit SMTP credentials.

## Lab 9 – visitor tracking
The catalog records a page view in `page_views`. Admin dashboard shows total views, orders and newsletter subscribers. This is a simple first-party counter, not Google Analytics.

## Lab 11 – payment
The payment page is a styled mock gateway with order summary, student attribution (Rinju Pokhrel, Roll 54; Harina Kumari Khati, Roll 49), and success/failure buttons. It does not connect to a bank, card network or real payment processor and never handles real card details.

## Important academic/demo notes
- This is an educational prototype, not a real pharmacy or medical advice service.
- Do not use it to sell prescription medicines or make clinical recommendations.
- Change the demo admin password before any shared deployment.
- The project uses SQLite for portability; the same relational design can be adapted to MySQL.


## Admin dashboard
Sign in with `admin@medicart.local` / `Admin@12345`, then open `admin.php`.
The admin can add, edit and delete medicines; maintain category, manufacturer, batch,
expiry date, SKU, price, discount, stock, description and optional image URL; view
low-stock and expired-medicine alerts; update order statuses; and review customer
orders and paid totals. New medicines are immediately listed in the public catalog.

Existing SQLite databases are upgraded automatically when `config.php` is loaded.
Do not delete `database/medicart.sqlite` or rerun the seed script if you want to keep
existing orders and data. If the admin password was already changed manually, it
will remain unchanged.


## UI refresh
The admin workspace includes a responsive overview hero, KPI cards, improved product/order/customer sections, and refined forms and tables. The sandbox payment screen displays order and customer details, test-mode notices, and simulated success/failure actions.
