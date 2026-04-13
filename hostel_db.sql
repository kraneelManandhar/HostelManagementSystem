DATABASE SQL HOSTEL:

CREATE DATABASE IF NOT EXISTS hostel_db;
USE hostel_db;

-- STUDENTS TABLE
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100) DEFAULT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    room_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ROOMS TABLE
CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    number VARCHAR(20) NOT NULL,
    floor_block VARCHAR(100) NOT NULL,
    type ENUM('single', 'double') DEFAULT 'single',
    roommate VARCHAR(150) DEFAULT NULL
);

-- FEES TABLE
CREATE TABLE fees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    paid DECIMAL(10,2) DEFAULT 0,
    pending DECIMAL(10,2) GENERATED ALWAYS AS (total - paid) STORED,
    status ENUM('Paid', 'Pending', 'Overdue') DEFAULT 'Pending',
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- COMPLAINTS TABLE
CREATE TABLE complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    room_number VARCHAR(50) NOT NULL,
    status ENUM('Pending', 'In Progress', 'Resolved') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- NOTICES TABLE
CREATE TABLE notices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    date DATE NOT NULL,
    time TIME NOT NULL,
    author VARCHAR(100) DEFAULT 'HOSTEL MANAGEMENT',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- SAMPLE DATA 
INSERT INTO rooms (number, floor_block, type, roommate)
VALUES
('A34', '3rd Floor Block A', 'single', NULL),
('B12', '2nd Floor Block B', 'double', 'John Doe');

INSERT INTO students (first_name, middle_name, last_name, email, password, room_id)
VALUES
('Abibsha', '—', 'Ghaju', 'abibsha@gmail.com', '123456', 1);

INSERT INTO fees (student_id, total, paid, status)
VALUES
(1, 670.00, 500.00, 'Overdue');

INSERT INTO complaints (student_id, title, description, room_number, status)
VALUES
(1, 'Water Leakage', 'Bathroom tap leaking continuously', 'A34', 'Pending');

INSERT INTO notices (title, description, date, time, author)
VALUES
('Laundry Update', 'Laundry open until midnight on weekends', '2026-10-24', '10:00:00', 'HOSTEL MANAGEMENT');