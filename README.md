# Hostel Management System

A web-based Hostel Management System built with PHP and MySQL that streamlines hostel operations for Owner/Management, warden, and students.

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
- [Contributors](#contributors)

---

## About

The Hostel Management System is a full-stack PHP application that provides a centralized platform for managing hostel operations. It supports multiple user roles — Owner, Warden, and Student — each with their own dedicated dashboard and access controls.

---

## Features

- **Multi-role authentication** — Separate dashboards and permissions for Owner, Warden, and Student
- **Student registration** — Multi-step registration with profile photo upload
- **Room management** — Track and manage single and double occupancy rooms
- **Fee management** — View and manage student fee records
- **Complaint system** — Students can submit and delete complaints; owners can review them
- **Notice board** — Post and manage hostel notices
- **Staff management** — View and manage hostel staff
- **AI Chat Bot** — Integrated AI assistant for hostel-related queries
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
     define('DB_NAME', 'hostel_db');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     ```

5. **Run the application**
   - Start Apache and MySQL from XAMPP Control Panel
   - Visit: [http://localhost/HostelManagementSystem/](http://localhost/HostelManagementSystem/)

---

## User Roles

| Role        | Access                                                                                       |
|-------------|----------------------------------------------------------------------------------------------|
| **Owner**   | Full access — students, rooms, fees, complaints, notices, staff                              |
| **Warden**  | Warden dashboard for day-to-day hostel supervision                                           |
| **Student** | Personal dashboard — view room info, fees, notices; submit/delete complaints                 |

Each role is protected by session-based middleware (`requireRole()`), so users are automatically redirected if they try to access an unauthorized page.

---

## Contributors

| Name | Role | Contributions |
|------|------|---------------|
| **Kraneel Manandhar** | Project Manager | Built the Login/Signup system, Registration System, authentication system, and password reset system. Developed the AI chatbot and the complete Owner module. Assisted Prayash in building the Warden module. Performed overall bug fixing and project coordination. |
| **Abibsha Ghaju** | Developer | Handled the complete Student module, dashboard, and related features. Also contributed to bug fixing across the project. |
| **Prayash Shrestha** | Developer | Handled the Warden module, implementing warden dashboard features and day-to-day hostel supervision functionality. |

---

> Built by [kraneelManandhar](https://github.com/kraneelManandhar) and the team
