<?php

class Validator {
    private array $user_input = [];
    private array $errors_array = [];

    public function __construct(array $raw_input)
    {
        $this->user_input = $raw_input;
    }

    private function value(string $field_name): string {
        $value = $this->user_input[$field_name] ?? '';
        return is_string($value) ? trim($value ) : '';
    }

    private function addError(string $field_name, string $message): void {
        $this->errors_array[$field_name][] = $message;
    }


    public function required(string $field_name): void {
        if ($this->value($field_name) === '') {
            $this->addError($field_name, "{$field_name} required");
        }
    }

    public function email(string $field_name = 'email'): void {
        $value = $this->value($field_name);
        if ($value === '') {
            return;
        }
        if(! filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field_name, "Invalid {$field_name} format");
            
        }
        
    }

    public function min (string $field_name, int $min_length): void {
        $value = $this->value($field_name);
        if ($value === '') {
            return;
        }
        if (strlen($value) < $min_length) {
            $this->addError($field_name, "{$field_name} should be at least {$min_length} characters");
        }
    }

    public function password (string $field_name = 'password'): void {
        $value = $this->value($field_name);
        if ($value === '') {
            return;
        }
        $uppercase = preg_match('@[A-Z]@', $value);
        $lowercase = preg_match('@[a-z]@', $value);
        $number = preg_match('@[0-9]@', $value);
        $special = preg_match('@[^\w]@', $value);
        if (! $uppercase || ! $lowercase || ! $number || ! $special) {
            $this->addError($field_name, "Password must contain:  one uppercase letter - one lowercase letter - one number - one special character");
        }
    }

    public function confirmPassword (string $field_name = 'confirm_password'): void {
        $value = $this->value($field_name);
        if ($value === '') {
            return;
        }
        $password = $this->value('password');
        if ($value !== $password) {
            $this->addError($field_name, "unmatched passwords");
        }
    }

    public function positiveNumber(string $field_name):void {
        $value = $this->value($field_name);
        if($value === '') {
            return;
        }
        if(!is_numeric($value)) {
            $this->addError($field_name, "negative values are not acceptable. choose positive number");
        }
        if ($value < 0) {
            $this->addError($field_name, "negative values are not acceptable. choose positive number");
        }
    }

    public function errors (): array {
        return $this->errors_array;
    }

    public function fails(): bool {
        return ! empty($this->errors_array);
    }
}