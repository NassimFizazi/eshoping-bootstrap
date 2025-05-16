<?php
class Validator {
    private $errors = [];

    // Get all validation errors
    public function getErrors() {
        return $this->errors;
    }

    // Check if there are any errors
    public function hasErrors() {
        return !empty($this->errors);
    }

    // Required field validation
    public function required($field, $value, $message = null) {
        if (empty($value)) {
            $this->errors[$field] = $message ?? "$field is required";
            return false;
        }
        return true;
    }

    // Minimum length validation
    public function minLength($field, $value, $length, $message = null) {
        if (strlen($value) < $length) {
            $this->errors[$field] = $message ?? "$field must be at least $length characters";
            return false;
        }
        return true;
    }

    // Maximum length validation
    public function maxLength($field, $value, $length, $message = null) {
        if (strlen($value) > $length) {
            $this->errors[$field] = $message ?? "$field must be at most $length characters";
            return false;
        }
        return true;
    }

    // Email validation
    public function email($field, $value, $message = null) {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $message ?? "$field must be a valid email address";
            return false;
        }
        return true;
    }

    // Numeric validation
    public function numeric($field, $value, $message = null) {
        if (!is_numeric($value)) {
            $this->errors[$field] = $message ?? "$field must be a number";
            return false;
        }
        return true;
    }

    // Integer validation
    public function integer($field, $value, $message = null) {
        if (!filter_var($value, FILTER_VALIDATE_INT)) {
            $this->errors[$field] = $message ?? "$field must be an integer";
            return false;
        }
        return true;
    }

    // Match validation (for password confirmation)
    public function match($field, $value, $matchField, $matchValue, $message = null) {
        if ($value !== $matchValue) {
            $this->errors[$field] = $message ?? "$field does not match $matchField";
            return false;
        }
        return true;
    }

    // Phone number validation
    public function phone($field, $value, $message = null) {
        if (!preg_match("/^[0-9]{10,15}$/", preg_replace("/[^0-9]/", "", $value))) {
            $this->errors[$field] = $message ?? "$field must be a valid phone number";
            return false;
        }
        return true;
    }

    // Zip code validation
    public function zipCode($field, $value, $message = null) {
        if (!preg_match("/^[0-9]{5}(-[0-9]{4})?$/", $value)) {
            $this->errors[$field] = $message ?? "$field must be a valid zip code";
            return false;
        }
        return true;
    }

    // Custom validation
    public function custom($field, $condition, $message) {
        if (!$condition) {
            $this->errors[$field] = $message;
            return false;
        }
        return true;
    }
}
?>
