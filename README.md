# Hostel Management System

A web-based Hostel Management System built with PHP and MySQL that streamlines hostel operations for administrators, wardens, owners, and students.

---

## Table of Contents

- [About](#about)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Project Structure](#project-structure)
- [Getting Started](#getting-started)
  - [Prerequisites](#prerequisites)
  - [Installation](#installation)
- [User Roles](#user-roles)
- [Contributing](#contributing)

---

## About

The Hostel Management System is a full-stack PHP application that provides a centralized platform for managing hostel operations. It supports multiple user roles — Owner, Admin, Warden, and Student — each with their own dedicated dashboard and access controls.

---

## Features

- **Multi-role authentication** — Separate dashboards and permissions for Owner, Admin, Warden, and Student
- **Student registration** — Multi-step registration with profile photo upload
- **Room management** — Track and manage single and double occupancy rooms
- **Fee management** — View and manage student fee records
- **Complaint system** — Students can submit and delete complaints; owners can review them
- **Notice board** — Post and manage hostel notices
- **Staff management** — View and manage hostel staff
- **Session-based access control** — Role-based route protection throughout the app

---

## Tech Stack

| Layer      | Technology          |
|------------|---------------------|
| Backend    | PHP (MVC pattern)   |
| Database   | MySQL               |
| Frontend   | HTML, CSS, JavaScript |
| Server     | Apache / XAMPP      |

---

## Project Structure

```
HostelManagementSystem/
├── config/
│   └── db.php                  # Database connection
├── controllers/
│   ├── AuthController.php      # Login/logout logic
│   ├── StudentController.php   # Student registration & dashboard data
│   └── ComplaintController.php # Complaint CRUD operations
├── models/
│   ├── Student.php
│   ├── User.php
│   ├── Room.php
│   ├── Fee.php
│   ├── Notice.php
│   └── Complaint.php
├── views/
│   ├── auth/                   # Login, register, set password pages
│   ├── dashboard/              # Role-specific dashboards
│   └── pages/                  # Public pages (about, staff, facilities)
├── public/
│   └── uploads/                # Uploaded profile photos
├── database.sql                # Database schema and seed data
└── index.php                   # Front controller / router
```

---

## Getting Started

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache server (XAMPP, WAMP, or LAMP recommended)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/kraneelManandhar/HostelManagementSystem.git
   ```

2. **Move to your server's web root**
   ```bash
   # For XAMPP on Windows:
   mv HostelManagementSystem C:/xampp/htdocs/

   # For XAMPP on Linux/Mac:
   mv HostelManagementSystem /opt/lampp/htdocs/
   ```

3. **Set up the database**
   - Open [phpMyAdmin](http://localhost/phpmyadmin)
   - Create a new database named `hostel_db`
   - Import `database.sql` from the project root

4. **Configure the database connection**
   - Open `config/db.php`
   - Update the credentials to match your local setup:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'hostel_management');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     ```

5. **Run the application**
   - Start Apache and MySQL from XAMPP Control Panel
   - Visit: [http://localhost/HostelManagementSystem/](http://localhost/HostelManagementSystem/)

---

## User Roles

| Role    | Access                                                                 |
|---------|------------------------------------------------------------------------|
| **Owner**   | Full access — students, rooms, fees, complaints, notices, staff    |
| **Admin**   | Admin dashboard with management capabilities                       |
| **Warden**  | Warden dashboard for day-to-day hostel supervision                 |
| **Student** | Personal dashboard — view room info, fees, notices; submit/delete complaints |

Each role is protected by session-based middleware (`requireRole()`), so users are automatically redirected if they try to access an unauthorized page.

---

## Contributing

Contributions are welcome! To get started:

1. Fork the repository
2. Create a new branch (`git checkout -b feature/your-feature`)
3. Commit your changes (`git commit -m 'Add your feature'`)
4. Push to the branch (`git push origin feature/your-feature`)
5. Open a Pull Request

---

> Built with ❤️ by [kraneelManandhar](https://github.com/kraneelManandhar)
