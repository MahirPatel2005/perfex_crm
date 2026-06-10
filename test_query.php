<?php
header('Content-Type: text/plain');
mysqli_report(MYSQLI_REPORT_OFF);

$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: 'M@hir2005';
$name = getenv('DB_NAME') ?: 'perfex_crm';

// Parse host and port if present
$port = 3306;
$clean_host = $host;
if (preg_match('/^(.*):(\d+)$/', $host, $matches)) {
    $clean_host = $matches[1];
    $port = (int)$matches[2];
}

$mysqli = mysqli_init();
if (!$mysqli) {
    die("mysqli_init failed\n");
}

$mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
$ca_path = dirname(__FILE__) . '/ca.pem';
$client_flags = file_exists($ca_path) ? MYSQLI_CLIENT_SSL : 0;
if (file_exists($ca_path)) {
    $mysqli->ssl_set(NULL, NULL, $ca_path, NULL, NULL);
}

if (!$mysqli->real_connect($clean_host, $user, $pass, $name, $port, NULL, $client_flags)) {
    die("Connection failed: " . mysqli_connect_error() . "\n");
}

echo "Connection established successfully!\n\n";

// Test 1: Query tbloptions
echo "Testing query on tbloptions:\n";
$res = $mysqli->query("SELECT * FROM tbloptions LIMIT 5");
if ($res) {
    echo "Query succeeded! Retrieved options:\n";
    while ($row = $res->fetch_assoc()) {
        echo " - " . $row['name'] . " = " . $row['value'] . "\n";
    }
} else {
    echo "Query failed: " . $mysqli->error . " (Code: " . $mysqli->errno . ")\n";
}
echo "\n";

// Test 2: Check tblstaff
echo "Testing query on tblstaff:\n";
$res = $mysqli->query("SELECT staffid, email, active FROM tblstaff");
if ($res) {
    echo "Query succeeded! Retrieved staff:\n";
    while ($row = $res->fetch_assoc()) {
        echo " - ID: " . $row['staffid'] . " | Email: " . $row['email'] . " | Active: " . $row['active'] . "\n";
    }
} else {
    echo "Query failed: " . $mysqli->error . " (Code: " . $mysqli->errno . ")\n";
}

$mysqli->close();
?>
