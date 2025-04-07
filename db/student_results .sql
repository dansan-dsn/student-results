-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 07, 2025 at 09:06 PM
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
-- Database: `student_results`
--

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `id` int(11) NOT NULL,
  `reg_no` int(11) NOT NULL,
  `reason` text NOT NULL,
  `details` text NOT NULL,
  `lecturer` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`id`, `reg_no`, `reason`, `details`, `lecturer`, `status`, `created_at`) VALUES
(1, 6, 'Faulty Marks', 'd', 2, 1, '2025-04-01 14:08:41'),
(2, 6, 'Faulty Marks', 'd', 2, 1, '2025-04-01 14:08:52'),
(3, 6, 'Faulty Marks', 'ddd', 2, 0, '2025-04-01 14:10:34'),
(4, 6, 'Faulty Marks', 'dddd', 2, 1, '2025-04-01 14:14:36'),
(5, 6, 'Faulty Marks', 'dddd', 2, 1, '2025-04-01 14:18:34'),
(6, 6, 'Missing Marks', 'try check sir', 2, 1, '2025-04-03 18:45:05'),
(7, 7, 'Faulty Marks', 'not now', 2, 1, '2025-04-05 15:06:54');

-- --------------------------------------------------------

--
-- Table structure for table `course`
--

CREATE TABLE `course` (
  `id` int(11) NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `course_code` varchar(10) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course`
--

INSERT INTO `course` (`id`, `course_name`, `course_code`, `created_at`) VALUES
(1, 'Bachelors of Computer Science', 'BCS', '2025-03-29 21:07:48'),
(4, 'Diploma in Computer Science', 'DCS', '2025-04-03 19:20:00');

-- --------------------------------------------------------

--
-- Table structure for table `course_unit`
--

CREATE TABLE `course_unit` (
  `id` int(11) NOT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(100) NOT NULL,
  `credit_units` int(10) NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_unit`
--

INSERT INTO `course_unit` (`id`, `code`, `name`, `credit_units`, `create_at`) VALUES
(1, '01SBCS', 'Operating Systems', 4, '2025-03-29 18:54:56'),
(3, 'DS121', 'Data Structures and Algorithms', 4, '2025-04-01 22:56:13'),
(4, 'A234', 'Automata', 4, '2025-04-01 22:56:28'),
(5, 'SW98', 'Software Engineering', 4, '2025-04-01 22:56:48');

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

CREATE TABLE `department` (
  `id` int(11) NOT NULL,
  `department_name` varchar(50) NOT NULL,
  `department_head` int(11) NOT NULL,
  `created_on` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `department`
--

INSERT INTO `department` (`id`, `department_name`, `department_head`, `created_on`) VALUES
(11, 'Science and Technology', 2, '2025-03-31 14:30:57');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `studentId` int(11) NOT NULL,
  `academic_year` varchar(20) NOT NULL,
  `semester` int(11) NOT NULL,
  `year_of_study` int(11) NOT NULL,
  `enrolled_on` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `studentId`, `academic_year`, `semester`, `year_of_study`, `enrolled_on`) VALUES
(4, 6, '2025', 1, 1, '2025-04-07 15:04:55'),
(8, 7, '2025', 1, 1, '2025-04-07 16:15:47');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL,
  `related_url` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `results`
--

CREATE TABLE `results` (
  `id` int(11) NOT NULL,
  `code` int(10) NOT NULL,
  `course_work` int(50) DEFAULT NULL,
  `exam` int(50) DEFAULT NULL,
  `academic_year` varchar(20) NOT NULL,
  `semester` int(11) NOT NULL,
  `year_of_study` int(11) NOT NULL,
  `studentId` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `results`
--

INSERT INTO `results` (`id`, `code`, `course_work`, `exam`, `academic_year`, `semester`, `year_of_study`, `studentId`, `created_at`) VALUES
(4, 1, 22, 55, '2025', 1, 1, 6, '2025-04-07 15:33:31'),
(5, 3, 3, 44, '2025', 1, 1, 6, '2025-04-07 15:44:30'),
(9, 1, 22, 70, '2025', 1, 1, 7, '2025-04-07 16:16:05');

-- --------------------------------------------------------

--
-- Table structure for table `room`
--

CREATE TABLE `room` (
  `id` int(11) NOT NULL,
  `room_name` varchar(50) NOT NULL,
  `capacity` int(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room`
--

INSERT INTO `room` (`id`, `room_name`, `capacity`, `created_at`) VALUES
(3, 'Main Hall', 150, '2025-03-30 20:48:59'),
(4, 'Library 2', 100, '2025-03-30 22:36:38');

-- --------------------------------------------------------

--
-- Table structure for table `room_allocation`
--

CREATE TABLE `room_allocation` (
  `id` int(11) NOT NULL,
  `course_unit_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `status` text DEFAULT NULL,
  `create_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room_allocation`
--

INSERT INTO `room_allocation` (`id`, `course_unit_id`, `room_id`, `date`, `start_time`, `status`, `create_at`) VALUES
(21, 1, 4, '2025-04-01', '01:42:00', 'pending', '2025-03-31 22:42:11'),
(22, 1, 4, '2025-04-01', '01:43:00', 'complete', '2025-03-31 22:43:52'),
(23, 3, 3, '2025-04-01', '01:43:00', 'pending', '2025-03-31 22:44:00'),
(24, 1, 3, '2025-04-01', '01:44:00', 'pending', '2025-03-31 22:44:11');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staffId` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `rank` varchar(20) NOT NULL,
  `department` int(11) DEFAULT NULL,
  `gender` text NOT NULL,
  `date_of_birth` date NOT NULL,
  `created_on` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`staffId`, `name`, `rank`, `department`, `gender`, `date_of_birth`, `created_on`) VALUES
(2, 'Staff Walden', 'Head Manager', 11, 'Male', '2025-03-31', '2025-03-31 14:58:39');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `studentId` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `student_no` varchar(50) DEFAULT NULL,
  `reg_no` varchar(30) DEFAULT NULL,
  `course` int(11) DEFAULT NULL,
  `department` int(11) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` text DEFAULT NULL,
  `nationality` varchar(30) DEFAULT NULL,
  `create_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`studentId`, `name`, `student_no`, `reg_no`, `course`, `department`, `date_of_birth`, `gender`, `nationality`, `create_at`) VALUES
(6, 'Student Dansan', '2343GDFD', '23/BCS/UMC', 1, 11, '2025-03-31', 'Male', 'Ugandan', '2025-03-31 17:30:00'),
(7, 'Agaba Walden', '12345kdfdhkfj', '2323klkd/', 4, 11, '2025-03-17', 'Male', 'Ugandan', '2025-04-05 14:14:12');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `role` text NOT NULL,
  `create_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `role`, `create_at`) VALUES
(2, 'staff@gmail.com', '$2y$10$SdZ9olbCEBwgQtviD0wKAORaCCVryLoaZGFfw3qnIvRkYM2Mi/EDq', 'staff', '2025-03-27 21:53:14'),
(6, 'student@gmail.com', '$2y$10$ZCOJgLBO0twxb6B2MxCWWeykotbFbUyj66husM0pdVTj1oECbnSfe', 'student', '2025-03-31 20:29:25'),
(7, 'dansan@gmail.com', '$2y$10$R5fRxcOS0b6CVAr1/cnLzuzS.fIgdWFhCiOihxEL3TFi18qgmutY2', 'student', '2025-04-05 17:12:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `course`
--
ALTER TABLE `course`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `course_unit`
--
ALTER TABLE `course_unit`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `department`
--
ALTER TABLE `department`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `results`
--
ALTER TABLE `results`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `room`
--
ALTER TABLE `room`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `room_allocation`
--
ALTER TABLE `room_allocation`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staffId`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`studentId`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `course`
--
ALTER TABLE `course`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `course_unit`
--
ALTER TABLE `course_unit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `department`
--
ALTER TABLE `department`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `results`
--
ALTER TABLE `results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `room`
--
ALTER TABLE `room`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `room_allocation`
--
ALTER TABLE `room_allocation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
