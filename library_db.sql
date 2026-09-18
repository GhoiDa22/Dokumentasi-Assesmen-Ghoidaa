-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 26, 2025 at 06:31 PM
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
-- Database: `library_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `email`, `password`) VALUES
(1, 'admin@example.com', 'admin123'),
(2, 'ida@gmail.com', 'ida123');

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` int NOT NULL,
  `title` varchar(100) NOT NULL,
  `author` varchar(100) NOT NULL,
  `year` int NOT NULL,
  `isbn` varchar(13) NOT NULL,
  `stock` int NOT NULL,
  `status` enum('available','unavailable') DEFAULT 'available',
  `cover` varchar(255) DEFAULT NULL,
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `title`, `author`, `year`, `isbn`, `stock`, `status`, `cover`, `description`) VALUES
(1, 'Buku Ilmiah', 'ghoida', 2025, '0987654321', 5, 'available', 'assets/images/books/6881b95891359.png', 'Buku IPS'),
(3, 'Buku IPS', 'Sabdo', 2025, '0987652344', 4, 'available', 'assets/images/books/6881da71c20d7.png', 'Buku MTK'),
(4, 'Buku Olahraga', 'RI', 2025, '982193826378', 6, 'available', 'assets/images/books/6881dd9f7396a.png', 'Buku Olahraga'),
(5, 'Habis Gelap Terbitlah Terang', 'Ra. Kartini', 2025, '0987654542', 0, 'available', 'assets/images/books/6881f65799845.png', 'Buku R. Kartini'),
(8, 'buku sejarah 2025', 'Andrea Hirata', 2025, '11928428738', 2, 'available', 'assets/images/books/68827c2128e30.png', 'buku sejarah'),
(9, 'Informatika', 'agus seru', 2025, '97283773', 3, 'available', 'assets/images/books/6882f811254c8.png', 'Informatika'),
(10, 'Informatika 2', 'ida', 2012, '0283763', 5, 'available', 'assets/images/books/6883027248422.png', 'Informatika 2');

-- --------------------------------------------------------

--
-- Table structure for table `fines`
--

CREATE TABLE `fines` (
  `id` int NOT NULL,
  `loan_id` int NOT NULL,
  `member_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','paid') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loans`
--

CREATE TABLE `loans` (
  `id` int NOT NULL,
  `member_id` int NOT NULL,
  `book_id` int NOT NULL,
  `loan_date` datetime NOT NULL,
  `due_date` datetime NOT NULL,
  `return_date` datetime DEFAULT NULL,
  `status` enum('active','overdue','returned') DEFAULT 'active',
  `fine` decimal(10,2) DEFAULT '0.00',
  `extension_count` int NOT NULL DEFAULT '0',
  `pickup_status` enum('pending','instructed','taken') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `loans`
--

INSERT INTO `loans` (`id`, `member_id`, `book_id`, `loan_date`, `due_date`, `return_date`, `status`, `fine`, `extension_count`, `pickup_status`) VALUES
(3, 5, 4, '2025-07-24 10:01:00', '2025-07-31 10:01:00', '2025-07-25 00:08:25', 'returned', '0.00', 0, 'pending'),
(4, 5, 4, '2025-07-24 10:01:24', '2025-08-07 10:01:24', NULL, 'active', '0.00', 1, 'pending'),
(7, 5, 3, '2025-07-24 12:35:35', '2025-07-31 12:35:35', '2025-07-25 10:24:53', 'returned', '0.00', 0, 'pending'),
(9, 5, 5, '2025-07-24 17:26:12', '2025-07-31 17:26:12', NULL, 'active', '0.00', 0, 'pending'),
(10, 16, 5, '2025-07-24 17:52:29', '2025-07-31 17:52:29', NULL, 'active', '0.00', 0, 'pending'),
(11, 16, 5, '2025-07-24 18:08:41', '2025-07-31 18:08:41', NULL, 'active', '0.00', 0, 'pending'),
(12, 17, 3, '2025-07-24 18:27:58', '2025-07-31 18:27:58', '2025-07-25 08:55:34', 'returned', '0.00', 0, 'pending'),
(13, 17, 8, '2025-07-24 18:32:47', '2025-08-07 18:32:47', '2025-07-25 01:34:13', 'returned', '0.00', 1, 'pending'),
(16, 18, 9, '2025-07-25 03:23:42', '2025-08-01 03:23:42', '2025-07-25 11:07:49', 'returned', '0.00', 0, 'pending'),
(17, 18, 4, '2025-07-25 04:10:29', '2025-08-08 04:10:29', NULL, 'active', '0.00', 1, 'taken'),
(20, 20, 9, '2025-07-26 15:35:53', '2025-08-02 15:35:53', NULL, 'active', '0.00', 0, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `nis` varchar(20) NOT NULL,
  `jurusan` varchar(50) DEFAULT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `kelas` varchar(50) DEFAULT NULL,
  `password` varchar(100) NOT NULL,
  `card_expiry_date` date NOT NULL,
  `photo` varchar(255) DEFAULT 'assets/images/default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `name`, `email`, `nis`, `jurusan`, `no_hp`, `kelas`, `password`, `card_expiry_date`, `photo`) VALUES
(5, 'bagas', '23.bagas.ananda@poltekindonusa.ac.id', 'NIS008', NULL, NULL, NULL, '$2y$10$DgD4Ge/b4jWMa2siwWBeMuoLbdGZL5JEfUUq17zFj9S1cc.L2dM9a', '2026-07-24', 'assets/images/members/6881ffffd2dc9_Pria Muda dan Nissan GT-R di Tokyo.png'),
(13, 'igna', 'igna@gmail.com', 'NIS002123', 'Teknik Jaringan dan Komputer', '08278164726', '10', 'igna123', '2026-07-24', 'assets/images/default.png'),
(14, 'yulia', 'yulia@gmail.com', 'NIS230032', 'Teknik Jaringan dan Komputer', '08926374812', '12', 'yulia123', '2026-07-24', 'assets/images/default.png'),
(15, 'ahsan', 'ahsan@gmail.com', 'NIS0088928', 'Teknik Jaringan dan Komputer', '09827363782', '12', 'ahsan123', '2026-07-24', 'assets/images/default.png'),
(16, 'bagasan', 'bagasan@gmail.com', 'NIS1111234', 'Teknik Jaringan dan Komputer', '09284736182', '11', 'bagasan123', '2026-07-24', 'assets/images/members/688272b9d56af_bot-icon.png'),
(17, 'ihdah', 'ihdah@gmail.com', 'NIS078564', 'Teknik Jaringan dan Komputer', '081578384630', '11', 'ihdah123', '2026-07-24', 'assets/images/members/68827b4d764c4_smkn1-logo.png'),
(18, 'ida', 'daidaa22@gmail.com', 'NIS24432', 'Perkantoran', '0852726267377', '12', 'ida123', '2026-07-25', 'assets/images/members/6882f99b5448e_smkn1-logo.png'),
(19, 'dipaip', 'dipaip@gmail.com', 'dipaip', 'Akuntansi', '082974638287', '11', 'dipaip', '2026-07-26', 'assets/images/default.png'),
(20, 'lembahana', '23.rizkia.ahsan@poltekindonusa.ac.id', 'NIS012397', 'Desain Komunikasi Visual', '09823756718', '11', 'lembahana', '2026-07-26', 'assets/images/members/68851af42916d_ahsan.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `isbn` (`isbn`);

--
-- Indexes for table `fines`
--
ALTER TABLE `fines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `loan_id` (`loan_id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `loans`
--
ALTER TABLE `loans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `book_id` (`book_id`),
  ADD KEY `fk_loans_members` (`member_id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `nis` (`nis`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `fines`
--
ALTER TABLE `fines`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loans`
--
ALTER TABLE `loans`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `fines`
--
ALTER TABLE `fines`
  ADD CONSTRAINT `fines_ibfk_1` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`),
  ADD CONSTRAINT `fines_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`);

--
-- Constraints for table `loans`
--
ALTER TABLE `loans`
  ADD CONSTRAINT `fk_loans_members` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `loans_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
