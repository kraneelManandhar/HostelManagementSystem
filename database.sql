<<<<<<< HEAD
CREATE DATABASE hostel;
USE hostel;

CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100),
    contact VARCHAR(20)
);

CREATE TABLE food (
    student_id INT,
    status BOOLEAN DEFAULT 0,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

CREATE TABLE laundry (
    student_id INT,
    status BOOLEAN DEFAULT 0,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

CREATE TABLE bathroom (
    student_id INT,
    status BOOLEAN DEFAULT 0,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

CREATE TABLE timing (
    student_id INT,
    time_in TIME,
    time_out TIME,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
=======
CREATE DATABASE hostel;
USE hostel;

CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100),
    contact VARCHAR(20)
);

CREATE TABLE food (
    student_id INT,
    status BOOLEAN DEFAULT 0,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

CREATE TABLE laundry (
    student_id INT,
    status BOOLEAN DEFAULT 0,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

CREATE TABLE bathroom (
    student_id INT,
    status BOOLEAN DEFAULT 0,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

CREATE TABLE timing (
    student_id INT,
    time_in TIME,
    time_out TIME,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
>>>>>>> a1168b8b45eef63cc27118b6696886423dcefc31
);