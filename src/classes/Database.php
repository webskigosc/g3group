<?php
require(dirname(__FILE__) . '/../config.php');

class Database
{
  private $host;
  private $name;
  private $user;
  private $password;
  private $pdo;

  public function __construct()
  {
    $this->host = DB_HOST;
    $this->name = DB_NAME;
    $this->user = DB_USER;
    $this->password = DB_PASSWORD;
  }

  public function connect()
  {
    $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->name . ";charset=utf8mb4";
    $options = [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      // PDO::MYSQL_ATTR_SSL_CA => '/path/to/your/ssl/ca/cert.pem', // Optional
      PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci'
    ];

    try {
      $this->pdo = new PDO($dsn, $this->user, $this->password, $options);
    } catch (PDOException $e) {
      throw new PDOException($e->getMessage(), (int)$e->getCode());
    }
  }

  public function query($sql, $params = [])
  {
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
  }

  public function fetchAll($sql, $params = [])
  {
    $stmt = $this->query($sql, $params);
    return $stmt->fetchAll();
  }

  public function fetchOne($sql, $params = [])
  {
    $stmt = $this->query($sql, $params);
    return $stmt->fetch();
  }

  public function close()
  {
    $this->pdo = null;
  }

  public function saveClientData($data)
  {
    if (!is_array($data) || empty($data)) {
      throw new InvalidArgumentException('Data must be a non-empty array');
    }

    $sql = 'INSERT INTO client (first_name, last_name, email, phone, client_no, choose, bank_account, agreement_gdpr, agreement_terms, agreement_ads) VALUES (:firstname, :lastname, :email, :phone, :clientno, :choose, :bankaccount, :agreementgdpr, :agreementterms, :agreementads)';

    $stmt = $this->pdo->prepare($sql);

    try {
      $stmt->bindParam(':firstname', $data['firstname']);
      $stmt->bindParam(':lastname', $data['lastname']);
      $stmt->bindParam(':email', $data['email']);
      $stmt->bindValue(':phone', isset($data['phone']) ? $data['phone'] : '');
      $stmt->bindParam(':clientno', $data['client']);
      $stmt->bindParam(':choose', $data['choose']);
      $stmt->bindValue(':bankaccount', isset($data['account']) ? $data['account'] : '');
      $stmt->bindValue(':agreementgdpr', isset($data['agreementgdpr']) ? $data['agreementgdpr'] : 0);
      $stmt->bindValue(':agreementterms', isset($data['agreementterms']) ? $data['agreementterms'] : 0);
      $stmt->bindValue(':agreementads', isset($data['agreementads']) ? $data['agreementads'] : 0);
      $stmt->execute();

      return $this->pdo->lastInsertId();
    } catch (PDOException $e) {
      throw new PDOException($e->getMessage(), (int)$e->getCode());
    }
  }

  public function getClients(string $sortBy = null, string $sortOrder = null): array
  {
    $allowedSortBy = ['first_name', 'last_name', 'email', 'phone', 'client_no', 'choose', 'bank_account'];
    $sortBy = in_array($sortBy, $allowedSortBy, true) ? $sortBy : 'first_name';

    $allowedSortOrder = ['ASC', 'DESC'];
    $sortOrder = in_array($sortOrder, $allowedSortOrder, true) ? strtoupper($sortOrder) : 'ASC';

    $sql = 'SELECT first_name, last_name, email, phone, client_no, choose, bank_account, agreement_gdpr, agreement_terms, agreement_ads FROM client';

    if ($sortBy && $sortOrder) {
      $sql .= ' ORDER BY ' . $sortBy . ' ' . $sortOrder;
    }
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll();
  }

  public function countClients($column, $phrase)
  {
    $sql = 'SELECT COUNT(*) FROM client WHERE ' . $column . ' LIKE :value';
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindValue(':value', '%' . $phrase . '%');
    $stmt->execute();
    return $stmt->fetchColumn();
  }
}
