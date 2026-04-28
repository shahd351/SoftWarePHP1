-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: 15 أبريل 2026 الساعة 18:20
-- إصدار الخادم: 5.7.24
-- PHP Version: 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `thamean`
--

-- --------------------------------------------------------

--
-- بنية الجدول `admin`
--

CREATE TABLE `admin` (
  `AdminID` int(11) NOT NULL,
  `UserName` varchar(50) NOT NULL,
  `Password` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- إرجاع أو استيراد بيانات الجدول `admin`
--

INSERT INTO `admin` (`AdminID`, `UserName`, `Password`) VALUES
(1, 'admin1', 'admin123');

-- --------------------------------------------------------

--
-- بنية الجدول `driver`
--

CREATE TABLE `driver` (
  `DriverID` int(11) NOT NULL,
  `FullName` varchar(50) NOT NULL,
  `PhoneNumber` varchar(10) NOT NULL,
  `NationalID` varchar(10) NOT NULL,
  `Status` varchar(10) NOT NULL,
  `AdminID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- إرجاع أو استيراد بيانات الجدول `driver`
--

INSERT INTO `driver` (`DriverID`, `FullName`, `PhoneNumber`, `NationalID`, `Status`, `AdminID`) VALUES
(1, 'Ahmed Alqahtani', '0551234567', '1234567890', 'Available', 1),
(2, 'Faisal Alharbi', '0509876543', '9876543210', 'Busy', 1),
(3, 'Yousef Almutairi', '0561122334', '1122334455', 'Available', 1);

-- --------------------------------------------------------

--
-- بنية الجدول `rating`
--

CREATE TABLE `rating` (
  `RatingID` int(11) NOT NULL,
  `Stars` int(11) NOT NULL,
  `DateSubmitted` date NOT NULL,
  `UserID` int(11) DEFAULT NULL,
  `RequestID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- إرجاع أو استيراد بيانات الجدول `rating`
--

INSERT INTO `rating` (`RatingID`, `Stars`, `DateSubmitted`, `UserID`, `RequestID`) VALUES
(1, 5, '2026-03-29', 2, 2),
(2, 4, '2026-03-30', 1, 1);

-- --------------------------------------------------------

--
-- بنية الجدول `request`
--

CREATE TABLE `request` (
  `RequestID` int(11) NOT NULL,
  `ItemType` varchar(20) NOT NULL,
  `ItemValueRange` varchar(20) NOT NULL,
  `PickUpLocation` varchar(150) NOT NULL,
  `DropOffLocation` varchar(150) NOT NULL,
  `SecurityCode` varchar(4) NOT NULL,
  `ServicePrice` double NOT NULL,
  `InsuranceCoverage` double NOT NULL,
  `Status` varchar(20) NOT NULL,
  `CreationDate` datetime NOT NULL,
  `UserID` int(11) DEFAULT NULL,
  `DriverID` int(11) DEFAULT NULL,
  `is_edited` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- إرجاع أو استيراد بيانات الجدول `request`
--

INSERT INTO `request` (`RequestID`, `ItemType`, `ItemValueRange`, `PickUpLocation`, `DropOffLocation`, `SecurityCode`, `ServicePrice`, `InsuranceCoverage`, `Status`, `CreationDate`, `UserID`, `DriverID`) VALUES
(1, 'Jewelry', '5000-10000', 'Riyadh, Al Rawabi', 'Riyadh, Al Narjis', '4567', 200, 10000, 'In Transit', '2026-04-20 10:30:00', 1, 1),
(2, 'Cash', '5000-10000', 'Riyadh, Al Safa', 'Riyadh, Al Narjis', '2233', 150, 10000, 'Delivered', '2026-02-19 14:00:00', 2, 2),
(3, 'Electronics', 'less5000', 'Riyadh, Al Malaz', 'Riyadh, Al Olaya', '7890', 120, 10000, 'Pending', '2026-04-21 09:15:00', 3, NULL);

-- --------------------------------------------------------

--
-- بنية الجدول `review`
--

CREATE TABLE `review` (
  `ReviewID` int(11) NOT NULL,
  `ReviewText` varchar(255) NOT NULL,
  `DateSubmitted` date NOT NULL,
  `UserID` int(11) DEFAULT NULL,
  `RequestID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- إرجاع أو استيراد بيانات الجدول `review`
--

INSERT INTO `review` (`ReviewID`, `ReviewText`, `DateSubmitted`, `UserID`, `RequestID`) VALUES
(1, 'Very professional and secure delivery service.', '2026-03-29', 2, 2),
(2, 'The process was clear and organized.', '2026-03-30', 1, 1);

-- --------------------------------------------------------

--
-- بنية الجدول `user`
--

CREATE TABLE `user` (
  `UserID` int(11) NOT NULL,
  `FullName` varchar(50) NOT NULL,
  `PhoneNumber` varchar(10) NOT NULL,
  `Password` varchar(20) NOT NULL,
  `DateOfBirth` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- إرجاع أو استيراد بيانات الجدول `user`
--

INSERT INTO `user` (`UserID`, `FullName`, `PhoneNumber`, `Password`, `DateOfBirth`) VALUES
(1, 'Nora Ahmed', '0512345678', 'pass1234', '1990-05-15'),
(2, 'Faisal Mohammed', '0523456789', 'pass1234', '1985-08-20'),
(3, 'Reem Khalid', '0534567890', 'pass1234', '2002-11-10'),
(4, 'Rawan Saad', '0545678901', 'pass1234', '2001-03-25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`AdminID`);

--
-- Indexes for table `driver`
--
ALTER TABLE `driver`
  ADD PRIMARY KEY (`DriverID`),
  ADD KEY `AdminID` (`AdminID`);

--
-- Indexes for table `rating`
--
ALTER TABLE `rating`
  ADD PRIMARY KEY (`RatingID`),
  ADD KEY `UserID` (`UserID`),
  ADD KEY `RequestID` (`RequestID`);

--
-- Indexes for table `request`
--
ALTER TABLE `request`
  ADD PRIMARY KEY (`RequestID`),
  ADD KEY `UserID` (`UserID`),
  ADD KEY `DriverID` (`DriverID`);

--
-- Indexes for table `review`
--
ALTER TABLE `review`
  ADD PRIMARY KEY (`ReviewID`),
  ADD KEY `UserID` (`UserID`),
  ADD KEY `RequestID` (`RequestID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`UserID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `AdminID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `driver`
--
ALTER TABLE `driver`
  MODIFY `DriverID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `rating`
--
ALTER TABLE `rating`
  MODIFY `RatingID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `request`
--
ALTER TABLE `request`
  MODIFY `RequestID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `review`
--
ALTER TABLE `review`
  MODIFY `ReviewID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- قيود الجداول المحفوظة
--

--
-- القيود للجدول `driver`
--
ALTER TABLE `driver`
  ADD CONSTRAINT `driver_ibfk_1` FOREIGN KEY (`AdminID`) REFERENCES `admin` (`AdminID`);

--
-- القيود للجدول `rating`
--
ALTER TABLE `rating`
  ADD CONSTRAINT `rating_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`),
  ADD CONSTRAINT `rating_ibfk_2` FOREIGN KEY (`RequestID`) REFERENCES `request` (`RequestID`);

--
-- القيود للجدول `request`
--
ALTER TABLE `request`
  ADD CONSTRAINT `request_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`),
  ADD CONSTRAINT `request_ibfk_2` FOREIGN KEY (`DriverID`) REFERENCES `driver` (`DriverID`);

--
-- القيود للجدول `review`
--
ALTER TABLE `review`
  ADD CONSTRAINT `review_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`),
  ADD CONSTRAINT `review_ibfk_2` FOREIGN KEY (`RequestID`) REFERENCES `request` (`RequestID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
