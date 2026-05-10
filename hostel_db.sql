-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 05, 2026 at 08:31 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hostel_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `cleaning`
--

CREATE TABLE `cleaning` (
  `id` int(11) NOT NULL,
  `room_id` int(11) DEFAULT NULL,
  `status` enum('Pending','Done') DEFAULT 'Pending',
  `cleaned_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `room_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('Pending','In Progress','Resolved') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`id`, `student_id`, `room_id`, `title`, `description`, `status`, `created_at`) VALUES
(19, 22, 9, 'Unhygienic Bathroom', 'Bathroom hygiene is not maintained in a decent manner.', 'Pending', '2026-05-05 17:08:34'),
(20, 19, 1, 'Food Quality Degraded', 'The food quality has degraded since past few days.\r\nWe are not having a balanced diet.', 'Pending', '2026-05-05 17:11:14'),
(21, 24, 2, 'Unhygienic Bathroom', 'Sanitation of Bathroom is not maintained for several days.', 'Pending', '2026-05-05 18:18:02');

-- --------------------------------------------------------

--
-- Table structure for table `fees`
--

CREATE TABLE `fees` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `paid` decimal(10,2) DEFAULT 0.00,
  `pending` decimal(10,2) GENERATED ALWAYS AS (`total` - `paid`) STORED,
  `status` enum('Paid','Pending','Overdue') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fees`
--

INSERT INTO `fees` (`id`, `student_id`, `total`, `paid`, `status`) VALUES
(2, 2, 0.00, 0.00, 'Pending'),
(3, 3, 0.00, 0.00, 'Paid'),
(4, 8, 0.00, 0.00, 'Pending'),
(5, 9, 0.00, 0.00, 'Pending'),
(6, 10, 0.00, 0.00, 'Paid'),
(7, 11, 0.00, 0.00, 'Pending'),
(8, 12, 0.00, 0.00, 'Pending'),
(10, 14, 0.00, 0.00, 'Pending'),
(15, 19, 0.00, 0.00, 'Pending'),
(16, 21, 0.00, 0.00, 'Pending'),
(17, 22, 0.00, 0.00, 'Pending'),
(19, 24, 0.00, 0.00, 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `food`
--

CREATE TABLE `food` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `meal_type` enum('Breakfast','Lunch','Dinner') DEFAULT NULL,
  `status` tinyint(1) DEFAULT 0,
  `date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `laundry`
--

CREATE TABLE `laundry` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `status` enum('Pending','Washing','Completed') DEFAULT 'Pending',
  `given_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `returned_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notices`
--

CREATE TABLE `notices` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `author` varchar(100) DEFAULT 'HOSTEL MANAGEMENT',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notices`
--

INSERT INTO `notices` (`id`, `title`, `description`, `date`, `time`, `author`, `created_at`) VALUES
(1, 'Laundry Update', 'Laundry open until midnight on weekends', '2026-10-24', '10:00:00', 'HOSTEL MANAGEMENT', '2026-04-12 18:13:25'),
(2, 'Holiday notice', 'Tommorrow I have decided to give you a hostel wide holiday.\r\n\r\n\r\n-The Management', '2026-04-28', '17:43:45', 'HOSTEL MANAGEMENT', '2026-04-28 15:43:45');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(150) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`, `used`, `created_at`) VALUES
(5, 'shirisha@gmail.com', '1d94e98f94a1caed297acd8b5f4318eac251206bdebe5432d513ab4877bbd331', '2026-05-02 18:17:42', 0, '2026-05-02 15:17:42'),
(6, 'np03cs4a240241@heraldcollege.edu.np', 'a0b9160f4c2e7209929be1c091aa31280166bfe13a1d8aa7294a38167f959047', '2026-05-03 14:57:17', 0, '2026-05-03 11:57:17'),
(9, 'mahhansykto@gmail.com', '4a981da132665a2602d5b2af4a1b615364ebd380d81a04af3fba7789a6edb22b', '2026-05-03 18:47:02', 1, '2026-05-03 12:02:02'),
(10, 'abibshag@gmail.com', '75aba3748e5e59b7527d81c6dcd5e8e12e9af573daa235c2f373e9e1d99c2446', '2026-05-04 01:04:37', 1, '2026-05-03 18:19:37'),
(11, 'adishree700@gmail.com', '0a87f745ab12bcfc172ab5fc1f1c704b78e4fb54a76c808e798835ea76fe1c52', '2026-05-05 19:36:18', 1, '2026-05-05 12:51:18');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `number` varchar(20) NOT NULL,
  `type` enum('single','double') NOT NULL DEFAULT 'double',
  `student1_id` int(11) DEFAULT NULL,
  `student2_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `number`, `type`, `student1_id`, `student2_id`) VALUES
(1, 'A1', 'single', 19, NULL),
(2, 'A2', 'single', 24, NULL),
(3, 'A3', 'single', NULL, NULL),
(4, 'A4', 'single', NULL, NULL),
(5, 'B1', 'single', NULL, NULL),
(6, 'B2', 'single', NULL, NULL),
(7, 'B3', 'single', NULL, NULL),
(8, 'B4', 'single', NULL, NULL),
(9, 'C1', 'double', 21, 22),
(10, 'C2', 'double', NULL, NULL),
(11, 'C3', 'double', NULL, NULL),
(12, 'C4', 'double', NULL, NULL),
(13, 'D1', 'double', NULL, NULL),
(14, 'D2', 'double', NULL, NULL),
(15, 'D3', 'double', NULL, NULL),
(16, 'D4', 'double', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `date_of_birth` date NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `college_name` varchar(255) NOT NULL,
  `permanent_address` text NOT NULL,
  `date_of_joining` date NOT NULL,
  `guardian_name` varchar(150) NOT NULL,
  `guardian_relationship` varchar(50) NOT NULL,
  `guardian_contact` varchar(20) NOT NULL,
  `preferred_room_type` enum('single','double') DEFAULT 'single',
  `room_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `first_name`, `middle_name`, `last_name`, `contact_number`, `date_of_birth`, `email`, `password`, `profile_photo`, `college_name`, `permanent_address`, `date_of_joining`, `guardian_name`, `guardian_relationship`, `guardian_contact`, `preferred_room_type`, `room_id`, `created_at`) VALUES
(2, 'Kraneel', '', 'Manandhar', '9841243263', '2006-09-21', 'np03cs4a240241@heraldcollege.edu.np', '$2y$10$IWEGEfbBvojFb452tTvI6OJ8ZZ/ppuSOcMqsTSLJHqn5arJXz8Ds2', 'student_69f0cbe7bbe944.48522978.jpg', 'Herald College', 'Gongabu', '2023-11-15', 'Gagdjabcjchaokcnakjc cajcbkjack', 'Parent', '900000000000', 'double', NULL, '2026-04-25 09:47:34'),
(3, 'Mega', NULL, 'Knight', '00000000000', '2024-03-07', 'nvflzivunjxkexexis@vtmpj.net', '$2y$10$yfF4mmux7YVo5Q3Y2donxee9qRN60.ra7vFTnIWojiVqyCeQnp8p6', 'public/uploads/69ecabe5ddef8_192754067.png', 'Herald College', 'Samakhushi', '2023-03-15', 'Normal Knight', 'Sibling', '9999999999', 'single', NULL, '2026-04-25 11:56:41'),
(8, 'Prajwal', 'Raj', 'Bansi', '00000000000', '2014-10-15', 'mahhansykto@gmail.com', '$2y$10$gI0kXHxtmBTrPFdkauRGHuAmRjFICxtwcQjbJkMku7IOkWeEnBNpG', '69f05760b81e4_577959422.jpg', 'Herald College', 'Samakhushi', '2033-11-21', 'Ryal Bhattarai', 'Sibling', '2222222222', 'double', NULL, '2026-04-28 06:45:09'),
(9, 'Sirjeet', NULL, 'Niger', '1234567890', '2006-02-18', 'sirjeet@gmail.com', '$2y$10$KLxxH1gyCmaeBs032EPtV.6U6F7yFIzXYEE6lkpwJbp5dMCfDGdg2', '69f05ab1131df_895725329.jpeg', 'Niger College', 'Lumbini', '2025-05-31', 'GMR GAI', 'Parent', '0987654321', 'single', NULL, '2026-04-28 06:59:12'),
(10, 'Pratigya', 'Pun', 'Magar', '9851036289', '2006-02-14', 'pratigya@gmail.com', '$2y$10$FOw3tI.34lniQlVHqGqu4Oq5XiDmT8ES86J2S02Gn2eckrnRkFWPy', '69f05cc31c3a7_848071048.jpg', 'Herald International College', 'Pokhara', '2024-02-07', 'Ram Pun Magar', 'Parent', '7890789078', 'single', NULL, '2026-04-28 07:08:01'),
(11, 'Shirish', NULL, 'Magar', '1111111111', '2006-03-19', 'shirisha@gmail.com', '$2y$10$weaFUDrfrnVX8aZjtswIEulbon3xZEqg7D74zxX1ZJWmMul.GKogK', '69f0a10a2cef1_843549509.jpg', 'Niger College', 'Taplejung', '2023-02-05', 'Ryal Bhattarai', 'Other', '9999999999', 'double', NULL, '2026-04-28 11:59:23'),
(12, 'Kushal', NULL, 'Miha', '45678987690', '2006-09-23', 'kushalmiha@gmail.com', '$2y$10$9c84wFw02rcHjn4YZyuNMemniOwrL1ReVQHoq76LjQU4jLDvIMbF.', NULL, 'Niger College', 'Tamel', '2008-03-06', 'nigga dai', 'Parent', '9999999999', 'double', NULL, '2026-04-29 03:22:36'),
(14, 'Kraneel', NULL, 'Manandhar', '1111111111', '2007-02-09', 'kraneelmanandhar@gmail.com', '$2y$10$2VHLjRRrEgsvROBQAVWeBOY4uazARc4HKIXkUdt2/MkeyPlxTBeHq', '69f72dfe1d577_412660825.jpg', 'Herald College', 'Gongabu', '2025-07-23', 'Gagdjabcjchaokcnakjc cajcbkjack', 'Parent', '7890789078', 'double', NULL, '2026-05-03 11:14:45'),
(19, 'Shaily', 'Kumari', 'Nepal', '9723415678', '2006-08-17', 'np03cs4a240113@heraldcollege.edu.np', '$2y$10$7XALl5MjQAruuv.zI1JnMu2CYJQLxcj/FAZlQ9YLEi.T6v8vHFpRS', '69f9ffd3adb8a_725383609.png', 'Sankar Dev Campus', 'Jhapa', '2026-05-05', 'Shiv Nepal', 'Parent', '9756142367', 'single', 1, '2026-05-05 14:34:16'),
(21, 'Adishree', '-', 'Ghaju', '9887654321', '2007-02-16', 'adishree700@gmail.com', '$2y$10$IXdeMbgWtPtbNieOMJRune73fvJyDFycHxA5FpTSm36FAtFdkL7qa', '69fa225b60c10_311297119.png', 'ABCD College', 'Bhaktapur', '2026-05-05', 'Dinesh Ghaju', 'Parent', '9887654321', 'double', 9, '2026-05-05 17:01:31'),
(22, 'Abibi', '-', 'Thapa', '9898754321', '2006-03-05', 'abibighaju@gmail.com', '$2y$10$i3I1d7pTsrkCld6GshJS7.XF0G6x6tYdvkgHHKQExsZIrDyKvBJ5O', '69fa237040f5e_279561916.png', 'XYZ College', 'Bhaktapur', '2026-05-05', 'Anita Thapa', 'Parent', '98856342167', 'double', 9, '2026-05-05 17:06:08'),
(24, 'Abibsha', 'Rani', 'Ghaju', '9841123463', '2006-03-19', 'abibshag@gmail.com', '$2y$10$5xz9ovpGcK3oji3zQVHrbOP0RqokEW1FI7s0/MA24q16zelinu/eS', '69fa319d6a17e_985648665.png', 'Global College', 'Bhaktapur', '2026-05-05', 'Abibsha Ghaju', 'Parent', '9745362712', 'single', 2, '2026-05-05 18:06:34');

--
-- Triggers `students`
--
DELIMITER $$
CREATE TRIGGER `after_student_insert` AFTER INSERT ON `students` FOR EACH ROW BEGIN
    INSERT INTO fees (student_id, total, paid, status)
    VALUES (NEW.id, 0.00, 0.00, 'Pending');
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `timing`
--

CREATE TABLE `timing` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `check_in` datetime DEFAULT NULL,
  `check_out` datetime DEFAULT NULL,
  `status` enum('IN','OUT') DEFAULT 'IN'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','warden','staff') NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `role`, `contact_number`, `created_at`) VALUES
(1, 'Zhadu', 'Wala', 'zhadumylyfe@gmail.com', '$2y$10$P59vR43dtuggGRoR64MleO5zlHdYn1EXx0YuXmlqMILZjvMJGqREi', 'staff', '00000000000', '2026-04-28 15:42:34'),
(2, 'abc', 'def', 'abcdef@gmail.com', '$2y$10$H/IHG2WCg3Nbu053bMxPkud1opWsxFdr7YtftF5t6/BIyCYbzkoXu', 'staff', '1234567890', '2026-04-28 16:37:11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cleaning`
--
ALTER TABLE `cleaning`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `fees`
--
ALTER TABLE `fees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `food`
--
ALTER TABLE `food`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `laundry`
--
ALTER TABLE `laundry`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `notices`
--
ALTER TABLE `notices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_student1` (`student1_id`),
  ADD KEY `fk_student2` (`student2_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `timing`
--
ALTER TABLE `timing`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cleaning`
--
ALTER TABLE `cleaning`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `fees`
--
ALTER TABLE `fees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `food`
--
ALTER TABLE `food`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `laundry`
--
ALTER TABLE `laundry`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notices`
--
ALTER TABLE `notices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `timing`
--
ALTER TABLE `timing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cleaning`
--
ALTER TABLE `cleaning`
  ADD CONSTRAINT `cleaning_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `complaints_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `complaints_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `fees`
--
ALTER TABLE `fees`
  ADD CONSTRAINT `fees_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `food`
--
ALTER TABLE `food`
  ADD CONSTRAINT `food_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `laundry`
--
ALTER TABLE `laundry`
  ADD CONSTRAINT `laundry_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `fk_student1` FOREIGN KEY (`student1_id`) REFERENCES `students` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_student2` FOREIGN KEY (`student2_id`) REFERENCES `students` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `timing`
--
ALTER TABLE `timing`
  ADD CONSTRAINT `timing_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
