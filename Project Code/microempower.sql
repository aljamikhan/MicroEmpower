-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 28, 2025 at 04:08 AM
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
-- Database: `exchatloan`
--

-- --------------------------------------------------------

--
-- Table structure for table `bankstaff`
--

CREATE TABLE `bankstaff` (
  `StaffID` int(11) NOT NULL,
  `ID` int(11) NOT NULL,
  `Branch` varchar(100) DEFAULT NULL,
  `Bank` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bankstaff`
--

INSERT INTO `bankstaff` (`StaffID`, `ID`, `Branch`, `Bank`) VALUES
(1, 2, 'Badda', 'Brac'),
(2, 6, 'Rampura', 'MTB');

-- --------------------------------------------------------

--
-- Table structure for table `chatmessage`
--

CREATE TABLE `chatmessage` (
  `MessageID` int(11) NOT NULL,
  `OfferID` int(11) NOT NULL,
  `SenderUserID` int(11) NOT NULL,
  `Message` text NOT NULL,
  `SentAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chatmessage`
--

INSERT INTO `chatmessage` (`MessageID`, `OfferID`, `SenderUserID`, `Message`, `SentAt`) VALUES
(1, 5, 2, 'what do you want', '2025-08-28 02:16:09'),
(2, 7, 2, 'is this enough?', '2025-08-28 02:24:53'),
(3, 7, 8, 'no. i need more', '2025-08-28 02:25:22'),
(4, 8, 9, 'ok Done. But i won\'t pay ha ha', '2025-08-28 03:00:08'),
(5, 8, 9, 'done', '2025-08-28 03:00:39'),
(6, 9, 6, 'are you surely gonna pay?', '2025-08-28 03:05:12'),
(7, 9, 9, 'no. who pays haaha', '2025-08-28 03:06:01');

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `CustomerID` int(11) NOT NULL,
  `ID` int(11) NOT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `AccountBalance` decimal(15,2) DEFAULT 0.00,
  `CreditScore` int(11) DEFAULT 0,
  `Income` decimal(15,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`CustomerID`, `ID`, `Address`, `AccountBalance`, `CreditScore`, `Income`) VALUES
(1, 1, 'dhaka', -336.11, 0, 1000.00),
(2, 3, 'dhaka', 0.00, 0, 2000.00),
(3, 4, 'Badda', 2458.33, 0, 100.00),
(4, 5, 'dhaka', 6000.00, 0, 100.00),
(5, 7, 'Badda', 15000.00, 0, 5000.00),
(6, 8, 'dhaka', 50000.00, 0, 3000.00),
(7, 9, 'Badda', 15222.22, 0, 50000.00),
(8, 10, 'dhaka', 0.00, 0, 3000.00);

-- --------------------------------------------------------

--
-- Table structure for table `loancontract`
--

CREATE TABLE `loancontract` (
  `LoanID` int(11) NOT NULL,
  `PrincipalAmount` decimal(15,2) DEFAULT NULL,
  `StartDate` date DEFAULT NULL,
  `CurrentInterestRate` decimal(5,2) DEFAULT NULL,
  `Status` varchar(50) DEFAULT NULL,
  `ApplicationID` int(11) DEFAULT NULL,
  `OfferID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loancontract`
--

INSERT INTO `loancontract` (`LoanID`, `PrincipalAmount`, `StartDate`, `CurrentInterestRate`, `Status`, `ApplicationID`, `OfferID`) VALUES
(1, 1000.00, '2025-08-27', 5.00, 'Active', 1, 1),
(2, 5000.00, '2025-08-27', 10.00, 'Active', 2, 2),
(3, 5000.00, '2025-08-27', 10.00, 'Active', 3, 3),
(4, 6000.00, '2025-08-27', 4.00, 'Active', 4, 4),
(5, 15000.00, '2025-08-27', 2.00, 'Active', 5, 6),
(6, 50000.00, '2025-08-28', 10.00, 'Active', 6, 7),
(7, 35000.00, '2025-08-28', 5.00, 'Active', 7, 8),
(8, 12000.00, '2025-08-28', 15.00, 'Active', 8, 9);

-- --------------------------------------------------------

--
-- Table structure for table `loanoffer`
--

CREATE TABLE `loanoffer` (
  `OfferID` int(11) NOT NULL,
  `OfferDate` date DEFAULT NULL,
  `InterestRate` decimal(5,2) DEFAULT NULL,
  `RepaymentTerm` int(11) DEFAULT NULL,
  `Status` varchar(50) DEFAULT NULL,
  `ApplicationID` int(11) DEFAULT NULL,
  `StaffID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loanoffer`
--

INSERT INTO `loanoffer` (`OfferID`, `OfferDate`, `InterestRate`, `RepaymentTerm`, `Status`, `ApplicationID`, `StaffID`) VALUES
(1, '2025-08-27', 5.00, 3, 'Accepted', 1, 1),
(2, '2025-08-27', 10.00, 2, 'Accepted', 2, 1),
(3, '2025-08-27', 10.00, 2, 'Accepted', 3, 1),
(4, '2025-08-27', 4.00, 2, 'Accepted', 4, 1),
(5, '2025-08-27', 10.00, 3, 'Proposed', 5, 1),
(6, '2025-08-27', 2.00, 1, 'Accepted', 5, 2),
(7, '2025-08-28', 10.00, 3, 'Accepted', 6, 1),
(8, '2025-08-28', 5.00, 3, 'Accepted', 7, 2),
(9, '2025-08-28', 15.00, 3, 'Accepted', 8, 2);

-- --------------------------------------------------------

--
-- Table structure for table `loanrequest`
--

CREATE TABLE `loanrequest` (
  `ApplicationID` int(11) NOT NULL,
  `ApplicationDate` date DEFAULT NULL,
  `AmountRequested` decimal(15,2) DEFAULT NULL,
  `Term` int(11) DEFAULT NULL,
  `Purpose` varchar(255) DEFAULT NULL,
  `Status` varchar(50) DEFAULT NULL,
  `CustomerID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loanrequest`
--

INSERT INTO `loanrequest` (`ApplicationID`, `ApplicationDate`, `AmountRequested`, `Term`, `Purpose`, `Status`, `CustomerID`) VALUES
(1, '2025-08-27', 1000.00, 3, 'farm', 'Approved', 1),
(2, '2025-08-27', 5000.00, 3, 'farm', 'Approved', 2),
(3, '2025-08-27', 5000.00, 2, 'farm', 'Approved', 3),
(4, '2025-08-27', 6000.00, 3, 'farm', 'Approved', 4),
(5, '2025-08-27', 15000.00, 3, 'farm', 'Approved', 5),
(6, '2025-08-28', 50000.00, 3, 'farm', 'Approved', 6),
(7, '2025-08-28', 35000.00, 3, 'farm', 'Approved', 7),
(8, '2025-08-28', 12000.00, 2, 'farm', 'Approved', 7);

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `PaymentID` int(11) NOT NULL,
  `AmountPaid` decimal(15,2) DEFAULT NULL,
  `PaymentDate` datetime DEFAULT NULL,
  `PaymentMethod` varchar(50) DEFAULT NULL,
  `ScheduleID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`PaymentID`, `AmountPaid`, `PaymentDate`, `PaymentMethod`, `ScheduleID`) VALUES
(1, 337.50, '2025-08-27 00:14:18', 'Cash', 1),
(2, 2541.67, '2025-08-27 21:19:54', 'Cash', 6),
(3, 336.11, '2025-08-28 02:54:06', 'Cash', 2),
(4, 11812.50, '2025-08-28 03:46:19', 'AccountBalance', 14),
(5, 4150.00, '2025-08-28 03:46:58', 'Cash', 17),
(6, 11715.28, '2025-08-28 03:56:27', 'Cash', 16),
(7, 4100.00, '2025-08-28 04:00:13', 'Cash', 18);

-- --------------------------------------------------------

--
-- Table structure for table `penalty`
--

CREATE TABLE `penalty` (
  `PenaltyID` int(11) NOT NULL,
  `PenaltyType` varchar(100) DEFAULT NULL,
  `Amount` decimal(15,2) DEFAULT NULL,
  `ScheduleID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `repaymentschedule`
--

CREATE TABLE `repaymentschedule` (
  `ScheduleID` int(11) NOT NULL,
  `InterestDue` decimal(15,2) DEFAULT NULL,
  `PrincipalDue` decimal(15,2) DEFAULT NULL,
  `PenaltyAmount` decimal(15,2) DEFAULT NULL,
  `DueDate` date DEFAULT NULL,
  `Status` varchar(50) DEFAULT NULL,
  `LoanID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `repaymentschedule`
--

INSERT INTO `repaymentschedule` (`ScheduleID`, `InterestDue`, `PrincipalDue`, `PenaltyAmount`, `DueDate`, `Status`, `LoanID`) VALUES
(1, 4.17, 333.33, 0.00, '2025-09-27', 'PAID', 1),
(2, 2.78, 333.33, 0.00, '2025-10-27', 'PAID', 1),
(3, 1.39, 333.33, 0.00, '2025-11-27', 'DUE', 1),
(4, 41.67, 2500.00, 0.00, '2025-09-27', 'DUE', 2),
(5, 20.83, 2500.00, 0.00, '2025-10-27', 'DUE', 2),
(6, 41.67, 2500.00, 0.00, '2025-09-27', 'PAID', 3),
(7, 20.83, 2500.00, 0.00, '2025-10-27', 'DUE', 3),
(8, 20.00, 3000.00, 0.00, '2025-09-27', 'DUE', 4),
(9, 10.00, 3000.00, 0.00, '2025-10-27', 'DUE', 4),
(10, 25.00, 15000.00, 0.00, '2025-09-27', 'DUE', 5),
(11, 416.67, 16666.67, 0.00, '2025-09-28', 'DUE', 6),
(12, 277.78, 16666.67, 0.00, '2025-10-28', 'DUE', 6),
(13, 138.89, 16666.67, 0.00, '2025-11-28', 'DUE', 6),
(14, 145.83, 11666.67, 0.00, '2025-09-28', 'PAID', 7),
(15, 97.22, 11666.67, 0.00, '2025-10-28', 'DUE', 7),
(16, 48.61, 11666.67, 0.00, '2025-11-28', 'PAID', 7),
(17, 150.00, 4000.00, 0.00, '2025-09-28', 'PAID', 8),
(18, 100.00, 4000.00, 0.00, '2025-10-28', 'PAID', 8),
(19, 50.00, 4000.00, 0.00, '2025-11-28', 'DUE', 8);

-- --------------------------------------------------------

--
-- Table structure for table `riskscore`
--

CREATE TABLE `riskscore` (
  `ScoreID` int(11) NOT NULL,
  `ScoreValue` decimal(5,2) DEFAULT NULL,
  `RiskValue` varchar(50) DEFAULT NULL,
  `CalculatedDate` date DEFAULT NULL,
  `CustomerID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `riskscore`
--

INSERT INTO `riskscore` (`ScoreID`, `ScoreValue`, `RiskValue`, `CalculatedDate`, `CustomerID`) VALUES
(1, 100.00, 'Good', '2025-08-26', 1),
(2, 102.00, 'Good', '2025-08-27', 1),
(3, 100.00, 'Good', '2025-08-27', 2),
(4, 100.00, 'Good', '2025-08-27', 3),
(5, 100.00, 'Good', '2025-08-27', 4),
(6, 100.00, 'Good', '2025-08-27', 5),
(7, 50.00, 'Good', '2025-08-28', 6),
(8, 100.00, 'Good', '2025-08-28', 7),
(9, 100.00, 'Good', '2025-08-28', 8);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `ID` int(11) NOT NULL,
  `UserName` varchar(100) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Role` varchar(50) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `ContactInfo` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`ID`, `UserName`, `Password`, `Role`, `Email`, `ContactInfo`) VALUES
(1, 'jami', '$2y$10$SUV5vKmNZzY9/yRTf2nEzeCN1uJWs82oIn.JR89JKhu1fqRsQBQYC', 'customer', 'jami@gmail.com', '01874292009'),
(2, 'brac', '$2y$10$ngR0ffldND/VIFtkBvVqoeTrsEJHLazGGP2StJ4xm8V2DvaWftn9e', 'bank', 'brac@gmail.com', '01719121195'),
(3, 'ritom', '$2y$10$bl017xoKd2cE1c1jLf2VpuRIy6FlQyEqLQKq.xQxGaT4bSSymefAO', 'customer', 'ritom@gmail.com', '01917272409'),
(4, 'safwan', '$2y$10$OhicPogTm.PJzTz3h39iI.9qi3yUCthJy8d5YsM8LNThRFeQBr8Cm', 'customer', 'safwan@gmail.com', '01719121195'),
(5, 'zoro', '$2y$10$2FFjB3gs43lPZs..pJBieOQQfjnJWznQhI6nomO0VmVf9EKRPydTi', 'customer', 'zoro@gmail.com', '01874292009'),
(6, 'Mutual Trust Bank', '$2y$10$Ra9Xhj8KyZ/UIlo0kL/H5u7RtAFaKe8QapS3y686gkNGNERnRpmKG', 'bank', 'mtb@gmail.com', '01719121195'),
(7, 'naruto', '$2y$10$.5lS3.tvbwy5u2iTVJTbvuYGkNv5MtX6yoKprOuJPne6XNtKdmakS', 'customer', 'naruto@gmail.com', '01874292009'),
(8, 'Sai', '$2y$10$SG1..5w391LTaPo7qyh32OOYn6XXQAm9Gb2dA/B5fMCnvFVHHVY7a', 'customer', 'sai@gmail.com', '01874292009'),
(9, 'sasuke', '$2y$10$ZbaSdz1jMtrQ8Ylh2GymYeD5o3mwtYvH3GcgMUZq2qIFJ7DEYXQda', 'customer', 'sasuke@gmail.com', '01874292009'),
(10, 'zorot', '$2y$10$om0iOnAA33zAciMtsK.MLeZgeWjfGne/92amOMMJW9CHDidRt9lmW', 'customer', 'zorot@gmail.com', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bankstaff`
--
ALTER TABLE `bankstaff`
  ADD PRIMARY KEY (`StaffID`),
  ADD UNIQUE KEY `ID` (`ID`);

--
-- Indexes for table `chatmessage`
--
ALTER TABLE `chatmessage`
  ADD PRIMARY KEY (`MessageID`),
  ADD KEY `SenderUserID` (`SenderUserID`),
  ADD KEY `idx_chat_offer` (`OfferID`,`SentAt`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`CustomerID`),
  ADD UNIQUE KEY `ID` (`ID`);

--
-- Indexes for table `loancontract`
--
ALTER TABLE `loancontract`
  ADD PRIMARY KEY (`LoanID`),
  ADD KEY `ApplicationID` (`ApplicationID`),
  ADD KEY `OfferID` (`OfferID`);

--
-- Indexes for table `loanoffer`
--
ALTER TABLE `loanoffer`
  ADD PRIMARY KEY (`OfferID`),
  ADD KEY `ApplicationID` (`ApplicationID`),
  ADD KEY `StaffID` (`StaffID`);

--
-- Indexes for table `loanrequest`
--
ALTER TABLE `loanrequest`
  ADD PRIMARY KEY (`ApplicationID`),
  ADD KEY `CustomerID` (`CustomerID`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`PaymentID`),
  ADD KEY `ScheduleID` (`ScheduleID`);

--
-- Indexes for table `penalty`
--
ALTER TABLE `penalty`
  ADD PRIMARY KEY (`PenaltyID`),
  ADD KEY `ScheduleID` (`ScheduleID`);

--
-- Indexes for table `repaymentschedule`
--
ALTER TABLE `repaymentschedule`
  ADD PRIMARY KEY (`ScheduleID`),
  ADD KEY `LoanID` (`LoanID`);

--
-- Indexes for table `riskscore`
--
ALTER TABLE `riskscore`
  ADD PRIMARY KEY (`ScoreID`),
  ADD KEY `CustomerID` (`CustomerID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bankstaff`
--
ALTER TABLE `bankstaff`
  MODIFY `StaffID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `chatmessage`
--
ALTER TABLE `chatmessage`
  MODIFY `MessageID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `CustomerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `loancontract`
--
ALTER TABLE `loancontract`
  MODIFY `LoanID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `loanoffer`
--
ALTER TABLE `loanoffer`
  MODIFY `OfferID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `loanrequest`
--
ALTER TABLE `loanrequest`
  MODIFY `ApplicationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `PaymentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `penalty`
--
ALTER TABLE `penalty`
  MODIFY `PenaltyID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `repaymentschedule`
--
ALTER TABLE `repaymentschedule`
  MODIFY `ScheduleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `riskscore`
--
ALTER TABLE `riskscore`
  MODIFY `ScoreID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bankstaff`
--
ALTER TABLE `bankstaff`
  ADD CONSTRAINT `bankstaff_ibfk_1` FOREIGN KEY (`ID`) REFERENCES `users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `chatmessage`
--
ALTER TABLE `chatmessage`
  ADD CONSTRAINT `chatmessage_ibfk_1` FOREIGN KEY (`OfferID`) REFERENCES `loanoffer` (`OfferID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `chatmessage_ibfk_2` FOREIGN KEY (`SenderUserID`) REFERENCES `users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `customer`
--
ALTER TABLE `customer`
  ADD CONSTRAINT `customer_ibfk_1` FOREIGN KEY (`ID`) REFERENCES `users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `loancontract`
--
ALTER TABLE `loancontract`
  ADD CONSTRAINT `loancontract_ibfk_1` FOREIGN KEY (`ApplicationID`) REFERENCES `loanrequest` (`ApplicationID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `loancontract_ibfk_2` FOREIGN KEY (`OfferID`) REFERENCES `loanoffer` (`OfferID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `loanoffer`
--
ALTER TABLE `loanoffer`
  ADD CONSTRAINT `loanoffer_ibfk_1` FOREIGN KEY (`ApplicationID`) REFERENCES `loanrequest` (`ApplicationID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `loanoffer_ibfk_2` FOREIGN KEY (`StaffID`) REFERENCES `bankstaff` (`StaffID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `loanrequest`
--
ALTER TABLE `loanrequest`
  ADD CONSTRAINT `loanrequest_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customer` (`CustomerID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`ScheduleID`) REFERENCES `repaymentschedule` (`ScheduleID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `penalty`
--
ALTER TABLE `penalty`
  ADD CONSTRAINT `penalty_ibfk_1` FOREIGN KEY (`ScheduleID`) REFERENCES `repaymentschedule` (`ScheduleID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `repaymentschedule`
--
ALTER TABLE `repaymentschedule`
  ADD CONSTRAINT `repaymentschedule_ibfk_1` FOREIGN KEY (`LoanID`) REFERENCES `loancontract` (`LoanID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `riskscore`
--
ALTER TABLE `riskscore`
  ADD CONSTRAINT `riskscore_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customer` (`CustomerID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
