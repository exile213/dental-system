-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jan 12, 2025 at 09:52 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `medical_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int NOT NULL,
  `doctor_id` int NOT NULL,
  `appointment_date` datetime NOT NULL,
  `service` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('available','scheduled','approved','rejected','not_available','not_available_morning','not_available_afternoon','not_available_full_day','requested','canceled') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `availability_type` enum('full_day','morning','afternoon') COLLATE utf8mb4_general_ci NOT NULL,
  `patient_id` int DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `doctor_id`, `appointment_date`, `service`, `status`, `availability_type`, `patient_id`, `is_available`) VALUES
(153, 1, '2025-01-12 13:06:00', 'Tooth removal', 'scheduled', 'full_day', 2, 1),
(154, 1, '2025-01-13 13:29:00', 'Dental fillings', '', 'full_day', 1, 1),
(156, 1, '2025-01-14 13:36:00', NULL, 'available', 'full_day', NULL, 1),
(157, 1, '2025-01-13 15:46:00', NULL, '', 'full_day', 1, 0),
(158, 1, '2025-01-16 00:00:00', 'Dental fillings', 'canceled', 'full_day', 1, 1),
(159, 1, '2025-01-13 00:00:00', 'Teeth Cleanings', 'scheduled', 'full_day', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `first_name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `specialization` varchar(100) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`id`, `user_id`, `first_name`, `last_name`, `specialization`) VALUES
(1, 2, 'qwewe', 'wewew', 'wewe');

-- --------------------------------------------------------

--
-- Table structure for table `medical_records`
--

CREATE TABLE `medical_records` (
  `id` int NOT NULL,
  `patient_id` int DEFAULT NULL,
  `doctor_id` int DEFAULT NULL,
  `record_date` datetime NOT NULL,
  `diagnosis` text COLLATE utf8mb4_general_ci,
  `treatment` text COLLATE utf8mb4_general_ci,
  `notes` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `message` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(1, 1, 'A patient has canceled their appointment.', 1, '2025-01-12 06:47:09'),
(2, 1, 'A patient has rescheduled their appointment.', 1, '2025-01-12 07:43:57'),
(3, 1, 'Your appointment has been approved.', 1, '2025-01-12 07:44:50'),
(4, 1, 'A patient has canceled their appointment.', 1, '2025-01-12 07:49:36'),
(5, 1, 'A patient has rescheduled their appointment.', 1, '2025-01-12 07:49:54'),
(6, 1, 'A patient has rescheduled their appointment.', 0, '2025-01-12 07:52:57'),
(7, 1, 'Your appointment has been approved.', 0, '2025-01-12 08:32:25'),
(8, 1, 'A patient has rescheduled their appointment.', 0, '2025-01-12 08:33:48'),
(9, 1, 'A patient has rescheduled their appointment.', 0, '2025-01-12 08:38:44'),
(10, 1, 'A patient has rescheduled their appointment.', 0, '2025-01-12 08:45:46'),
(11, 1, 'A patient has rescheduled their appointment.', 0, '2025-01-12 08:51:08'),
(12, 1, 'A patient has rescheduled their appointment.', 0, '2025-01-12 09:06:51'),
(13, 1, 'A patient has rescheduled their appointment.', 0, '2025-01-12 09:19:33'),
(14, 1, 'A patient has rescheduled their appointment.', 0, '2025-01-12 09:23:19'),
(15, 2, 'A patient has rescheduled their appointment.', 0, '2025-01-12 09:44:12'),
(16, 2, 'A patient has canceled their appointment.', 0, '2025-01-12 09:46:29');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `first_name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `date_of_birth` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `user_id`, `first_name`, `last_name`, `date_of_birth`) VALUES
(1, 1, 'alde', 'alde', '2025-01-09'),
(2, 3, 'jen', 'chome', '2025-01-09'),
(3, 4, 'wefjb', 'fddjbhsdbsvjk', '2025-02-03'),
(4, 5, 'jane', 'paloma', '2002-09-28'),
(5, 7, 'jane', 'paloma', '2002-09-28');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `user_type` enum('patient','doctor') COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `user_type`) VALUES
(1, 'alde', '$2y$10$63xhbictUwbule1iilk6Fe3aKaRbYyfT7E0Su8UapGDIr51zB7xCW', 'admin1@gmail.com', 'patient'),
(2, 'doctor', '$2y$10$OgLnd0enCHU2UD7pRN.O2uM.qdAMjv7qDoABRvp/RDY8mIaP9kpJa', 'doctor@gmail.com', 'doctor'),
(3, 'chome', '$2y$10$VOQIW9nq5eSEM0TaOJMs8uV01K9xrcca/tZ6JKhqZD5EhCN3ea06e', 'jen.chome@gmail.com', 'patient'),
(4, 'aj', '$2y$10$V0svgUKzaXm6WVkvYZkHUedW4n4IK7sa0WmidSsB9ns/hBVDai.O6', 'sdkfjsdjobjfski@gmail.com', 'patient'),
(5, '22jm', '$2y$10$Dq6hio1t3i95Q4PJcCVOrOFyaMcoJkPMLTldlvwkDbgk8aktR5MVW', 'janemarpaloma@gmail.com', 'patient'),
(7, 'j', '$2y$10$he6PVP25lXXn13GUWkBTUe6By2oh8jPppb5LXzDUeXAYEKbHUcdgy', 'paloma@gmail.com', 'patient');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_patient_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=160;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `medical_records`
--
ALTER TABLE `medical_records`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`);

--
-- Constraints for table `doctors`
--
ALTER TABLE `doctors`
  ADD CONSTRAINT `doctors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD CONSTRAINT `medical_records_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  ADD CONSTRAINT `medical_records_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `fk_patient_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
