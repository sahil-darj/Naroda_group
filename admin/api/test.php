<?php
include 'config.php';
$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
echo "Tables: " . implode(', ', $tables) . "\n";

// Check projects
$projects = $pdo->query('SELECT id, name FROM projects')->fetchAll();
echo "Projects: ";
foreach($projects as $p) echo "[{$p['id']}] {$p['name']}  ";
echo "\n";

// Check project_details table exists
$cols = $pdo->query("DESCRIBE project_details")->fetchAll(PDO::FETCH_COLUMN);
echo "project_details columns: " . implode(', ', $cols) . "\n";
