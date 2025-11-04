-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 04, 2025 at 02:52 PM
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
-- Database: `archive_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `archives`
--

CREATE TABLE `archives` (
  `id` int(11) NOT NULL,
  `letter_id` int(11) DEFAULT NULL,
  `archived_by` int(11) DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `receiver` varchar(100) NOT NULL,
  `sender` varchar(100) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `letter_type` varchar(100) NOT NULL,
  `ref_no` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `email`, `created_at`, `updated_at`) VALUES
(1, 'Human Resources', 'hr@company.com', '2025-11-04 11:22:37', '2025-11-04 11:22:37'),
(2, 'Information Technology', 'it@company.com', '2025-11-04 11:22:37', '2025-11-04 11:22:37'),
(3, 'Finance', 'finance@company.com', '2025-11-04 11:22:37', '2025-11-04 11:22:37'),
(4, 'Passport Services', 'passport@company.com', '2025-11-04 11:22:37', '2025-11-04 11:22:37'),
(5, 'mezgeb', 'me@gmail.com', '2025-11-04 11:55:52', '2025-11-04 11:55:52');

-- --------------------------------------------------------

--
-- Table structure for table `letters`
--

CREATE TABLE `letters` (
  `id` int(11) NOT NULL,
  `type` enum('incoming','outgoing') NOT NULL,
  `ref_no` varchar(50) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `sender` varchar(255) NOT NULL,
  `receiver` varchar(255) NOT NULL,
  `date_received_sent` date NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','new','seen') DEFAULT 'pending',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `department` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `letters`
--

INSERT INTO `letters` (`id`, `type`, `ref_no`, `subject`, `sender`, `receiver`, `date_received_sent`, `description`, `file_path`, `status`, `created_by`, `created_at`, `department`) VALUES
(9, 'incoming', '34554', 'for information request', 'ethio-telecom', 'ICS HR', '2025-11-03', '', 'uploads/1762235384_1762177342_sample-letter.pdf', 'seen', NULL, '2025-11-04 05:49:44', 'HR'),
(10, 'incoming', '34554', 'for information request', 'ethio-telecom', 'ICS HR', '2025-11-03', '', 'uploads/1762236949_sample-letter.pdf', 'seen', NULL, '2025-11-04 06:15:49', 'HR'),
(11, 'incoming', '34554', 'for information request', 'ethio-telecom', 'ICS HR', '2025-11-03', '', 'uploads/1762238081_What is Lorem Ipsum (1).pdf', 'seen', NULL, '2025-11-04 06:34:41', 'HR');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `position` enum('admin','chief officer','officer','director','chief director') DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `password`, `position`, `department`, `created_at`) VALUES
(6, 'dagim', 'dagim1', '$2y$10$SYcPz0Wy0KxwcQA8lGs1K.U9zKIxSydlsb2r1ettk6o0n6/MZDcj2', 'director', 'HR', '2025-11-04 09:24:57'),
(8, 'dani', 'dani', '$2y$10$BaTTHCAeg2Hfa27bkT44guUjTt6wtzUVVYZshLQuYGuiS/EWOaQxe', 'admin', 'Operations', '2025-11-04 10:11:31'),
(9, 'test', 'test2', '$2y$10$9mT.aDQBh7lhyOTOZ5y96uo5cAO56C4uColkgr7EqdnTZn5COC216', 'admin', 'Operations', '2025-11-04 10:18:33'),
(10, 'gudata', 'gudeta', '$2y$10$3Cu7rNnjUCjhZGAj14biIOhG4OojOlQw4F42VBaHgGAD0lXHU9OnG', 'director', 'Finance', '2025-11-04 11:52:36');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `archives`
--
ALTER TABLE `archives`
  ADD PRIMARY KEY (`id`),
  ADD KEY `letter_id` (`letter_id`),
  ADD KEY `archived_by` (`archived_by`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `letters`
--
ALTER TABLE `letters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `archives`
--
ALTER TABLE `archives`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `letters`
--
ALTER TABLE `letters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `archives`
--
ALTER TABLE `archives`
  ADD CONSTRAINT `archives_ibfk_1` FOREIGN KEY (`letter_id`) REFERENCES `letters` (`id`),
  ADD CONSTRAINT `archives_ibfk_2` FOREIGN KEY (`archived_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `letters`
--
ALTER TABLE `letters`
  ADD CONSTRAINT `letters_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
