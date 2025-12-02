<?php
require __DIR__ . "/config.php";
echo "Connected to MySQL successfully!<br>";

$res = $conn->query("SHOW TABLES");
if ($res) {
    echo "Tables in `$dbname`:<br>";
    while ($row = $res->fetch_array()) {
        echo "&bull; " . htmlspecialchars($row[0]) . "<br>";
    }
} else {
    echo "Query error: " . $conn->error;
}
