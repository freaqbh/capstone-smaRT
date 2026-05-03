-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 28, 2026 at 01:53 AM
-- Server version: 8.0.45-0ubuntu0.24.04.1
-- PHP Version: 8.4.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `smart_backend`
--

-- --------------------------------------------------------

--
-- Table structure for table `blockchain`
--

CREATE TABLE `blockchain` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bendahara_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis_kas` enum('PEMASUKAN','PENGELUARAN') COLLATE utf8mb4_unicode_ci NOT NULL,
  `nominal` bigint NOT NULL,
  `keterangan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `previous_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `current_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blockchain`
--

INSERT INTO `blockchain` (`id`, `bendahara_id`, `jenis_kas`, `nominal`, `keterangan`, `created_at`, `previous_hash`, `current_hash`) VALUES
('019dd18e-1dea-7291-9a03-14a016e8f6b5', '019dd189-51b7-70b9-8755-4023fd40c05e', 'PEMASUKAN', 50000, 'Iuran Kas April - Pak Budi', '2026-04-28 00:47:25', '0000000000000000000000000000000000000000000000000000000000000000', 'fcc539cca9e54d6bd24feb5f815746e93b1c5d05c0cbb76a0de46daac7a3509d'),
('019dd18e-1e10-7106-8387-add7d8e4ed03', '019dd189-51b7-70b9-8755-4023fd40c05e', 'PENGELUARAN', 25000, 'Beli air galon untuk balai RT', '2026-04-28 00:47:25', 'fcc539cca9e54d6bd24feb5f815746e93b1c5d05c0cbb76a0de46daac7a3509d', 'ac788f26f5ec986d985166228f6e5b468048165a2830dff4676d9800ed24d1a2'),
('019dd18e-ad44-7298-94d2-4117996bb18e', '019dd189-51b7-70b9-8755-4023fd40c05e', 'PEMASUKAN', 50000, 'Iuran Kas April - Pak Budi', '2026-04-28 00:48:01', 'fcc539cca9e54d6bd24feb5f815746e93b1c5d05c0cbb76a0de46daac7a3509d', 'd565a6ad66821c049444484264e0d9018bd65c5b6ec00acfbdcd593e2c61db0d'),
('019dd18e-ad69-7102-af40-297d44c96c56', '019dd189-51b7-70b9-8755-4023fd40c05e', 'PENGELUARAN', 25000, 'Beli air galon untuk balai RT', '2026-04-28 00:48:01', 'd565a6ad66821c049444484264e0d9018bd65c5b6ec00acfbdcd593e2c61db0d', 'f7fde66dce2fc8814fda568cb30566fb739375deb95bb6695d46446ced8a9cb5');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `blockchain`
--
ALTER TABLE `blockchain`
  ADD PRIMARY KEY (`id`),
  ADD KEY `blockchain_bendahara_id_foreign` (`bendahara_id`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `blockchain`
--
ALTER TABLE `blockchain`
  ADD CONSTRAINT `blockchain_bendahara_id_foreign` FOREIGN KEY (`bendahara_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
