#MyClasses is a package folder with my usable or developed classes. Please use carefull!

##IDatabase

This class is a MySQL Database access wrapper using MySQLi.

It can:
- Connect to a MySQL server
- Get MySQL server statistics
- Execute a SELECT SQL query and return all the results into an array
- Execute SQL INSERT, UPDATE and DELETE queries given the SQL query
- Execute SQL INSERT or UPDATE queries given the table name, the list of field names and values
- Get the number of records in a table that match a given condition
- Get a single field or a single row of a table that matches a condition

####Sample uses:

Include the neccessary classes:
```php
include_once 'classes/IDatabase.class.php';
include_once 'classes/EException.class.php';
```

...or use autoload.

Create a connection and run a basic query:
```php
$connection = IDatabase::getSingleton();
$sample = $connection->select("SELECT * FROM sampletable");
```

##Wunderground API

```php
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
```


##FormDataValidator

```php
require_once 'classes/FormDataValidator.class.php';
 
$accepted = array('name', 'nick', 'email', 'message'); 
$required = array('name', 'nick', 'message'); 

$validator = new FormDataValidator($_POST, $accepted, $required); 
$resp = $validator->validate(); 

var_export($resp); 
```

##Basket

Include main class
```php
include_once 'Basket.class.php';
 ```

Sample summary function for testing:
```php 
  function summarylist() { 
    $items = Basket::getItemList(); 
    var_export($items); 
    print "<br>Full price: " . Basket::getFullPrice(); 
    print "<br>Item quantity: " . Basket::getItemCount(); 
    print "<br>Item types: " . Basket::getItemtypeCount(); 
    print "<br>Last modify: " . Basket::getLastModify(false). " (".Basket::getLastModify().")"; 
  } 
```

Empty basket:
```php
  Basket::emptyBasket(); 
  print "Simple Static Basket with session storage<br>"; 
  print "<br>Basket (empty) [emptyBasket, getItemList]<br>"; 
  summarylist(); 
```

Add simple or multiple items:
```php  
  print "<br><br>Add a simple item [changeItem]<br>"; 
  Basket::changeItem('sugar_01', 12.25, 'Sugar, 1kg', 0.75); 
  summarylist();
   
  print "<br><br>Add multiple items [addItemFromArray]<br>"; 
  $add_items = array( 
    array('id' => 'salt_01', 'quantity' => 4.57, 'name' => 'Salt, 1kg', 'price' => 0.65), 
    array('id' => 'salt_02', 'quantity' => 3.10, 'name' => 'Salt, 2kg', 'price' => 1.15), 
    array('id' => 'salt_03', 'quantity' => 2.90, 'name' => 'Salt, 5kg', 'price' => 2.55), 
    array('id' => 'sugar_02', 'quantity' => 1.34, 'name' => 'Sugar, 2kg', 'price' => 1.40), 
    array('id' => 'sugar_03', 'quantity' => 13.45, 'name' => 'Sugar, 5kg', 'price' => 3.59), 
  ); 
  Basket::addItemFromArray($add_items); 
  summarylist(); 
```

Remove a single item:
```php
  print "<br><br>Remove salt_03 [removeItem]<br>"; 
  Basket::removeItem('salt_03'); 
  summarylist(); 
```

Change item quantity, price or name:
```php
  print "<br><br>Change the quantity of salt_02 to 3.45 [changeItem]<br>"; 
  Basket::changeItem('salt_02', 3.45, null, 0); 
  summarylist(); 
```

```php
  print "<br><br>Change the price of salt_02 to 1.23 [changeItem]<br>"; 
  Basket::changeItem('salt_02', 0, null, 1.23); 
  summarylist(); 
```

```php
  print "<br><br>Change the name of salt_02 to Sugar, 1.5kg [changeItem]<br>"; 
  Basket::changeItem('salt_02', 0, 'Sugar, 1.5kg', 0); 
  summarylist(); 
```