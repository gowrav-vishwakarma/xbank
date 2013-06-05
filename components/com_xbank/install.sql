/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50133
Source Host           : localhost:3306
Source Database       : jaya_soft

Target Server Type    : MYSQL
Target Server Version : 50133
File Encoding         : 65001

Date: 2011-10-22 16:44:05
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for `#__xaccess_system`
-- ----------------------------
DROP TABLE IF EXISTS `#__xaccess_system`;
CREATE TABLE `#__xaccess_system` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_access_system_staff1` (`staff_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of #__xaccess_system
-- ----------------------------

-- ----------------------------
-- Table structure for `#__xaccounts`
-- ----------------------------
DROP TABLE IF EXISTS `#__xaccounts`;
CREATE TABLE `#__xaccounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agents_id` int(11) DEFAULT '0',
  `OpeningBalanceDr` double DEFAULT NULL,
  `OpeningBalanceCr` double DEFAULT NULL,
  `ClosingBalance` double DEFAULT '0',
  `CurrentBalanceDr` double DEFAULT '0' COMMENT '	',
  `CurrentInterest` varchar(45) DEFAULT '0',
  `ActiveStatus` tinyint(1) DEFAULT '1',
  `Nominee` varchar(45) DEFAULT NULL,
  `NomineeAge` smallint(6) DEFAULT NULL,
  `RelationWithNominee` varchar(45) DEFAULT NULL,
  `MinorNomineeDOB` varchar(20) DEFAULT NULL,
  `MinorNomineeParentName` varchar(45) DEFAULT NULL,
  `ModeOfOperation` varchar(6) DEFAULT 'Self',
  `member_id` int(11) NOT NULL,
  `DefaultAC` tinyint(1) DEFAULT '0',
  `schemes_id` int(11) NOT NULL,
  `AccountNumber` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `branch_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `CurrentBalanceCr` double DEFAULT '0',
  `LastCurrentInterestUpdatedAt` datetime DEFAULT NULL,
  `InterestToAccount` int(11) DEFAULT NULL,
  `RdAmount` double DEFAULT '0',
  `LoanInsurranceDate` datetime DEFAULT NULL,
  `dealer_id` int(11) NOT NULL,
  `LockingStatus` tinyint(1) DEFAULT '0',
  `LoanAgainstAccount` int(11) DEFAULT NULL,
  `affectsBalanceSheet` tinyint(1) DEFAULT '0',
  `MaturedStatus` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `AccountNumber_UNIQUE` (`AccountNumber`),
  KEY `fk_accounts_agents1` (`agents_id`),
  KEY `fk_accounts_member1` (`member_id`),
  KEY `fk_accounts_schemes1` (`schemes_id`),
  KEY `fk_accounts_branch1` (`branch_id`),
  KEY `fk_accounts_staff1` (`staff_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Various Accounts for users';

-- ----------------------------
-- Records of #__xaccounts
-- ----------------------------

-- ----------------------------
-- Table structure for `#__xagents`
-- ----------------------------
DROP TABLE IF EXISTS `jos_xagents`;
CREATE TABLE `jos_xagents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `ActiveStatus` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `AccountNumber` varchar(100) DEFAULT NULL,
  `Gaurantor1Name` varchar(100) DEFAULT NULL,
  `Gaurantor1FatherHusbandName` varchar(100) DEFAULT NULL,
  `Gaurantor1Address` varchar(200) DEFAULT NULL,
  `Gaurantor1Occupation` varchar(100) DEFAULT NULL,
  `Gaurantor2Name` varchar(100) DEFAULT NULL,
  `Gaurantor2FatherHusbandName` varchar(100) DEFAULT NULL,
  `Gaurantor2Address` varchar(200) DEFAULT NULL,
  `Gaurantor2Occupation` varchar(100) DEFAULT NULL,
  `Sponsor_id` int(11) DEFAULT NULL,
  `AgentCode` varchar(20) DEFAULT NULL,
  `Path` varchar(200) DEFAULT NULL,
  `LegCount` int(11) DEFAULT '0',
  `Rank` int(11) DEFAULT '0',
  `Tree_id` int(11) DEFAULT '0',
  `BusinessCreditPoints` int(11) DEFAULT '0',
  `Rank_1_Count` int(11) DEFAULT '0',
  `Rank_2_Count` int(11) DEFAULT '0',
  `Rank_3_Count` int(11) DEFAULT '0',
  `Rank_4_Count` int(11) DEFAULT '0',
  `Rank_5_Count` int(11) DEFAULT '0',
  `Rank_6_Count` int(11) DEFAULT '0',
  `Rank_7_Count` int(11) DEFAULT '0',
  `Rank_8_Count` int(11) DEFAULT '0',
  `Rank_9_Count` int(11) DEFAULT '0',
  `Rank_10_Count` int(11) DEFAULT '0',
  `Rank_11_Count` int(11) DEFAULT '0',
  `Rank_12_Count` int(11) DEFAULT '0',
  `Rank_13_Count` int(11) DEFAULT '0',
  `Rank_14_Count` int(11) DEFAULT '0',
  `Rank_15_Count` int(11) DEFAULT '0',
  `Rank_16_Count` int(11) DEFAULT '0',
  `Rank_17_Count` int(11) DEFAULT '0',
  `Rank_18_Count` int(11) DEFAULT '0',
  `Rank_19_Count` int(11) DEFAULT '0',
  `Rank_20_Count` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_agents_member1` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- ----------------------------
-- Records of #__xagents
-- ----------------------------

-- ----------------------------
-- Table structure for `#__xbalance_sheet`
-- ----------------------------
DROP TABLE IF EXISTS `#__xbalance_sheet`;
CREATE TABLE `#__xbalance_sheet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Head` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of #__xbalance_sheet
-- ----------------------------
INSERT INTO #__xbalance_sheet VALUES ('1', 'Liabilities');
INSERT INTO #__xbalance_sheet VALUES ('2', 'Assets');
INSERT INTO #__xbalance_sheet VALUES ('3', 'Capital Account');
INSERT INTO #__xbalance_sheet VALUES ('4', 'Expenses');
INSERT INTO #__xbalance_sheet VALUES ('5', 'Income');
INSERT INTO #__xbalance_sheet VALUES ('6', 'Suspence Account');
INSERT INTO #__xbalance_sheet VALUES ('7', 'Fixed Assets');
INSERT INTO #__xbalance_sheet VALUES ('8', 'Branch/Divisions');
INSERT INTO #__xbalance_sheet VALUES ('9', 'Current Liabilities');

-- ----------------------------
-- Table structure for `#__xbank_holidays`
-- ----------------------------
DROP TABLE IF EXISTS `#__xbank_holidays`;
CREATE TABLE `#__xbank_holidays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `HolidayDate` date DEFAULT NULL,
  `month` int(11) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `branch_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_bank_holidays_branch1` (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of #__xbank_holidays
-- ----------------------------

-- ----------------------------
-- Table structure for `#__xbranch`
-- ----------------------------
DROP TABLE IF EXISTS `#__xbranch`;
CREATE TABLE `#__xbranch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(45) NOT NULL,
  `Address` text,
  `Code` varchar(3) DEFAULT NULL,
  `PerformClosings` tinyint(4) DEFAULT '1',
  `SendSMS` tinyint(4) DEFAULT NULL,
  `published` tinyint(4) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `Name_UNIQUE` (`Name`),
  UNIQUE KEY `Code_UNIQUE` (`Code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='The collection of Branches Information';

-- ----------------------------
-- Records of #__xbranch
-- ----------------------------
-- INSERT INTO #__xbranch VALUES ('1', 'Default', 'Address', 'DFL', null, null, '1');

-- ----------------------------
-- Table structure for `#__xcategory`
-- ----------------------------
DROP TABLE IF EXISTS `#__xcategory`;
CREATE TABLE `#__xcategory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(45) DEFAULT NULL,
  `Description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of #__xcategory
-- ----------------------------

-- ----------------------------
-- Table structure for `#__xcideveloper_projects`
-- ----------------------------
DROP TABLE IF EXISTS `#__xcideveloper_projects`;
CREATE TABLE `#__xcideveloper_projects` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `component` varchar(50) CHARACTER SET utf8 NOT NULL,
  `com_name` varchar(50) CHARACTER SET utf8 NOT NULL,
  `extension_type` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `params` text CHARACTER SET utf8,
  `published` tinyint(4) DEFAULT NULL,
  `manifest` text CHARACTER SET utf8,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of #__xcideveloper_projects
-- ----------------------------
INSERT INTO #__xcideveloper_projects VALUES ('1', 'xbank', 'xbank', 'com', null, '1', '<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n<!DOCTYPE install PUBLIC \"-//Joomla! 1.5//DTD component 1.0//EN\" \"http://joomla.org/xml/dtd/1.5/component-install.dtd\">\r\n<install type=\"component\" version=\"1.5.0\">\r\n  <name>xbank</name>\r\n  <creationDate>01-July-2010</creationDate>\r\n  <author>Xavoc International</author>\r\n  <authorEmail>gowravvishwakarma@gmail.com</authorEmail>\r\n  <authorUrl>http://www.xavoc.com</authorUrl>\r\n  <copyright>Copyright (C) 2011 Xavoc International. All rights reserved.</copyright>\r\n  <license>http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL</license>\r\n  <version>2.0.0</version>\r\n<releaseDate>{releaseDate}</releaseDate>\r\n  <description>xBanking Software for Co-Operative Societies</description>\r\n  <installfile/>\r\n  <uninstallfile/>\r\n  <install>\r\n    <sql>\r\n  <files folder=\"admin/sql\" driver=\"mysql\" charset=\"utf8\">install.sql</files>\r\n    </sql>\r\n  </install>\r\n  <uninstall>\r\n    <sql>\r\n  <files folder=\"admin/sql\" driver=\"mysql\" charset=\"utf8\">uninstall.sql</files>\r\n    </sql>\r\n  </uninstall>\r\n  <update>\r\n    <sql/>\r\n  </update>\r\n  <files folder=\"site\">\r\n    <folder>assets</folder>\r\n    <folder>cache</folder>\r\n    <folder>config</folder>\r\n    <folder>controllers</folder>\r\n    <folder>core</folder>\r\n    <folder>errors</folder>\r\n    <folder>helpers</folder>\r\n    <folder>hooks</folder>\r\n    <folder>language</folder>\r\n    <folder>libraries</folder>\r\n    <folder>logs</folder>\r\n    <folder>models</folder>\r\n    <folder>third_party</folder>\r\n    <folder>views</folder>\r\n    <filename>index.html</filename>\r\n    <filename>xbank.php</filename>\r\n  </files>\r\n  <languages folder=\"site/language\">\r\n    <language tag=\"index\">english/index.html</language>\r\n  </languages>\r\n  <administration>\r\n    <menu img=\"component\" link=\"option=com_xbank\">xbank</menu>\r\n    <files folder=\"admin\">\r\n      <folder>assets</folder>\r\n      <folder>cache</folder>\r\n      <folder>config</folder>\r\n      <folder>controllers</folder>\r\n      <folder>core</folder>\r\n      <folder>errors</folder>\r\n      <folder>helpers</folder>\r\n      <folder>hooks</folder>\r\n      <folder>language</folder>\r\n      <folder>libraries</folder>\r\n      <folder>logs</folder>\r\n      <folder>models</folder>\r\n     <folder>signatures</folder>\r\n     <folder>third_party</folder>\r\n      <folder>views</folder>\r\n	  <folder>system</folder>\r\n      <filename>config.xml</filename>\r\n      <filename>index.html</filename>\r\n      <filename>xbank.php</filename>\r\n    <filename>install.sql</filename>\r\n    <filename>uninstall.sql</filename>\r\n    </files>\r\n    <languages folder=\"admin/language\">\r\n      <language tag=\"index\">english/index.html</language>\r\n    </languages>\r\n  </administration>\r\n</install>\r\n');

-- ----------------------------
-- Table structure for `#__xclosings`
-- ----------------------------
DROP TABLE IF EXISTS `#__xclosings`;
CREATE TABLE `#__xclosings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `daily` datetime DEFAULT NULL,
  `weekly` datetime DEFAULT NULL,
  `monthly` datetime DEFAULT NULL,
  `halfyearly` datetime DEFAULT NULL,
  `yearly` datetime DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_closings_branch1` (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of #__xclosings
-- ----------------------------

-- ----------------------------
-- Table structure for `#__xconfig`
-- ----------------------------
DROP TABLE IF EXISTS `#__xconfig`;
CREATE TABLE `#__xconfig` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Title` varchar(50) NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of #__xconfig
-- ----------------------------
INSERT INTO #__xconfig VALUES ('1', 'member', 'MemberRegistrationCharges=10\n');
INSERT INTO #__xconfig VALUES ('2', 'SavingAndCurrent', 'Default_Accounts=\n');
INSERT INTO #__xconfig VALUES ('3', 'Recurring', 'Default_Accounts=\n');

-- ----------------------------
-- Table structure for `#__xdealer`
-- ----------------------------
DROP TABLE IF EXISTS `#__xdealer`;
CREATE TABLE `#__xdealer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `DealerName` varchar(255) NOT NULL,
  `Address` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of #__xdealer
-- ----------------------------

-- ----------------------------
-- Table structure for `#__xdocuments`
-- ----------------------------
DROP TABLE IF EXISTS `#__xdocuments`;
CREATE TABLE `#__xdocuments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(45) DEFAULT NULL,
  `SavingAccount` tinyint(1) DEFAULT NULL,
  `FixedMISAccount` tinyint(1) DEFAULT NULL,
  `LoanAccount` tinyint(1) DEFAULT NULL,
  `RDandDDSAccount` tinyint(1) DEFAULT NULL,
  `CCAccount` tinyint(1) DEFAULT NULL,
  `OtherAccounts` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of #__xdocuments
-- ----------------------------

-- ----------------------------
-- Table structure for `#__xdocuments_submitted`
-- ----------------------------
DROP TABLE IF EXISTS `#__xdocuments_submitted`;
CREATE TABLE `#__xdocuments_submitted` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `accounts_id` int(11) NOT NULL,
  `documents_id` int(11) NOT NULL,
  `Description` text,
  PRIMARY KEY (`id`),
  KEY `fk_documents_submitted_documents1` (`documents_id`),
  KEY `fk_documents_submitted_accounts1` (`accounts_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of #__xdocuments_submitted
-- ----------------------------

-- ----------------------------
-- Table structure for `#__xevents`
-- ----------------------------
DROP TABLE IF EXISTS `#__xevents`;
CREATE TABLE `#__xevents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Event` varchar(45) DEFAULT NULL,
  `CodeSQL` text,
  `schemes_id` int(11) NOT NULL,
  `Sno` smallint(6) DEFAULT NULL,
  `Description` text,
  `ActiveStatus` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`,`schemes_id`),
  KEY `fk_events_schemes1` (`schemes_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of #__xevents
-- ----------------------------

-- ----------------------------
-- Table structure for `#__xitems`
-- ----------------------------
DROP TABLE IF EXISTS `#__xitems`;
CREATE TABLE `#__xitems` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(45) DEFAULT NULL,
  `Price` float DEFAULT NULL,
  `Description` text,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_items_category1` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of #__xitems
-- ----------------------------

-- ----------------------------
-- Table structure for `#__xlog`
-- ----------------------------
DROP TABLE IF EXISTS `#__xlog`;
CREATE TABLE `#__xlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Message` text,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `accounts_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_log_accounts1` (`accounts_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of #__xlog
-- ----------------------------

-- ----------------------------
-- Table structure for `#__xmember`
-- ----------------------------
DROP TABLE IF EXISTS `jos_xmember`;
CREATE TABLE `jos_xmember` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(45) DEFAULT NULL,
  `CurrentAddress` text,
  `FatherName` varchar(45) DEFAULT NULL,
  `Cast` varchar(45) DEFAULT NULL,
  `PermanentAddress` text,
  `Occupation` varchar(45) DEFAULT NULL,
  `Age` smallint(6) DEFAULT NULL,
  `Nominee` varchar(45) DEFAULT NULL,
  `RelationWithNominee` varchar(45) DEFAULT NULL,
  `NomineeAge` smallint(6) DEFAULT NULL,
  `Witness1Name` varchar(45) DEFAULT NULL,
  `Witness1FatherName` varchar(45) DEFAULT NULL,
  `Witness1Address` text,
  `Witness2Name` varchar(45) DEFAULT NULL,
  `Witness2FatherName` varchar(45) DEFAULT NULL,
  `Witness2Address` text,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `branch_id` int(11) NOT NULL,
  `PhoneNos` text,
  `PanNo` varchar(10) DEFAULT NULL,
  `IsMinor` tinyint(1) DEFAULT NULL,
  `MinorDOB` date DEFAULT NULL,
  `ParentName` varchar(45) DEFAULT NULL,
  `RelationWithParent` varchar(45) DEFAULT NULL,
  `ParentAddress` int(11) DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `netmember_id` int(11) DEFAULT NULL,
  `MemberCode` varchar(45) DEFAULT NULL,
  `DOB` datetime DEFAULT NULL,
  `FilledForm60` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Has the member filled form 60 if Pan Number not there',
  `IsCustomer` tinyint(4) DEFAULT '0',
  `IsMember` tinyint(4) DEFAULT '0',
  `CustomerCode` varchar(45) DEFAULT NULL,
  `parent_member_id` int(11) NOT NULL DEFAULT '0',
  `customer_created_at` datetime DEFAULT NULL,
  `OfficeAddress` varchar(200) DEFAULT NULL,
  `OfficePhoneNos` varchar(100) DEFAULT NULL,
  `Email` varchar(50) DEFAULT NULL,
  `BloodGroup` varchar(10) DEFAULT NULL,
  `MaritalStatus` varchar(20) DEFAULT NULL,
  `NumberOfChildren` int(11) DEFAULT NULL,
  `MarriageDate` date DEFAULT NULL,
  `HighestQualification` varchar(50) DEFAULT NULL,
  `OccupationDetails` varchar(50) DEFAULT NULL,
  `EmployerAddress` varchar(200) DEFAULT NULL,
  `SelfEmployeeDetails` varchar(100) DEFAULT NULL,
  `FamilyMonthlyIncome` varchar(10) DEFAULT NULL,
  `Bank` varchar(50) DEFAULT NULL,
  `Branch` varchar(50) DEFAULT NULL,
  `AccountNumber` varchar(30) DEFAULT NULL,
  `DebitCreditCardNo` varchar(45) DEFAULT NULL,
  `DebitCreditCardIssuingBank` varchar(50) DEFAULT NULL,
  `PassportNo` varchar(20) DEFAULT NULL,
  `PassportIssuedAt` varchar(50) DEFAULT NULL,
  `EmployerCard` tinyint(4) DEFAULT NULL,
  `Passport` tinyint(4) DEFAULT NULL,
  `PanCard` tinyint(4) DEFAULT NULL,
  `VoterIdCard` tinyint(4) DEFAULT NULL,
  `DrivingLicense` tinyint(4) DEFAULT NULL,
  `GovtArmyIdCard` tinyint(4) DEFAULT NULL,
  `RationCard` tinyint(4) DEFAULT NULL,
  `OtherDocument` tinyint(4) DEFAULT NULL,
  `DocumentDescription` varchar(100) DEFAULT NULL,
  `CameToKnowByNewspaper` tinyint(4) DEFAULT NULL,
  `CameToKnowByTelevision` tinyint(4) DEFAULT NULL,
  `CameToKnowByAdvertisement` tinyint(4) DEFAULT NULL,
  `CameToKnowByFriends` tinyint(4) DEFAULT NULL,
  `CameToKnowByFieldworker` tinyint(4) DEFAULT NULL,
  `OtherDetails` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `MemberCode` (`MemberCode`),
  KEY `fk_user_branch` (`branch_id`),
  KEY `fk_member_staff1` (`staff_id`)
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=latin1 COMMENT='Account Holders are the users of bank system';


-- ----------------------------
-- Records of #__xmember
-- ----------------------------
-- INSERT INTO #__xmember VALUES ('1', 'DFL Default', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, now(), now(), '1', null, null, null, null, null, null, null, '1', null);

-- ----------------------------
-- Table structure for `#__xpremiums`
-- ----------------------------
DROP TABLE IF EXISTS `#__xpremiums`;
CREATE TABLE `#__xpremiums` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `accounts_id` int(11) NOT NULL,
  `Amount` varchar(45) DEFAULT NULL,
  `Paid` tinyint(1) DEFAULT NULL,
  `Skipped` tinyint(1) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `PaidOn` datetime DEFAULT NULL,
  `AgentCommissionSend` tinyint(1) DEFAULT NULL,
  `AgentCommissionPercentage` double DEFAULT NULL,
  `DueDate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_premiums_accounts1` (`accounts_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of #__xpremiums
-- ----------------------------

-- ----------------------------
-- Table structure for `#__xreports`
-- ----------------------------
DROP TABLE IF EXISTS `#__xreports`;
CREATE TABLE `#__xreports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(45) DEFAULT NULL,
  `formFields` text,
  `CodeToRun` text,
  `Results` text,
  `CodeBeforeForm` text,
  `ReportTitle` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of #__xreports
-- ----------------------------
INSERT INTO #__xreports VALUES ('8', 'test1', '[{\"Type\":\"text\",\"Label\":\"Name\",\"Properties\":{\"name\":\"name\",\"class\":\"input req-string\"}},{\"Type\":\"textArea\",\"Label\":\"Address\",\"Properties\":{\"name\":\"address\"}},{\"Type\":\"submit\",\"Label\":\"Go\",\"Properties\":[]}]', '$result=$this->db->query(\"select now() as nDate, now(\\\"H:m:s\\\") as nTime\")->result();', '{\"nDate\":\"My Date\",\"nTime\":\"My Time\"}', null, null);
INSERT INTO #__xreports VALUES ('9', 'testing', '$form->open(\"one\",\"index.php?//mod_reports/generateReport/$reportID\")->text(\"Name\",\"name=\'name\' class=\'input req-string\'\")\n->lookupDB(\"Account number : \",\"name=\'AccountNumber\' class=\'input req-string\'\",\"index.php?//ajax/lookupDBDQL\",array(\"select\"=>\"a.*\",\"from\"=>\"Accounts a\",\"leftJoin\"=>\"a.Branch b\",\"where\"=>\"a.AccountNumber Like \'%\\$term%\'\",\"andWhere\"=>\"b.id=\'2\'\",\"limit\"=>\"10\"),array(\"AccountNumber\"),\"\")\n->submit(\"go\");', '$result=$this->db->query(\"select id, AccountNumber from accounts\")->result();', 'id=my ID, AccountNumber = Accnum', '/**/', null);
INSERT INTO #__xreports VALUES ('10', 'Day Book', '$form->open(\"one\",\"index.php?//mod_reports/generateReport/$reportID\")\n->dateBox(\"Transaction Date\",\"name=\'transactionDate\' class=\'input\'\")\n->submit(\"go\");', '$b=Branch::getCurrentBranch()->id;\n$result=$this->db->query(\"select t.Narration, t.amountDr, t.amountCr , t.created_at, t.voucher_no, a.AccountNumber, a.CurrentBalanceCr, a.CurrentBalanceDr  from transactions t join accounts a on t.accounts_id=a.id where t.branch_id=\".$b.\"  and t.created_at like \'\".inp(\"transactionDate\").\"%\'\")->result();', 'created_at=Date, Narration=Particulars, voucher_no=Vch No., amountDr=Debit amt., amountCr=Credit amt.', '/**/', 'DAY BOOK');
INSERT INTO #__xreports VALUES ('11', 'cash book', '$form->open(\"one\",\"index.php?//mod_reports/generateReport/$reportID\")\n->dateBox(\"Date From\",\"name=\'dateFrom\' class=\'input\'\")\n->dateBox(\"Date To\",\"name=\'dateTo\' class=\'input\'\")\n->submit(\"go\");', '$b=Branch::getCurrentBranch()->id; \n$result = $this->db->query(\" SELECT * FROM ( SELECT\n DRTransaction.updated_at as `Date`,CONCAT(\'TO  \', if(m.`Name` like \'%Default%\',a.AccountNumber,m.`Name`)) as Particulars,	DRTransaction.Narration as Narration,\nDRTransaction.voucher_no as Voucher_no,DRTransaction.amountDr AS Debit,\n\'\' as Credit \nFROM\n	transactions AS DRTransaction\n INNER JOIN transactions AS CRTransaction ON DRTransaction.voucher_no = CRTransaction.voucher_no \nINNER JOIN accounts ON DRTransaction.accounts_id = accounts.id\n INNER JOIN accounts a ON CRTransaction.accounts_id = a.id \nINNER JOIN member m ON m.id = a.member_id \nINNER JOIN schemes ON accounts.schemes_id = schemes.id \nWHERE\n	DRTransaction.branch_id = $b \nAND CRTransaction.branch_id = $b\n AND schemes.`Name` = \'\".CASH_ACCOUNT_SCHEME.\"\' \nAND DRTransaction.amountDr = CRTransaction.amountCr\n AND DRTransaction.amountDr > 0\n And DRTransaction.updated_at BETWEEN  \'\".inp(\"dateFrom\").\"\'  AND  \'\".inp(\"dateTo\").\"\' \n \nUNION\n	SELECT CRTransaction.updated_at as `Date`,CONCAT(\'BY  \', if(m.`Name` like \'%Default%\',a.AccountNumber,m.`Name`)) as Particulars,CRTransaction.Narration as Narration,\nCRTransaction.voucher_no as Voucher_no,\n\'\' as Debit,	CRTransaction.amountCr as Credit FROM	transactions AS DRTransaction\n	INNER JOIN transactions AS CRTransaction ON DRTransaction.voucher_no = CRTransaction.voucher_no \nINNER JOIN accounts ON CRTransaction.accounts_id = accounts.id\n INNER JOIN accounts a ON DRTransaction.accounts_id = a.id \nINNER JOIN member m ON m.id = a.member_id\n INNER JOIN schemes ON accounts.schemes_id = schemes.id \nWHERE\n	DRTransaction.branch_id = $b \nAND CRTransaction.branch_id = $b \nAND schemes.`Name` = \'\".CASH_ACCOUNT_SCHEME.\"\'\nAND DRTransaction.amountCr = CRTransaction.amountDr\n AND CRTransaction.amountCr > 0\nAND DRTransaction.updated_at BETWEEN \'\".inp(\"dateFrom\").\"\' AND \'\".inp(\"dateTo\").\"\' \n\nUNION\n\n	SELECT\n		DRTransaction.updated_at as `Date`,\n		CONCAT(\'TO  \', if(m.`Name` like \'%Default%\',accounts.AccountNumber,m.`Name`)) as Particulars,\n		DRTransaction.Narration as Narration,\n		DRTransaction.voucher_no as Voucher_no,\n		\'\' as Debit,\n		CRTransaction.amountCr as Credit\n	FROM\n		transactions AS DRTransaction\n	INNER JOIN transactions AS CRTransaction ON CRTransaction.voucher_no = DRTransaction.voucher_no\nINNER JOIN accounts ON DRTransaction.accounts_id = accounts.id\nINNER JOIN member m ON m.id = accounts.member_id\nINNER JOIN schemes ON accounts.schemes_id = schemes.id\nWHERE\n	DRTransaction.branch_id = $b\nAND CRTransaction.branch_id = $b\nAND schemes.`Name` = \'\".CASH_ACCOUNT_SCHEME.\"\'\nAND DRTransaction.amountDr > 0\nAND CRTransaction.amountCr > DRTransaction.amountDr\nAND DRTransaction.transaction_type_id = (select DISTINCT(tt.id) from transaction_type tt join transactions t on tt.id=t.transaction_type_id where tt.`Transaction` = \'\".TRA_JV_ENTRY.\"\')\nAND DRTransaction.updated_at BETWEEN \'\".inp(\"dateFrom\").\"\' AND \'\".inp(\"dateTo\").\"\' )\nAS cashbook \n\nORDER BY voucher_no\")->result();', 'Date=Date, Particulars=Particulars, Narration=Vch Type, Voucher_no=Vch Number, Debit=Debit, Credit=Credit', '/**/', 'CASH BOOK');

-- ----------------------------
-- Table structure for `#__xschemes`
-- ----------------------------
DROP TABLE IF EXISTS `jos_xschemes`;
CREATE TABLE `jos_xschemes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(45) DEFAULT NULL,
  `MinLimit` double DEFAULT NULL,
  `MaxLimit` double DEFAULT NULL,
  `Interest` varchar(45) DEFAULT NULL,
  `InterestMode` varchar(45) DEFAULT NULL,
  `InterestRateMode` varchar(45) DEFAULT NULL,
  `LoanType` tinyint(1) DEFAULT NULL,
  `AccountOpenningCommission` varchar(45) DEFAULT '0',
  `Commission` double DEFAULT NULL,
  `ActiveStatus` tinyint(1) DEFAULT NULL,
  `branch_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ProcessingFees` double DEFAULT NULL,
  `balance_sheet_id` int(11) NOT NULL,
  `PostingMode` varchar(45) DEFAULT NULL COMMENT 'Y,HF,Q,M...',
  `PremiumMode` varchar(45) DEFAULT NULL,
  `CreateDefaultAccount` tinyint(1) DEFAULT NULL,
  `SchemeType` varchar(45) DEFAULT NULL,
  `InterestToAnotherAccount` tinyint(1) DEFAULT '0',
  `NumberOfPremiums` int(11) DEFAULT NULL,
  `MaturityPeriod` int(11) DEFAULT NULL,
  `InterestToAnotherAccountPercent` varchar(45) DEFAULT NULL,
  `isDepriciable` tinyint(4) DEFAULT '0',
  `DepriciationPercentBeforeSep` varchar(45) DEFAULT NULL,
  `DepriciationPercentAfterSep` varchar(45) DEFAULT NULL,
  `ProcessingFeesinPercent` tinyint(1) DEFAULT '0' COMMENT 'whether the processing fees for accounts is in percentage',
  `published` tinyint(1) DEFAULT '1',
  `SchemePoints` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_account_type_branch1` (`branch_id`),
  KEY `fk_schemes_balance_sheet1` (`balance_sheet_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Various Accounts that a bank can manage';


-- ----------------------------
-- Records of #__xschemes
-- ----------------------------
--    INSERT INTO #__xschemes VALUES ('1', 'Saving Account', '0', '-1', '4', 'Y', '0', '0', null, '1', '1', '2011-02-05 11:23:25', '2011-10-19 07:29:51', null, '1', 'HF', null, '0', 'SavingAndCurrent', '0', null, null, '0', null, null, null, '0', '1');
--    INSERT INTO #__xschemes VALUES ('2', 'Cash Account', '0', '-1', '0', 'Y', '0', '0', null, '1', '1', '2011-02-05 11:23:25', '2011-02-05 11:23:25', null, '2', 'Y', null, '1', 'Default', '0', null, null, '0', null, null, null, '0', '1');
--    INSERT INTO #__xschemes VALUES ('3', 'Bank Accounts', '0', '-1', '0', 'Y', '0', '0', null, '1', '1', '2011-02-05 11:23:25', '2011-02-05 11:23:25', null, '2', 'Y', null, '0', 'Default', '0', null, null, '0', null, null, null, '0', '1');
--    INSERT INTO #__xschemes VALUES ('4', 'Bank OD', '0', '-1', '0', 'Y', '0', '0', null, '1', '1', '2011-02-05 11:23:25', '2011-02-05 11:23:25', null, '1', 'Y', null, '0', 'Default', '0', null, null, '0', null, null, null, '0', '1');
--    INSERT INTO #__xschemes VALUES ('5', 'Current Assests', '0', '-1', '0', 'Y', '0', '0', null, '1', '1', '2011-02-05 11:23:25', '2011-02-05 11:23:25', null, '2', 'Y', null, '0', 'Default', '0', null, null, '0', null, null, null, '0', '1');
--    INSERT INTO #__xschemes VALUES ('6', 'Share Capital', '0', '-1', '0', 'Y', '0', '0', null, '1', '1', '2011-02-05 11:23:25', '2011-02-05 11:23:25', null, '3', 'Y', null, '1', 'Default', '0', null, null, '0', null, null, null, '0', '1');
--    INSERT INTO #__xschemes VALUES ('7', 'Current Liabilities', '0', '-1', '0', 'Y', '0', '0', null, '1', '1', '2011-02-05 11:23:25', '2011-02-05 11:23:25', null, '1', 'Y', null, '0', 'Default', '0', null, null, '0', null, null, null, '0', '1');
--    INSERT INTO #__xschemes VALUES ('8', 'Deposit(Assest)', '0', '-1', '0', 'Y', '0', '0', null, '1', '1', '2011-02-05 11:23:25', '2011-02-05 11:23:25', null, '2', 'Y', null, '0', 'Default', '0', null, null, '0', null, null, null, '0', '1');
--    INSERT INTO #__xschemes VALUES ('9', 'Direct Expenses', '0', '-1', '0', 'Y', '0', '0', null, '1', '1', '2011-02-05 11:23:26', '2011-02-05 11:23:26', null, '4', 'Y', null, '1', 'Default', '0', null, null, '0', null, null, null, '0', '1');
--    INSERT INTO #__xschemes VALUES ('10', 'Direct Income', '0', '-1', '0', 'Y', '0', '0', null, '1', '1', '2011-02-05 11:23:26', '2011-02-05 11:23:26', null, '5', 'Y', null, '1', 'Default', '0', null, null, '0', null, null, null, '0', '1');
--    INSERT INTO #__xschemes VALUES ('11', 'Duties Taxes', '0', '-1', '0', 'Y', '0', '0', null, '1', '1', '2011-02-05 11:23:26', '2011-02-05 11:23:26', null, '9', 'Y', null, '1', 'Default', '0', null, null, '0', null, null, null, '0', '1');
--    INSERT INTO #__xschemes VALUES ('12', 'Fixed Assets', '0', '-1', '0', 'Y', '0', '0', null, '1', '1', '2011-02-05 11:23:26', '2011-02-05 11:23:26', null, '2', 'Y', null, '0', 'Default', '0', null, null, '0', null, null, null, '0', '1');
--    INSERT INTO #__xschemes VALUES ('13', 'Indirect Expenses', '0', '-1', '0', 'Y', '0', '0', null, '1', '1', '2011-02-05 11:23:26', '2011-02-05 11:23:26', null, '4', 'Y', null, '1', 'Default', '0', null, null, '0', null, null, null, '0', '1');
--    INSERT INTO #__xschemes VALUES ('14', 'Indirect Income', '0', '-1', '0', 'Y', '0', '0', null, '1', '1', '2011-02-05 11:23:26', '2011-02-05 11:23:26', null, '5', 'Y', null, '1', 'Default', '0', null, null, '0', null, null, null, '0', '1');
--    INSERT INTO #__xschemes VALUES ('15', 'Investment', '0', '-1', '0', 'Y', '0', '0', null, '1', '1', '2011-02-05 11:23:26', '2011-02-05 11:23:26', null, '2', 'Y', null, '0', 'Default', '0', null, null, '0', null, null, null, '0', '1');
--    INSERT INTO #__xschemes VALUES ('16', 'Loan Advance(Assets)', '0', '-1', '0', 'Y', '0', '0', null, '1', '1', '2011-02-05 11:23:27', '2011-02-05 11:23:27', null, '2', 'Y', null, '0', 'Default', '0', null, null, '0', null, null, null, '0', '1');
--    INSERT INTO #__xschemes VALUES ('17', 'Loan(Liabilities)', '0', '-1', '0', 'Y', '0', '0', null, '1', '1', '2011-02-05 11:23:27', '2011-02-05 11:23:27', null, '1', 'Y', null, '0', 'Default', '0', null, null, '0', null, null, null, '0', '1');
--    INSERT INTO #__xschemes VALUES ('18', 'Misc Expenses(Assets)', '0', '-1', '0', 'Y', '0', '0', null, '1', '1', '2011-02-05 11:23:27', '2011-02-05 11:23:27', null, '2', 'Y', null, '0', 'Default', '0', null, null, '0', null, null, null, '0', '1');
--    INSERT INTO #__xschemes VALUES ('19', 'Provision', '0', '-1', '0', 'Y', '0', '0', null, '1', '1', '2011-02-05 11:23:27', '2011-02-05 11:23:27', null, '1', 'Y', null, '0', 'Default', '0', null, null, '0', null, null, null, '0', '1');
--    INSERT INTO #__xschemes VALUES ('20', 'Reserve Surpuls', '0', '-1', '0', 'Y', '0', '0', null, '1', '1', '2011-02-05 11:23:27', '2011-02-05 11:23:27', null, '3', 'Y', null, '0', 'Default', '0', null, null, '0', null, null, null, '0', '1');
--    INSERT INTO #__xschemes VALUES ('21', 'Retained Earnings', '0', '-1', '0', 'Y', '0', '0', null, '1', '1', '2011-02-05 11:23:27', '2011-02-05 11:23:27', null, '3', 'Y', null, '0', 'Default', '0', null, null, '0', null, null, null, '0', '1');
--    INSERT INTO #__xschemes VALUES ('22', 'Secured(Loan)', '0', '-1', '0', 'Y', '0', '0', null, '1', '1', '2011-02-05 11:23:27', '2011-02-05 11:23:27', null, '1', 'Y', null, '0', 'Default', '0', null, null, '0', null, null, null, '0', '1');
--    INSERT INTO #__xschemes VALUES ('23', 'Sundry Creditor', '0', '-1', '0', 'Y', '0', '0', null, '1', '1', '2011-02-05 11:23:27', '2011-02-05 11:23:27', null, '1', 'Y', null, '0', 'Default', '0', null, null, '0', null, null, null, '0', '1');
--    INSERT INTO #__xschemes VALUES ('24', 'Sundry Debtor', '0', '-1', '0', 'Y', '0', '0', null, '1', '1', '2011-02-05 11:23:27', '2011-02-05 11:23:27', null, '2', 'Y', null, '0', 'Default', '0', null, null, '0', null, null, null, '0', '1');
--    INSERT INTO #__xschemes VALUES ('25', 'Suspence Account', '0', '-1', '0', 'Y', '0', '0', null, '1', '1', '2011-02-05 11:23:27', '2011-02-05 11:23:27', null, '6', 'Y', null, '0', 'Default', '0', null, null, '0', null, null, null, '0', '1');

-- ----------------------------
-- Records of #__users
-- ----------------------------
-- INSERT INTO #__users(`name`,username,email,`password`,usertype,sendEmail,gid,registerDate) VALUES ('DFL Default', 'xadminho', 'admin@xavoc.com', MD5('a'), 'Administrator', '1', 24, now());


-- ----------------------------
-- Records of #__core_acl_aro
-- ----------------------------
-- INSERT INTO #__core_acl_aro(section_value,`value`,`name`) VALUES ('users',(select id from #__users where `name` = 'DFL Default' and username = 'xadminho'),'DFL Default');


-- ----------------------------
-- Records of #__core_acl_groups_aro_map
-- ----------------------------
-- INSERT INTO #__core_acl_groups_aro_map(group_id,aro_id) VALUES (24,(select id from #__core_acl_aro where `value` = (select id from #__users where `name` = 'DFL Default' and username = 'xadminho') ));



-- ----------------------------
-- Table structure for `#__xstaff`
-- ----------------------------
DROP TABLE IF EXISTS `#__xstaff`;
CREATE TABLE `#__xstaff` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `StaffID` varchar(45) NOT NULL,
  `Password` varchar(45) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `AccessLevel` int(11) DEFAULT NULL,
  `jid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `StaffID_UNIQUE` (`StaffID`),
  KEY `fk_staff_branch1` (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of #__xstaff
-- ----------------------------
-- INSERT INTO #__xstaff VALUES ('1', 'xadminho', 'a', '1', '100', (select id from #__users where `name` = 'DFL Default' and username = 'xadminho'));

-- ----------------------------
-- Table structure for `#__xstaff_attendance`
-- ----------------------------
DROP TABLE IF EXISTS `#__xstaff_attendance`;
CREATE TABLE `#__xstaff_attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Date` datetime DEFAULT NULL,
  `Attendance` varchar(45) DEFAULT NULL,
  `Narration` varchar(200) DEFAULT NULL,
  `staff_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_staff_attendance_staff1` (`staff_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of #__xstaff_attendance
-- ----------------------------

-- ----------------------------
-- Table structure for `#__xstaff_details`
-- ----------------------------
DROP TABLE IF EXISTS `#__xstaff_details`;
CREATE TABLE `#__xstaff_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `JoiningDate` datetime DEFAULT NULL,
  `BasicPay` varchar(45) DEFAULT NULL,
  `PF` varchar(45) DEFAULT NULL,
  `VariablePay` varchar(45) DEFAULT NULL,
  `SavingAccount` varchar(45) DEFAULT NULL,
  `staff_id` int(11) NOT NULL,
  `Name` varchar(45) DEFAULT NULL,
  `FatherName` varchar(45) DEFAULT NULL,
  `PresentAddress` varchar(200) DEFAULT NULL,
  `PermanentAddress` varchar(200) DEFAULT NULL,
  `MobileNo` varchar(15) DEFAULT NULL,
  `LandlineNo` varchar(25) DEFAULT NULL,
  `DOB` datetime DEFAULT NULL,
  `OtherDetails` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_staff_details_staff1` (`staff_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of #__xstaff_details
-- ----------------------------

-- ----------------------------
-- Table structure for `#__xstaff_payments`
-- ----------------------------
DROP TABLE IF EXISTS `#__xstaff_payments`;
CREATE TABLE `#__xstaff_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Date` datetime DEFAULT NULL,
  `Payment` varchar(45) DEFAULT NULL,
  `PaymentAgainst` varchar(45) DEFAULT NULL,
  `staff_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_staff_payments_staff1` (`staff_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of #__xstaff_payments
-- ----------------------------

-- ----------------------------
-- Table structure for `#__xstock`
-- ----------------------------
DROP TABLE IF EXISTS `#__xstock`;
CREATE TABLE `#__xstock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Quantity` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `items_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_stock_items1` (`items_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of #__xstock
-- ----------------------------

-- ----------------------------
-- Table structure for `#__xstock_log`
-- ----------------------------
DROP TABLE IF EXISTS `#__xstock_log`;
CREATE TABLE `#__xstock_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `StockAllotedDate` datetime DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `QuantityAlloted` int(11) DEFAULT NULL,
  `StockStatus` int(11) DEFAULT NULL,
  `items_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_stock_log_items1` (`items_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of #__xstock_log
-- ----------------------------

-- ----------------------------
-- Table structure for `#__xtemp`
-- ----------------------------
DROP TABLE IF EXISTS `#__xtemp`;
CREATE TABLE `#__xtemp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `AccountNumber` varchar(100) DEFAULT NULL,
  `LoanFromAccountPrevious` varchar(100) DEFAULT NULL,
  `LoanFromAccountNew` varchar(100) DEFAULT NULL,
  `taskdone` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of #__xtemp
-- ----------------------------

-- ----------------------------
-- Table structure for `#__xtemp_loan_accounts`
-- ----------------------------
DROP TABLE IF EXISTS `#__xtemp_loan_accounts`;
CREATE TABLE `#__xtemp_loan_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `AccountNumber` varchar(255) NOT NULL,
  `premiums_paid` varchar(11) DEFAULT NULL,
  `penalty` double DEFAULT NULL,
  `amount_paid` double DEFAULT NULL,
  `interest_amount` double DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of #__xtemp_loan_accounts
-- ----------------------------

-- ----------------------------
-- Table structure for `#__xtemp_saving_accounts`
-- ----------------------------
DROP TABLE IF EXISTS `#__xtemp_saving_accounts`;
CREATE TABLE `#__xtemp_saving_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `AccountNumber` varchar(255) NOT NULL,
  `premiums_paid` int(11) DEFAULT '0',
  `penalty` double DEFAULT NULL,
  `amount_paid` double DEFAULT NULL,
  `interest_amount` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of #__xtemp_saving_accounts
-- ----------------------------

-- ----------------------------
-- Table structure for `#__xtemp_share_accounts`
-- ----------------------------
DROP TABLE IF EXISTS `#__xtemp_share_accounts`;
CREATE TABLE `#__xtemp_share_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `memberid` varchar(100) DEFAULT NULL,
  `Name` varchar(100) DEFAULT NULL,
  `amountToDeposit` varchar(100) DEFAULT NULL,
  `branchid` varchar(100) DEFAULT '3',
  `taskdone` varchar(100) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of #__xtemp_share_accounts
-- ----------------------------

-- ----------------------------
-- Table structure for `#__xtransactions`
-- ----------------------------
DROP TABLE IF EXISTS `#__xtransactions`;
CREATE TABLE `#__xtransactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `accounts_id` int(11) NOT NULL COMMENT 'From Account',
  `transaction_type_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `voucher_no` bigint(20) DEFAULT NULL,
  `Narration` text,
  `amountDr` double DEFAULT '0',
  `amountCr` double DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `branch_id` int(11) NOT NULL,
  `reference_account` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_transactions_accounts1` (`accounts_id`),
  KEY `fk_transactions_transaction_type1` (`transaction_type_id`),
  KEY `fk_transactions_staff1` (`staff_id`),
  KEY `fk_transactions_branch1` (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Store all kind of transactions Here .. ';

-- ----------------------------
-- Records of #__xtransactions
-- ----------------------------

-- ----------------------------
-- Table structure for `#__xtransaction_type`
-- ----------------------------
DROP TABLE IF EXISTS `#__xtransaction_type`;
CREATE TABLE `#__xtransaction_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Transaction` varchar(45) DEFAULT NULL,
  `FromAC` varchar(45) DEFAULT NULL,
  `ToAC` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of #__xtransaction_type
-- ----------------------------
INSERT INTO #__xtransaction_type VALUES ('1', 'UserWithdrawl', 'UserAccount', 'BankCash');
INSERT INTO #__xtransaction_type VALUES ('2', 'AgentCommission', 'BankCash', 'MemberAgentSavingAccount');
INSERT INTO #__xtransaction_type VALUES ('3', 'NewMemberRegistrationAmount', 'UserAccount', 'BRANCH_Cash_Account');
INSERT INTO #__xtransaction_type VALUES ('4', 'SavingAccountAmountDeposit', 'UserAccount', 'BRANCH_Cash_Account');
INSERT INTO #__xtransaction_type VALUES ('5', 'SavingAccountAmountWithdrawl', 'UserAccount', 'BRANCH_Cash_Account');
INSERT INTO #__xtransaction_type VALUES ('6', 'TRA_ACCOUNT_OPEN_AGENT_COMMISSION', 'UserAccount', 'BRANCH_Cash_Account');
INSERT INTO #__xtransaction_type VALUES ('7', 'NewMemberRegistrationAmunt', 'xxx', 'yyy');
INSERT INTO #__xtransaction_type VALUES ('8', 'LoanAccountOpen', 'xxx', 'yyy');
INSERT INTO #__xtransaction_type VALUES ('9', 'FixedAccountInitialDeposit', 'xxx', 'yyy');
INSERT INTO #__xtransaction_type VALUES ('10', 'RecurringAccountAmountDeposit', 'xxx', 'yyy');
INSERT INTO #__xtransaction_type VALUES ('11', 'AgentsPremiumCommissionDepositInSavingAccount', 'xxx', 'yyy');
INSERT INTO #__xtransaction_type VALUES ('12', 'CCAccountOpen', 'xxx', 'yyy');
INSERT INTO #__xtransaction_type VALUES ('13', 'Journal Voucher Entry', 'xxx', 'yyy');
INSERT INTO #__xtransaction_type VALUES ('14', 'InterestPostingsInSavingAccounts', 'xxx', 'yyy');
INSERT INTO #__xtransaction_type VALUES ('15', 'DepriciationAmountCalculated', 'xxx', 'yyy');
INSERT INTO #__xtransaction_type VALUES ('16', 'InterestPostingsInFixedAccounts', 'xxx', 'yyy');
INSERT INTO #__xtransaction_type VALUES ('17', 'InterestPostingsInREcurringAccounts', 'xxx', 'yyy');
INSERT INTO #__xtransaction_type VALUES ('18', 'LoanAccountAmountDeposit', 'xxx', 'yyy');
INSERT INTO #__xtransaction_type VALUES ('19', 'ShareAccountOpen', 'xxx', 'yyy');
INSERT INTO #__xtransaction_type VALUES ('20', 'DDSAccountAmountDeposit', 'xxx', 'yyy');
INSERT INTO #__xtransaction_type VALUES ('21', 'CCAccountAmountDeposit', 'xxx', 'yyy');
INSERT INTO #__xtransaction_type VALUES ('22', 'CCAccountAmountWithdrawl', 'xxx', 'yyy');
INSERT INTO #__xtransaction_type VALUES ('23', 'ForCloseAccountAmountDeposit', 'xxx', 'yyy');
INSERT INTO #__xtransaction_type VALUES ('24', 'DDSAccountAmountWithdrawl', 'xxx', 'yyy');
INSERT INTO #__xtransaction_type VALUES ('25', 'FixedDepositAccountAmountWithdrawl', 'xxx', 'yyy');
INSERT INTO #__xtransaction_type VALUES ('26', 'RecurringAccountAmountWithdrawl', 'xxx', 'yyy');
INSERT INTO #__xtransaction_type VALUES ('28', 'InterestPostingsInLoanAccounts', 'xxx', 'yyy');
INSERT INTO #__xtransaction_type VALUES ('29', 'InterestPostingsInCCAccounts', 'xxx', 'yyy');
INSERT INTO #__xtransaction_type VALUES ('30', 'InterestPostingsInMISAccounts', 'xxx', 'yyy');
INSERT INTO #__xtransaction_type VALUES ('31', 'PenaltyAccountAmountDeposit', 'xxx', 'yyy');

-- ----------------------------
-- Table structure for `#__xxyz`
-- ----------------------------
DROP TABLE IF EXISTS `#__xxyz`;
CREATE TABLE `#__xxyz` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `AccountNumber` varchar(255) NOT NULL,
  `premiums_paid` int(11) DEFAULT NULL,
  `penalty` double DEFAULT NULL,
  `amount_paid` double DEFAULT NULL,
  `interest_amount` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of #__xxyz
-- ----------------------------
