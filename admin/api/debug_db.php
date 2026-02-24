<?php
include 'config.php';
try {
    $stmt = $pdo->query("SELECT * FROM projects");
    $projects = $stmt->fetchAll();
    echo "Total Projects: " . count($projects) . "\n";
    foreach ($projects as $p) {
        echo "ID: {$p['id']}, Name: {$p['name']}, Type: {$p['type']}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
