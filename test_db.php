<?php
header('Content-Type: text/plain');

$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: 'M@hir2005';
$name = getenv('DB_NAME') ?: 'perfex_crm';
$ssl_on = getenv('DB_SSL') !== 'false';

echo "Testing Database Connection to Aiven MySQL\n";
echo "-----------------------------------------\n";
echo "Host resolved: $host\n";
echo "User resolved: $user\n";
echo "DB resolved: $name\n";
echo "Password length: " . strlen($pass) . "\n";
echo "SSL Enabled: " . ($ssl_on ? 'YES' : 'NO') . "\n\n";

// Parse host and port if present
$port = 3306;
$clean_host = $host;
if (preg_match('/^(.*):(\d+)$/', $host, $matches)) {
    $clean_host = $matches[1];
    $port = (int)$matches[2];
}

echo "Clean Host: $clean_host\n";
echo "Port: $port\n\n";

// Test 1: Standard connection without SSL
echo "Test 1: Connecting WITHOUT SSL...\n";
$mysqli = mysqli_init();
if (!$mysqli) {
    echo "mysqli_init failed\n";
} else {
    $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
    if (@$mysqli->real_connect($clean_host, $user, $pass, $name, $port)) {
        echo "SUCCESS! Connected without SSL.\n";
        $mysqli->close();
    } else {
        echo "FAILED: " . mysqli_connect_error() . " (Code: " . mysqli_connect_errno() . ")\n";
    }
}
echo "\n";

// Test 2: Connection with SSL but no CA file validation (ssl_verify = false)
echo "Test 2: Connecting WITH SSL (ssl_verify = false)...\n";
$mysqli = mysqli_init();
if (!$mysqli) {
    echo "mysqli_init failed\n";
} else {
    $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
    // Force SSL flags
    $client_flags = MYSQLI_CLIENT_SSL;
    if (defined('MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT')) {
        $client_flags |= MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT;
    }
    // Set empty SSL parameters to trigger SSL mode
    $mysqli->ssl_set(NULL, NULL, NULL, NULL, NULL);
    if (@$mysqli->real_connect($clean_host, $user, $pass, $name, $port, NULL, $client_flags)) {
        echo "SUCCESS! Connected with SSL (no verify).\n";
        $mysqli->close();
    } else {
        echo "FAILED: " . mysqli_connect_error() . " (Code: " . mysqli_connect_errno() . ")\n";
    }
}
echo "\n";

// Test 3: Connection with SSL and system CA certificate
echo "Test 3: Connecting WITH SSL using system CA (/etc/ssl/certs/ca-certificates.crt)...\n";
$mysqli = mysqli_init();
if (!$mysqli) {
    echo "mysqli_init failed\n";
} else {
    $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
    $ca_path = '/etc/ssl/certs/ca-certificates.crt';
    echo "CA path exists? " . (file_exists($ca_path) ? 'YES' : 'NO') . "\n";
    
    $client_flags = MYSQLI_CLIENT_SSL;
    $mysqli->ssl_set(NULL, NULL, $ca_path, NULL, NULL);
    if (@$mysqli->real_connect($clean_host, $user, $pass, $name, $port, NULL, $client_flags)) {
        echo "SUCCESS! Connected with SSL (system CA).\n";
        $mysqli->close();
    } else {
        echo "FAILED: " . mysqli_connect_error() . " (Code: " . mysqli_connect_errno() . ")\n";
    }
}
echo "\n";

// Test 4: Verify default database list
echo "Test 4: Trying to connect to host without database name...\n";
$mysqli = mysqli_init();
if ($mysqli) {
    $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
    if (@$mysqli->real_connect($clean_host, $user, $pass, null, $port)) {
        echo "SUCCESS! Connected to MySQL server without selecting DB.\n";
        $res = $mysqli->query("SHOW DATABASES");
        if ($res) {
            echo "Databases found:\n";
            while ($row = $res->fetch_row()) {
                echo " - " . $row[0] . "\n";
            }
        }
        $mysqli->close();
    } else {
        echo "FAILED: " . mysqli_connect_error() . " (Code: " . mysqli_connect_errno() . ")\n";
    }
}
?>
