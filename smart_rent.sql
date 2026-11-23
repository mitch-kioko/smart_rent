-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 23, 2026 at 02:49 PM
-- Server version: 10.11.16-MariaDB
-- PHP Version: 8.3.36

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `smart_rent`
--

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `payment_id` int(10) UNSIGNED NOT NULL,
  `tenancy_id` int(10) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` enum('cash','mpesa','bank') NOT NULL,
  `reference` varchar(50) DEFAULT NULL,
  `receipt_number` varchar(20) NOT NULL,
  `recorded_by` int(10) UNSIGNED NOT NULL,
  `created_on` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`payment_id`, `tenancy_id`, `amount`, `payment_date`, `payment_method`, `reference`, `receipt_number`, `recorded_by`, `created_on`) VALUES
(1, 1, 16000.00, '2026-09-15', 'mpesa', 'UJKHO2HKDA', 'RCPT-001', 5, '2026-09-16 12:05:48'),
(2, 1, 16000.00, '2026-10-13', 'mpesa', 'URGKA1CKBM', 'RCPT-002', 5, '2026-10-15 12:05:48'),
(3, 1, 16000.00, '2026-11-14', 'cash', NULL, 'RCPT-003', 5, '2026-11-17 12:05:48'),
(4, 2, 5000.00, '2026-10-14', 'bank', 'CRBK12HLDO', 'RCPT-004', 5, '2026-10-22 12:05:48'),
(5, 2, 5000.00, '2026-10-23', 'cash', NULL, 'RCPT-005', 5, '2026-10-23 13:10:00'),
(6, 3, 8000.00, '2026-11-07', 'mpesa', 'UBGCFA3RCBF', 'RCPT-006', 5, '2026-11-08 12:16:30'),
(7, 3, 8000.00, '2026-11-23', 'mpesa', 'TEST12345', 'RCT-007', 1, '2026-11-23 10:14:44'),
(15, 2, 20000.00, '2026-11-23', 'mpesa', 'TEST123456', 'RCT-0008', 5, '2026-11-23 10:48:09'),
(16, 1, 8000.00, '2026-11-23', 'mpesa', 'TEST1234', 'RCT-0009', 5, '2026-11-23 10:48:47'),
(17, 7, 20000.00, '2026-11-23', 'mpesa', 'TEST67', 'RCT-0010', 5, '2026-11-23 11:09:57'),
(18, 8, 10000.00, '2026-11-23', 'mpesa', 'TEST123', 'RCT-0011', 1, '2026-11-23 12:29:01'),
(19, 8, 5000.00, '2026-11-23', 'cash', NULL, 'RCT-0012', 1, '2026-11-23 13:22:01');

-- --------------------------------------------------------

--
-- Table structure for table `tenancies`
--

CREATE TABLE `tenancies` (
  `tenancy_id` int(11) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `unit_id` int(10) UNSIGNED NOT NULL,
  `rent_per_month` decimal(10,2) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('Ongoing','Ended') NOT NULL DEFAULT 'Ongoing'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tenancies`
--

INSERT INTO `tenancies` (`tenancy_id`, `tenant_id`, `unit_id`, `rent_per_month`, `start_date`, `end_date`, `status`) VALUES
(1, 2, 3, 16000.00, '2026-08-15', NULL, 'Ongoing'),
(2, 3, 1, 10000.00, '2026-09-23', NULL, 'Ongoing'),
(3, 4, 2, 8000.00, '2026-10-06', NULL, 'Ongoing'),
(7, 6, 4, 20000.00, '2026-11-23', '2026-11-23', 'Ended'),
(8, 6, 4, 20000.00, '2026-11-23', NULL, 'Ongoing');

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `unit_id` int(11) UNSIGNED NOT NULL,
  `unit_number` varchar(20) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `rent_per_month` decimal(10,2) NOT NULL,
  `status` enum('Vacant','Occupied') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`unit_id`, `unit_number`, `description`, `rent_per_month`, `status`) VALUES
(1, 'A1', '1 bedroom, ground floor', 10000.00, 'Occupied'),
(2, 'B1', 'Bedsitter, first floor', 8000.00, 'Occupied'),
(3, 'C1', '2 bedrooms, 3rd floor ', 16000.00, 'Occupied'),
(4, 'D2', '3 bedroom, second floor', 20000.00, 'Occupied');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `first_name` varchar(30) NOT NULL,
  `last_name` varchar(30) NOT NULL,
  `phone_number` varchar(15) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Landlord','Caretaker','Tenant') NOT NULL DEFAULT 'Tenant',
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `phone_number`, `email`, `password`, `role`, `status`) VALUES
(1, 'Max', 'Verstappen', '0733669922', 'maxverstappen@gmail.com', '$2y$10$7PNAnkA3Om.jUedUolyCKOLbkFAae8AowA/HCLCZZUjb9P1V0bxpW', 'Landlord', 'Active'),
(2, 'Charles', 'Leclerc', '0716324864', 'charlesleclerc@gmail.com', '$2y$10$m/ju2h66.W4nMh1doHynMea0H7OGQpNvhXr1f5KoNVDM3Uy/aSwku', 'Tenant', 'Inactive'),
(3, 'Carlos', 'Sainz', '0711223344', 'carlos55@gmail.com', '$2y$10$gFQJyZoUoOWBigtXLXVM..Ctdm1jbrZmfxLMlXGr2gidlBG7.SqSy', 'Tenant', 'Active'),
(4, 'Lando', 'Norris', '0748121632', 'lando@gmail.com', '$2y$10$chKwLQiS4OOglmjEmdB8HuLG7iCXcfJI0lGBAto8/LpPTa5G/uNjC', 'Tenant', 'Active'),
(5, 'Nico', 'Rosberg', '0761224488', 'nicorosberg@gmail.com', '$2y$10$yRzuQ6ryEilLBhkHUcSo0eACbtYDNAFHJA1SuYijT4SyoTOlrqXvO', 'Caretaker', 'Active'),
(6, 'Oscar', 'Piastri', '0764251339', 'oscar@gmail.com', '$2y$10$7QYYkZ9R.YMD.cgehDoVjeFs6fX6KBp4SZJWc72Z8PBWJm9FcyJ9a', 'Tenant', 'Active'),
(14, 'Sebastian', 'Vettel', '0751020444', 'sebvettel@gmail.com', '$2y$10$YNaXJt62xwX8M4vEopGhMe0R5.1oQGkNK.AXNDFahOVFV.v9PbPy2', 'Tenant', 'Active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD UNIQUE KEY `receipt_number` (`receipt_number`),
  ADD KEY `tenancy_id` (`tenancy_id`),
  ADD KEY `recorded_by` (`recorded_by`);

--
-- Indexes for table `tenancies`
--
ALTER TABLE `tenancies`
  ADD PRIMARY KEY (`tenancy_id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `unit_id` (`unit_id`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`unit_id`),
  ADD UNIQUE KEY `unit_number` (`unit_number`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`(20));

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `tenancies`
--
ALTER TABLE `tenancies`
  MODIFY `tenancy_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `unit_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`tenancy_id`) REFERENCES `tenancies` (`tenancy_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `payment_ibfk_2` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `tenancies`
--
ALTER TABLE `tenancies`
  ADD CONSTRAINT `tenancies_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `tenancies_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `units` (`unit_id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
