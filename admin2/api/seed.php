<?php
include 'config.php';

try {
    // Clear existing data (optional, but good for a fresh start)
    $pdo->exec("DELETE FROM featured_properties");
    $pdo->exec("DELETE FROM project_pricing");
    $pdo->exec("DELETE FROM project_gallery");
    $pdo->exec("DELETE FROM project_apartment_plans");
    $pdo->exec("DELETE FROM projects");

    // 1. Insert Projects
    $projects = [
        [
            'id' => 1,
            'name' => 'Naroda Lavish',
            'type' => 'Premium Residences',
            'price' => '12,500',
            'location' => 'Ahmedabad, Gujarat',
            'description' => 'Experience the height of urban living with premium 3 & 4 BHK apartments.',
            'image' => 'naroda_group_assets/1 (26).jpeg'
        ],
        [
            'id' => 2,
            'name' => 'Naroda Arise',
            'type' => 'Modern Luxury',
            'price' => '8,500',
            'location' => 'Ahmedabad, Gujarat',
            'description' => 'Rise to a new level of sophistication and modern lifestyle.',
            'image' => 'naroda_group_assets/arise.png'
        ]
    ];

    foreach ($projects as $p) {
        $stmt = $pdo->prepare("INSERT INTO projects (id, name, type, price, location, description, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$p['id'], $p['name'], $p['type'], $p['price'], $p['location'], $p['description'], $p['image']]);
    }

    // 2. Insert Apartment Plans
    $plans = [
        [1, '3bhk', '1850', 3, 3, 3, 'Spacious 3 BHK with grand balconies.'],
        [1, '4bhk', '2450', 4, 4, 4, 'Bespoke 4 BHK luxury residences.'],
        [2, '2bhk', '1250', 2, 2, 2, 'Compact and efficient modern living.'],
        [2, '3bhk', '1650', 3, 3, 2, 'Luxury 3 BHK with premium views.']
    ];

    foreach ($plans as $pl) {
        $stmt = $pdo->prepare("INSERT INTO project_apartment_plans (project_id, plan_type, super_area, bedrooms, bathrooms, balconies, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute($pl);
    }

    // 3. Insert Pricing
    foreach ($plans as $pl) {
        $stmt = $pdo->prepare("INSERT INTO project_pricing (project_id, plan_type, starting_price, sq_ft, bedrooms, bathrooms, parking, available_units, availability_status, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$pl[0], $pl[1], '₹ 85.00 Lacs', $pl[2], $pl[3], $pl[4], '2 Covered', 10, 'Available', 'All inclusive pricing including basic taxes.']);
    }

    // 4. Insert Gallery
    $gallery = [
        [1, 'naroda_group_assets/1 (21).jpeg'],
        [1, 'naroda_group_assets/1 (26).jpeg'],
        [2, 'naroda_group_assets/arise.png'],
        [2, 'naroda_group_assets/1 (26).jpeg']
    ];

    foreach ($gallery as $g) {
        $stmt = $pdo->prepare("INSERT INTO project_gallery (project_id, image_path) VALUES (?, ?)");
        $stmt->execute($g);
    }

    // 5. Insert Featured Properties
    $featured = [
        [1, 'For Sale', 'Luxury Penthouse', 'Lavish Tower A', '1.2 Cr', '3200', 4, 5, 'READY', 'naroda_group_assets/1 (26).jpeg', 'Grand penthouse with city views.', 'Gym, Pool, Spa', '4BR, 5BT', 'Prime location', 'OC'],
        [2, 'For Sale', 'Modern 3BHK', 'Arise Block C', '65 Lacs', '1650', 3, 3, 'READY', 'naroda_group_assets/arise.png', 'Modern design with smart home features.', 'Lawn, Clubhouse', '3BR, 3BT', 'Near Ring Road', 'Legal Approved']
    ];

    foreach ($featured as $f) {
        $stmt = $pdo->prepare("INSERT INTO featured_properties (project_id, property_type, title, location, price, area, bedrooms, bathrooms, status, image, overview, amenities, floor_plans, location_details, documents) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute($f);
    }

    echo "Seed completed successfully with new schema!";

} catch (PDOException $e) {
    die("Error seeding database: " . $e->getMessage());
}
?>
