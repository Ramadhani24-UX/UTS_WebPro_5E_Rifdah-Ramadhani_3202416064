-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for db5e
CREATE DATABASE IF NOT EXISTS `db5e` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `db5e`;

-- Dumping structure for table db5e.nilaimhsw
CREATE TABLE IF NOT EXISTS `nilaimhsw` (
  `nim` char(10) NOT NULL,
  `namalengkap` varchar(50) NOT NULL,
  `nilaiakhir` decimal(5,2) NOT NULL,
  `nilaihuruf` char(1) DEFAULT NULL,
  `predikat` varchar(20) DEFAULT NULL,
  `created_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `photo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`nim`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db5e.nilaimhsw: ~10 rows (approximately)
INSERT INTO `nilaimhsw` (`nim`, `namalengkap`, `nilaiakhir`, `nilaihuruf`, `predikat`, `created_date`, `modified_date`, `photo`) VALUES
	('3201546765', 'Aryana Subadyo', 90.00, 'A', 'Sangat Memuaskan', '2026-10-13 04:37:57', '2026-10-26 13:21:07', 'foto_1793020154_815.jpg'),
	('3201567656', 'Asyana Naykilaa', 70.65, 'B', 'Memuaskan', '2026-10-13 08:23:49', '2026-10-26 13:21:41', 'foto_1793020172_175.jpg'),
	('3201675658', 'Dino Cahyadi', 25.56, 'E', 'Tidak Lulus', '2026-10-13 08:03:34', '2026-10-26 13:10:18', 'foto_1793020218_552.jpg'),
	('3202415678', 'Arkana Bernadya', 45.90, 'E', 'Tidak Lulus', '2026-10-15 06:50:15', '2026-10-26 13:10:00', 'foto_1793020200_811.jpg'),
	('3202416064', 'Rifdah Ramadhani', 100.00, 'A', 'Sangat Memuaskan', '2026-10-13 04:30:32', '2026-10-26 13:09:03', 'foto_1793020143_411.jpg'),
	('3202516756', 'Quolla Aryana', 61.70, 'C', 'Kurang Memuaskan', '2026-10-13 04:37:04', '2026-10-26 13:22:13', 'foto_1793020192_262.jpg'),
	('3202516787', 'Adinda Azzahra', 69.99, 'C', 'Kurang Memuaskan', '2026-10-13 07:44:27', '2026-10-26 13:22:53', 'foto_1793020209_299.jpg'),
	('3202516789', 'Ernada Tarino', 81.05, 'B', 'Memuaskan', '2026-10-26 13:13:38', '2026-10-26 13:21:17', 'foto_1793020432_441.jpg'),
	('3202564566', 'Serge Yunio', 98.05, 'A', 'Sangat Memuaskan', '2026-10-26 13:11:27', '2026-10-26 13:11:35', 'foto_1793020295_742.jpg'),
	('3202564567', 'Rama Aryana', 87.67, 'A', 'Sangat Memuaskan', '2026-10-13 08:25:18', '2026-10-26 13:09:23', 'foto_1793020163_154.jpg'),
	('3202565435', 'Salsabila Zuraida', 78.90, 'B', 'Memuaskan', '2026-10-13 07:45:10', '2026-10-26 13:09:42', 'foto_1793020182_865.jpg'),
	('3202567809', 'Zogwa Ansenqi', 43.90, 'E', 'Tidak Lulus', '2026-10-26 13:12:55', '2026-10-26 13:12:55', 'foto_1793020375_747.jpg'),
	('3202567898', 'Afwan Ganadi', 50.90, 'E', 'Tidak Lulus', '2026-10-26 13:19:29', '2026-10-26 13:21:53', 'foto_1793020769_977.jpg'),
	('3202568790', 'Fandy Adhan', 67.89, 'C', 'Kurang Memuaskan', '2026-10-26 13:12:01', '2026-10-26 13:12:12', 'foto_1793020332_474.jpg'),
	('3202789867', 'Randy Fahwan', 10.67, 'E', 'Tidak Lulus', '2026-10-26 13:13:18', '2026-10-26 13:13:18', 'foto_1793020398_541.jpg');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
