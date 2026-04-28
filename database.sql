-- Create and use the database
CREATE DATABASE IF NOT EXISTS hostel_db;
USE hostel_db;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- ========================
-- 1. USERS (Admin + Warden)
-- ========================
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `first_name` VARCHAR(100),
  `last_name` VARCHAR(100),
  `email` VARCHAR(150) UNIQUE,
  `password` VARCHAR(255),
  `role` ENUM('admin','warden','manager','staff') NOT NULL,
  `contact_number` VARCHAR(20),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Default manager user (password: "password")
INSERT INTO `users` (`first_name`, `last_name`, `email`, `password`, `role`, `contact_number`) VALUES
('Admin', 'Manager', 'manager@pentatonic.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager', '9800000000');

-- ========================
-- 2. ROOMS
-- ========================
CREATE TABLE `rooms` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `number` VARCHAR(20) NOT NULL,
  `floor_block` VARCHAR(100),
  `type` ENUM('single','double') DEFAULT 'single',
  `roommate` VARCHAR(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `rooms`
INSERT INTO `rooms` (`id`, `number`, `floor_block`, `type`, `roommate`) VALUES
(1, 'A34', '3rd Floor Block A', 'single', NULL),
(2, 'B12', '2nd Floor Block B', 'double', 'John Doe');

-- ========================
-- 3. STUDENTS
-- ========================
CREATE TABLE `students` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `first_name` VARCHAR(100) NOT NULL,
  `middle_name` VARCHAR(100) DEFAULT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `contact_number` VARCHAR(20) NOT NULL,
  `date_of_birth` DATE NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `profile_photo` VARCHAR(255) DEFAULT NULL,
  `college_name` VARCHAR(255) NOT NULL,
  `permanent_address` TEXT NOT NULL,
  `date_of_joining` DATE NOT NULL,
  `guardian_name` VARCHAR(150) NOT NULL,
  `guardian_relationship` VARCHAR(50) NOT NULL,
  `guardian_contact` VARCHAR(20) NOT NULL,
  `preferred_room_type` ENUM('single','double') DEFAULT 'single',
  `room_id` INT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `students`
INSERT INTO `students` (`id`, `first_name`, `middle_name`, `last_name`, `contact_number`, `date_of_birth`, `email`, `password`, `profile_photo`, `college_name`, `permanent_address`, `date_of_joining`, `guardian_name`, `guardian_relationship`, `guardian_contact`, `preferred_room_type`, `room_id`, `created_at`) VALUES
(2, 'Kraneel', NULL, 'Manandhar', '9841243263', '2006-09-21', 'np03cs4a240241@heraldcollege.edu.np', '$2y$10$IWEGEfbBvojFb452tTvI6OJ8ZZ/ppuSOcMqsTSLJHqn5arJXz8Ds2', NULL, 'Herald College', 'Gongabu', '2023-11-15', 'Gagdjabcjchaokcnakjc cajcbkjack', 'Parent', '900000000000', 'double', NULL, '2026-04-25 09:47:34'),
(3, 'Mega', NULL, 'Knight', '00000000000', '2024-03-07', 'nvflzivunjxkexexis@vtmpj.net', '$2y$10$yfF4mmux7YVo5Q3Y2donxee9qRN60.ra7vFTnIWojiVqyCeQnp8p6', 'public/uploads/69ecabe5ddef8_192754067.png', 'Herald College', 'Samakhushi', '2023-03-15', 'Normal Knight', 'Sibling', '9999999999', 'single', NULL, '2026-04-25 11:56:41'),
(5, 'Abibsha', 'Rani', 'Ghaju', '9841123456', '2006-03-19', 'abibshag@gmail.com', '$2y$10$7uJiz4TECM2jerM9bzOHreVwv8/v3/ECVQTnQrCy75dndglxsRr8e', '69efa7e89ca1a_721605999.png', 'Global College', 'Bhaktapur', '2026-04-27', 'Srijana Ghaju', 'Parent', '9841234560', 'single', 1, '2026-04-27 18:16:18'),
(6, 'Prajwal', 'Raj', 'Bansi', '00000000000', '1997-07-19', 'hansyktomah@gmail.com', '$2y$10$VZohJLIjmvBJbWZK9s5KYOGbKX6TXlqZh6w8JnsaVu5b5JAeUr7lq', NULL, 'Herald College', 'Taplejung', '2024-09-28', 'Nischal Raj Bansi', 'Sibling', '2222222222', 'double', NULL, '2026-04-28 06:17:17'),
(8, 'Prajwal', 'Raj', 'Bansi', '00000000000', '2014-10-15', 'mahhansykto@gmail.com', '$2y$10$Q654aGJS9SYaaZOZi40UBeaiACwtJ0hzC/XyFuB2h5hHeNokUU/5W', '69f05760b81e4_577959422.jpg', 'Herald College', 'Samakhushi', '2033-11-21', 'Ryal Bhattarai', 'Sibling', '2222222222', 'double', NULL, '2026-04-28 06:45:09'),
(9, 'Sirjeet', NULL, 'Niger', '1234567890', '2006-02-18', 'sirjeet@gmail.com', '$2y$10$KLxxH1gyCmaeBs032EPtV.6U6F7yFIzXYEE6lkpwJbp5dMCfDGdg2', '69f05ab1131df_895725329.jpeg', 'Niger College', 'Lumbini', '2025-05-31', 'GMR GAI', 'Parent', '0987654321', 'single', NULL, '2026-04-28 06:59:12');

-- ========================
-- 4. COMPLAINTS (Updated Schema)
-- ========================
CREATE TABLE `complaints` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT,
  `room_id` INT,
  `title` VARCHAR(255),
  `description` TEXT,
  `status` ENUM('Pending','In Progress','Resolved') DEFAULT 'Pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `complaints` (Mapped room_number 'A34' to room_id 1)
INSERT INTO `complaints` (`id`, `student_id`, `room_id`, `title`, `description`, `status`, `created_at`) VALUES
(14, 5, 1, 'Unhygienic Bathroom', 'Bathroom is not maintained.', 'Pending', '2026-04-27 19:20:34');

-- ========================
-- 5. FEES
-- ========================
CREATE TABLE `fees` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT,
  `total` DECIMAL(10,2),
  `paid` DECIMAL(10,2) DEFAULT 0.00,
  `pending` DECIMAL(10,2) GENERATED ALWAYS AS (`total` - `paid`) STORED,
  `status` ENUM('Paid','Pending','Overdue') DEFAULT 'Pending',
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `fees`
INSERT INTO `fees` (`id`, `student_id`, `total`, `paid`, `status`) VALUES
(1, 5, 670.00, 500.00, 'Overdue');

-- ========================
-- 6. NOTICES
-- ========================
CREATE TABLE `notices` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `date` DATE NOT NULL,
  `time` TIME NOT NULL,
  `author` VARCHAR(100) DEFAULT 'HOSTEL MANAGEMENT',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `notices`
INSERT INTO `notices` (`id`, `title`, `description`, `date`, `time`, `author`, `created_at`) VALUES
(1, 'Laundry Update', 'Laundry open until midnight on weekends', '2026-10-24', '10:00:00', 'HOSTEL MANAGEMENT', '2026-04-12 18:13:25');

-- ========================
-- 7. FOOD
-- ========================
CREATE TABLE `food` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT,
  `meal_type` ENUM('Breakfast','Lunch','Dinner'),
  `status` BOOLEAN DEFAULT 0,
  `date` DATE,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================
-- 8. CLEANING
-- ========================
CREATE TABLE `cleaning` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `room_id` INT,
  `status` ENUM('Pending','Done') DEFAULT 'Pending',
  `cleaned_at` TIMESTAMP NULL,
  FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================
-- 9. LAUNDRY
-- ========================
CREATE TABLE `laundry` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT,
  `status` ENUM('Pending','Washing','Completed') DEFAULT 'Pending',
  `given_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `returned_at` TIMESTAMP NULL,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================
-- 10. TIMING
-- ========================
CREATE TABLE `timing` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT,
  `check_in` DATETIME,
  `check_out` DATETIME,
  `status` ENUM('IN','OUT') DEFAULT 'IN',
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;