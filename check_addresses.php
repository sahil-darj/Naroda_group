<?php
$response = @file_get_contents('http://localhost/My%20Web%20Sites/narodagroup/shilpgroup.com/admin2/api/api.php?action=get_projects');
if ($response === false) {
    echo "API failed. Checking direct DB.\n";
    include 'admin2/api/config.php';
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $res = $conn->query("SELECT id, name, location_address FROM projects");
    while($row = $res->fetch_assoc()) {
        print_r($row);
    }
} else {
    $projects = json_decode($response, true);
    foreach ($projects as $p) {
        echo "ID: {$p['id']} | Name: {$p['name']} | Address: {$p['location_address']}\n";
    }
}
