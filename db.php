<?php
error_reporting(E_ALL);

$config = [];
$configFile = __DIR__ . '/config.local.php';

if (is_file($configFile)) {
    $loadedConfig = require $configFile;
    if (is_array($loadedConfig)) {
        $config = $loadedConfig;
    }
}

$debugMode = filter_var(getenv('APP_DEBUG') ?: ($config['debug'] ?? '0'), FILTER_VALIDATE_BOOLEAN);
ini_set('display_errors', $debugMode ? '1' : '0');

function read_env_value(string $key): ?string
{
    $value = getenv($key);
    if ($value !== false && $value !== '') {
        return (string) $value;
    }

    foreach ([$_ENV, $_SERVER] as $source) {
        if (isset($source[$key]) && $source[$key] !== '') {
            return (string) $source[$key];
        }
    }

    return null;
}

function read_config_value(array $config, array $envKeys, array $configKeys, string $default = ''): string
{
    foreach ($envKeys as $envKey) {
        $value = read_env_value($envKey);
        if ($value !== null) {
            return $value;
        }
    }

    foreach ($configKeys as $configKey) {
        if (isset($config[$configKey]) && $config[$configKey] !== '') {
            return (string) $config[$configKey];
        }
    }

    return $default;
}

if (isset($config['db']) && is_array($config['db'])) {
    $config = array_merge($config['db'], $config);
}

$host = read_config_value($config, ['DB_HOST', 'MYSQL_HOST'], ['host', 'hostname']);
$db = read_config_value($config, ['DB_NAME', 'MYSQL_DATABASE'], ['database', 'db', 'dbname']);
$user = read_config_value($config, ['DB_USER', 'MYSQL_USER'], ['user', 'username']);
$pass = read_config_value($config, ['DB_PASS', 'MYSQL_PASSWORD'], ['password', 'pass']);
$charset = read_config_value($config, ['DB_CHARSET', 'MYSQL_CHARSET'], ['charset'], 'utf8mb4');

if ($host === '' || $db === '' || $user === '' || $pass === '') {
    $missing = [];
    if ($host === '') $missing[] = 'host';
    if ($db === '') $missing[] = 'database';
    if ($user === '') $missing[] = 'user';
    if ($pass === '') $missing[] = 'password';
    
    $errorMsg = "Die Datenbank-Konfiguration ist unvollstaendig. (Fehlend: " . implode(', ', $missing) . ")";
    if (!is_file($configFile)) {
        $errorMsg .= " - Datei config.local.php wurde NICHT gefunden.";
    } elseif (!is_array($config) || empty($config)) {
        $errorMsg .= " - Datei gefunden, aber Inhalt ist kein gueltiges Array oder leer.";
    }
    
    error_log($errorMsg);
    die($errorMsg . " Bitte Server-Konfiguration pruefen.");
}

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // 1. Ensure Table 'users' exists (for admin login)
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('admin', 'bride', 'groom') NOT NULL DEFAULT 'admin',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 2. Ensure Table 'guests' exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS guests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        guest_hash VARCHAR(32) NOT NULL UNIQUE,
        guest_side ENUM('bride', 'groom', 'both') NOT NULL DEFAULT 'both',
        salutation_1 VARCHAR(20) DEFAULT NULL,
        first_name_1 VARCHAR(100) NOT NULL,
        last_name_1 VARCHAR(100) NOT NULL,
        salutation_2 VARCHAR(20) DEFAULT NULL,
        first_name_2 VARCHAR(100) DEFAULT NULL,
        last_name_2 VARCHAR(100) DEFAULT NULL,
        phone_number VARCHAR(50) DEFAULT NULL,
        invited_events JSON DEFAULT NULL,
        family_members JSON DEFAULT NULL,
        with_family TINYINT(1) NOT NULL DEFAULT 0,
        status ENUM('pending', 'accepted', 'declined', 'partial') DEFAULT 'pending',
        rsvp_status_events JSON DEFAULT NULL,
        attending_members JSON DEFAULT NULL,
        attending_members_version TINYINT(1) NOT NULL DEFAULT 1,
        invitation_sent TINYINT(1) NOT NULL DEFAULT 0,
        message TEXT DEFAULT NULL,
        dietary_info TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 3. Check for specific column migrations
    $requiredColumns = [
        'role' => "ALTER TABLE users ADD COLUMN role ENUM('admin', 'bride', 'groom') NOT NULL DEFAULT 'admin' AFTER password_hash",
        'guest_side' => "ALTER TABLE guests ADD COLUMN guest_side ENUM('bride', 'groom', 'both') NOT NULL DEFAULT 'both' AFTER guest_hash",
        'invited_events' => "ALTER TABLE guests ADD COLUMN invited_events JSON DEFAULT NULL AFTER phone_number",
        'rsvp_status_events' => "ALTER TABLE guests ADD COLUMN rsvp_status_events JSON DEFAULT NULL AFTER status",
        'with_family' => "ALTER TABLE guests ADD COLUMN with_family TINYINT(1) NOT NULL DEFAULT 0 AFTER family_members",
        'attending_members_version' => "ALTER TABLE guests ADD COLUMN attending_members_version TINYINT(1) NOT NULL DEFAULT 1 AFTER attending_members",
        'phone_number' => "ALTER TABLE guests ADD COLUMN phone_number VARCHAR(50) DEFAULT NULL AFTER last_name_2",
        'invitation_sent' => "ALTER TABLE guests ADD COLUMN invitation_sent TINYINT(1) NOT NULL DEFAULT 0",
    ];

    foreach ($requiredColumns as $column => $migrationSql) {
        try {
            if ($column === 'role') {
                $pdo->query("SELECT $column FROM users LIMIT 1");
            } else {
                $pdo->query("SELECT $column FROM guests LIMIT 1");
            }
        } catch (\PDOException $e) {
            try {
                $pdo->exec($migrationSql);
            } catch (\PDOException $inner) {
                // Ignore errors if column already exists
            }
        }
    }
} catch (\PDOException $e) {
    error_log($e->getMessage());
    if ($debugMode) {
        die("Datenbankfehler: " . htmlspecialchars($e->getMessage()));
    }
    die("Verbindung zur Datenbank fehlgeschlagen. Bitte pruefen Sie die Konfiguration.");
}
