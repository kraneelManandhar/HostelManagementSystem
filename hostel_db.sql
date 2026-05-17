-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 14, 2026 at 08:35 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

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

--
-- Dumping data for table `cleaning`
--

INSERT INTO `cleaning` (`id`, `room_id`, `status`, `cleaned_at`) VALUES
(1, 2, 'Pending', NULL),
(2, 1, 'Done', '2026-05-05 03:32:44'),
(3, 3, 'Pending', NULL),
(4, 4, 'Done', '2026-05-05 03:32:54'),
(5, 5, 'Done', '2026-05-05 03:32:55'),
(6, 11, 'Done', '2026-05-06 02:01:09'),
(7, 7, 'Pending', NULL);

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
(14, 5, 1, 'Unhygienic Bathroom', 'Bathroom is not maintained.', 'Pending', '2026-04-27 19:20:34'),
(17, 2, 2, 'Hukumbasi nikal', 'Desh bata nikl', 'Resolved', '2026-05-12 17:15:15'),
(19, 19, 20, 'User Interface Problem', 'Very disturbing design, to much simulation on eyes', 'Pending', '2026-05-13 07:06:22');

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
(1, 5, 670.00, 670.00, 'Paid'),
(2, 2, 0.00, 0.00, 'Paid'),
(3, 3, 0.00, 0.00, 'Paid'),
(4, 8, 0.00, 0.00, 'Paid'),
(5, 9, 0.00, 0.00, 'Paid'),
(6, 10, 764.00, 764.00, 'Paid'),
(7, 11, 0.00, 0.00, 'Paid'),
(8, 12, 0.00, 0.00, 'Paid'),
(10, 14, 0.00, 0.00, 'Paid'),
(11, 15, 100000.00, 10000.00, ''),
(12, 16, 0.00, 0.00, 'Paid'),
(13, 11, 10000.00, 10000.00, 'Paid'),
(14, 18, 99999.00, 99999.00, 'Paid'),
(15, 19, 10000.00, 10000.00, 'Paid'),
(17, 22, 0.00, 0.00, 'Paid');

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

--
-- Dumping data for table `food`
--

INSERT INTO `food` (`id`, `student_id`, `meal_type`, `status`, `date`) VALUES
(1, 5, NULL, 0, '2026-05-04'),
(2, 2, NULL, 1, '2026-05-12'),
(3, 16, NULL, 0, '2026-05-06'),
(4, 14, NULL, 1, '2026-05-06'),
(5, 8, NULL, 1, '2026-05-06'),
(6, 15, NULL, 1, '2026-05-06');

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

--
-- Dumping data for table `laundry`
--

INSERT INTO `laundry` (`id`, `student_id`, `status`, `given_at`, `returned_at`) VALUES
(1, 5, 'Pending', '2026-05-04 15:06:43', NULL),
(2, 3, 'Pending', '2026-05-04 16:51:40', NULL),
(3, 14, 'Completed', '2026-05-04 17:30:54', '2026-05-06 02:57:44'),
(4, 8, 'Completed', '2026-05-06 06:42:45', '2026-05-06 02:57:45');

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
(13, 'mahhansykto@gmail.com', '376c8751a7102a42b9f41ce1005466eba8232799ecbb89513ec3d9f37dd05987', '2026-05-04 13:44:04', 0, '2026-05-04 06:59:04'),
(14, 'ysw8e@deltajohnsons.com', 'f25173abbe985b627fd7d08efe77c755433228bf39847085c55330e811f5da94', '2026-05-05 13:59:42', 1, '2026-05-05 07:14:42'),
(17, 'gmrsrijit@gmail.com', 'f99f5b2015033fa65da484333feb52f8e0a6d99492f22b22a6ece5aa1caaf639', '2026-05-13 13:48:51', 1, '2026-05-13 07:03:51'),
(18, 'np03cs4a240241@heraldcollege.edu.np', '7e41e19f2cf31e10f0e39bb648eceb6632445d7a567bcdd28a0328258eff0edb', '2026-05-13 22:27:25', 1, '2026-05-13 15:42:25');

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
(1, 'A1', 'single', 10, NULL),
(2, 'B1', 'double', 2, 11),
(3, 'A2', 'single', 12, NULL),
(4, 'A3', 'double', 14, 3),
(5, 'A4', 'single', 8, NULL),
(6, 'B2', 'double', 16, 5),
(7, 'B3', 'single', NULL, NULL),
(8, 'B4', 'single', NULL, NULL),
(9, 'C1', 'single', 22, NULL),
(10, 'C2', 'single', NULL, NULL),
(11, 'C3', 'single', 18, NULL),
(12, 'C4', 'double', 15, 9),
(13, 'D1', 'double', NULL, NULL),
(14, 'D2', 'double', 17, NULL),
(15, 'D3', 'double', NULL, NULL),
(16, 'D4', 'single', NULL, NULL),
(17, 'E1', 'single', NULL, NULL),
(18, 'E2', 'single', NULL, NULL),
(19, 'E3', 'single', NULL, NULL),
(20, 'E4', 'single', 19, NULL);

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
(2, 'Kraneel', NULL, 'Manandhar', '9841243263', '2006-09-21', 'np03cs4a240241@heraldcollege.edu.np', '$2y$10$hi7W8JXkIYzCxv4cbwjtXe5HdKS7DKPjCjKtdG7llQWJtGXa6KC5C', 'student_69f0cbe7bbe944.48522978.jpg', 'Herald College', 'Gongabu', '2023-11-15', 'Gagdjabcjchaokcnakjc cajcbkjack', 'Parent', '9812345678', 'double', 2, '2026-04-25 09:47:34'),
(3, 'Mega', NULL, 'Knight', '00000000000', '2024-03-07', 'nvflzivunjxkexexis@vtmpj.net', '$2y$10$yfF4mmux7YVo5Q3Y2donxee9qRN60.ra7vFTnIWojiVqyCeQnp8p6', 'public/uploads/69ecabe5ddef8_192754067.png', 'Herald College', 'Samakhushi', '2023-03-15', 'Normal Knight', 'Sibling', '9999999999', 'single', 4, '2026-04-25 11:56:41'),
(5, 'Abibsha', 'Rani', 'Ghaju', '9800000000', '2006-03-19', 'abibshag@gmail.com', '$2y$10$7uJiz4TECM2jerM9bzOHreVwv8/v3/ECVQTnQrCy75dndglxsRr8e', '69efa7e89ca1a_721605999.png', 'Global College', 'Bhaktapur', '2026-04-27', 'Srijana Ghaju', 'Parent', '9841234560', 'single', 6, '2026-04-27 18:16:18'),
(8, 'Prajwal', 'Raj', 'Bansi', '00000000000', '2014-10-15', 'mahhansykto@gmail.com', '$2y$10$kZ6CmkWSfcoUzMvYrT/gBeK2gGNcM575cjXILMvbieK9kQYpZdCpG', '69f05760b81e4_577959422.jpg', 'Herald College', 'Samakhushi', '2033-11-21', 'Ryal Bhattarai', 'Sibling', '2222222222', 'double', 5, '2026-04-28 06:45:09'),
(9, 'Sirjeet', NULL, 'Niger', '1234567890', '2006-02-18', 'sirjeet@gmail.com', '$2y$10$KLxxH1gyCmaeBs032EPtV.6U6F7yFIzXYEE6lkpwJbp5dMCfDGdg2', '69f05ab1131df_895725329.jpeg', 'Niger College', 'Lumbini', '2025-05-31', 'GMR GAI', 'Parent', '0987654321', 'single', 12, '2026-04-28 06:59:12'),
(10, 'Pratigya', 'Pun', 'Magar', '9851036280', '2006-02-14', 'pratigya@gmail.com', '$2y$10$FOw3tI.34lniQlVHqGqu4Oq5XiDmT8ES86J2S02Gn2eckrnRkFWPy', '69f05cc31c3a7_848071048.jpg', 'Herald International College', 'Pokhara', '2024-02-07', 'Ram Pun Magar', 'Parent', '7890789078', 'single', 1, '2026-04-28 07:08:01'),
(11, 'Shirish', NULL, 'Magar', '1111111111', '2006-03-19', 'shirisha@gmail.com', '$2y$10$weaFUDrfrnVX8aZjtswIEulbon3xZEqg7D74zxX1ZJWmMul.GKogK', '69f0a10a2cef1_843549509.jpg', 'Niger College', 'Taplejung', '2023-02-05', 'Ryal Bhattarai', 'Other', '9999999999', 'double', 2, '2026-04-28 11:59:23'),
(12, 'Kushal', NULL, 'Miha', '45678987690', '2006-09-23', 'kushalmiha@gmail.com', '$2y$10$9c84wFw02rcHjn4YZyuNMemniOwrL1ReVQHoq76LjQU4jLDvIMbF.', NULL, 'Niger College', 'Tamel', '2008-03-06', 'nigga dai', 'Parent', '9999999999', 'double', 3, '2026-04-29 03:22:36'),
(14, 'Kraneel', NULL, 'Manandhar', '1111111111', '2007-02-09', 'kraneelmanandhar@gmail.com', '$2y$10$2VHLjRRrEgsvROBQAVWeBOY4uazARc4HKIXkUdt2/MkeyPlxTBeHq', '69f72dfe1d577_412660825.jpg', 'Herald College', 'Gongabu', '2025-07-23', 'Gagdjabcjchaokcnakjc cajcbkjack', 'Parent', '7890789078', 'double', 4, '2026-05-03 11:14:45'),
(15, 'Shreya', 'Thulung', 'Rai', '9809568263', '2007-10-25', 'ysw8e@deltajohnsons.com', '$2y$10$CNw/PLGKVLfmmw2B1CZ1auShpfU1.nNvnBoV3scEhaeDhN56SUYoO', NULL, 'CANVAS International College', 'Solukhumbu', '2024-07-23', 'Ram Thulung Rai', 'Parent', '9841739484', 'single', 12, '2026-05-05 07:14:15'),
(16, 'Ishan', NULL, 'Manandhar', '0000000000', '2007-02-01', 'manandharishan63@gmail.com', '$2y$10$uBxjxCCMLlN6hBkqA.m6bepF./OZ57bJpmsCISHHmLYSjYccj4ECW', 'student_69fa2b7d3e6637.48486337.jpg', 'Patan Multiple Campus', 'Patan', '2025-10-08', 'Kraneel Manandhar', 'Sibling', '9841243263', 'double', 6, '2026-05-05 17:07:32'),
(17, 'Xiaomi', NULL, 'Laptop', '9810100129', '2005-07-18', 'xiaomi@gmail.com', '$2y$10$2Rxb/tL7Uw7DNwUmbWR6P..gYoA6UGcjf12g6DVZaURRlHy4uc5za', '69fa3a57ebbe6_162470425.jpg', 'Herald College', 'Lumbini', '2017-06-28', 'China', 'Other', '9820830685', 'double', 14, '2026-05-05 18:44:10'),
(18, 'lakshya', NULL, 'Gurung', '9843878297', '2004-03-28', 'lakshya@gmail.com', '$2y$10$gY1U9IMWCSq/SOVJxqfI6emVlQuoHtdSoJ2IBAmL0jGukelFpIAsi', '69fae7bed13df_752953558.png', 'Herald College', 'Patan', '2025-10-05', 'Nischal Raj Bansi', 'Parent', '2222222222', 'single', 11, '2026-05-06 07:04:07'),
(19, 'Srijit', NULL, 'Ghimire', '9809854678', '2006-04-26', 'gmrsrijit@gmail.com', '$2y$10$pRBQ2mMUakAvQajhm1tmC.Ivg1jqkr0M4k4gCys/02w8ou7BGf/Rq', 'student_6a04230f72c692.79737283.png', 'KhanaPinaJina College', 'Ghar', '2009-02-20', 'Walalalalallalala', 'Other', '9763618969', 'single', 20, '2026-05-10 02:12:37'),
(22, 'Krish', NULL, 'Soul Society', '9841243263', '2006-09-11', 'prajwaldulal54@gmail.com', '$2y$10$tHQwcgmQgFwssfL9gOKIyOll3.fU6EBvzTL3dVOo9L9YVIeOi5fye', 'student_6a0568bd007aa0.85936841.jpg', 'Herald College', 'Ghar', '2006-02-02', 'Gagdjabcjchaokcnakjc cajcbkjack', 'Sibling', '9763618969', 'single', 9, '2026-05-14 06:17:03');

--
-- Triggers `students`
--
DELIMITER $$
CREATE TRIGGER `after_student_insert` AFTER INSERT ON `students` FOR EACH ROW BEGIN
    INSERT INTO fees (student_id, total, paid, status)
    VALUES (NEW.id, 100000.00, 0.00, 'Pending');
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

--
-- Dumping data for table `timing`
--

INSERT INTO `timing` (`id`, `student_id`, `check_in`, `check_out`, `status`) VALUES
(1, 5, '2026-05-04 23:17:00', '2026-05-18 03:36:00', 'OUT'),
(2, 2, '2026-05-12 17:02:00', '2026-05-12 15:02:00', 'IN');

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
  `role` enum('warden','staff') NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `role`, `contact_number`, `created_at`) VALUES
(1, 'Zhadu', 'Wala', 'zhadumylyfe@gmail.com', '$2y$10$P59vR43dtuggGRoR64MleO5zlHdYn1EXx0YuXmlqMILZjvMJGqREi', 'staff', '00000000000', '2026-04-28 15:42:34'),
(2, 'abc', 'def', 'abcdef@gmail.com', '$2y$10$H/IHG2WCg3Nbu053bMxPkud1opWsxFdr7YtftF5t6/BIyCYbzkoXu', 'staff', '1234567890', '2026-04-28 16:37:11'),
(3, 'Watashino', 'Soul Society', 'watashino@gmail.com', '$2y$10$5gAqNzQWvlchRVwDxq0RhOzGI9d8C.eQWGOJaiEj.5UWzPUzxPhFm', 'staff', '9812345678', '2026-05-13 05:08:49');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `fees`
--
ALTER TABLE `fees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `food`
--
ALTER TABLE `food`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `laundry`
--
ALTER TABLE `laundry`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `notices`
--
ALTER TABLE `notices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `timing`
--
ALTER TABLE `timing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
