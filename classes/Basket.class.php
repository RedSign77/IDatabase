<?php

/**
 * Basket class for webpages
 *
 * @author Zoltán Németh
 * @version 1.0
 * @copyright (c) 2014
 *
 */
define("MODE_SUB", -1);
define("MODE_ADD",  1);
define("MODE_OVERWRITE", 2);

class Basket
{

    private static $sessionKey = "BAS_SESS";
    private static $items = "ITEMS";
    private static $lastModify = "LASTMOD";
    private static $counter = "COUNT";

    /**
     * Set Last modification time stamp
     */
    private static function setLastModify()
    {
        $_SESSION[self::$sessionKey][self::$lastModify] = time();
    }

    /**
     * Change basket counter
     *
     * @param float $quantity
     * @param int $mode : MODE_SUB subtract quantity, MODE_ADD increase quantity
     */
    private static function changeCount($quantity, $mode = MODE_SUB)
    {
        switch ($mode) {
            case MODE_SUB:
                $_SESSION[self::$sessionKey][self::$counter] -= $quantity;
                break;
            case MODE_ADD:
                $_SESSION[self::$sessionKey][self::$counter] += $quantity;
                break;
        }
        self::setLastModify();
    }

    /**
     * Empty the basket
     */
    public static function emptyBasket()
    {
        $_SESSION[self::$sessionKey] = null;
    }

    /**
     * Gets the number of item types
     *
     * @return float
     */
    public static function getItemtypeCount()
    {
        return count($_SESSION[self::$sessionKey][self::$items]);
    }

    /**
     * Gets the number of items
     *
     * @return float
     */
    public static function getItemCount()
    {
        return $_SESSION[self::$sessionKey][self::$counter];
    }

    /**
     * Gets the item list
     *
     * @return array
     */
    public function getItemList()
    {
        return $_SESSION[self::$sessionKey][self::$items];
    }

    /**
     * Gets the last modification time in a format of timestamp or datetime
     *
     * @param bool $ts
     * @return string
     */
    public static function getLastModify($ts = TRUE)
    {
        if ($ts) {
            return $_SESSION[self::$sessionKey][self::$lastModify];
        } else {
            return date("Y-m-d H:i:s", $_SESSION[self::$sessionKey][self::$lastModify]);
        }
    }

    /**
     * Remove items from a basket
     *
     * @param string $id
     * @param int $quantity
     * @return boolean
     */
    public static function removeItem($id, $quantity = 0)
    {
        $ret = FALSE;
        if ($quantity == 0) {
            if (isset($_SESSION[self::$sessionKey][self::$items][$id])) {
                self::changeCount($_SESSION[self::$sessionKey][self::$items][$id]['quantity']);
                $ret = TRUE;
                unset($_SESSION[self::$sessionKey][self::$items][$id]);
            }
        } else {
            // substract items from basket
            if (self::changeItem($id, $quantity, null, 0, MODE_SUB)) {
                $ret = TRUE;
            }
        }
        return $ret;
    }

    /**
     * Change the items in the basket, for example: add, subtract, empty, modify
     *
     * @param string $id
     * @param float $quantity
     * @param string $name
     * @param int $price
     * @param int $mode : MODE_ADD, MODE_SUB, MODE_OVERWRITE (default)
     * @return boolean
     */
    public static function changeItem($id, $quantity, $name = NULL, $price = 0, $mode = MODE_OVERWRITE)
    {
        $ret = FALSE;
        switch ($mode) {
            case MODE_ADD:
                if (isset($_SESSION[self::$sessionKey][self::$items][$id])) {
                    $_SESSION[self::$sessionKey][self::$items][$id]['quantity'] += $quantity;
                    self::changeCount($quantity, MODE_ADD);
                    $ret = TRUE;
                }
                break;
            case MODE_OVERWRITE:
                $_SESSION[self::$sessionKey][self::$items][$id] = array(
                    'name' => $name,
                    'quantity' => $quantity,
                    'price' => $price,
                );
                self::changeCount($quantity, MODE_ADD);
                $ret = TRUE;
                break;
            case MODE_SUB:
                if (isset($_SESSION[self::$sessionKey][self::$items][$id])) {
                    $_SESSION[self::$sessionKey][self::$items][$id]['quantity'] -= $quantity;
                    $ret = TRUE;
                    self::changeCount($quantity);
                }
                break;
        }
        return $ret;
    }

    /**
     * Sets items from a single array to the basket
     *
     * @param array $data : list of items (id, quantity, name, price)
     * @param int $mode : MODE_ADD, MODE_SUB, MODE_OVERWRITE (default)
     * @return int: the count of added item types
     */
    public static function addItemFromArray($data, $mode = MODE_OVERWRITE)
    {
        $ret = 0;
        if (count($data) > 0) {
            foreach ($data as $d) {
                self::changeItem($d['id'], $d['quantity'], $d['name'], $d['price'], $mode);
                $ret++;
            }
        }
        return $ret;
    }

    /**
     * Calculates the full price of the basket
     *
     * @return float
     */
    public static function getFullPrice()
    {
        $ret = 0;
        if (count($_SESSION[self::$sessionKey][self::$items])) {
            foreach ($_SESSION[self::$sessionKey][self::$items] as $item) {
                $ret += ($item['price'] * $item['quantity']);
            }
        }
        return $ret;
    }

}