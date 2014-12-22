<?php
/**
 * Created by PhpStorm.
 * User: RedSign
 * Date: 2014.12.22.
 * Time: 19:31
 */

class EException extends Exception {

    public function __construct($message, $code = 0, $previous = null) {
        parent::__construct(trim($message), intval($code), $previous);
        $this->setLOG($message."| Error code: ".$code);
    }

    private function setLOG($text) {
        $file = null;
        if(isAjax())
            $file = fopen("../logs/" . date("Ym", time()) . ".txt", "a");
        else
            $file = fopen("logs/" . date("Ym", time()) . ".txt", "a");
        if (!is_null($file)) {
            fputs($file, date("Y-m-d H:i:s") . " :: " . getIP() . " >> " . $text . "\n");
            fclose($file);
        }
    }

}