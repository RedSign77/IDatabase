<?php

/**
 * Database manager class with mysqli OOP
 * @name IDatabase2
 * @author Zoltan Nemeth
 * @created 2013-07-13
 */
class IDatabase
{
    /*
     *  Access data for the database
     */

    private $host = "localhost";
    private $user = "";
    private $pass = "";
    private $db = "";
    private $mysqli;
    private $strQueryString;
    private static $objDb;

    /**
     * construct
     */
    private function __construct()
    {
        try {
            @$this->mysqli = new mysqli($this->host, $this->user, $this->pass, $this->db);
            if ($this->mysqli->connect_error)
                throw new EException("File: " . basename(__FILE__) . ", line: " . __LINE__ . " >> " . $this->mysqli->connect_error, $this->mysqli->connect_errno);
            if (!$this->mysqli->set_charset("utf8")) {
                throw new EException("File: " . basename(__FILE__) . ", line: " . __LINE__ . " >> " . $this->mysqli->error, $this->mysqli->connect_errno);
            }
        } catch (EException $e) {
            die("Sorry, database error. Try again later.");
        }
    }

    /**
     * Get singleton pattern
     *
     * @return IDatabase
     */
    public static function getSingleton()
    {
        if (!isset(self::$objDb)) {
            self::$objDb = new IDatabase();
        }
        return self::$objDb;
    }

    /**
     * Get mysqli info
     *
     * @return string
     */
    public function info()
    {
        return $this->mysqli->stat();
    }

    /**
     * Close connection
     *
     * @return bool
     */
    public function close()
    {
        return $this->mysqli->close();
    }

    /**
     * Get last query string
     *
     * @return mixed
     */
    public function getLastQuery()
    {
        return $this->strQueryString;
    }

    /**
     * Run mysql select query
     *
     * @param $strQuery
     * @return array
     * @throws EException
     */
    public function select($strQuery)
    {
        $ret = array();
        if ($this->valid($this->strQueryString = $strQuery)) {
            $result = $this->mysqli->query($this->strQueryString, MYSQLI_USE_RESULT);
            if ($result) {
                while ($tmp = $result->fetch_assoc()) {
                    $ret[] = $tmp;
                }
            } else {
                throw new EException("IDatabase (" . __METHOD__ . ", " . __LINE__ . ")<br>Error: <i>" . $this->mysqli->error . "</i>");
            }
            mysqli_free_result($result);
        } else {
            throw new EException("IDatabase (" . __METHOD__ . ", " . __LINE__ . ")<br>Error: <i>" . $this->mysqli->error . "</i>");
        }
        return $ret;
    }

    /**
     * Check mysql select string
     *
     * @param $query
     * @return string
     */
    private function valid($query)
    {
        return $this->mysqli->real_escape_string($query);
    }

    /**
     * Execute a query
     *
     * @param $strQuery
     * @param bool $validate
     * @param int $use
     * @return bool|mysqli_result
     */
    public function executeQuery($strQuery, $validate = false, $use = MYSQLI_USE_RESULT)
    {
        if ($validate)
            $strQuery = $this->valid($strQuery);
        return $this->mysqli->query($strQuery, $use);
    }

    /**
     * Run a delete query
     *
     * @param $strQuery
     * @return int
     */
    public function delete($strQuery)
    {
        $ret = 0;
        if ($this->executeQuery($strQuery))
            $ret = $this->mysqli->affected_rows;
        return $ret;
    }

    /**
     * Run an insert query
     *
     * @param $strQuery
     * @return int|mixed
     */
    public function insert($strQuery)
    {
        $ret = 0;
        if ($this->executeQuery($strQuery, false))
            $ret = $this->mysqli->insert_id;
        return $ret;
    }

    /**
     * Run an update query
     *
     * @param $strQuery
     * @return int
     */
    public function update($strQuery)
    {
        return $this->delete($strQuery);
    }

    /**
     * Get a row number from a table with a condition
     *
     * @param $strTable
     * @param string $strWhere
     * @return int|mixed
     * @throws EException
     */
    public function getRowNumber($strTable, $strWhere = "")
    {
        $ret = 0;
        $this->strQueryString = "SELECT count(*) as counter FROM " . $strTable . ($strWhere != "" ? " WHERE " . $strWhere : null);
        $arrRet = $this->select($this->strQueryString);
        $ret = current($arrRet);
        if (isset($ret['counter']))
            $ret = intval($ret['counter']);
        return $ret;
    }

    /**
     * Get a single field value from a query
     *
     * @param $strTable
     * @param $strField
     * @param string $strWhere
     * @return mixed
     * @throws EException
     */
    public function getSingleData($strTable, $strField, $strWhere = "")
    {
        $this->strQueryString = "SELECT " . $strField . " FROM " . $strTable . ($strWhere != "" ? " WHERE " . $strWhere : null);
        $arrRet = $this->select($this->strQueryString);
        $arrRet = current($arrRet);
        return ($arrRet[$strField]);
    }

    /**
     * Get a single row
     *
     * @param $strTable
     * @param string $strWhere
     * @param string $strOrderBy
     * @param array $fields
     * @return mixed
     * @throws EException
     */
    public function getOneRow($strTable, $strWhere = "", $strOrderBy = "", $fields = array())
    {
        $this->strQueryString = "SELECT " . (count($fields) > 0 ? join(",", $fields) : "*") . " FROM " . $strTable . ($strWhere != "" ? " WHERE " . $strWhere : null) . ($strOrderBy != "" ? " ORDER BY " . $strOrderBy : null . " LIMIT 1");
        $arrRet = $this->select($this->strQueryString);
        return current($arrRet);
    }

    /**
     * Insert an array on the table key
     *
     * @param array $arrData
     * @return int affected row id
     * @throws EException
     */
    public function insertA($arrData)
    {
        $intRet = 0;
        if (!isset($arrData['table'])) {
            throw new EException("No table found on " . __METHOD__, 1002);
        }
        if (count($arrData) < 2) {
            throw new EException("No data fields found on " . __METHOD__, 1003);
        }
        $strQuery = "INSERT INTO " . $arrData['table'];
        unset($arrData['table']);
        $strQuery .= "(" . implode(",", array_keys($arrData)) . ") VALUES";
        $strQuery .= "('" . implode("','", $arrData) . "')";
        $intRet = $this->insert($strQuery);
        return $intRet;
    }

    /**
     * Update row on the pairs of key(s) and value(s) with where key!
     *
     * @param array $arrData
     * @return int affected row numbers
     * @throws EException
     */
    public function updateA($arrData)
    {
        $intRet = 0;
        if (!isset($arrData['table'])) {
            throw new EException("NO TABLE: " . __METHOD__ . ", " . __LINE__);
        }
        if (count($arrData) < 2) {
            throw new EException("NO DATAFIELDS: " . __METHOD__ . ", " . __LINE__);
        }
        $this->adminLog($arrData);
        $strWhere = " WHERE " . $arrData['where'];
        unset($arrData['where']);
        $strQuery = "UPDATE " . $arrData['table'] . " SET ";
        unset($arrData['table']);
        $i = 0;
        foreach ($arrData as $k => $v) {
            if ($i > 0)
                $strQuery .= "," . $k . "='" . $v . "'";
            else
                $strQuery .= $k . "='" . $v . "'";
            $i++;
        }
        $strQuery .= $strWhere;
        $intRet = $this->update($strQuery);
        return $intRet;
    }

    /**
     * Check table is exists
     *
     * @param $table
     * @return bool
     * @throws EException
     */
    public function tableExists($table)
    {
        $ret = $this->select("SHOW TABLES LIKE '" . $table . "'");
        if (count($ret) > 0)
            return true;
        else
            return false;
    }

    /**
     * Log to a selected admin table
     *
     * @param $data
     * @return int
     * @throws EException
     */
    public function adminLog($data)
    {
        $inserted = array(
            'table' => 'admin_naplo',
            'userID' => User::getUID(),
            'text' => 'Update table ' . $data['table'] . ' in: ' . $data['where'],
        );
        $ret = $this->insertA($inserted);
        return $ret;
    }

    /**
     * Call a mysqli procedure
     *
     * @param $function
     * @return array
     * @throws EException
     */
    public function call($function)
    {
        return $this->select($function);
    }

}
