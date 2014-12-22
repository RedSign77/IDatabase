<?php
/**
 *  WunderGround Weather class for Wunderground API
 *
 *  @name WunderGroundAPI
 *  @author Zoltan Nemeth
 *  @date 2013-02-04
 *  @mail signred@gmail.com
 */
/**
 * Class WunderGroundAPI
 */
class WunderGroundAPI {

    private $key = "";
    private $db = null;
    public static $version = 1;
    private static $caching = TRUE; // Set FALSE to cachnig off
    private static $sessId = "WGAPI";
    private static $sessKey = "weather_api_key"; // WunderGround API key here!
    private static $sessURL = "weather_api_url"; // WunderGround API url here!
    private static $cacheTable = "wg_cache";
    private static $defAction = "forecast";
    private $keystone = "!key";
    private $lanstone = "!lang";
    private $actstone = "!action";
    private $url = "";
    private $langCode = "EN";
    private $acceptedLanguages = array("HU", "EN", "DE"); // Full list: http://api.wunderground.com/weather/api/d/docs?d=language-support
    private $default_city = "";
    private $country = null;

    /**
     * construct
     *
     * @param null $service_key
     * @param null $service_url
     * @param null $lang
     */
    public function __construct($service_key = null, $service_url = null, $lang = null) {
        if (self::$caching && is_null($this->db)) {
            $this->db = IDatabase::getSingleton();
        }
        $key = (is_null($service_key) ? $_SESSION[self::$sessId][self::$sessKey] : $this->setServiceKey($service_key));
        if (empty($key)) {
            die("WunderGround API : No service key selected.");
        }
        else {
            $this->key = $key;
        }
        $url = (is_null($service_url) ? $_SESSION[self::$sessId][self::$sessURL] : $this->setServiceURL($service_url));
        if (empty($url)) {
            die("WunderGround API : No service URL selected.");
        }
        else {
            $this->url = str_replace($this->keystone, $this->key, $url);
        }
        if (!is_null($lang)) {
            $lang = strtoupper(trim($lang));
            if (!in_array($lang, $this->acceptedLanguages)) {
                $this->langCode = $lang;
            }
        }
        $this->default_city = "Budapest";
    }

    /**
     * Set service key
     *
     * @param $key
     * @return mixed
     */
    private function setServiceKey($key) {
        $_SESSION[self::$sessId][self::$sessKey] = $key;
        return $key;
    }

    /**
     * Set service url
     *
     * @param $url
     * @return mixed
     */
    private function setServiceURL($url) {
        $_SESSION[self::$sessId][self::$sessURL] = $url;
        return $url;
    }

    /**
     * Get key
     *
     * @return mixed|string
     */
    public function getKey() {
        return $this->key;
    }

    /**
     * Set city
     *
     * @param $city
     * @param null $country
     */
    public function setCity($city, $country = null) {
        if (!empty($city)) {
            $this->default_city = $city;
        }
        if (!is_null($country)) {
            $this->country = $country;
        }
    }

    /**
     * Get data
     *
     * @param string $action
     * @return bool|mixed|null
     */
    public function getData($action = '') {
        $url = $this->getURL($action);
        $data = $this->cache($url);
        if (!$data) {
            $data = $this->process($url);
            if ($data && self::$caching) {
                $this->setCache($url, $data);
            }
        }
        return $data;
    }

    /**
     * Parse city and make Wunderground API special URL
     *
     * @param string $action
     * @return string $rurl;
     */
    private function getURL($action) {
        $rurl = "";
        $patterns = array(
            $this->keystone => $this->key,
            $this->lanstone => "lang:" . $this->langCode,
            $this->actstone => $this->getAction($action),
        );
        $rurl = str_replace(array_keys($patterns), $patterns, $this->url);
        $rurl.= (is_null($this->country) ? null : $this->country . "/") . preg_replace('/[^A-Za-z0-9-]+/', '_', $this->default_city) . ".json";
        return $rurl;
    }

    /**
     * Process function
     *
     * @param $url
     * @return bool|mixed
     */
    private function process($url) {
        $ret = false;
        $data = json_decode(file_get_contents($url), true);
        if (!is_null($data)) {
            $ret = $data;
        }
        return $ret;
    }

    /**
     * Refresh cache
     *
     */
    private function refreshCache() {
        if (self::$caching) {
            $this->db->delete("DELETE FROM " . self::$cacheTable . " WHERE date<'" . date("Y-m-d 00:00:00", time()) . "';");
        }
    }

    /**
     * Add data to cache
     *
     * @param $url
     * @return mixed|null
     */
    private function cache($url) {
        $ret = null;
        if (self::$caching) {
            $this->refreshCache();
            $ret = $this->db->getOneRow(self::$cacheTable, "url='" . $url . "'");
        }
        if (count($ret) > 0) {
            $ret = unserialize($ret['response']);
        }
        return $ret;
    }

    /**
     * Set the cache
     *
     * @param $url
     * @param $response
     * @throws EException
     */
    private function setCache($url, $response) {
        $this->db->insertA(
            array(
                'table' => self::$cacheTable,
                'url' => $url,
                'response' => serialize($response),
            )
        );
    }

    /**
     * Get API action from key
     *
     * @param $action
     * @return string
     */
    private function getAction($action) {
        $enabled_actions = array('forecast', 'alerts', 'almanac', 'astronomy', 'conditions', 'currenthurricane', 'forecast10day', 'geolookup', 'hourly', 'hourly10day', 'rawtide', 'satellite', 'tide', 'webcams', 'yesterday');
        if (in_array($action, $enabled_actions)) {
            return ($action);
        }
        else {
            return self::$defAction;
        }
    }

    /**
     * Overwrite for checking data
     *
     * @return string
     */
    public function __toString() {
        $ret = "<pre>";
        $ret .= "<br />WunderGround API class V" . self::$version;
        $ret .= "<br>Cache: " . (self::$caching ? "on" : "off");
        $ret .= "<br>Cache table: " . self::$cacheTable;
        $ret .= "<br>Default action: " . self::$defAction;
        $ret .= "<br>Default language: " . $this->langCode;
        $ret .= "<br>Accepted languages: " . implode(", ", $this->acceptedLanguages);
        $ret .= "<br />API session: ";
        foreach ($_SESSION[self::$sessId] as $sk => $sv) {
            $ret .= "<br /> > " . $sk . ": " . $sv;
        }
        $ret .= "</pre>";
        return ($ret);
    }

}
