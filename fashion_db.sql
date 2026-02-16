-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 08, 2026 at 02:33 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fashion_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id` int(11) NOT NULL,
  `transaksi_id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `harga` int(11) NOT NULL,
  `subtotal` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id`, `transaksi_id`, `produk_id`, `qty`, `harga`, `subtotal`) VALUES
(6, 15, 1, 10, 5000, 50000),
(7, 16, 6, 1, 85000, 85000),
(8, 18, 4, 10, 65000, 650000),
(9, 19, 2, 10, 3500, 35000),
(10, 21, 1, 2, 5000, 10000),
(11, 22, 1, 2, 5000, 10000),
(12, 23, 6, 2, 85000, 170000),
(17, 23, 5, 2, 3000, 6000),
(20, 46, 6, 2, 85000, 170000),
(21, 23, 8, 2, 7000, 14000),
(31, 456, 16, 1, 15000, 15000),
(32, 457, 18, 1, 23000, 23000),
(33, 458, 5, 1, 3000, 3000),
(34, 459, 18, 1, 23000, 23000),
(35, 460, 17, 2, 20000, 40000),
(36, 461, 6, 1, 85000, 85000),
(37, 461, 17, 1, 20000, 20000);

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id` int(11) NOT NULL,
  `kode_barang` varchar(20) NOT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `kategori_id` int(11) NOT NULL,
  `harga` int(11) NOT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id`, `kode_barang`, `nama_produk`, `kategori_id`, `harga`, `foto`) VALUES
(1, 'BRG001', 'Aqua Botol 600ml', 1, 5000, 'produk_20260127090049_171323e5.jpg'),
(2, 'BRG002', 'Indomie Goreng', 1, 3500, 'produk_20260127090037_d4642429.jpg'),
(4, 'BRG004', 'Beras Ramos 5kg', 3, 65000, 'produk_20260127090018_a927725d.jpg'),
(5, 'BRG005', 'Pulpen Standard', 4, 3000, 'produk_20260127090002_04db2c4a.jpg'),
(6, 'BRG006', 'Mouse Wireless', 5, 85000, 'produk_20260127085947_b5c5ec14.jpg'),
(7, 'BRG007', 'Masker Medis', 6, 25000, 'produk_20260127085934_d9dc646a.jpg'),
(8, 'BRG008', 'Sabun Lifebuoy', 6, 7000, 'produk_20260127085920_78cada8c.jpg'),
(16, '001', 'Baterai AA', 2, 15000, 'produk_20260126214536_783d4b1e.webp'),
(17, '002', 'Kabel HDMI', 3, 20000, 'produk_20260126214519_19eeb494.webp'),
(18, '003', 'Case Iphone 11', 6, 23000, 'produk_20260126212636_98f1980d.webp');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int(11) NOT NULL,
  `nomor_bukti` varchar(30) NOT NULL,
  `tanggal` date NOT NULL,
  `total_bayar` int(11) NOT NULL,
  `diskon` decimal(10,2) DEFAULT 0.00,
  `uang_bayar` decimal(10,2) DEFAULT 0.00,
  `kembalian` decimal(10,2) DEFAULT 0.00,
  `status_bayar` enum('BELUM LUNAS','LUNAS') DEFAULT 'BELUM LUNAS'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id`, `nomor_bukti`, `tanggal`, `total_bayar`, `diskon`, `uang_bayar`, `kembalian`, `status_bayar`) VALUES
(15, 'ADAVS', '2026-01-07', 50000, 0.00, 0.00, 0.00, 'LUNAS'),
(16, 'bbad', '2026-01-07', 85000, 0.00, 0.00, 0.00, 'BELUM LUNAS'),
(18, 'ffgh123', '2026-01-09', 650000, 0.00, 0.00, 0.00, 'LUNAS'),
(19, 'xaabcd123', '2026-01-10', 35000, 0.00, 0.00, 0.00, 'LUNAS'),
(21, 'TRX-20260111-001', '2026-01-11', 10000, 0.00, 0.00, 0.00, 'LUNAS'),
(22, 'TR001FS', '2026-01-25', 10000, 0.00, 0.00, 0.00, 'BELUM LUNAS'),
(23, 'TR0017SKS', '2026-01-25', 190000, 0.00, 0.00, 0.00, 'LUNAS'),
(46, 'TR0017SKS', '0000-00-00', 170000, 0.00, 0.00, 0.00, 'LUNAS'),
(456, 'TRX-20260127-067', '2026-01-27', 15000, 0.00, 0.00, 0.00, 'LUNAS'),
(457, 'TRX-20260127-029', '2026-01-27', 23000, 0.00, 0.00, 0.00, 'LUNAS'),
(458, 'TRX-20260127-052', '2026-01-27', 3000, 0.00, 0.00, 0.00, 'LUNAS'),
(459, 'TRX-20260127-203', '2026-01-27', 23000, 0.00, 0.00, 0.00, 'LUNAS'),
(460, 'TRX-20260128-392', '2026-01-28', 40000, 0.00, 50000.00, 10000.00, 'LUNAS'),
(461, 'TRX-20260129-670', '2026-01-29', 105000, 0.00, 200000.00, 95000.00, 'LUNAS');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `role` enum('admin') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `foto`, `role`, `created_at`) VALUES
(1, 'admin', '$2b$12$gfyL4TN13wNN60qkb/UZ3eN6J3zUPa27ZJfybBupi0VBpl0NuWiB2', 'user_1_1769497434.png', 'admin', '2026-01-26 03:08:10');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaksi_id` (`transaksi_id`),
  ADD KEY `produk_id` (`produk_id`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=462;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`transaksi_id`) REFERENCES `transaksi` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
