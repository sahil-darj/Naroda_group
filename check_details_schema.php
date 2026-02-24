<?php
include 'admin/api/config.php';
$cols = $pdo->query("SHOW COLUMNS FROM project_details")->fetchAll(PDO::FETCH_COLUMN);
file_put_contents('schema_details_out.txt', "PROJECT_DETAILS columns: " . implode(', ', $cols) . "\n");
echo "Done - see schema_details_out.txt";
?>
