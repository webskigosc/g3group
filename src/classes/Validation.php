<?php
class Validation
{
  private $errors = [];
  private $data;

  public function validate($data, $rules)
  {
    $this->data = $data;
    if (empty($data) || !is_array($data)) {
      $this->addError('general', 'Brak danych do walidacji');
      return false;
    }

    $requiredFields = array_keys(array_filter($rules, function ($rule) {
      return isset($rule['required']) && $rule['required'];
    }));

    $missingFields = array_diff($requiredFields, array_keys($data));

    if (!empty($missingFields)) {
      foreach ($missingFields as $field) {
        $lable = $rules[$field]['label'] ?? $field;
        $this->addError($field, "$lable jest wymagane");
      }
    }

    foreach ($rules as $field => $rule) {
      if (!isset($data[$field]) && !$this->isConditionRequired($rule, $field)) {
        continue;
      }

      $value = $data[$field] ?? null;
      $label = $rule['label'] ?? $field;
      if ($this->isConditionRequired($rule, $field) && empty($value)) {
        $this->addError($field, "$label jest wymagane");
        continue;
      }
      foreach ($rule as $validationType => $validationParam) {
        if ($validationType === 'requiredIf') {
          continue;
        }
        $method = "validate" . ucfirst($validationType);
        if (method_exists($this, $method)) {
          if (empty($value) && $validationType !== 'required') {
            continue;
          }
          $this->$method($field, $label, $value, $validationParam);
        }
      }
    }
    return empty($this->errors);
  }

  private function isConditionRequired($rule, $field)
  {
    if (isset($rule['requiredIf'])) {
      $condition = $rule['requiredIf'];
      if (is_callable($condition)) {
        return $condition($this->data);
      } elseif (is_array($condition)) {
        $otherField = $condition[0];
        $otherValue = $condition[1];
        return isset($this->data[$otherField]) && $this->data[$otherField] == $otherValue;
      }
    }
    return false;
  }
  private function validateRequired($field, $label, $value, $param)
  {
    if ($param && empty($value)) {
      $this->addError($field, "$label jest wymagane");
    }
  }

  private function validateMinLength($field, $label, $value, $param)
  {
    if (strlen($value) < $param) {
      $this->addError($field, "$label musi mieć co najmniej $param znaków");
    }
  }

  private function validateMaxLength($field, $label, $value, $param)
  {
    if (strlen($value) > $param) {
      $this->addError($field, "$label nie może przekracza $param znaków");
    }
  }

  private function validatePattern($field, $label, $value, $param)
  {
    if (!preg_match($param, $value)) {
      $this->addError($field, "$label ma nieprawidłowy format");
    }
  }

  private function validateEmail($field, $label, $value)
  {
    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
      $this->addError($field, "$label ma nieprawidłowy format");
    }
  }

  private function validateInteger($field, $label, $value)
  {
    if (!filter_var($value, FILTER_VALIDATE_INT)) {
      $this->addError($field, "$label musi być liczbą całkowitą");
    }
  }

  private function validateString($field, $label, $value)
  {
    if (!is_string($value)) {
      $this->addError($field, "$label ma nieprawidłowy format");
    }
  }

  private function addError($field, $message)
  {
    $this->errors[$field][] = $message;
  }

  public function getErrors()
  {
    return $this->errors;
  }
}
