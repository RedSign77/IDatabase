<?php
/**
 * Simple Validator class for forms
 *
 * @author Németh Zoltán
 * @since 2014-06-18
 * @package PHP 5.2 or greater (filter_var)
 */
/**
 * Class FormDataValidator
 */
class FormDataValidator {

    private $required = null;
    private $accepted = array();
    private $submitted = array();
    private $errors = array();
    private $validated = array();

    /**
     * construct
     *
     * @param $submitted
     * @param $accepted
     * @param null $required
     */
    public function __construct($submitted, $accepted, $required = null) {
        $this->submitted = $submitted;
        $this->accepted = $accepted;
        $this->required = $required;
    }

    /**
     * Validate fields
     *
     * @return array
     */
    public function validate() {
        $this->errors = array();
        if (is_array($this->submitted) && count($this->submitted) > 0) {
            $this->required();
            foreach ($this->submitted as $fkey => $fdata) {
                if ($this->isAcceptable($fkey)) {
                    if (!isset($this->errors[$fkey])) {
                        $preval = $this->preValidateFromKey($fkey, $fdata);
                        if ($preval['found'] && !is_null($preval['message'])) {
                            $this->addError($fkey, $preval['message']);
                        }
                        else {
                            $this->validated[$fkey] = (!is_null($preval['data']) ? $preval['data'] : $fdata);
                        }
                    }
                    else {
                        $this->addError($fkey, $this->getErrorMessage($fkey));
                    }
                }
            }
        }
        return array('errors' => $this->errors, 'validated' => $this->validated);
    }

    /**
     * Required checking
     */
    private function required() {
        if (!is_null($this->required)) {
            foreach ($this->required as $rkey) {
                if (!isset($this->submitted[$rkey]) || empty($this->submitted[$rkey])) {
                    $this->addError($rkey, $this->getErrorMessage($rkey, true));
                }
            }
        }
    }

    /**
     * Add error if necesseray
     *
     * @param $key
     * @param $msg
     */
    private function addError($key, $msg) {
        if (!isset($this->errors[$key])) {
            $this->errors[$key] = $msg;
        }
    }

    /**
     * Field is acceptable
     *
     * @param $key
     * @return bool
     */
    private function isAcceptable($key) {
        if (is_array($this->accepted)) {
            return in_array($key, $this->accepted);
        }
        return false;
    }

    /**
     * Field is required
     *
     * @param $key
     * @return bool
     */
    private function isRequired($key) {
        if (is_array($this->required)) {
            return in_array($key, $this->required);
        }
    }

    /**
     * Pre validate for custom fields
     *
     * @param $key
     * @param $value
     * @return array
     */
    private function preValidateFromKey($key, $value) {
        $ret = array(
            'found' => false,
            'message' => null,
        );
        switch ($key) {
            case "email":
                if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $ret = array(
                        'found' => true,
                        'message' => null,
                    );
                }
                else {
                    $ret = array(
                        'found' => true,
                        'message' => $this->getErrorMessage($key),
                    );
                }
                break;
        }
        return $ret;
    }

    /**
     * Get custom error messages
     *
     * @param $fieldName
     * @param bool $required
     * @return bool|string
     */
    private function getErrorMessage($fieldName, $required = false) {
        $ret = false;
        switch ($fieldName) {
            case "email":
                $ret = $required ? "The e-mail address field is required!" : "Please you give a valid e-mail address!";
                break;
            case "nick":
                $ret = $required ? "The nick field is required!" : "The nick field format is not valid!";
                break;
            case "name":
                $ret = $required ? "The name field is required!" : "The name field format is not valid!";
                break;
            case "message":
                $ret = $required ? "The message field is required!" : "The message field format is not valid!";
                break;
            default:
                $ret = $required ? "the " . $fieldName . " field is required!" : "The " . $fieldName . " field format is not valid!";
                break;
        }
        return $ret;
    }

}