<h3>MyClasses is a package folder with my usable or developed classes. Please use carefull!</h3>

<b>IDatabase.class</b>

This class is a MySQL Database access wrapper using MySQLi.

It can:
- Connect to a MySQL server
- Get MySQL server statistics
- Execute a SELECT SQL query and return all the results into an array
- Execute SQL INSERT, UPDATE and DELETE queries given the SQL query
- Execute SQL INSERT or UPDATE queries given the table name, the list of field names and values
- Get the number of records in a table that match a given condition
- Get a single field or a single row of a table that matches a condition

Sample uses:

IDatabase
---------

include_once 'classes/IDatabase.class.php';
include_once 'classes/EException.class.php';

 $connection = IDatabase::getSingleton();

 $sample = $connection->select("SELECT * FROM sampletable");

Wunderground API
----------------

include_once 'classes/EException.class.php'; 
include_once 'classes/IDatabase.class.php'; 
include_once 'classes/Wunderground.class.php'; 

define("SERVICE_KEY", ""); // Add service key here! 
define("SERVICE_URL", "http://api.wunderground.com/api/!key/!action/lang:!lang/q/"); 

$wunderground = new WunderGroundAPI(SERVICE_KEY, SERVICE_URL); 
// Check class data 
print $wunderground; 
// Set city and country 
$wunderground->setCity('Sydney','AU'); 
// Get data, if enable caching then from the cache 
// Actions: forecast, alerts, almanac, astronomy, conditions, currenthurricane, forecast10day, geolookup, hourly, hourly10day, rawtide, satellite, tide, webcams, yesterday
$ret = $wunderground->getData('forecast'); 

