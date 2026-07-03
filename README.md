# Cafeteria Management System

A full-stack cafeteria ordering and management system built with **plain PHP** following the MVC architectural pattern.

This project was built as a learning project to understand how modern backend frameworks such as Laravel work internally before using them.

---

## Features

### Client

- Register and login
- Browse products
- View product variants
- Add items to cart
- Update cart quantities
- Checkout
- Choose payment method (Cash or Card)
- View order history
- Modify pending orders
- Online payment simulation

### Admin

- Manage categories
- Manage products
- Manage product variants
- View all orders
- Update order status
- Track payment status
- Record cash payments
- Cancel pending orders
- Automatic stock restoration after cancellation
- Payment refund simulation

---

## Technologies

- PHP
- MySQL
- PDO
- HTML
- XAMPP

---

## Architecture

The application follows a simplified MVC architecture.

```
Controllers
    ↓
Repositories
    ↓
Database
```

Business logic is primarily implemented inside repository classes.

Database transactions are used to preserve consistency during operations such as:

- Checkout
- Stock updates
- Order cancellation
- Refund processing

---

## Project Structure

```
app/
    controllers/
    repositories/
    middlewares/
    helpers/
    validations/
    config/
    core/

routes/

views/

public/

storage/

```

---

## Business Rules Implemented

Some of the business rules implemented include:

- Preserve historical order prices.
- Reduce stock immediately after checkout.
- Restore stock after cancellation.
- Prevent editing non-pending orders.
- Prevent duplicate payments.
- Support Cash and Card payment methods.
- Support payment refunds for cancelled paid orders.
- Ownership checks on customer orders.
- Authentication and authorization.
- Transaction-safe checkout process.

---

## Installation

Clone the repository

```bash
git clone https://github.com/syntax-weaver/cafe.git
```

Create a database.

Import the SQL file.

Create

```
app/config/db_credentials.php
```

using

```
app/config/db_credentials.example.php
```

Configure your database credentials.

Point your web server to the `public` directory.

Start Apache and MySQL.

---

## Future Improvements

This project intentionally stops before introducing modern PHP tooling.

Future improvements would include:

- Composer
- PSR-4 Autoloading
- Namespaces
- Dependency Injection
- Service Layer
- Interfaces
- Environment Variables
- Logging
- Unit Testing
- API Layer
- Payment Gateway Integration

These concepts will be implemented in my next project.

---

## Purpose

The purpose of this project was educational.

Instead of using Laravel immediately, the goal was to understand the problems that modern frameworks solve by implementing many of them manually.

This project serves as Version 1 of my backend development journey.