<?php
include 'admin/api/config.php';
$cols = $pdo->query("SHOW COLUMNS FROM projects")->fetchAll(PDO::FETCH_COLUMN);
file_put_contents('schema_out.txt', "PROJECTS columns: " . implode(', ', $cols) . "\n");

$row = $pdo->query("SELECT * FROM projects LIMIT 1")->fetch(PDO::FETCH_ASSOC);
file_put_contents('schema_out.txt', print_r($row, true), FILE_APPEND);
echo "Done - see schema_out.txt";
