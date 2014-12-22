This class is a MySQL Database access wrapper using MySQLi.

It can:

- Connect to a MySQL server
- Get MySQL server statistics
- Execute a SELECT SQL query and return all the results into an array
- Execute SQL INSERT, UPDATE and DELETE queries given the SQL query
- Execute SQL INSERT or UPDATE queries given the table name, the list of field names and values
- Get the number of records in a table that match a given condition
- Get a single field or a single row of a table that matches a condition

Sample use:
include_once 'classes/IDatabase.class.php';
include_once 'classes/EException.class.php';

 $connection = IDatabase::getSingleton();

 $sample = $connection->select("SELECT * FROM sampletable");
