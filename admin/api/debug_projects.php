<?php
include 'config.php';
$stmt = $pdo->query("SELECT id, name, detail_url FROM projects ORDER BY id DESC");
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($projects);
?>
