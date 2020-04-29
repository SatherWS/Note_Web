-- phpMyAdmin SQL Dump
-- version 4.9.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 05, 2020 at 12:59 AM
-- Server version: 8.0.18
-- PHP Version: 7.3.11

--
-- Database: lhapps
--
CREATE DATABASE if not exists lhapps;
USE lhapps;
DROP TABLE journal;
DROP TABLE todo_list;
-- --------------------------------------------------------

--
-- Table structure for table day_logger
--

CREATE TABLE journal (
  id int(11) primary key auto_increment,
  subject varchar(45) NOT NULL,
  message varchar(300) NOT NULL,
  rating int(11) NOT NULL,
  date_created datetime DEFAULT CURRENT_TIMESTAMP
);

--
-- Table structure for table todo_list
--

CREATE TABLE todo_list (
	id int primary key auto_increment not null,
	description varchar(45) not null,
	start_date datetime,
	deadline date NOT NULL,
	importance varchar(10) NOT NULL,
	date_created datetime DEFAULT CURRENT_TIMESTAMP
);