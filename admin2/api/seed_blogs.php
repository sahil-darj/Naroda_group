<?php
include 'config.php';

try {
    $blogs = [
        [
            'title' => 'Affordable vs Luxury Properties in Ahmedabad',
            'short_description' => 'Choosing a home in Ahmedabad is an exciting journey, but with so many options, how do you decide what’s best for you. There are myriad choices between affordable properties in Ahmedabad and luxury properties in Ahmedabad...',
            'image' => 'naroda_group_assets/1 (26).jpeg',
            'created_at' => '2025-08-11 10:00:00'
        ],
        [
            'title' => 'Flat for Sale in Ahmedabad by Top Builders',
            'short_description' => 'If you’re looking to buy a property in Ahmedabad, remember Shilp group has ‘Everything for Everyone’. From luxurious flats to dream workspaces, from residential properties in Shilaj to premium commercial buildings in GIFT City...',
            'image' => 'naroda_group_assets/arise.png',
            'created_at' => '2025-08-05 10:00:00'
        ],
        [
            'title' => 'Buying a Property in Ahmedabad - A Guide',
            'short_description' => 'Ahmedabad is considered a Tier 1 city in India. The city owes this to its growing infrastructure, connectivity, rapid economic development and the growing consumerism. Today’s Amdavadi knows what he/she wants...',
            'image' => 'naroda_group_assets/1 (21).jpeg',
            'created_at' => '2025-08-01 10:00:00'
        ]
    ];

    foreach ($blogs as $blog) {
        $stmt = $pdo->prepare("INSERT INTO blogs (title, short_description, image, created_at) VALUES (?, ?, ?, ?)");
        $stmt->execute([$blog['title'], $blog['short_description'], $blog['image'], $blog['created_at']]);
    }

    echo "Blogs seeded successfully!";
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
