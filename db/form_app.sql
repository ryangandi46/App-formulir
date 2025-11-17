-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 17 Nov 2025 pada 09.44
-- Versi server: 8.0.30
-- Versi PHP: 8.3.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `form_app`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `forms`
--

CREATE TABLE `forms` (
  `id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `allow_attachments` tinyint(1) NOT NULL DEFAULT '0',
  `public_key` varchar(64) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `forms`
--

INSERT INTO `forms` (`id`, `title`, `description`, `created_at`, `allow_attachments`, `public_key`) VALUES
(10, 'data siswa', '', '2025-11-16 16:07:09', 1, '7ef9cf8eeaf4053d12953c23ec47c3a5'),
(11, 'data penduduk', '', '2025-11-16 16:59:06', 0, '6fdbdcaaae33e48eeeee191c8a66704b'),
(14, 'data penduduk', '', '2025-11-17 08:31:42', 1, '2162ead96846c0d7a66da67a045c2e09');

-- --------------------------------------------------------

--
-- Struktur dari tabel `questions`
--

CREATE TABLE `questions` (
  `id` int NOT NULL,
  `form_id` int DEFAULT NULL,
  `question` text COLLATE utf8mb4_general_ci,
  `type` enum('text','textarea','number','date','radio','checkbox','select','file') COLLATE utf8mb4_general_ci NOT NULL,
  `options` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `questions`
--

INSERT INTO `questions` (`id`, `form_id`, `question`, `type`, `options`) VALUES
(14, 10, 'siapa nama kamu', 'text', ''),
(15, 10, 'apakah kamu sehat', 'radio', 'iya\r\ntidak'),
(16, 10, 'alamat', 'textarea', ''),
(17, 10, 'gaji ortu', 'number', ''),
(18, 10, 'tanggal lahir', 'date', ''),
(19, 10, 'jenis kelamin', 'radio', 'laki\r\nperempuan'),
(20, 10, 'tinggal di', 'checkbox', 'bandung\r\njakarta'),
(21, 10, 'guru fav', 'select', 'agus\r\nasep'),
(22, 11, 'nama', 'text', ''),
(23, 11, 'NIK', 'number', ''),
(30, 14, 'ktp', 'file', ''),
(31, 14, 'kk', 'file', ''),
(32, 14, 'sim', 'file', '');

-- --------------------------------------------------------

--
-- Struktur dari tabel `responses`
--

CREATE TABLE `responses` (
  `id` int NOT NULL,
  `form_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `responses`
--

INSERT INTO `responses` (`id`, `form_id`, `created_at`) VALUES
(1, 9, '2025-11-16 15:06:31'),
(2, 7, '2025-11-16 15:27:19'),
(3, 10, '2025-11-16 16:07:47'),
(4, 10, '2025-11-16 16:09:59'),
(5, 10, '2025-11-16 16:32:08'),
(6, 10, '2025-11-16 16:35:34'),
(7, 10, '2025-11-16 16:39:39'),
(8, 10, '2025-11-16 16:48:40'),
(9, 11, '2025-11-16 16:59:26'),
(10, 14, '2025-11-17 08:42:00'),
(11, 14, '2025-11-17 09:21:47');

-- --------------------------------------------------------

--
-- Struktur dari tabel `response_answers`
--

CREATE TABLE `response_answers` (
  `id` int NOT NULL,
  `response_id` int DEFAULT NULL,
  `question_id` int DEFAULT NULL,
  `answer` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `response_answers`
--

INSERT INTO `response_answers` (`id`, `response_id`, `question_id`, `answer`) VALUES
(1, 1, 12, '200000'),
(2, 1, 13, '2025-11-16'),
(3, 2, 7, 'agus'),
(4, 2, 8, 'Laki-laki'),
(5, 2, 9, 'bandung'),
(6, 2, 10, 'cemara'),
(7, 2, 11, 'angkot'),
(8, 3, 14, 'agus'),
(9, 3, 15, 'iya'),
(10, 3, 16, 'bandung'),
(11, 3, 17, '4000000'),
(12, 3, 18, '2006-09-02'),
(13, 3, 19, 'laki'),
(14, 3, 20, 'bandung'),
(15, 3, 21, 'asep'),
(16, 4, 14, 'agus'),
(17, 4, 15, 'iya'),
(18, 4, 16, 'bandung'),
(19, 4, 17, '4000000'),
(20, 4, 18, '2006-09-02'),
(21, 4, 19, 'laki'),
(22, 4, 20, 'bandung'),
(23, 4, 21, 'asep'),
(24, 5, 14, 'asep'),
(25, 5, 15, 'iya'),
(26, 5, 16, 'bandung'),
(27, 5, 17, '390000'),
(28, 5, 18, '2018-07-04'),
(29, 5, 19, 'laki'),
(30, 5, 20, 'jakarta'),
(31, 5, 21, 'asep'),
(32, 6, 14, 'adam'),
(33, 6, 15, 'iya'),
(34, 6, 16, 'bandung'),
(35, 6, 17, '4999998'),
(36, 6, 18, '1979-03-25'),
(37, 6, 19, 'laki'),
(38, 6, 20, 'jakarta'),
(39, 6, 21, 'agus'),
(40, 7, 14, 'rofi'),
(41, 7, 15, 'tidak'),
(42, 7, 16, 'Ipsum quis neque ad'),
(43, 7, 17, '34'),
(44, 7, 18, '1990-01-21'),
(45, 7, 19, 'perempuan'),
(46, 7, 20, 'jakarta'),
(47, 7, 21, 'agus'),
(48, 8, 14, 'dadang'),
(49, 8, 15, 'iya'),
(50, 8, 16, 'Quas totam necessita'),
(51, 8, 17, '7699999'),
(52, 8, 18, '1989-01-08'),
(53, 8, 19, 'perempuan'),
(54, 8, 20, 'bandung, jakarta'),
(55, 8, 21, 'agus'),
(56, 9, 22, 'agus'),
(57, 9, 23, '1234566789'),
(58, 10, 30, 'icon banjir.png'),
(59, 10, 31, 'icon cuaca.jpeg'),
(60, 10, 32, 'icon cuaca2.jpeg'),
(61, 11, 30, 'icon banjir.png'),
(62, 11, 31, 'icon cuaca.jpeg'),
(63, 11, 32, 'icon cuaca2.jpeg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `response_files`
--

CREATE TABLE `response_files` (
  `id` int NOT NULL,
  `response_id` int DEFAULT NULL,
  `question_id` int DEFAULT NULL,
  `filename` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mime_type` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `size` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `response_files`
--

INSERT INTO `response_files` (`id`, `response_id`, `question_id`, `filename`, `original_name`, `mime_type`, `size`, `created_at`) VALUES
(1, 1, NULL, 'file_6919e877e94bc3.18706390_icon_banjir.png', 'icon banjir.png', 'image/png', 9309, '2025-11-16 15:06:31'),
(2, 2, NULL, 'file_6919ed5873b6c9.53115139_icon_cuaca.jpeg', 'icon cuaca.jpeg', 'image/jpeg', 22759, '2025-11-16 15:27:20'),
(3, 3, NULL, 'file_6919f6d532f9c6.98926852_icon_banjir.png', 'icon banjir.png', 'image/png', 9309, '2025-11-16 16:07:49'),
(4, 4, NULL, 'file_6919f75991fde1.90004042_icon_banjir.png', 'icon banjir.png', 'image/png', 9309, '2025-11-16 16:10:01'),
(5, 5, NULL, 'file_6919fc8869cad0.97811619.jpeg', 'icon cuaca2.jpeg', NULL, 20406, '2025-11-16 16:32:08'),
(6, 8, NULL, 'file_691a0068e65d60.56926863.docx', 'Permohonan-Konversi-MSIB.docx', NULL, 22176, '2025-11-16 16:48:40'),
(7, 10, NULL, 'file_691adfd8d0a093.06528885.png', 'icon banjir.png', NULL, 9309, '2025-11-17 08:42:00'),
(8, 10, NULL, 'file_691adfd8d0f344.38984807.jpeg', 'icon cuaca.jpeg', NULL, 22759, '2025-11-17 08:42:00'),
(9, 10, NULL, 'file_691adfd8d14be5.41705022.jpeg', 'icon cuaca2.jpeg', NULL, 20406, '2025-11-17 08:42:00'),
(10, 11, 30, 'file_691ae92ba995d3.78645238.png', 'icon banjir.png', NULL, 9309, '2025-11-17 09:21:47'),
(11, 11, 31, 'file_691ae92ba9f102.09015367.jpeg', 'icon cuaca.jpeg', NULL, 22759, '2025-11-17 09:21:47'),
(12, 11, 32, 'file_691ae92baa5693.50594834.jpeg', 'icon cuaca2.jpeg', NULL, 20406, '2025-11-17 09:21:47');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$12$CfmE5A9gElNtc6dW4BKuYuzofWqM0RXo8Yk/IsiZsuU4cJDIRTWX2'),
(2, 'ryan', '$2y$10$JeLHllOqJdm33jgY32h/.u.gGb.NK446Ltx1H/HmFeYSbQgSmeMdu');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `forms`
--
ALTER TABLE `forms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `public_key` (`public_key`);

--
-- Indeks untuk tabel `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `form_id` (`form_id`);

--
-- Indeks untuk tabel `responses`
--
ALTER TABLE `responses`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `response_answers`
--
ALTER TABLE `response_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `response_id` (`response_id`);

--
-- Indeks untuk tabel `response_files`
--
ALTER TABLE `response_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `response_files_response_id_index` (`response_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `forms`
--
ALTER TABLE `forms`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT untuk tabel `responses`
--
ALTER TABLE `responses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `response_answers`
--
ALTER TABLE `response_answers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT untuk tabel `response_files`
--
ALTER TABLE `response_files`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`form_id`) REFERENCES `forms` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `response_answers`
--
ALTER TABLE `response_answers`
  ADD CONSTRAINT `response_answers_ibfk_1` FOREIGN KEY (`response_id`) REFERENCES `responses` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `response_files`
--
ALTER TABLE `response_files`
  ADD CONSTRAINT `response_files_ibfk_1` FOREIGN KEY (`response_id`) REFERENCES `responses` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
