-- MySQL dump 10.13  Distrib 5.5.37, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: finances
-- ------------------------------------------------------
-- Server version	5.5.37-0+wheezy1-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `accountType`
--
USE `my_financials`;

DROP TABLE IF EXISTS `accountType`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accountType` (
  `atid` int(3) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) DEFAULT NULL,
  `sortOrder` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`atid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accountType`
--

LOCK TABLES `accountType` WRITE;
/*!40000 ALTER TABLE `accountType` DISABLE KEYS */;
INSERT INTO `accountType` VALUES (1,'Checking',1),(2,'Savings',2),(3,'Currency',3),(4,'Mortgage',5),(5,'Credit Cards',6),(6,'Loan',7),(7,'Brokerage',4);
/*!40000 ALTER TABLE `accountType` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `accounts`
--

DROP TABLE IF EXISTS `accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accounts` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `initialBalance` double(10,2) DEFAULT '0.00',
  `balance` double(10,2) DEFAULT NULL,
  `entityId` int(8) NOT NULL,
  `accountType` tinyint(2) DEFAULT '0',
  `assetId` int(8) DEFAULT NULL,
  `liquid` tinyint(1) DEFAULT '1',
  `active` int(1) DEFAULT '1',
  `hasLoanInterest` tinyint(1) DEFAULT '0',
  `sortOrder` int(3) DEFAULT '999',
  `notes` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `assets`
--

DROP TABLE IF EXISTS `assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assets` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) DEFAULT NULL,
  `categoryId` int(12) DEFAULT NULL,
  `initialValue` double(10,2) DEFAULT NULL,
  `currentValue` double(10,2) DEFAULT NULL,
  `liquid` tinyint(1) DEFAULT '0',
  `notes` text,
  `countryId` int(3) DEFAULT NULL,
  `picture` varchar(256) DEFAULT NULL,
  `sold` tinyint(1) DEFAULT '0',
  `datePurchased` date DEFAULT NULL,
  `dateSold` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `categoryId` (`categoryId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `categories` VALUES (1,'Auto Insurance'),(2,'Auto Maintenance'),(3,'Auto Registration'),(4,'Auto Repair'),(5,'Automobiles'),(6,'Bedding'),(7,'Beer'),(8,'Books'),(9,'Cell Phone'),(10,'Cleaning Supplies'),(11,'Coffee'),(12,'Computers / Electronics'),(13, 'Concerts'),(14,'Credit Card Fee'),(15,'Dental Care'),(16,'Dining Out'),(17,'Education'),(18,'Electricity'),(19,'Events'),(20,'Food'),(21,'Furnishings'),(22,'Gasoline'),(23,'Gifts'),(24,'Groceries'),(25,'Gym'),(26,'Haircut'),(27,'Healthcare'),(28,'Home Needs'),(29,'Income'),(30,'Income Tax'),(31,'Insurance'),(32,'Interest'),(33,'Internet'),(34,'Investment'),(35,'Kitchen Equipment'),(36,'Laundry'),(37,'Leisure'),(38,'Liquor'),(39,'Lodging'),(40,'Magazines'),(41,'Massage'),(42,'Membership'),(43,'Miscellaneous'),(44,'Movies'),(45,'Parking'),(46,'Personal Care'),(47,'Pets'),(48,'Property Tax'),(49,'Rent'),(50,'Renters Insurance'),(51,'Salary'),(52,'Stamps'),(53,'Supplements'),(54,'Taxes'),(55,'Tea'),(56,'Television'),(57,'Tools'),(58,'Transportation'),(59,'Travel'),(60,'Video Games'),(61,'Water'),(62,'Wine'),(63,'Yoga');
--
-- Table structure for table `countries`
--

DROP TABLE IF EXISTS `countries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `countries` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL,
  `abbreviation` varchar(3) DEFAULT NULL,
  `continent` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `countries`
--

LOCK TABLES `countries` WRITE;
/*!40000 ALTER TABLE `countries` DISABLE KEYS */;
INSERT INTO `countries` VALUES (207,'No country','',0),(1,'United States of America','us',6),(2,'Canada','ca',6),(3,'Mexico','mx',6),(4,'Belgium','be',5),(5,'Germany','de',5),(6,'The Netherlands','nl',5),(7,'Italy','it',5),(8,'Switzerland','ch',5),(10,'France','fr',5),(11,'Spain','es',5),(12,'Japan','jp',3),(13,'Sweden','se',5),(14,'Norway','no',5),(15,'Denmark','dk',5),(16,'China','cn',3),(17,'Russia','ru',5),(18,'India','in',3),(19,'United Kingdom','uk',5),(20,'Liechtenstein','li',5),(21,'Hong Kong','hk',3),(22,'Jamaica','jm',6),(23,'Philippines','ph',3),(24,'Peru','pe',7),(25,'Poland','pl',5),(26,'Portugal','pt',5),(27,'Panama','pa',6),(28,'Pakistan','pk',3),(29,'Saudi Arabia','sa',3),(30,'Scotland','sct',5),(31,'Singapore','sg',3),(32,'South Africa','za',1),(33,'Romania','ro',5),(34,'Colombia','co',7),(35,'Finland','fi',5),(36,'Mongolia','mn',3),(37,'Nepal','np',3),(38,'New Zealand','nz',4),(39,'Australia','au',4),(40,'Austria','at',5),(41,'Belize','bz',6),(42,'Brazil','br',7),(43,'Argentina','ar',7),(44,'Bahamas','bs',6),(45,'Chile','cl',7),(46,'Democratic Republic of the Congo','cd',1),(47,'Costa Rica','cr',6),(48,'Cuba','cu',6),(49,'Egypt','eg',1),(50,'El Salvador','sv',6),(51,'Ethiopia','et',1),(52,'Georgia','ge',3),(53,'Greece','gr',5),(54,'Greenland','gl',6),(55,'Guatemala','gt',6),(56,'Haiti','ht',6),(57,'Iceland','is',5),(59,'Iraq','iq',3),(60,'Ireland','ie',5),(61,'Israel','il',3),(62,'Jordan','jo',3),(63,'Kenya','ke',1),(64,'Latvia','lv',5),(65,'Luxembourg','lu',5),(66,'Madagascar','mg',1),(67,'Mali','ml',1),(68,'Morocco','ma',1),(69,'Namibia','na',1),(70,'Bolivia','bo',7),(71,'Paraguay','py',7),(72,'Uruguay','uy',7),(73,'Ecuador','ec',7),(74,'Venezuela','ve',7),(75,'Guyana','gy',7),(76,'Suriname','sr',7),(77,'French Guiana','gf',7),(78,'Trinidad and Tobago','tt',6),(79,'Honduras','hn',6),(80,'Nicaragua','ni',6),(81,'Puetro Rico','pr',6),(82,'Dominica','dm',6),(83,'Czech Republic','cz',5),(84,'Bermuda','bm',6),(85,'Sri Lanka','lk',3),(86,'Afganistan','af',3),(87,'Albania','al',5),(88,'Algeria','ag',1),(89,'Angola','an',1),(90,'Armenia','am',5),(91,'Aruba','au',6),(92,'Bulgaria','bg',5),(93,'Bangladesh','bd',3),(95,'Botswana','bw',1),(96,'Bahrain','bh',3),(97,'Bhutan','bh',3),(98,'Belarus','by',3),(100,'Chad','td',1),(101,'Croatia','hr',5),(103,'Andorra','ad',5),(104,'Antigua and Barbuda','ag',6),(105,'Azerbaijan','az',3),(106,'Barbados','bb',6),(107,'Benin','bj',1),(108,'Bosnia and Herzegovina','ba',5),(109,'Burkina Faso','bf',1),(110,'Burundi','bi',1),(111,'Cambodia','kh',3),(112,'Brunei','bn',1),(113,'Cameroon','cm',1),(114,'Cape Verde','cv',1),(115,'Central African Republic','cf',1),(116,'Comoros','km',1),(117,'Republic of the Congo','cg',1),(118,'Côte d\'Ivoire','ci',1),(119,'Cyprus','cy',5),(120,'Djibouti','dj',1),(121,'Dominican Republic','do',6),(122,'Timor-Leste','tl',4),(123,'Equatorial Guinea','gq',1),(124,'Eritrea','er',1),(125,'Estonia','ee',5),(126,'Fiji','fj',4),(127,'Oman','om',3),(128,'Qatar','qa',3),(129,'Rwanda','rw',1),(130,'Yemen','ye',3),(131,'Zambia','zm',1),(132,'Zimbabwe','zw',1),(133,'Vanatu','vu',4),(134,'Vatican City','vi',5),(135,'Vietnam','vn',3),(136,'Gabon','ga',1),(137,'Gambia','gm',1),(138,'Ghana','gh',1),(139,'Grenada','gd',1),(140,'Guinea','gn',1),(141,'Guinea-Bissau','gw',1),(142,'Hungary','hu',3),(143,'Indonesia','id',4),(144,'Iran','ir',3),(145,'Kazakhstan','kz',3),(146,'Kiribati','ki',4),(147,'North Korea','kp',3),(148,'South Korea','kr',3),(149,'Kosovo','ko',5),(150,'Kuwait','kw',3),(151,'Kyrgyzstan','kg',3),(152,'Laos','la',3),(153,'Lebanon','lb',3),(154,'Liberia','lr',1),(155,'Libya','ly',1),(156,'Lithuania','lt',5),(157,'Lesotho','ls',1),(158,'Macedonia','mk',5),(159,'Malawi','mw',1),(160,'Malaysia','my',3),(161,'Maldives','mv',3),(162,'Malta','mt',5),(163,'Marshall Islands','mh',4),(164,'Mauritania','mr',1),(165,'Federated States of Micronesia','fm',4),(166,'Moldova','md',5),(167,'Monaco','mc',5),(168,'Montenegro','me',5),(169,'Mozambique','mz',1),(170,'Myanmar','mm',3),(171,'Nauru','nr',4),(172,'Niger','ne',1),(173,'Nigeria','ng',1),(174,'Palau','pa',4),(175,'Palestine','ps',3),(176,'Papua New Guinea','pg',4),(177,'Uganda','ug',1),(178,'Ukraine','ua',5),(179,'United Arab Emirates','ae',3),(180,'Uzbekistan','uz',3),(181,'Taiwan','tw',3),(182,'Tajikistan','tj',3),(183,'Tanzania','tz',1),(184,'Thailand','th',3),(185,'Togo','tg',1),(186,'Tonga','to',4),(187,'Tunisia','tn',1),(188,'Turkey','tr',3),(189,'Turkmenistan','tm',3),(190,'Tuvalu','tv',4),(191,'Saint Kitts and Nevis','kn',6),(192,'Saint Lucia','lc',6),(193,'Saint Vincent and the Grenadines','vc',6),(194,'Samoa','ws',4),(195,'San Marino','sm',5),(196,'São Tomé and Príncipe','st',1),(197,'Senegal','sn',1),(198,'Serbia','sk',5),(199,'Seychelles','sc',1),(200,'Sierra Leone','sl',1),(201,'Slovakia','sk',5),(202,'Slovenia','si',5),(203,'Solomon Islands','sb',4),(204,'Somalia','so',1),(205,'Sudan','sd',1),(206,'Wales','wal',5);
/*!40000 ALTER TABLE `countries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `entities`
--

DROP TABLE IF EXISTS `entities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `entities` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gasMileage`
--

DROP TABLE IF EXISTS `gasMileage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gasMileage` (
  `entryId` int(8) NOT NULL AUTO_INCREMENT,
  `assetId` int(8) NOT NULL DEFAULT '0',
  `transactionId` int(12) NOT NULL DEFAULT '0',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `gasPrice` double(7,3) DEFAULT NULL,
  `gasPumped` double(7,3) DEFAULT NULL,
  `amount` double(10,2) DEFAULT NULL,
  `mileage` int(8) DEFAULT NULL,
  PRIMARY KEY (`entryId`),
  KEY `transactionId` (`transactionId`),
  KEY `assetId` (`assetId`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `loanDetailUpdates`
--

DROP TABLE IF EXISTS `loanDetailUpdates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `loanDetailUpdates` (
  `accountId` int(3) DEFAULT NULL,
  `rate` double(6,3) DEFAULT NULL,
  `payment` double(8,2) DEFAULT NULL,
  `date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `loanDetails`
--

DROP TABLE IF EXISTS `loanDetails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `loanDetails` (
  `accountId` int(3) DEFAULT NULL,
  `purchasePrice` double(12,2) DEFAULT NULL,
  `downPayment` double(12,2) DEFAULT NULL,
  `rate` double(6,3) DEFAULT NULL,
  `term` int(2) DEFAULT NULL,
  `payment` double(8,2) DEFAULT NULL,
  `firstPayment` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `monthlyCashflow`
--

DROP TABLE IF EXISTS `monthlyCashflow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `monthlyCashflow` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) DEFAULT NULL,
  `type` enum('Income','Expense') DEFAULT NULL,
  `amount` decimal(8,2) DEFAULT NULL,
  `variableAmount` tinyint(1) DEFAULT NULL,
  `variableAmountCategoryId` int(12) DEFAULT NULL,
  `variableAmountLookBehind` int(3) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `networth`
--

DROP TABLE IF EXISTS `networth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `networth` (
  `id` int(7) NOT NULL AUTO_INCREMENT,
  `value` double(10,2) DEFAULT NULL,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uri` varchar(512) NOT NULL,
  `controller` varchar(128) NOT NULL,
  `action` varchar(120) DEFAULT NULL,
  `title` varchar(512) DEFAULT NULL,
  `applyView` tinyint(1) NOT NULL DEFAULT '1',
  `loginRequired` tinyint(1) DEFAULT NULL,
  `queryString` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=223 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages`
--

LOCK TABLES `pages` WRITE;
/*!40000 ALTER TABLE `pages` DISABLE KEYS */;
INSERT INTO `pages` VALUES (176,'/','User','index','My Finances',1,1,''),(177,'/login','User','login','Login Page',1,0,''),(178,'/user/logout','User','logout','User Logout',0,1,''),(179,'/loginSubmit','User','loginSubmit','User Login Submit',0,0,''),(180,'/account/show-transactions','Account','showTransactions','Transaction Listing',1,1,''),(181,'/account/save','Account','save','Account Save',0,1,''),(182,'/transaction/search','Transaction','search','Transaction Search',1,1,''),(183,'/entity/search','Entity','search','Entity Search (AJAX)',0,1,''),(184,'/category/search','Category','search','Category Search (AJAX)',0,1,''),(185,'/tag/add','Tag','add','Tag Add',0,1,''),(186,'/tag/search','Tag','search','Tag Search (AJAX)',0,1,''),(187,'/asset/search','Asset','search','Asset Search (AJAX)',0,1,''),(188,'/asset/delete','Asset','delete','Asset Delete (AJAX)',0,1,''),(189,'/asset/save','Asset','save','Asset Save (AJAX)',0,1,''),(190,'/assets/','Asset','listAll','Asset Listing',1,1,''),(191,'/transaction/delete','Transaction','delete','Transaction Delete (AJAX)',0,1,''),(192,'/transaction/save','Transaction','save','Transaction Save (AJAX)',0,1,''),(193,'/report/asset-allocation','Report','assetAllocation','Asset Allocation Report',1,1,''),(194,'/report/liquid-asset-allocation','Report','liquidAssetAllocation','Liquid Asset Allocation Report',1,1,''),(195,'/report/expenses-by-category','Report','expensesByCategory','Expenses By Category',1,1,''),(196,'/report/expenses-by-entity','Report','expensesByEntity','Expenses By Entity',1,1,''),(197,'/report/expenses-by-tag','Report','expensesByTag','Expenses By Tag',1,1,''),(198,'/automobile/maintenance-info','Automobile','maintenanceInfo','Automobile Maintenance Info AJAX',0,1,''),(199,'/automobile/gas-mileage-info','Automobile','gasMileageInfo','Automobile Gas Mileage Info AJAX',0,1,''),(200,'/precious-metal-update-prices','PreciousMetal','updatePrices','Precious Metal Price Update',0,0,''),(201,'/automobiles','Automobile','index','My Automobiles',1,1,''),(202,'/assets','Asset','listAll','Asset Listing',1,1,''),(203,'/automobile/gas-mileage','Automobile','gasMileage','Gas Mileage Report',1,1,''),(204,'/automobile/maintenance','Automobile','maintenance','Vehicle Maintenance Report',1,1,'view=maintenance'),(205,'/automobile/log','Automobile','maintenance','Vehicle Log',1,1,'view=log'),(206,'/report/orphaned-transactions','Report','orphanedTransactions','Orphaned Transactions',1,1,''),(207,'/monthly-budget','MonthlyBudget','index','Monthly Budget',1,1,''),(208,'/automobile/tax-info','Automobile','taxInfo','Automobile Tax Info AJAX',0,1,''),(209,'/automobile/insurance-info','Automobile','insuranceInfo','Automobile Insurance Info AJAX',0,1,''),(210,'/automobile/tco','Automobile','tco','Automobile Total Cost of Ownership (TCO) Report',1,1,''),(211,'/automobile/update-maintenance-notes','Automobile','updateMaintenanceNotes','Maintenance Notes Update',0,1,''),(212,'/precious-metal-asset-info','PreciousMetal','assetInfo','Precious Metal Asset Info AJAX',0,1,''),(213,'/session-update','User','sessionUpdate','Session Update',0,1,''),(214,'/mortgage-calculator','MortgageCalculator','index','Mortgage Calculator',1,1,''),(215,'/report/networth','Report','networth','Networth Report',1,1,''),(216,'/update-networth','Networth','update','Update Net Worth',0,0,''),(217,'/report/gas-prices','Report','gasPrices','Gas Price Report',1,1,''),(218,'/update-stock-prices','Stocks','updatePrices','Update Stock Prices',0,0,''),(219,'/account/assets','Account','assets','Account Assets Overview',1,1,''),(220,'/categories','Category','listAll','Category Listing',1,1,''),(221,'/entities','Entity','listAll','Entity Listing',1,1,''),(222,'/tags','Tag','listAll','Tag Listing',1,1,'');
/*!40000 ALTER TABLE `pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plugins`
--

DROP TABLE IF EXISTS `plugins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plugins` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) DEFAULT NULL,
  `enabled` tinyint(1) DEFAULT '0',
  `script` varchar(128) DEFAULT NULL,
  `classname` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plugins`
--

LOCK TABLES `plugins` WRITE;
/*!40000 ALTER TABLE `plugins` DISABLE KEYS */;
INSERT INTO `plugins` VALUES (1,'Automobile Plugin',1,'automobiles','Automobile'),(2,'Precious Metals Plugin',1,'precious-metals','PreciousMetals'),(3,'Automobile Gas Mileage',0,'gas-mileage','GasMileage');
/*!40000 ALTER TABLE `plugins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `preciousMetalAssets`
--

DROP TABLE IF EXISTS `preciousMetalAssets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `preciousMetalAssets` (
  `assetId` int(8) DEFAULT NULL,
  `quantity` int(4) DEFAULT NULL,
  `typeId` int(3) DEFAULT NULL,
  `automaticPricing` tinyint(1) DEFAULT '1',
  UNIQUE KEY `assetId` (`assetId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `preciousMetalPrices`
--

DROP TABLE IF EXISTS `preciousMetalPrices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `preciousMetalPrices` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `metal` enum('Gold','Silver') DEFAULT NULL,
  `price` decimal(8,2) DEFAULT NULL,
  `timestamp` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `preciousMetalTypes`
--

DROP TABLE IF EXISTS `preciousMetalTypes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `preciousMetalTypes` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) DEFAULT NULL,
  `year` int(4) DEFAULT NULL,
  `metal` enum('Silver','Gold') DEFAULT NULL,
  `weight` decimal(8,3) DEFAULT NULL,
  `purity` decimal(4,4) DEFAULT NULL,
  `premium` double(8,2) DEFAULT NULL,
  `averagePrice` decimal(8,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=150 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `stockAssets`
--

DROP TABLE IF EXISTS `stockAssets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockAssets` (
  `accountId` int(5) NOT NULL,
  `ticker` varchar(32) NOT NULL DEFAULT '',
  `qty` double(10,2) DEFAULT NULL,
  `avgPrice` double(10,2) DEFAULT NULL,
  `total` double(10,2) DEFAULT NULL,
  `currentValue` double(12,2) DEFAULT NULL,
  `priceUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ticker`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stockPrices`
--

DROP TABLE IF EXISTS `stockPrices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockPrices` (
  `ticker` varchar(32) DEFAULT NULL,
  `price` double(10,2) DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stockTransactions`
--

DROP TABLE IF EXISTS `stockTransactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockTransactions` (
  `transactionId` int(12) NOT NULL DEFAULT '0',
  `transactionType` enum('Purchase','Sale') DEFAULT NULL,
  `ticker` varchar(32) DEFAULT NULL,
  `qty` double(10,2) DEFAULT NULL,
  `price` double(10,2) DEFAULT NULL,
  `fees` double(10,2) DEFAULT NULL,
  `total` double(10,2) DEFAULT NULL,
  PRIMARY KEY (`transactionId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tagMapping`
--

DROP TABLE IF EXISTS `tagMapping`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tagMapping` (
  `tagId` int(12) NOT NULL,
  `transactionId` int(12) NOT NULL,
  UNIQUE KEY `tagId` (`tagId`,`transactionId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tags` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `transactionCategory`
--

DROP TABLE IF EXISTS `transactionCategory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transactionCategory` (
  `transactionId` int(12) DEFAULT NULL,
  `categoryId` int(8) DEFAULT NULL,
  `amount` double(10,2) DEFAULT NULL,
  KEY `transactionId` (`transactionId`),
  KEY `categoryId` (`categoryId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transactions` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `accountId` int(8) DEFAULT NULL,
  `entityId` int(8) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `amount` double(10,2) DEFAULT NULL,
  `tax` double(10,2) DEFAULT NULL,
  `interest` double(10,2) DEFAULT '0.00',
  `transactionNumber` int(12) DEFAULT NULL,
  `pairedTransaction` int(12) DEFAULT '0',
  `notes` text,
  `transactionType` enum('Withdrawal','Deposit','Transfer') DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `accountBalance` double(14,2) DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `accountId` (`accountId`),
  KEY `entityId` (`entityId`),
  KEY `date` (`date`),
  KEY `pairedTransaction` (`pairedTransaction`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(2) NOT NULL,
  `name` varchar(32) NOT NULL,
  `password` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vehicleInfo`
--

DROP TABLE IF EXISTS `vehicleInfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vehicleInfo` (
  `assetId` int(8) NOT NULL,
  `vin` varchar(64) DEFAULT NULL,
  `startingOdometer` int(8) DEFAULT NULL,
  `maintenanceNotes` text,
  PRIMARY KEY (`assetId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vehicleInsurance`
--

DROP TABLE IF EXISTS `vehicleInsurance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vehicleInsurance` (
  `assetId` int(8) DEFAULT NULL,
  `transactionId` int(12) DEFAULT NULL,
  `amount` double(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vehicleMaintenance`
--

DROP TABLE IF EXISTS `vehicleMaintenance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vehicleMaintenance` (
  `assetId` int(8) NOT NULL DEFAULT '0',
  `transactionId` int(12) NOT NULL DEFAULT '0',
  `amount` double(10,2) DEFAULT NULL,
  `datePerformed` date DEFAULT NULL,
  `mileage` int(8) DEFAULT NULL,
  `notes` text,
  PRIMARY KEY (`assetId`,`transactionId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `vehicleTax`
--

DROP TABLE IF EXISTS `vehicleTax`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vehicleTax` (
  `assetId` int(8) DEFAULT NULL,
  `transactionId` int(12) DEFAULT NULL,
  `amount` double(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;


/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
