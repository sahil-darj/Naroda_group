<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'ng';
$charset = 'utf8mb4';

try {
    $pdo = new PDO("mysql:host=$host;charset=$charset", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET $charset COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$db`;");

    // Projects Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS projects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        type VARCHAR(255),
        price VARCHAR(100),
        location VARCHAR(255),
        description TEXT,
        image VARCHAR(255),
        detail_url VARCHAR(255),
        status ENUM('Active', 'Inactive') DEFAULT 'Active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Testimonials Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS testimonials (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        project VARCHAR(255),
        content TEXT,
        rating INT DEFAULT 5,
        status ENUM('Published', 'Pending') DEFAULT 'Published',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Inquiries Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS inquiries (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255),
        email VARCHAR(255),
        phone VARCHAR(20),
        project VARCHAR(255),
        message TEXT,
        status ENUM('New', 'Contacted', 'Closed') DEFAULT 'New',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Project Apartment Plans (enhanced)
    $pdo->exec("CREATE TABLE IF NOT EXISTS project_apartment_plans (
        id INT AUTO_INCREMENT PRIMARY KEY,
        project_id INT,
        plan_type VARCHAR(50),
        super_area VARCHAR(100),
        carpet_area VARCHAR(100),
        bedrooms INT DEFAULT 0,
        bathrooms INT DEFAULT 0,
        balconies INT DEFAULT 0,
        floor_image VARCHAR(255),
        description TEXT,
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
    )");

    // Add carpet_area and floor_image if not exist (for existing tables)
    try { $pdo->exec("ALTER TABLE project_apartment_plans ADD COLUMN carpet_area VARCHAR(100) AFTER super_area"); } catch(Exception $e) {}
    try { $pdo->exec("ALTER TABLE project_apartment_plans ADD COLUMN floor_image VARCHAR(255) AFTER balconies"); } catch(Exception $e) {}

    // Project Gallery
    $pdo->exec("CREATE TABLE IF NOT EXISTS project_gallery (
        id INT AUTO_INCREMENT PRIMARY KEY,
        project_id INT,
        image_path VARCHAR(255),
        caption VARCHAR(255),
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
    )");
    try { $pdo->exec("ALTER TABLE project_gallery ADD COLUMN caption VARCHAR(255) AFTER image_path"); } catch(Exception $e) {}

    // Project Pricing (enhanced)
    $pdo->exec("CREATE TABLE IF NOT EXISTS project_pricing (
        id INT AUTO_INCREMENT PRIMARY KEY,
        project_id INT,
        plan_type VARCHAR(50),
        starting_price VARCHAR(100),
        sq_ft VARCHAR(100),
        bedrooms INT DEFAULT 0,
        bathrooms INT DEFAULT 0,
        parking VARCHAR(100),
        available_units INT DEFAULT 0,
        availability_status VARCHAR(50) DEFAULT 'Available',
        description TEXT,
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
    )");

    // Project Details (rich content per project) - NEW comprehensive table
    $pdo->exec("CREATE TABLE IF NOT EXISTS project_details (
        id INT AUTO_INCREMENT PRIMARY KEY,
        project_id INT UNIQUE,
        -- Overview / About
        overview_title VARCHAR(255),
        overview_description TEXT,
        overview_highlights TEXT,        -- JSON array of bullet points
        -- Stats
        stat_amenities VARCHAR(50),
        stat_bhk_sizes VARCHAR(50),
        stat_units VARCHAR(50),
        stat_possession VARCHAR(100),
        -- Amenities
        amenities TEXT,                  -- JSON array: [{icon, title, description}]
        -- Location
        location_address TEXT,
        location_map_iframe TEXT,        -- Full Google Maps embed iframe code
        location_highlights TEXT,        -- JSON array of nearby landmarks
        -- Legal & Documentation
        -- Specifications
        specifications TEXT,             -- JSON: {structure, flooring, kitchen, doors, windows, electrical, plumbing}
        -- Documents / Brochure
        brochure_pdf VARCHAR(255),
        brochure_label VARCHAR(100),
        -- Hero image override
        hero_image VARCHAR(255),
        -- Welcome section image
        welcome_image VARCHAR(255),
        -- CTA phone
        cta_phone VARCHAR(30),
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
    )");

    // Featured Properties Table (enhanced)
    $pdo->exec("CREATE TABLE IF NOT EXISTS featured_properties (
        id INT AUTO_INCREMENT PRIMARY KEY,
        project_id INT,
        property_type VARCHAR(50),
        title VARCHAR(255),
        location VARCHAR(255),
        price VARCHAR(100),
        area VARCHAR(100),
        bedrooms INT DEFAULT 0,
        bathrooms INT DEFAULT 0,
        status VARCHAR(50),
        image VARCHAR(255),
        overview TEXT,
        amenities TEXT,
        floor_plans TEXT,
        location_details TEXT,
        documents TEXT,
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
    )");

    echo "<strong>✅ Setup Complete!</strong><br>";
    echo "All tables created/updated successfully.<br>";
    echo "Database: <code>$db</code><br>";
    echo "<a href='../home.html'>Go to Admin Panel</a>";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
