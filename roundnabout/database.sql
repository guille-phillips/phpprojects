-- phpMyAdmin SQL Dump
-- version 4.0.10.7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 23, 2015 at 06:26 AM
-- Server version: 5.5.35-cll-lve
-- PHP Version: 5.4.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `roundnabout`
--

-- --------------------------------------------------------

--
-- Table structure for table `places`
--

CREATE TABLE IF NOT EXISTS `places` (
  `id` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `latitude` float NOT NULL,
  `longitude` float NOT NULL,
  `category` varchar(20) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telephone` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `postcode` varchar(8) NOT NULL,
  `entry_rates` varchar(255) NOT NULL,
  `opening_times` varchar(255) DEFAULT NULL,
  `rating` decimal(2,1) DEFAULT NULL,
  `more_info` text,
  `disabled_facilities` text,
  `facilities` text,
  `good_stuff` text,
  `bad_stuff` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15 ;

--
-- Dumping data for table `places`
--

INSERT INTO `places` (`id`, `name`, `latitude`, `longitude`, `category`, `email`, `telephone`, `address`, `postcode`, `entry_rates`, `opening_times`, `rating`, `more_info`, `disabled_facilities`, `facilities`, `good_stuff`, `bad_stuff`) VALUES
(4, 'Hove Lagoon Skatepark', 50.8265, -0.195914, 'Skatepark, Free', 'tony@pekuliar.com', '["07818543162"]', '["Hove Lagoon","Kingsway","Hove","East Sussex"]', 'BN3 4LX', '["FREE"]', '["24 Hours"]', '4.0', 'Hove Lagoon skatepark is a sweet little concrete skatepark, right on the coast with one of the coolest locations about a minutes walk from the beach. With a super smooth surface, it opens up both street and transition areas of the park with many flowing lines.\r\nThe skatepark comprises of a long 4 foot flat-bank with drop-in block at one end of the park, ideal for grinds, slides and stalls. This leads to the mid-section which is a big fun-box/driveway with flat and down, square kink rail in the middle, pyramid style hips to one side and steep hubbas to the other. This all leads to a 5 foot quarter-pipe to the left and flat-bank to the right, both connected to a 4½ foot mini the along the back wall of the skatepark.', '', 'Refreshments, toilets, cafe', 'Spine ramp in the centre of a street park. Great halfpipe all concrete park. Open 24 hours', 'nothing'),
(5, 'Beach Green Playpark', 50.8279, -0.279277, 'Playground', 'tony@pekuliar.com', '07818543162', 'Beach Green, Shoreham', '', 'FREE', '24 Hours', '4.0', 'A\r\nB\r\nC\r\nD', '', 'Toilets', 'Great Slide', 'Rubbish'),
(6, 'King Alfred leisure centre', 50.8251, -0.17895, 'Indoor, leisure cent', 'kingalfredenquiries@freedom-leisure.co.uk', '["01273 290290"]', '["Kingsway","Hove","East Sussex"]', 'BN3 2WW', '["Membership"]', '["8:30 a","m",null,"m",""]', '4.0', 'The wetside facilities include a 25m 6 lane swimming pool which ranges from a depth of 1.2 to 2.5m. There are two sports halls, based in the old pool halls.', '', '25m swimming pool with viewing area. Leisure pool with flume and fun pool features. Gym with cardiovascular and resistance equipment. 2 x sports halls (5-a-side football, badminton, volleyball, basketball, table tennis etc). Swim school - courses and individual lessons are available for all ages and abilities. Birthday parties. Junior gym sessions.', 'Recently refurbished, friendly staff, the changing room and shower area have all been renewed.', 'Run down, dirty in parts and baby pool not that warm. Lifeguards do not seem to police the lanes well which, sometimes, makes for unhappy customers!'),
(7, 'Huxleys Experience', 51.0533, -0.303792, 'Outdoor, cost,', 'huxleys@hotmail.co.uk', '["01403 273458"]', '["Falcon Lodge","Sedgwick Lane","Brighton road","Horsham","West Sussex"]', 'RH13 6QD', '[null,"95","",null,"95","",null,"25","",null,"50"]', '["Wednesday to Saturday and Bank Holidays 11am to 5pm","Sunday 11am to 4","30pm","Just Sundays in winter"]', '5.0', 'At the centre they have a wide range of birds from hawks to eagles, falcons to owls, and vultures to kites! They have around 70 birds in total all waiting to see you.\r\n\r\nThey even have a few character birds such as Coco the Crested Caracara, Reaper the Raven and Sydney the Laughing Kookaburra!\r\n\r\nThey have a special team of owls who take it in turns to meet and greet the public and 12noon and 2pm! They also have a fantastic flying team who love to show off in the 2:30pm display as well as at other various events and shows around the South of England.\r\n\r\nPlus a gorgeous Japanese Water Garden.', '', '', 'Amazing value for such a great afternoon. Very friendly, entertaining and knowledgeable staff, a good number of birds and a great flying display.', ''),
(8, 'Dunreyth Alpacas', 50.8788, -0.873511, 'Outdoor, Cost, Anima', 'info@dunreythalpacas.co.uk', '["Peta: 07766 252310 or Bruce: 07799 583637"]', '["Adsdean","Funtington","Chichester","West Sussex"]', 'PO18 9DN', '[null,""]', '["Open to visitors: Tuesday to Sunday from 9am to 4pm","Closed on Mondays"]', '3.0', 'Dunreyth Alpacas is a small business owned by a husband and wife team near Chichester on the side of the South Downs. Alpaca Walking. Walks have to be booked in advance. Minimum age is 6 years old. At least two Alpacas need to be booked per walk. The walk takes approx. 50 min.', '', 'Picnic tables. Toilets.', 'Nice countryside walk.', 'Quite expensive'),
(9, 'Woods Mill Nature Reserve', 50.9113, -0.266976, 'outdoor, free, natur', 'enquiry@wildlifetrusts.org', '["01636 677711"]', '["Horn Lane","Small Dole","Henfield","Sussex","",""]', 'BN5 9SD', '["Free"]', '["Open throughout the year (except for two weeks at Christmas)"]', '4.0', 'Best time to visit is April - July.', '', 'Toilets, disabled toilet. Size is 13 hectares. All-weather path and boardwalk across lake and reedbed suitable for wheelchairs.Car park. No dogs allowed.', 'Free, beautiful walks.', 'Limited car parking'),
(10, 'Fishers Farm Park', 51.0306, -0.492098, 'Outdoor, Animals, Fa', 'info@fishersfarmpark.co.uk', '["01403 700063"]', '["Fishers Farm Park","Newpound Lane","Wisborough Green","West Sussex"]', 'RH14 0EG', '["2015 Prices:","","Mid Season Sample prices:","",null,"75","",null,"75","","Groups: see website for group discounts and fantastic options for schools and nurseries","Membership holders get 10% off everything! (including admission for friends and relative', '["Open All Year (closed Xmas day and boxing day only) Open:10m Close: 5pm"]', '4.0', 'Restaurant: Waitress service, home-cooked food and fantastic children’s options on the menu. Barista Coffee Bar: Freshly ground coffee conveniently located next to 5 different soft-play zones! Cafe Moo Moo: Moooove on down to this “udderly” brilliant cafe serving hot food fast for the busy visitor. Alpine Lodge. “Sugar and Spice and all things nice” with doughnuts, ice creams and treats for everyone. Splash Attack (seasonal): Drinks and ice creams served all day!', '', 'Toilets, disabled toilets, baby care centre, toy shop, mums microwaves, private nursing areas.', 'Mobility Scooters available for use and “Free of charge”', ''),
(11, 'Washbrooks Family Farm', 50.9314, -0.18266, 'Farm, Animals, Playg', NULL, '["01273 832201"]', '["Brighton Road","Hurstpierpoint","Hassocks","West Sussex"]', 'BN6 9EF', '[null,"",null,"50","",null,"50","",null,"50","","Family ticket (2 adults",null]', '["Open everyday from 9","30am until 5pm","Closed from Christmas Day until News Years Day",""]', '5.0', 'Indoor play area, tractor rides, outdoor adventure playground, giant bouncy pillow, living maize maze, sandpit. Lots of animals', '', 'Toilets, tea room, picnic facilities', 'Giant jumping pillow', ''),
(12, 'Woods Mill Nature Reserve', 50.9113, -0.266976, 'outdoor, free, natur', 'enquiry@wildlifetrusts.org', '["01636 677711"]', '["Horn Lane","Small Dole","Henfield","Sussex","",""]', 'BN5 9SD', '["Free"]', '["Open throughout the year (except for two weeks at Christmas)"]', '4.0', 'Best time to visit is April - July.', '', 'Toilets, disabled toilet. Size is 13 hectares. All-weather path and boardwalk across lake and reedbed suitable for wheelchairs.Car park. No dogs allowed.', 'Free, beautiful walks.', 'Limited car parking'),
(13, 'Brighton Fishing Museum', 50.82, -0.142012, 'Free, Museum, Indoor', 'jessicapetitfishingmuseum@gmail.com', '["01273 723064"]', '["201 Kings Road","Brighton"]', 'BN1 1NB', '["Free"]', '["Open 7 days a week"]', '3.0', 'The Brighton Fishing Museum is located a short distance to the west of Brighton Pier in Brighton, Sussex, England. It opened in 1992 in the area known as the Fishing District in one of the arches on the Kings Road.', '', 'Full disabled access', 'Free', 'Very small'),
(14, 'Adur Recreation Ground', 50.8311, -0.2843, 'Free, Outdoor, Park,', NULL, '[""]', '["Brighton Road","Shoreham-by-Sea","West Sussex",""]', 'BN43 5LT', '["Free"]', '[""]', '3.0', 'Large open space next to Adur River and Shoreham Airport. Multi-use site for events, car-boot sales, BMX track, model car club, two free carparks, outdoor centre and play area. Used by local residents, children and parents. An area of natural beauty with Dogs Trust as neighbours and lovely views in all directions.', '', '', '', '');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
