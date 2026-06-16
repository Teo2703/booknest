Mini Online Bookstore E-Commerce System
CIT6224 Web Application Development

Project Overview
This project is a Mini Online Bookstore E-Commerce System developed using HTML, CSS, JavaScript, PHP, MySQL, and XAMPP. The system allows customers to browse books, register, login, add books to cart, place orders, and view order history. Administrators can manage book records and customer orders.

Required Software
1. XAMPP for Windows
2. Web browser such as Google Chrome or Microsoft Edge
3. Visual Studio Code or any code editor

Setup Instructions
1. Install and open XAMPP.
2. Start Apache and MySQL in the XAMPP Control Panel.
3. Copy the project folder into:

   C:\xampp\htdocs\booknest

4. Open phpMyAdmin using:

   http://localhost/phpmyadmin

5. Create a new database named:

   booknest

6. Import the database file:

   booknest.sql

7. After importing, make sure the database contains the following tables:
   - users
   - books
   - orders
   - order_items

8. Open the system in a web browser using:

   http://localhost/booknest/

Database Configuration
The database connection is stored in:

   db.php

Default database settings:
   Host: 127.0.0.1
   Username: root
   Password: empty
   Database name: booknest
   Port: 3306 or 3307

The db.php file supports both port 3306 and 3307 to allow different XAMPP configurations.

Login Credentials

Administrator Account
Email: admin@booknest.com
Password: 123456
Role: Administrator

Customer Account
Email: amanda@example.com
Password: 123456
Role: Customer

Main Features

Customer Features
1. Customer registration with server-side validation
2. Customer login and logout
3. Customer profile page
4. Book browsing and search
5. Book details page
6. Add books to cart
7. Update, remove, and clear cart items
8. Checkout and order placement
9. Order history viewing

Administrator Features
1. Admin login
2. Admin dashboard
3. Add new book
4. Edit book details
5. Delete book records
6. View customer orders
7. Update order status

Security Features
1. Password hashing using password_hash()
2. Password verification using password_verify()
3. Prepared statements to prevent SQL injection
4. htmlspecialchars() to reduce Cross-Site Scripting risk
5. PHP session management
6. Role-based access control for customer and admin pages
7. Server-side validation for important forms
8. JavaScript client-side validation for better user experience

Folder Structure

auth/
Contains registration, login, logout, and customer profile pages.

books/
Contains book listing, book details, search, and filtering pages.

orders/
Contains shopping cart, checkout, and order history pages.

admin/
Contains admin dashboard, manage books, manage orders, and admin action files.

css/
Contains the main stylesheet.

js/
Contains JavaScript validation file.

Database File
booknest.sql

Notes
This project runs locally using XAMPP. No online payment gateway is included. The checkout function simulates a basic order placement process.