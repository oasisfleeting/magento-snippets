<?php

/**
 * This script imports customers from the WCMS customers table into Magento using the Magento methods.
 *
 * Instructions:
 *
 * 0. BACKUP MAGENTO DATABASE. SCRIPT WILL REMOVE ALL CUSTOMERS.
 * 1. (Optional) Configure the current store on the line with Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
 * 2. Configure local database connections
 * 3. Review whether to save COUNTY appended to the CITY (in address section below)
 */

// Set the timezone to London because of the date of birth timestamps
date_default_timezone_set('Europe/London');

// Remove any memory limits
ini_set('display_errors', 1);
ini_set('memory_limit', -1);

// Import any required files
require_once('CountryList.php');
require_once('app/Mage.php');

// Setup Magento
Mage::register('isSecureArea', true);
Mage::app()->setUpdateMode(false);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
Mage::setIsDeveloperMode(true);

// Database Connections
define('WCMS_DBHOST', 'localhost');
define('WCMS_DBNAME', 'dbname');
define('WCMS_DBUSER', 'root');
define('WCMS_DBPASS', '');

// Not Needed
define('MAGE_DBHOST', 'localhost');
define('MAGE_DBNAME', 'mage');
define('MAGE_DBUSER', 'root');
define('MAGE_DBPASS', '');

// Line Breaks
define("BR", "\r\n\r\n");

// Get customer records from WCMS database
$db = new PDO('mysql:host=' . WCMS_DBHOST . ';dbname='. WCMS_DBNAME .';charset=utf8', WCMS_DBUSER, WCMS_DBPASS);
$stmt = $db->query('SELECT * FROM wcms_customers');
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Processing " . count($results) . " customers" . BR;

// First DELETE all Magento customers and addresses
echo "Deleting all customers in Magento first..." . BR;
Mage::getModel('customer/customer')->getCollection()->delete();
echo "All Magento customers deleted.. Moving on to import all customers now.." . BR;

// Magento store ID and website ID - change if needed
$websiteId = Mage::app()->getWebsite()->getId();
$store = Mage::app()->getStore();

// Initialise the country list so we can get the 2 digit codes
$countryList = new CountryList;

// Loop through each customer
foreach($results as $result)
{
	// Get Customer Data
	$prefix = $result['c_title'];
	$firstName = $result['c_firstname'];
	$lastName = $result['c_lastname'];
	$telephone = $result['c_telephone'];
	$email = $result['c_emailaddress'];
	$dob = isset($result['c_dob']) && ! empty($result['c_dob']) && ! is_null($result['c_dob']) ? $result['c_dob'] : null;
	$password = $result['c_password']; // Unused

	// Fill the customer entity with data
	$customer = Mage::getModel('customer/customer')->addData([
		'store'         => $store,
		'website_id'    => $websiteId,
		'prefix' 		=> $prefix,
		'firstname'     => $firstName,
		'lastname'      => $lastName,
		'email'         => $email,
		'telephone'     => $telephone,
		'dob' 			=> $dob,
		'password'      => chr( mt_rand( 97 ,122 ) ) .substr( md5( time( ) ) ,1 ),
	]);

	// Save the customer into database or print any errors
	try{
		$customer->save();
		echo "Customer ID " . $customer->getId() . " successfully saved." . BR;
	}
	catch (Exception $e) {
		Zend_Debug::dump($e->getMessage());
	}

	// Serialised Data (Addresses), fixed for errors and unserialised
	$cData = $result['c_data'];
	$cData = preg_replace_callback ( '!s:(\d+):"(.*?)";!',
		function($match) {
			return ($match[1] == strlen($match[2])) ? $match[0] : 's:' . strlen($match[2]) . ':"' . $match[2] . '";';
		},
		$cData );
	$cData = unserialize($cData);

	// Check for addresses, otherwise set to null
	$addresses = isset($cData['c_addresses']) ? $cData['c_addresses'] : null;

	// Process Addresses
	if( count($addresses) > 0 && !(is_null($addresses)) )
	{
		//echo "Processing " . count($addresses) . " addresses for this customer" . BR;
		foreach($addresses as $a)
		{
			// Get the address info
			$streetAddress = $a['a_streetaddress'];
			$city = $a['a_city'];
			$county = $a['a_county'];

			// REMOVE THIS IF YOU DON'T WANT TO APPEND THE COUNTY TO CITY
			$city .= ", " . $county;

			$postcode = $a['a_postcode'];

			// Save the address into Magento for this customer
			$address = Mage::getModel("customer/address");
			$address->setCustomerId($customer->getId())
				->setPrefix($customer->getPrefix())
				->setFirstname($customer->getFirstname())
				->setMiddleName($customer->getMiddlename())
				->setLastname($customer->getLastname())
				->setStreet($streetAddress)
				->setCity($city)
				->setPostcode($postcode)
				->setTelephone($telephone);

			// Save the country IF IT WORKS
			$country = $a['a_country'];
			$countryTwoLetters = $countryList->getTwoLetterCodeById($country);
			if( $countryTwoLetters !== false )
			{
				$address->setCountryId($countryTwoLetters);
			}

			// Save as default for billing and shipping and save in address book
			$address->setIsDefaultBilling('1')
				->setIsDefaultShipping('1')
				->setSaveInAddressBook('1');

			// Save the address or print any errors
			try{
				$address->save();
			}
			catch (Exception $e) {
				Zend_Debug::dump($e->getMessage());
			}
		}
	}
	else
	{
		// No Addresses
	}
}
