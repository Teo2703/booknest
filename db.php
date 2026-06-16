<?php
mysqli_report(MYSQLI_REPORT_OFF);

$conn = null;
$lastError = "";

/*
    1. Try cloud database first if config/db_config.php exists
    2. If cloud config not found or connection fails, fallback to local 3306 / 3307
*/

$configPath = __DIR__ . '/config/db_config.php';

if (file_exists($configPath)) {
    $config = require $configPath;

    if (!empty($config['use_cloud'])) {
        $cloudHost = $config['cloud_host'];
        $cloudPort = (int)$config['cloud_port'];
        $cloudUser = $config['cloud_user'];
        $cloudPassword = $config['cloud_password'];
        $cloudDatabase = $config['cloud_database'];
        $cloudCa = $config['cloud_ca'];

        $conn = mysqli_init();

        if (!empty($cloudCa) && file_exists($cloudCa)) {
            mysqli_ssl_set($conn, null, null, $cloudCa, null, null);
        }

        $connected = @mysqli_real_connect(
            $conn,
            $cloudHost,
            $cloudUser,
            $cloudPassword,
            $cloudDatabase,
            $cloudPort,
            null,
            MYSQLI_CLIENT_SSL
        );

        if ($connected) {
            $conn->set_charset("utf8mb4");
        } else {
            $lastError = mysqli_connect_error();
            $conn = null;
        }
    }
}

/* Local fallback: support teammate 3306 and your 3307 */
if ($conn === null) {
    $host = "127.0.0.1";
    $user = "root";
    $password = "";
    $database = "booknest";
    $ports = [3306, 3307];

    foreach ($ports as $port) {
        $conn = @new mysqli($host, $user, $password, $database, $port);

        if (!$conn->connect_error) {
            $conn->set_charset("utf8mb4");
            break;
        }

        $lastError = $conn->connect_error;
        $conn = null;
    }
}

if ($conn === null) {
    die("Database connection failed. Error: " . $lastError);
}
?>