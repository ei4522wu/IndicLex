-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 24, 2026 at 01:27 AM
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
-- Database: `indiclex_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `words`
--

CREATE TABLE `words` (
  `id` int(7) NOT NULL COMMENT 'This is primary key',
  `dictionary_name` varchar(50) NOT NULL,
  `lang_1` varchar(100) NOT NULL,
  `lang_2` varchar(100) NOT NULL,
  `lang_3` varchar(100) DEFAULT NULL,
  `notes` varchar(100) DEFAULT NULL COMMENT 'This is for any notes/comments the dictionary creators can keep'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `words`
--

INSERT INTO `words` (`id`, `dictionary_name`, `lang_1`, `lang_2`, `lang_3`, `notes`) VALUES
(1, 'Telugu - Urdu', 'అంక\r\n', 'బాజూ   పిడిప్', NULL, NULL),
(2, 'Telugu - Urdu', 'హేల\r\n\r\n', 'జెలీల్', NULL, NULL),
(3, 'Telugu - Urdu', 'హేల\r\n\r\n', 'జెలీల్', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `words`
--
ALTER TABLE `words`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `words`
--
ALTER TABLE `words`
  MODIFY `id` int(7) NOT NULL AUTO_INCREMENT COMMENT 'This is primary key', AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
