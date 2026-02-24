<?php
include 'config.php';
$stmt = $pdo->query("SELECT DISTINCT type FROM projects");
while($row = $stmt->fetch()) {
    echo $row['type'] . "\n";
}
?>
