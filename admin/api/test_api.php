<?php
// Quick API test
$_GET['action'] = 'get_project_details';
$_GET['id'] = 1;
$_SERVER['REQUEST_METHOD'] = 'GET';

ob_start();
include 'api.php';
$output = ob_get_clean();

$decoded = json_decode($output, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "✅ API returns valid JSON\n";
    echo "Keys: " . implode(', ', array_keys($decoded)) . "\n";
    echo "Project: " . ($decoded['project']['name'] ?? 'N/A') . "\n";
    echo "Plans count: " . count($decoded['plans'] ?? []) . "\n";
    echo "Gallery count: " . count($decoded['gallery'] ?? []) . "\n";
    echo "Details: " . ($decoded['details'] ? 'present' : 'null') . "\n";
} else {
    echo "❌ Invalid JSON: " . json_last_error_msg() . "\n";
    echo "Output: " . substr($output, 0, 500) . "\n";
}
