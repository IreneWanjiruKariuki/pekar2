-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 15, 2026 at 12:47 PM
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
-- Database: `pekar2`
--
CREATE DATABASE IF NOT EXISTS `pekar2` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `pekar2`;

-- --------------------------------------------------------

--
-- Table structure for table `card`
--

DROP TABLE IF EXISTS `card`;
CREATE TABLE `card` (
  `jobNo` int(11) NOT NULL,
  `date` date NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `technician_name` varchar(255) NOT NULL,
  `date_started` date NOT NULL,
  `date_finished` date DEFAULT NULL,
  `lpo_no` varchar(40) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `card`
--

INSERT INTO `card` (`jobNo`, `date`, `customer_name`, `technician_name`, `date_started`, `date_finished`, `lpo_no`) VALUES
(5, '2025-03-13', 'k University', 'John Kamau', '2025-03-21', '2025-03-13', '140621'),
(41, '2026-01-08', 'Strathmore University', 'John Kamau', '2026-01-10', '2026-01-23', '142642'),
(42, '2026-01-05', 'Kenyatta University', 'John Kamau', '2026-01-01', '2026-01-03', '140621'),
(43, '2026-01-09', 'Strathmore University', 'Julius Midigo', '2026-01-14', '2026-01-17', '142642');

-- --------------------------------------------------------

--
-- Table structure for table `card_item`
--

DROP TABLE IF EXISTS `card_item`;
CREATE TABLE `card_item` (
  `jobNo` int(11) NOT NULL,
  `machine_serial_number` varchar(100) NOT NULL,
  `job_description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `card_item`
--

INSERT INTO `card_item` (`jobNo`, `machine_serial_number`, `job_description`) VALUES
(5, '89', 'asdfgh'),
(5, '87', 'sdfghjkl'),
(41, '-', 'pppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppp'),
(41, '-', 'ppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppp'),
(42, '-', 'A major focus of the slides is Bias. In our previous chat about \\\"Data Cleaning,\\\" we learned that \\\"garbage in = garbage out.\\\" If the data is biased, the agent becomes biased. The slides identify five specific types:'),
(42, '-', 'A major focus of the slides is Bias. In our previous chat about \\\"Data Cleaning,\\\" we learned that \\\"garbage in = garbage out.\\\" If the data is biased, the agent becomes biased. The slides identify five specific types:'),
(43, '-', 'huhuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuu');

-- --------------------------------------------------------

--
-- Table structure for table `card_spare`
--

DROP TABLE IF EXISTS `card_spare`;
CREATE TABLE `card_spare` (
  `jobNo` int(11) NOT NULL,
  `spare_part` varchar(255) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `unit_cost` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) GENERATED ALWAYS AS (`quantity` * `unit_cost`) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `card_spare`
--

INSERT INTO `card_spare` (`jobNo`, `spare_part`, `quantity`, `unit_cost`) VALUES
(5, 'sdfghj', 9, 5.00),
(5, 'sdfghj', 8, 5.00),
(5, 'sdfghj', 2, 5.00),
(41, '-', 90, 5.00),
(41, '-', 9, 5.00),
(42, '-', 90, 5.00),
(42, '-', 8, 51.00),
(43, '-', 8, 51.00),
(43, '-', 8, 5.00);

-- --------------------------------------------------------

--
-- Table structure for table `invoice`
--

DROP TABLE IF EXISTS `invoice`;
CREATE TABLE `invoice` (
  `invoice_no` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `lpo_no` varchar(100) DEFAULT NULL,
  `contact` varchar(100) DEFAULT NULL,
  `delivery_no` varchar(100) DEFAULT NULL,
  `tel` varchar(50) DEFAULT NULL,
  `date` date NOT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `vat` decimal(10,2) DEFAULT NULL,
  `grand_total` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoice`
--

INSERT INTO `invoice` (`invoice_no`, `name`, `address`, `lpo_no`, `contact`, `delivery_no`, `tel`, `date`, `total`, `vat`, `grand_total`) VALUES
(226, 'Kenyatta University', 'P.O Box 43844-00100', '140509', '-', '60', '(020) 8703248', '2026-01-09', 872.00, 139.52, 1011.52),
(227, 'Kenyatta University', 'P.O Box 43844-00100', '140324', '-', '60', '(020) 8703248', '2026-01-16', 872.00, 139.52, 1011.52);

-- --------------------------------------------------------

--
-- Table structure for table `invoice_item`
--

DROP TABLE IF EXISTS `invoice_item`;
CREATE TABLE `invoice_item` (
  `invoice_no` int(11) NOT NULL,
  `item_code` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_cost` decimal(10,2) GENERATED ALWAYS AS (`quantity` * `unit_price`) STORED,
  `vatable` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `note`
--

DROP TABLE IF EXISTS `note`;
CREATE TABLE `note` (
  `delivery_no` int(11) NOT NULL,
  `deliver_to` varchar(255) NOT NULL,
  `lpo_no` varchar(100) DEFAULT NULL,
  `dated` date NOT NULL,
  `delivery_date` date NOT NULL,
  `delivered_by` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `note`
--

INSERT INTO `note` (`delivery_no`, `deliver_to`, `lpo_no`, `dated`, `delivery_date`, `delivered_by`) VALUES
(22, 'Kenyatta University', '140621', '2026-01-06', '2026-01-15', 'Peter Mwangi'),
(23, 'Kenyatta University', '140509', '2026-01-10', '2026-01-17', 'Peter Mwangi');

-- --------------------------------------------------------

--
-- Table structure for table `note_item`
--

DROP TABLE IF EXISTS `note_item`;
CREATE TABLE `note_item` (
  `item_id` int(11) NOT NULL,
  `delivery_no` int(11) NOT NULL,
  `item` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `unit` varchar(50) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `note_item`
--

INSERT INTO `note_item` (`item_id`, `delivery_no`, `item`, `description`, `unit`, `quantity`) VALUES
(7, 22, '1', 'yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy', 'Piece', 90),
(8, 22, '2', 'oooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooo', 'Piece', 9),
(15, 23, '2', 'pppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppp', 'Piece', 9);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `card`
--
ALTER TABLE `card`
  ADD PRIMARY KEY (`jobNo`);

--
-- Indexes for table `card_item`
--
ALTER TABLE `card_item`
  ADD KEY `jobNo` (`jobNo`);

--
-- Indexes for table `card_spare`
--
ALTER TABLE `card_spare`
  ADD KEY `jobNo` (`jobNo`);

--
-- Indexes for table `invoice`
--
ALTER TABLE `invoice`
  ADD PRIMARY KEY (`invoice_no`);

--
-- Indexes for table `note`
--
ALTER TABLE `note`
  ADD PRIMARY KEY (`delivery_no`);

--
-- Indexes for table `note_item`
--
ALTER TABLE `note_item`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `delivery_no` (`delivery_no`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `note_item`
--
ALTER TABLE `note_item`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `card_item`
--
ALTER TABLE `card_item`
  ADD CONSTRAINT `card_item_ibfk_1` FOREIGN KEY (`jobNo`) REFERENCES `card` (`jobNo`) ON DELETE CASCADE;

--
-- Constraints for table `card_spare`
--
ALTER TABLE `card_spare`
  ADD CONSTRAINT `card_spare_ibfk_1` FOREIGN KEY (`jobNo`) REFERENCES `card` (`jobNo`) ON DELETE CASCADE;

--
-- Constraints for table `note_item`
--
ALTER TABLE `note_item`
  ADD CONSTRAINT `note_item_ibfk_1` FOREIGN KEY (`delivery_no`) REFERENCES `note` (`delivery_no`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
