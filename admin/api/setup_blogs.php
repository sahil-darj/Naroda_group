<?php
include 'config.php';

try {
    // Blogs Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS blogs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        image VARCHAR(255),
        short_description TEXT,
        content TEXT,
        author VARCHAR(100) DEFAULT 'Admin',
        status ENUM('Published', 'Draft') DEFAULT 'Published',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // Advantages Table (if missing)
    $pdo->exec("CREATE TABLE IF NOT EXISTS advantages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        icon VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    echo "Blogs and Advantages tables created successfully!";
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
