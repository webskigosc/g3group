<?php

require('Validation.php');
require('Database.php');

class AjaxHandler
{
  private $validator;
  private $database;

  public function __construct()
  {
    $this->validator = new Validation();
    $this->database = new Database();
  }

  public function handleRequest()
  {
    if ($this->isAjaxRequest()) {
      $requestMethod = $this->getRequestMethod();
      $action = $this->getAction();

      if ($requestMethod === 'POST' && $action === 'submit_form') {
        $data = $this->sanitizeArray($_POST);
        $rules = $this->getFormRules();
        $this->processForm($data, $rules);
      } elseif ($requestMethod === 'GET' && $action === 'get_clients') {
        $data = $this->sanitizeArray($_GET);
        $rules = $this->getClientsRules();
        $this->processGetClients($data, $rules);
      } elseif ($requestMethod === 'GET' && $action === 'count_records') {
        $data = $this->sanitizeArray($_GET);
        $rules = $this->getCountRecordsRules();
        $this->processCountRecords($data, $rules);
      } else {
        $this->sendError('Nieprawidłowe zapytanie');
      }
    } else {
      header('HTTP/1.1 403 Forbidden');
      exit;
    }
  }

  private function isAjaxRequest(): bool
  {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
  }

  private function getRequestMethod(): string
  {
    return $_SERVER['REQUEST_METHOD'];
  }

  private function getAction(): string
  {
    return isset($_GET['action']) ? $this->getSanitizedValue($_GET['action']) : '';
  }

  private function sanitizeArray(array $array): array
  {
    $sanitizedArray = [];
    foreach ($array as $key => $value) {
      if (isset($value) && !is_null($value)) {
        $sanitizedArray[$key] = $this->getSanitizedValue($value);
      }
    }

    return $sanitizedArray;
  }

  private function getSanitizedValue($value): string
  {
    $sanitizedValue = trim($value);
    $sanitizedValue = stripslashes($sanitizedValue);
    $sanitizedValue = htmlspecialchars($sanitizedValue, ENT_QUOTES);

    return $sanitizedValue;
  }

  private function processForm(array $data, array $rules)
  {
    if ($this->validator->validate($data, $rules)) {
      $this->addPrefix($data, 'phone', '+48');
      $this->addPrefix($data, 'account', 'PL');
      $this->database->connect();
      $this->database->saveClientData($data);
      $this->sendSuccess('Formularz wysłany pomyślnie');
    } else {
      $errors = $this->validator->getErrors();
      $this->sendError('Formularz zawiera błędy', $errors);
    }
  }

  private function processGetClients(array $data, array $rules)
  {
    if ($this->validator->validate($data, $rules)) {
      $this->database->connect();
      $result = $this->database->getClients($data['sort_by'], $data['sort_order']);
      $this->sendData($result);
    } else {
      $errors = $this->validator->getErrors();
      $this->sendError('Błąd w zapytaniu', $errors);
    }
  }

  private function processCountRecords(array $data, array $rules)
  {
    if ($this->validator->validate($data, $rules)) {
      $this->database->connect();
      $result = $this->database->countClients($data['type'], $data['phrase']);
      $this->sendData($result);
    } else {
      $errors = $this->validator->getErrors();
      $this->sendError('Błąd w zapytaniu', $errors);
    }
  }

  private function getFormRules(): array
  {
    return [
      'firstname' => [
        'label' => 'Imie',
        'required' => true,
        'minLength' => 2,
        'maxLength' => 255,
        'pattern' => '/^[\-A-Za-zżźćńółęąśŻŹĆĄŚĘŁÓŃ ]{2,255}$/'
      ],
      'lastname' => [
        'label' => 'Nazwisko',
        'minLength' => 2,
        'maxLength' => 255,
        'required' => true,
        'pattern' => '/^[\-A-Za-zżźćńółęąśŻŹĆĄŚĘŁÓŃ ]{2,255}$/'
      ],
      'email' => [
        'label' => 'Email',
        'required' => true,
        'email' => true,
      ],
      'phone' => [
        'label' => 'Telefon',
        'required' => false,
        'pattern' => '/^[0-9]{3} [0-9]{3} [0-9]{3}$/'
      ],
      'client' => [
        'label' => 'Numer klienta',
        'required' => true,
        'pattern' => '/^[0]{3}[0-9]{3}-[A-Z]{5}$/'
      ],
      'choose' => [
        'label' => 'Wybór',
        'required' => true,
        'pattern' => '/^(1|2)$/'
      ],
      'account' => [
        'label' => 'Numer konta',
        'requiredIf' => ['choose', 1],
        'pattern' => '/^[0-9]{2} [0-9]{4} [0-9]{4} [0-9]{4} [0-9]{4} [0-9]{4} [0-9]{4}$/'
      ],
      'agreementgdpr' => [
        'label' => 'Oświadczenie 1',
        'required' => true,
        'pattern' => '/^(0|1)$/'
      ],
      'agreementterms' => [
        'label' => 'Oświadczenie 2',
        'required' => true,
        'integer' => true
      ],
      'agreementads' => [
        'label' => 'Zgoda 1',
        'required' => false,
        'integer' => true
      ],
    ];
  }

  private function getClientsRules(): array
  {
    return [
      'sort_by' => [
        'required' => true,
        'label' => 'Sortuj wg',
        'pattern' => '/^(first_name|last_name|email|phone|client_no|choose|bank_account|agreement_gdpr|agreement_terms|agreement_ads)$/'
      ],
      'sort_order' => [
        'required' => true,
        'label' => 'Kolejność',
        'pattern' => '/^(ASC|DESC)$/'
      ],
    ];
  }

  private function getCountRecordsRules(): array
  {
    return [
      'type' => [
        'required' => true,
        'label' => 'Typ',
        'pattern' => '/^(first_name|last_name|email)$/'
      ],
      'phrase' => [
        'required' => true,
        'label' => 'Fraza',
        'minLength' => 2,
        'maxLength' => 255
      ],
    ];
  }

  private function addPrefix(array &$data, string $field, string $prefix)
  {
    if (!empty($data[$field]) && !preg_match('/^' . $prefix . '/', $data[$field])) {
      $data[$field] = $prefix . $data[$field];
    }
  }

  private function sendData($data = null)
  {
    $response = !is_null($data) ? array('success' => true, 'data' => $data) : array('success' => false, 'message' => 'Brak danych');
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
  }

  private function sendSuccess($message, $data = null)
  {
    $response = array('success' => true, 'message' => $message, 'data' => $data);
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
  }

  private function sendError($message, $errors = null)
  {
    $response = array('success' => false, 'message' => $message, 'errors' => $errors);
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
  }
}
