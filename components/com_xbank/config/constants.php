<?php
/*------------------------------------------------------------------------
# com_xcideveloper - Seamless merging of CI Development Style with Joomla CMS
# ------------------------------------------------------------------------
# author    Xavoc International / Gowrav Vishwakarma
# copyright Copyright (C) 2011 xavoc.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.xavoc.com
# Technical Support:  Forum - http://xavoc.com/index.php?option=com_discussions&view=index&Itemid=157
-------------------------------------------------------------------------*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
?><?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ',							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE',		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE',	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE',					'ab');
define('FOPEN_READ_WRITE_CREATE',				'a+b');
define('FOPEN_WRITE_CREATE_STRICT',				'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');


/* End of file constants.php */
/* Location: ./application/config/constants.php */

// xBAnk CONSTANTS
define('SP',							' ');
define('SAVING_ACCOUNT_SCHEME',			'Saving Account');
define('BRANCH_TDS_ACCOUNT',                    'TDS');
define('LIABILITIES_HEAD',				'Liabilities');
define('CASH_ACCOUNT_SCHEME',           'Cash Account');
define('ASSETS_HEAD',                   'Assets');
define('BANK_ACCOUNTS_SCHEME',          'Bank Accounts');
define('BANK_OD_SCHEME',                'Bank OD');
define('CURRENT_ASSESTS_SCHEME',        'Current Assests');
define('CAPITAL_ACCOUNT_SCHEME',       'Share Capital');
define('CAPITAL_ACCOUNT_HEAD',          'Capital Account');
define('CURRENT_LIABILITIES_SCHEME',    'Current Liabilities');
define('DEPOSITS_ASSETS_SCHEME',        'Deposit(Assest)');
define('DIRECT_EXPENSES_SCHEME',        'Direct Expenses');
define('EXPENSES_HEAD',					'Expenses');
define('DIRECT_INCOME_SCHEME',			'Direct Income');
define('INCOME_HEAD',					'Income');
define('DUTIES_TAXES_SCHEME',			'Duties Taxes');
define('FIXED_ASSETS',					'Fixed Assets');
define('INDIRECT_EXPENSES',				'Indirect Expenses');
define('INDIRECT_INCOME',				'Indirect Income');
define('INVESTMENT_SCHEME',				'Investment');
define('LOAN_ADVANCE_ASSETS_SCHEME',	'Loan Advance(Assets)');
define('LOAN_LIABILITIES_SCHEME',		'Loan(Liabilities)');
define('MISC_EXPENSES_ASSETS_SCHEME',	'Misc Expenses(Assets)');
define('PROVISION_SCHEME',				'Provision');
define('RESERVE_SURPULS_SCHEME',        'Reserve Surpuls');
define('RETAINED_EARNINGS_SCHEME',		'Retained Earnings');
define('SECURED_LOAN_SCHEME',			'Secured(Loan)');
define('SUNDRY_CREDITOR_SCHEME',		'Sundry Creditor');
define('SUNDRY_DEBTOR_SCHEME',			'Sundry Debtor');
define('SUSPENCE_HEAD',				'Suspence Account');
define('SUSPENCE_ACCOUNT_SCHEME',		'Suspence Account');
define('FIXED_ASSETS_HEAD',				'Fixed Assets');
define('BRANCH_AND_DIVISIONS_HEAD',                  'Branch/Divisions' );
define('BRANCH_AND_DIVISIONS',                  'Branch & Divisions' );


define('INTEREST_RECEIVED_ON',				'Interest Received On ');
define('PROCESSING_FEE_RECEIVED',				'Processing Fee Received On ');
define('PENALTY_DUE_TO_LATE_PAYMENT_ON',		'Penalty Due To Late Payment On ');
define('FOR_CLOSE_ACCOUNT_ON',		'For Close Account On ');
define('INTEREST_PAID_ON',				'Interest Paid On ');
define('COMMISSION_PAID_ON',				'Commission Paid On ');
define('ADMISSION_FEE_ACCOUNT',			'Admission Fee');
define('CASH_ACCOUNT',					'Cash Account');
define('INTEREST_PROVISION_ON',                         'Interest Provision On ');
define('DEPRECIATION_ON_FIXED_ASSETS',                  'Depreciation On Fixed Assets');


define('TRA_SAVING_ACCOUNT_AMOUNT_DEPOSIT',	'SavingAccountAmountDeposit');
define('TRA_SAVING_ACCOUNT_AMOUNT_WITHDRAWL',	'SavingAccountAmountWithdrawl');
define('TRA_ACCOUNT_OPEN_AGENT_COMMISSION',	'TRA_ACCOUNT_OPEN_AGENT_COMMISSION');
define('TRA_RECURRING_ACCOUNT_AMOUNT_DEPOSIT',	'RecurringAccountAmountDeposit');
define('TRA_RECURRING_ACCOUNT_AMOUNT_WITHDRAWL',	'RecurringAccountAmountWithdrawl');
define('TRA_DDS_ACCOUNT_AMOUNT_DEPOSIT',         'DDSAccountAmountDeposit');
define('TRA_DDS_ACCOUNT_AMOUNT_WITHDRAWL',         'DDSAccountAmountWithdrawl');
define('TRA_LOAN_ACCOUNT_AMOUNT_DEPOSIT',	'LoanAccountAmountDeposit');
define('TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT',	'AgentsPremiumCommissionDepositInSavingAccount');
define('TRA_FIXED_ACCOUNT_DEPOSIT',             'FixedAccountInitialDeposit');
define('TRA_FD_ACCOUNT_AMOUNT_WITHDRAWL',	'FixedDepositAccountAmountWithdrawl');
define('TRA_LOAN_ACCOUNT_OPEN',                 'LoanAccountOpen');
define('TRA_CC_ACCOUNT_OPEN',                   'CCAccountOpen');
define('TRA_JV_ENTRY',                          'Journal Voucher Entry');
define('TRA_DEFAULT_ACCOUNT_DEPOSIT_ENTRY',             'Default Account Deposit Enrty');
define('TRA_NEW_MEMBER_REGISTRATIO_AMOUNT',     'NewMemberRegistrationAmount');
define('TRA_PENALTY_ACCOUNT_AMOUNT_DEPOSIT',	'PenaltyAccountAmountDeposit');
define('TRA_FOR_CLOSE_ACCOUNT_AMOUNT_DEPOSIT',	'ForCloseAccountAmountDeposit');
define('TRA_CC_ACCOUNT_AMOUNT_DEPOSIT',         'CCAccountAmountDeposit');
define('TRA_CC_ACCOUNT_AMOUNT_WITHDRAWL',	'CCAccountAmountWithdrawl');
define('TRA_DEPRICIATION_AMOUNT_CALCULATED',	'DepriciationAmountCalculated');
define('TRA_SHARE_ACCOUNT_OPEN',                 'ShareAccountOpen');
define('TRA_RECURRING_ACCOUNT_COLLECTION_CHARGES_DEPOSIT',  'RecurringAccountCollectionChargesDeposit');

//define('CURRENT_BRANCH_CASH_ACCOUNT',	"Branch::getDefaultBranch()->Code.SP.CASH_ACCOUNT_SCHEME'");
//define('CURRENT_BRANCH_CASH_ACCOUNT',	'Doctrine::getTable("Accounts")->findOneByBranch_idAndAccountnumber(Branch::getDefaultBranch()->id,Branch::getDefaultBranch()->Code.SP.CASH_ACCOUNT_SCHEME);')

// define('ACCOUNT_TYPES',                         "DDS");
define('ACCOUNT_TYPES',                         "Loan,CC,FixedAndMis,Default,SavingAndCurrent,Recurring,DDS");
define('ACCOUNT_TYPE_DEFAULT',                  "Default");
define('ACCOUNT_TYPE_BANK',                     "SavingAndCurrent");
define('ACCOUNT_TYPE_FIXED',                    "FixedAndMis");
define('ACCOUNT_TYPE_RECURRING',                "Recurring");
define('ACCOUNT_TYPE_DDS',                      "DDS");
define('ACCOUNT_TYPE_LOAN',                     "Loan");
define('ACCOUNT_TYPE_CC',                       "CC");
// define('ACCOUNT_TYPE_DHANSANCHAYA',             "DhanSanchaya");
// define('ACCOUNT_TYPE_MONEYBACK',                "MoneyBack");


define('OPENNING_COMMISSION'                    ,'OpenningCommission');
define('PREMIUM_COMMISSION'                     ,'PremiumCommission');

define('RECURRING_MODES'                        ,'Y,HF,Q,M,W,D');
define('RECURRING_MODE_YEARLY'                  ,'Y');
define('RECURRING_MODE_HALFYEARLY'              ,'HF');
define('RECURRING_MODE_QUATERLY'                ,'Q');
define('RECURRING_MODE_MONTHLY'                 ,'M');
define('RECURRING_MODE_WEEKLY'                  ,'W');
define('RECURRING_MODE_DAILY'                   ,'D');

define('CC_AMOUNT'                   ,'RdAmount');
define('LOAN_AMOUNT'                   ,'RdAmount');

define('TRA_INTEREST_POSTING_IN_SAVINGS',       'InterestPostingsInSavingAccounts');
define('TRA_INTEREST_POSTING_IN_FIXED_ACCOUNT', 'InterestPostingsInFixedAccounts');
define('TRA_INTEREST_POSTING_IN_MIS_ACCOUNT', 'InterestPostingsInMISAccounts');
define('TRA_INTEREST_POSTING_IN_HID_ACCOUNT', 'InterestPostingsInHIDAccounts');
define('TRA_INTEREST_POSTING_IN_CC_ACCOUNT', 'InterestPostingsInCCAccounts');
define('TRA_INTEREST_POSTING_IN_RECURRING',     'InterestPostingsInREcurringAccounts');
define('TRA_INTEREST_POSTING_IN_DDS',     'InterestPostingsInDDSAccounts');
define('TRA_INTEREST_POSTING_IN_LOAN',     'InterestPostingsInLoanAccounts');


define('FIELD_TEMP_PENALTY',                    'CurrentInterest');

define('SIGNATURE_FILE_PATH',                   '/administrator/components/com_xbank/signatures/' );

/* Defining Access Level Constants  */

define('xADMIN',  100);
define('BRANCH_ADMIN',  80);
define('POWER_USER',  50);
define('USER',  20);

define('STOCK_ADDED',  1);
define('STOCK_REMOVED', 0);
define('STOCK_ALLOTED', 2);
define('STOCK_RETURNED', 3);

define('PRESENT',   'P');
define('LEAVE',   'L');
define('ABSENT',   'A');
//define('IS_HID_SCHEME',    1);
//define('TEMP_HID_FIELD',    'PostingMode');

define('RATE_PER_SHARE',        100);
define('TDS_PERCENTAGE',   '10');
define('xBANKSCHEMEPATH', constant($xCICurrentExtension.'APPPATH')."controllers/xbankschemes");

define('SET_COMMISSIONS_IN_MONTHLY',    true);
define('SET_DATE',                      false);

define("ROUND_TO",      2);
define("COMMISSION_ROUND_TO",      0);

define("DO_TRANSACTIONS",   true);

define("ROWS_IN_DATA",      25);

define("DEFAULT_STAFF", "Manager");
define("STAFF", "Manager");
define("MEMBER",    "Registered");

define("COMMISSION_PAYABLE_ON",     "Commission Payable On");
define("TDS_PAYABLE",               "TDS Payable");
define("COLLECTION_PAYABLE_ON",     "Collection Payable On");
define("COLLECTION_PAID_ON",     "Collection Charges Paid On");


define("REDUCING_RATE", 'Reducing');
define("FLAT_RATE", "Flat");

define("BALANCE_SHEET", true);