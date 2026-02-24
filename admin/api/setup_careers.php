<?php
include 'config.php';

try {
    // 1. Jobs Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS jobs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        experience VARCHAR(100),
        description TEXT,
        status ENUM('Active', 'Closed') DEFAULT 'Active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. Job Applications Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS job_applications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        job_id INT,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        designation VARCHAR(255),
        resume_path VARCHAR(255),
        status ENUM('New', 'Reviewed', 'Shortlisted', 'Rejected') DEFAULT 'New',
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE SET NULL
    )");

    echo "Career tables created successfully!";
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
