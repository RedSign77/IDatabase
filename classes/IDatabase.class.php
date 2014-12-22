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

    public function __construct()
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

    public static function getSingleton()
    {
        if (!isset(self::$objDb)) {
            self::$objDb = new IDatabase();
        }
        return self::$objDb;
    }

    public function info()
    {
        return $this->mysqli->stat();
    }

    public function close()
    {
        return $this->mysqli->close();
    }

    public function getLastQuery()
    {
        return $this->strQueryString;
    }

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

    private function valid($querystring)
    {
        return $this->mysqli->real_escape_string($querystring);
    }

    public function executeQuery($strQuery, $validate = false, $use = MYSQLI_USE_RESULT)
    {
        if ($validate)
            $strQuery = $this->valid($strQuery);
        return $this->mysqli->query($strQuery, $use);
    }

    public function delete($strQuery)
    {
        $ret = 0;
        if ($this->executeQuery($strQuery))
            $ret = $this->mysqli->affected_rows;
        return $ret;
    }

    public function insert($strQuery)
    {
        $ret = 0;
        if ($this->executeQuery($strQuery, false))
            $ret = $this->mysqli->insert_id;
        return $ret;
    }

    public function update($strQuery)
    {
        return $this->delete($strQuery);
    }

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

    public function getSingleData($strTable, $strField, $strWhere = "")
    {
        $this->strQueryString = "SELECT " . $strField . " FROM " . $strTable . ($strWhere != "" ? " WHERE " . $strWhere : null);
        $arrRet = $this->select($this->strQueryString);
        $arrRet = current($arrRet);
        return ($arrRet[$strField]);
    }

    public function getOneRow($strTable, $strWhere = "", $strOrderBy = "", $fields = array())
    {
        $this->strQueryString = "SELECT " . (count($fields) > 0 ? join(",", $fields) : "*") . " FROM " . $strTable . ($strWhere != "" ? " WHERE " . $strWhere : null) . ($strOrderBy != "" ? " ORDER BY " . $strOrderBy : null . " LIMIT 1");
        $arrRet = $this->select($this->strQueryString);
        return current($arrRet);
    }

    /**
     * @name insertA
     * @param array $arrData
     * Insert an array on the table key
     */
    public function insertA($arrData)
    {
        $intRet = 0;
        if (!isset($arrData['table'])) {
            throw new EException("No table found on " . __METHOD__, 1002);
            return $intRet;
        }
        if (count($arrData) < 2) {
            throw new EException("No datafields found on " . __METHOD__, 1003);
            return $intRet;
        }
        $strQuery = "INSERT INTO " . $arrData['table'];
        unset($arrData['table']);
        $strQuery .= "(" . implode(",", array_keys($arrData)) . ") VALUES";
        $strQuery .= "('" . implode("','", $arrData) . "')";
        $intRet = $this->insert($strQuery);
        return $intRet;
    }

    /**
     * @name updateA
     * @param array $arrData
     * Update row on the pairs of key(s) and value(s) with where key!
     */
    public function updateA($arrData)
    {
        $intRet = 0;
        if (!isset($arrData['table'])) {
            throw new EException("NO TABLE: " . __METHOD__ . ", " . __LINE__);
            return $intRet;
        }
        if (count($arrData) < 2) {
            throw new EException("NO DATAFIELDS: " . __METHOD__ . ", " . __LINE__);
            return $intRet;
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

    public function tableExists($table)
    {
        $ret = $this->select("SHOW TABLES LIKE '" . $table . "'");
        if (count($ret) > 0)
            return true;
        else
            return false;
    }

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

    public function call($function)
    {
        return $this->select($function);
    }

}
