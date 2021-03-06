-- phpMyAdmin SQL Dump
-- version 4.1.10
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 08, 2015 at 04:19 PM
-- Server version: 5.1.67-andiunpam
-- PHP Version: 5.6.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `shopping_list`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `test_multi_sets`()
    DETERMINISTIC
begin
        select user() as first_col;
        select user() as first_col, now() as second_col;
        select user() as first_col, now() as second_col, now() as third_col;
        end$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `shopping_list`
--

CREATE TABLE IF NOT EXISTS `shopping_list` (
  `stock_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT '1',
  `item_date` date NOT NULL,
  `purchased` tinyint(4) NOT NULL DEFAULT '0',
  `dirty` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`stock_id`,`item_date`),
  KEY `stock_id` (`stock_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `shopping_list`
--

INSERT INTO `shopping_list` (`stock_id`, `quantity`, `item_date`, `purchased`, `dirty`) VALUES
(1, 2, '2015-04-19', 1, 1),
(1, 2, '2015-04-26', 1, 1),
(1, 2, '2015-05-03', 1, 1),
(1, 2, '2015-05-07', 1, 1),
(1, 2, '2015-05-17', 1, 1),
(1, 2, '2015-05-24', 1, 1),
(1, 2, '2015-05-31', 1, 1),
(1, 2, '2015-06-07', 1, 1),
(1, 2, '2015-06-15', 1, 1),
(1, 2, '2015-06-28', 1, 1),
(1, 2, '2015-07-05', 1, 1),
(2, 1, '2015-05-31', 1, 1),
(2, 1, '2015-07-05', 1, 1),
(3, 1, '2015-04-26', 1, 1),
(3, 1, '2015-05-07', 1, 1),
(3, 1, '2015-05-31', 1, 1),
(3, 1, '2015-06-15', 1, 1),
(3, 0, '2015-07-05', 0, 1),
(4, 1, '2015-04-19', 1, 1),
(4, 1, '2015-04-26', 1, 1),
(4, 1, '2015-05-03', 1, 1),
(4, 1, '2015-05-24', 1, 1),
(4, 1, '2015-06-28', 1, 1),
(5, 3, '2015-04-19', 1, 1),
(5, 2, '2015-04-26', 1, 1),
(5, 1, '2015-05-03', 1, 1),
(5, 3, '2015-05-07', 1, 1),
(5, 1, '2015-05-17', 1, 1),
(5, 3, '2015-05-24', 1, 1),
(5, 1, '2015-05-31', 1, 1),
(5, 2, '2015-06-15', 1, 1),
(5, 2, '2015-06-28', 1, 1),
(5, 2, '2015-07-05', 1, 1),
(6, 1, '2015-04-26', 1, 1),
(6, 1, '2015-05-03', 1, 1),
(6, 3, '2015-05-07', 1, 1),
(6, 3, '2015-05-24', 1, 1),
(6, 1, '2015-06-07', 1, 1),
(6, 3, '2015-06-15', 1, 1),
(6, 1, '2015-06-28', 1, 1),
(6, 3, '2015-07-05', 1, 1),
(7, 1, '2015-05-03', 1, 1),
(11, 1, '2015-04-26', 1, 1),
(11, 1, '2015-05-03', 1, 1),
(11, 1, '2015-05-07', 1, 1),
(11, 1, '2015-05-24', 1, 1),
(11, 1, '2015-06-07', 1, 1),
(11, 1, '2015-06-15', 1, 1),
(11, 1, '2015-06-24', 1, 1),
(11, 1, '2015-06-28', 1, 1),
(11, 1, '2015-07-05', 1, 1),
(12, 1, '2015-05-07', 1, 1),
(13, 1, '2015-04-20', 1, 1),
(15, 9, '2015-06-07', 1, 1),
(16, 4, '2015-04-19', 1, 1),
(16, 5, '2015-04-26', 1, 1),
(16, 4, '2015-05-03', 1, 1),
(16, 3, '2015-05-07', 1, 1),
(16, 3, '2015-05-17', 1, 1),
(16, 3, '2015-05-24', 1, 1),
(16, 5, '2015-05-31', 1, 1),
(16, 4, '2015-06-07', 1, 1),
(16, 3, '2015-06-15', 1, 1),
(16, 5, '2015-06-24', 1, 1),
(16, 3, '2015-06-28', 1, 1),
(16, 5, '2015-07-05', 1, 1),
(17, 1, '2015-05-31', 1, 1),
(18, 1, '2015-05-24', 1, 1),
(19, 1, '2015-04-26', 1, 1),
(19, 1, '2015-05-03', 1, 1),
(19, 1, '2015-05-07', 1, 1),
(19, 1, '2015-06-07', 1, 1),
(19, 1, '2015-07-05', 1, 1),
(20, 1, '2015-05-24', 1, 1),
(20, 1, '2015-05-31', 1, 1),
(20, 1, '2015-06-07', 1, 1),
(20, 1, '2015-06-15', 1, 1),
(20, 0, '2015-07-05', 0, 1),
(21, 1, '2015-05-07', 1, 1),
(22, 1, '2015-05-03', 1, 1),
(22, 1, '2015-05-07', 1, 1),
(22, 1, '2015-05-24', 1, 1),
(23, 2, '2015-04-19', 1, 1),
(23, 2, '2015-05-03', 1, 1),
(23, 1, '2015-05-07', 1, 1),
(23, 1, '2015-05-17', 1, 1),
(23, 1, '2015-05-24', 1, 1),
(23, 1, '2015-05-31', 1, 1),
(23, 1, '2015-06-07', 1, 1),
(23, 1, '2015-06-15', 1, 1),
(23, 1, '2015-06-28', 1, 1),
(23, 0, '2015-07-05', 0, 1),
(26, 1, '2015-05-31', 1, 1),
(26, 1, '2015-06-15', 1, 1),
(26, 0, '2015-07-05', 0, 1),
(27, 1, '2015-04-19', 1, 1),
(27, 1, '2015-05-17', 1, 1),
(27, 1, '2015-05-31', 1, 1),
(27, 1, '2015-07-05', 1, 1),
(28, 2, '2015-04-19', 1, 1),
(28, 2, '2015-04-26', 1, 1),
(28, 1, '2015-05-03', 1, 1),
(28, 2, '2015-05-07', 1, 1),
(28, 1, '2015-05-24', 1, 1),
(28, 2, '2015-05-31', 1, 1),
(28, 1, '2015-06-15', 1, 1),
(28, 1, '2015-06-28', 1, 1),
(28, 1, '2015-07-05', 1, 1),
(29, 1, '2015-04-19', 1, 1),
(29, 1, '2015-05-03', 1, 1),
(29, 1, '2015-05-17', 1, 1),
(29, 1, '2015-05-31', 1, 1),
(29, 1, '2015-06-15', 1, 1),
(29, 1, '2015-06-28', 1, 1),
(29, 0, '2015-07-05', 0, 1),
(30, 1, '2015-04-19', 1, 1),
(30, 1, '2015-05-03', 1, 1),
(30, 1, '2015-05-24', 1, 1),
(30, 1, '2015-06-07', 1, 1),
(30, 0, '2015-07-05', 0, 1),
(31, 1, '2015-05-17', 1, 1),
(33, 1, '2015-04-19', 1, 1),
(33, 1, '2015-05-17', 1, 1),
(33, 1, '2015-06-15', 1, 1),
(35, 2, '2015-04-19', 1, 1),
(35, 1, '2015-04-26', 1, 1),
(35, 1, '2015-05-03', 1, 1),
(35, 1, '2015-05-07', 1, 1),
(35, 2, '2015-05-17', 1, 1),
(35, 1, '2015-05-24', 1, 1),
(35, 1, '2015-05-31', 1, 1),
(35, 2, '2015-06-28', 1, 1),
(38, 1, '2015-05-24', 1, 1),
(39, 1, '2015-05-07', 1, 1),
(40, 1, '2015-04-19', 1, 1),
(40, 1, '2015-06-28', 1, 1),
(41, 1, '2015-04-19', 1, 1),
(41, 1, '2015-06-28', 1, 1),
(43, 1, '2015-04-26', 1, 1),
(43, 1, '2015-06-07', 1, 1),
(43, 1, '2015-07-05', 1, 1),
(44, 1, '2015-05-17', 1, 1),
(45, 1, '2015-05-31', 1, 1),
(46, 2, '2015-05-07', 1, 1),
(46, 2, '2015-05-24', 1, 1),
(46, 2, '2015-06-07', 1, 1),
(46, 2, '2015-06-28', 1, 1),
(47, 1, '2015-05-03', 1, 1),
(48, 1, '2015-04-26', 1, 1),
(48, 1, '2015-05-03', 1, 1),
(48, 2, '2015-05-07', 1, 1),
(48, 2, '2015-05-24', 1, 1),
(48, 1, '2015-06-07', 1, 1),
(48, 1, '2015-06-15', 1, 1),
(48, 2, '2015-06-28', 1, 1),
(48, 2, '2015-07-05', 1, 1),
(66, 1, '2015-06-07', 1, 1),
(67, 2, '2015-04-19', 1, 1),
(67, 2, '2015-05-03', 1, 1),
(67, 2, '2015-05-07', 1, 1),
(67, 1, '2015-05-17', 1, 1),
(67, 2, '2015-05-24', 1, 1),
(67, 2, '2015-05-31', 1, 1),
(67, 1, '2015-06-15', 1, 1),
(67, 2, '2015-07-05', 1, 1),
(69, 1, '2015-05-07', 1, 1),
(71, 1, '2015-04-19', 1, 1),
(71, 1, '2015-06-15', 1, 1),
(72, 1, '2015-05-03', 1, 1),
(72, 1, '2015-06-28', 1, 1),
(74, 1, '2015-04-19', 1, 1),
(74, 1, '2015-05-31', 1, 1),
(74, 0, '2015-07-05', 0, 1),
(75, 1, '2015-04-19', 1, 1),
(76, 1, '2015-04-19', 1, 1),
(76, 1, '2015-05-03', 1, 1),
(76, 1, '2015-05-17', 1, 1),
(76, 1, '2015-06-07', 1, 1),
(76, 1, '2015-06-24', 1, 1),
(76, 1, '2015-07-05', 1, 1),
(77, 1, '2015-04-19', 1, 1),
(77, 1, '2015-05-31', 1, 1),
(77, 0, '2015-07-05', 0, 1),
(78, 8, '2015-04-19', 1, 1),
(79, 1, '2015-04-20', 1, 1),
(80, 2, '2015-04-26', 1, 1),
(80, 2, '2015-05-07', 1, 1),
(81, 1, '2015-04-26', 1, 1),
(82, 1, '2015-04-26', 1, 1),
(82, 1, '2015-05-07', 1, 1),
(83, 1, '2015-04-26', 1, 1),
(84, 1, '2015-05-03', 1, 1),
(84, 1, '2015-05-31', 1, 1),
(84, 1, '2015-06-28', 1, 1),
(85, 1, '2015-05-03', 1, 1),
(86, 1, '2015-05-07', 1, 1),
(87, 1, '2015-05-07', 1, 1),
(88, 1, '2015-05-07', 1, 1),
(89, 1, '2015-05-07', 1, 1),
(90, 1, '2015-05-17', 1, 1),
(91, 1, '2015-05-17', 1, 1),
(92, 1, '2015-05-17', 1, 1),
(93, 1, '2015-05-24', 1, 1),
(94, 1, '2015-05-24', 1, 1),
(95, 1, '2015-05-24', 1, 1),
(96, 1, '2015-05-24', 1, 1),
(97, 1, '2015-05-24', 1, 1),
(98, 1, '2015-05-24', 1, 1),
(99, 1, '2015-05-24', 1, 1),
(100, 1, '2015-05-24', 1, 1),
(101, 1, '2015-05-27', 1, 1),
(102, 1, '2015-06-15', 1, 1),
(103, 1, '2015-05-31', 1, 1),
(104, 1, '2015-05-31', 1, 1),
(105, 1, '2015-06-07', 1, 1),
(106, 1, '2015-06-07', 1, 1),
(107, 1, '2015-07-05', 1, 1),
(108, 1, '2015-07-05', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `stock`
--

CREATE TABLE IF NOT EXISTS `stock` (
  `stock_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT 'New Stock Item',
  PRIMARY KEY (`stock_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=109 ;

--
-- Dumping data for table `stock`
--

INSERT INTO `stock` (`stock_id`, `name`) VALUES
(1, 'Baked Beans'),
(2, 'Mouthwash'),
(3, 'Cheese'),
(4, 'Ham'),
(5, 'Tomato Juice'),
(6, 'Orange Juice'),
(7, 'Coffee'),
(8, 'Tea'),
(9, 'Salt'),
(10, 'Sugar'),
(11, 'Eggs'),
(12, 'Anti Perspirant'),
(13, 'Hair Wax'),
(14, 'Kitchen Roll'),
(15, 'Toilet Roll'),
(16, 'Bananas'),
(17, 'Onions'),
(18, 'Red Pepper'),
(19, 'Green Pepper'),
(20, 'Shepherd''s Pie'),
(21, 'Cottage Pie'),
(22, 'Lasagne'),
(23, 'Pizza'),
(26, 'Salami'),
(27, 'Picallily'),
(28, 'Bread Stick'),
(29, 'Bread Loaf'),
(30, 'Chicken'),
(31, 'Lamb Mince'),
(32, 'Beef Mince'),
(33, 'Penne Pasta'),
(34, 'Spaghetti'),
(35, 'Tomato Tins'),
(36, 'Peas'),
(37, 'Viakal'),
(38, 'Bleach'),
(39, 'Soap Liquid'),
(40, 'Clothes Liquid'),
(41, 'Conditioner'),
(42, 'Washing Up Liquid'),
(43, 'Shower Gel'),
(44, 'Soap'),
(45, 'Curry Paste'),
(46, 'Carrots'),
(47, 'Risotto Rice'),
(48, 'Milk'),
(49, 'Pasta Filled'),
(66, 'Toothpaste'),
(67, 'Soup'),
(69, 'Fish Pie'),
(71, 'Honey'),
(72, 'Porridge'),
(74, 'Basmati Rice'),
(75, 'Fish Oil 1000mg'),
(76, 'Vitamins'),
(77, 'Dishwasher Tabs'),
(78, 'Coca Cola'),
(79, 'Bin Liners'),
(80, 'Tomatoes'),
(81, 'Garlic'),
(82, 'Lemons'),
(83, 'Olive Oil Extra Virgin'),
(84, 'Muesli'),
(85, 'Biscuits'),
(86, 'Floor Cleaner'),
(87, 'Marmite'),
(88, 'Bacon'),
(89, 'White Wine'),
(90, 'Hot Sauce'),
(91, 'Mussels'),
(92, 'Baguette'),
(93, '1001 Cleaner'),
(94, 'Couscous'),
(95, 'Chickpeas'),
(96, 'Potatoes'),
(97, 'Tuna'),
(98, 'Olives'),
(99, 'Mayonnaise'),
(100, 'White Wine Vinegar'),
(101, 'Htp'),
(102, 'St Johns Wort'),
(103, 'Ginger'),
(104, 'Coriander'),
(105, 'Sandwich Bags'),
(106, 'Margarine'),
(107, 'Chicken Stock Cubes'),
(108, 'Veg Stock Cubes');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
