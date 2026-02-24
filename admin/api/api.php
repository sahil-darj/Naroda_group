<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

ob_clean();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    http_response_code(200);
    exit();
}

    include 'config.php';

    function log_activity($pdo, $type, $message) {
        $stmt = $pdo->prepare("INSERT INTO activity_log (type, message) VALUES (?, ?)");
        $stmt->execute([$type, $message]);
    }

    $action = $_GET['action'] ?? '';
    $method = $_SERVER['REQUEST_METHOD'];

    // For PUT/PATCH/DELETE, we might need to parse input if it's not FormData
    // But since we are using fetch with FormData from JS (which converts PATCH to POST usually if not careful, 
    // but fetch with method: 'PATCH' and body: FormData works in modern browsers, though PHP's $_POST won't see it).
    // Actually, if we use FormData with PATCH, PHP won't populate $_POST.
    // We can use a workaround: check php://input if $_POST is empty and method is not GET.
    
    $input_data = $_POST;
    if (in_array($method, ['PUT', 'PATCH', 'DELETE']) && empty($_POST)) {
        // If it's multipart/form-data, it's harder to parse php://input.
        // Let's assume the client might send JSON or we just use GET params for ID in DELETE.
        $raw_input = file_get_contents('php://input');
        $json_input = json_decode($raw_input, true);
        if ($json_input) {
            $input_data = array_merge($input_data, $json_input);
        }
    }

try {
    switch ($action) {

        case 'login':
            $username = $_POST['username'] ?? $input_data['username'] ?? '';
            $password = $_POST['password'] ?? $input_data['password'] ?? '';
            
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
            $stmt->execute([$username, $password]);
            $user = $stmt->fetch();
            
            if ($user) {
                echo json_encode(['success' => true, 'user' => ['username' => $user['username'], 'role' => $user['role']]]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Invalid username or password']);
            }
            break;

        // ─── DASHBOARD ───────────────────────────────────────────────────────
        case 'get_dashboard_stats':
            $projectCount     = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
            $inquiryCount     = $pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'New'")->fetchColumn();
            $testimonialCount = $pdo->query("SELECT COUNT(*) FROM testimonials")->fetchColumn();
            
            // Get Recent Activity
            $activities = $pdo->query("SELECT *, created_at as date FROM activity_log ORDER BY created_at DESC LIMIT 5")->fetchAll();
            
            // Get Views for dynamic days
            $days = intval($_GET['days'] ?? 7);
            $viewsData = [];
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $count = $pdo->prepare("SELECT SUM(view_count) FROM project_views WHERE view_date = ?");
                $count->execute([$date]);
                $c = $count->fetchColumn() ?: 0;
                
                // Better labeling based on range
                if ($days <= 7) {
                    $label = date('D', strtotime($date)); // MON, TUE...
                } elseif ($days <= 31) {
                    $label = date('j', strtotime($date)); // 1, 2, 3...
                } else {
                    $label = date('d/m', strtotime($date)); // 24/02...
                }

                $viewsData[] = [
                    'label' => $label, 
                    'full_label' => date('D, d M Y', strtotime($date)),
                    'value' => intval($c)
                ];
            }

            // Get views per project
            $projectViews = $pdo->query("SELECT p.name, SUM(v.view_count) as views FROM projects p LEFT JOIN project_views v ON p.id = v.project_id GROUP BY p.id ORDER BY views DESC")->fetchAll();

            echo json_encode([
                'projects'     => $projectCount,
                'inquiries'    => $inquiryCount,
                'testimonials' => $testimonialCount,
                'families'     => '2,048+',
                'activities'   => $activities,
                'views'        => $viewsData,
                'project_views' => $projectViews
            ]);
            break;
            
        case 'log_view':
            $projectId = intval($_GET['id'] ?? 0);
            if ($projectId) {
                $date = date('Y-m-d');
                $stmt = $pdo->prepare("INSERT INTO project_views (project_id, view_date, view_count) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE view_count = view_count + 1");
                $stmt->execute([$projectId, $date]);
                echo json_encode(['success' => true]);
            }
            break;

        // ─── PROJECTS ────────────────────────────────────────────────────────
        case 'get_projects':
            $limit  = isset($_GET['limit']) ? intval($_GET['limit']) : 0;
            $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
            $type   = $_GET['type'] ?? '';
            
            $query = "SELECT * FROM projects WHERE 1=1";
            $params = [];
            
            if ($type) {
                $query .= " AND type = ?";
                $params[] = $type;
            }
            
            $query .= " ORDER BY created_at DESC";
            
            if ($limit > 0) {
                $query .= " LIMIT ? OFFSET ?";
            }
            
            $stmt = $pdo->prepare($query);
            foreach ($params as $i => $val) {
                $stmt->bindValue($i + 1, $val);
            }
            if ($limit > 0) {
                $limitPos = count($params) + 1;
                $offsetPos = count($params) + 2;
                $stmt->bindValue($limitPos, $limit, PDO::PARAM_INT);
                $stmt->bindValue($offsetPos, $offset, PDO::PARAM_INT);
            }
            $stmt->execute();
            echo json_encode($stmt->fetchAll());
            break;

        case 'save_project':
            $id = intval($_POST['id'] ?? $_GET['id'] ?? $input_data['id'] ?? 0);
            $name        = $_POST['name']        ?? $input_data['name']        ?? '';
            $type        = $_POST['type']        ?? $input_data['type']        ?? '';
            $price       = $_POST['price']       ?? $input_data['price']       ?? '';
            $location    = $_POST['location']    ?? $input_data['location']    ?? '';
            $description = $_POST['description'] ?? $input_data['description'] ?? '';
            $detail_url  = $_POST['detail_url']  ?? $input_data['detail_url']  ?? '';
            $image       = $_POST['image']       ?? $input_data['image']       ?? '';

            if ($method === 'POST' && !$id) {
                $stmt = $pdo->prepare("INSERT INTO projects (name, type, price, location, description, detail_url, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$name, $type, $price, $location, $description, $detail_url, $image])) {
                    $newId = $pdo->lastInsertId();
                    log_activity($pdo, 'project', "Added new project: " . $name);
                    echo json_encode(['success' => true, 'id' => $newId]);
                }
            } elseif (($method === 'PUT' || $method === 'PATCH' || $method === 'POST') && $id) {
                $stmt = $pdo->prepare("UPDATE projects SET name=?, type=?, price=?, location=?, description=?, detail_url=?, image=? WHERE id=?");
                if ($stmt->execute([$name, $type, $price, $location, $description, $detail_url, $image, $id])) {
                    log_activity($pdo, 'project', "Updated project: " . $name);
                    echo json_encode(['success' => true, 'id' => $id]);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Invalid method for project save']);
            }
            break;

        case 'delete_project':
            $id = intval($_GET['id'] ?? $input_data['id'] ?? 0);
            if ($id) {
                // Delete associated data first
                $pdo->prepare("DELETE FROM project_details WHERE project_id = ?")->execute([$id]);
                $pdo->prepare("DELETE FROM project_apartment_plans WHERE project_id = ?")->execute([$id]);
                $pdo->prepare("DELETE FROM project_gallery WHERE project_id = ?")->execute([$id]);
                $pdo->prepare("DELETE FROM project_pricing WHERE project_id = ?")->execute([$id]);
                $pdo->prepare("DELETE FROM featured_properties WHERE project_id = ?")->execute([$id]);
                
                $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
                $stmt->execute([$id]);
                log_activity($pdo, 'project', "Deleted project ID: " . $id);
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'No ID provided']);
            }
            break;

        // ─── PROJECT FULL DETAILS (all sub-data) ─────────────────────────────
        case 'get_project_details':
            $id = intval($_GET['id'] ?? 0);

            $project = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
            $project->execute([$id]);
            $projectData = $project->fetch();

            $plans = $pdo->prepare("SELECT * FROM project_apartment_plans WHERE project_id = ? ORDER BY id ASC");
            $plans->execute([$id]);
            $plansData = $plans->fetchAll();

            $gallery = $pdo->prepare("SELECT * FROM project_gallery WHERE project_id = ? ORDER BY id ASC");
            $gallery->execute([$id]);
            $galleryData = $gallery->fetchAll();

            $pricing = $pdo->prepare("SELECT * FROM project_pricing WHERE project_id = ? ORDER BY id ASC");
            $pricing->execute([$id]);
            $pricingData = $pricing->fetchAll();

            $featured = $pdo->prepare("SELECT * FROM featured_properties WHERE project_id = ? ORDER BY id ASC");
            $featured->execute([$id]);
            $featuredData = $featured->fetchAll();

            $details = $pdo->prepare("SELECT * FROM project_details WHERE project_id = ?");
            $details->execute([$id]);
            $detailsData = $details->fetch();

            if ($detailsData) {
                foreach (['overview_highlights', 'amenities', 'location_highlights', 'specifications'] as $f) {
                    if (!empty($detailsData[$f])) {
                        $decoded = json_decode($detailsData[$f], true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $detailsData[$f] = $decoded;
                        }
                    }
                }
            }

            echo json_encode([
                'project'  => $projectData,
                'plans'    => $plansData,
                'gallery'  => $galleryData,
                'pricing'  => $pricingData,
                'featured' => $featuredData,
                'details'  => $detailsData ?: null,
            ]);
            break;

        // ─── SAVE PROJECT DETAILS (rich content) ─────────────────────────────
        case 'save_project_details':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $projectId = intval($_POST['project_id'] ?? 0);
                $tab       = $_POST['tab'] ?? '';

                if ($tab === 'plans') {
                    $planType   = $_POST['plan_type']   ?? '';
                    $superArea  = $_POST['super_area']  ?? '';
                    $carpetArea = $_POST['carpet_area'] ?? '';
                    $bedrooms   = intval($_POST['bedrooms']  ?? 0);
                    $bathrooms  = intval($_POST['bathrooms'] ?? 0);
                    $balconies  = intval($_POST['balconies'] ?? 0);
                    $desc       = $_POST['description'] ?? '';
                    $floorImg   = $_POST['floor_image'] ?? '';

                    if (isset($_FILES['floor_image']) && $_FILES['floor_image']['error'] === UPLOAD_ERR_OK) {
                        $uploadDir = 'uploads/floorplans/';
                        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                        $fileName = time() . '_' . basename($_FILES['floor_image']['name']);
                        if (move_uploaded_file($_FILES['floor_image']['tmp_name'], $uploadDir . $fileName)) {
                            $floorImg = 'api/' . $uploadDir . $fileName;
                        }
                    }

                    $existing = $pdo->prepare("SELECT id FROM project_apartment_plans WHERE project_id = ? AND plan_type = ?");
                    $existing->execute([$projectId, $planType]);
                    $existingRow = $existing->fetch();

                    if ($existingRow) {
                        $stmt = $pdo->prepare("UPDATE project_apartment_plans SET super_area=?, carpet_area=?, bedrooms=?, bathrooms=?, balconies=?, floor_image=?, description=? WHERE project_id=? AND plan_type=?");
                        $stmt->execute([$superArea, $carpetArea, $bedrooms, $bathrooms, $balconies, $floorImg, $desc, $projectId, $planType]);
                        log_activity($pdo, 'project', "Updated " . strtoupper($planType) . " plan for project ID: " . $projectId);
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO project_apartment_plans (project_id, plan_type, super_area, carpet_area, bedrooms, bathrooms, balconies, floor_image, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$projectId, $planType, $superArea, $carpetArea, $bedrooms, $bathrooms, $balconies, $floorImg, $desc]);
                        log_activity($pdo, 'project', "Added " . strtoupper($planType) . " plan to project ID: " . $projectId);
                    }
                    echo json_encode(['success' => true, 'message' => 'Plan saved']);

                } elseif ($tab === 'pricing') {
                    $planType    = $_POST['plan_type']           ?? '';
                    $startPrice  = $_POST['starting_price']      ?? '';
                    $sqFt        = $_POST['sq_ft']               ?? '';
                    $beds        = intval($_POST['bedrooms']      ?? 0);
                    $baths       = intval($_POST['bathrooms']     ?? 0);
                    $parking     = $_POST['parking']             ?? '';
                    $units       = intval($_POST['available_units'] ?? 0);
                    $avStatus    = $_POST['availability_status'] ?? 'Available';
                    $desc        = $_POST['description']         ?? '';

                    $existing = $pdo->prepare("SELECT id FROM project_pricing WHERE project_id = ? AND plan_type = ?");
                    $existing->execute([$projectId, $planType]);
                    $existingRow = $existing->fetch();

                    if ($existingRow) {
                        $stmt = $pdo->prepare("UPDATE project_pricing SET starting_price=?, sq_ft=?, bedrooms=?, bathrooms=?, parking=?, available_units=?, availability_status=?, description=? WHERE project_id=? AND plan_type=?");
                        $stmt->execute([$startPrice, $sqFt, $beds, $baths, $parking, $units, $avStatus, $desc, $projectId, $planType]);
                        log_activity($pdo, 'project', "Updated pricing for " . strtoupper($planType) . " in project ID: " . $projectId);
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO project_pricing (project_id, plan_type, starting_price, sq_ft, bedrooms, bathrooms, parking, available_units, availability_status, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$projectId, $planType, $startPrice, $sqFt, $beds, $baths, $parking, $units, $avStatus, $desc]);
                        log_activity($pdo, 'project', "Added pricing for " . strtoupper($planType) . " to project ID: " . $projectId);
                    }
                    echo json_encode(['success' => true, 'message' => 'Pricing saved']);

                } elseif ($tab === 'gallery') {
                    $imagePath = $_POST['image_path'] ?? '';
                    $caption   = $_POST['caption']    ?? '';
                    if ($imagePath) {
                        $stmt = $pdo->prepare("INSERT INTO project_gallery (project_id, image_path, caption) VALUES (?, ?, ?)");
                        $stmt->execute([$projectId, $imagePath, $caption]);
                        log_activity($pdo, 'project', "Added new gallery image to project ID: " . $projectId);
                        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
                    } else {
                        echo json_encode(['success' => false, 'error' => 'No image path']);
                    }

                } elseif ($tab === 'rich_details') {
                    $fields = [
                        'overview_title'       => $_POST['overview_title']       ?? '',
                        'overview_description' => $_POST['overview_description'] ?? '',
                        'overview_highlights'  => $_POST['overview_highlights']  ?? '[]',
                        'stat_amenities'       => $_POST['stat_amenities']       ?? '',
                        'stat_bhk_sizes'       => $_POST['stat_bhk_sizes']       ?? '',
                        'stat_units'           => $_POST['stat_units']           ?? '',
                        'blocks'               => $_POST['blocks']               ?? '',
                        'stat_possession'      => $_POST['stat_possession']      ?? '',
                        'amenities'            => $_POST['amenities']            ?? '[]',
                        'location_address'     => $_POST['location_address']     ?? '',
                        'location_map_iframe'  => $_POST['location_map_iframe']  ?? '',
                        'location_highlights'  => $_POST['location_highlights']  ?? '[]',
                        'specifications'       => $_POST['specifications']       ?? '{}',
                        'brochure_label'       => $_POST['brochure_label']       ?? 'Download Brochure',
                        'cta_phone'            => $_POST['cta_phone']            ?? '',
                        'video_url'            => $_POST['video_url']            ?? '',
                    ];

                    if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
                        $uploadDir = 'uploads/heroes/';
                        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                        $fileName = time() . '_' . basename($_FILES['hero_image']['name']);
                        if (move_uploaded_file($_FILES['hero_image']['tmp_name'], $uploadDir . $fileName)) {
                            $fields['hero_image'] = 'api/' . $uploadDir . $fileName;
                        }
                    } else {
                        $fields['hero_image'] = $_POST['hero_image'] ?? '';
                    }

                    if (isset($_FILES['welcome_image']) && $_FILES['welcome_image']['error'] === UPLOAD_ERR_OK) {
                        $uploadDir = 'uploads/welcome/';
                        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                        $fileName = time() . '_' . basename($_FILES['welcome_image']['name']);
                        if (move_uploaded_file($_FILES['welcome_image']['tmp_name'], $uploadDir . $fileName)) {
                            $fields['welcome_image'] = 'api/' . $uploadDir . $fileName;
                        }
                    } else {
                        $fields['welcome_image'] = $_POST['welcome_image'] ?? '';
                    }

                    if (isset($_FILES['brochure_pdf']) && $_FILES['brochure_pdf']['error'] === UPLOAD_ERR_OK) {
                        $uploadDir = 'uploads/brochures/';
                        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                        $fileName = time() . '_' . basename($_FILES['brochure_pdf']['name']);
                        if (move_uploaded_file($_FILES['brochure_pdf']['tmp_name'], $uploadDir . $fileName)) {
                            $fields['brochure_pdf'] = 'api/' . $uploadDir . $fileName;
                        }
                    } else {
                        $fields['brochure_pdf'] = $_POST['brochure_pdf'] ?? '';
                    }

                    $existing = $pdo->prepare("SELECT id FROM project_details WHERE project_id = ?");
                    $existing->execute([$projectId]);
                    $existingRow = $existing->fetch();

                    if ($existingRow) {
                        $setClauses = array_map(fn($k) => "$k = :$k", array_keys($fields));
                        $sql = "UPDATE project_details SET " . implode(', ', $setClauses) . " WHERE project_id = :project_id";
                        $fields['project_id'] = $projectId;
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute($fields);
                        log_activity($pdo, 'project', "Updated detailed info for project ID: " . $projectId);
                    } else {
                        $fields['project_id'] = $projectId;
                        $cols = implode(', ', array_keys($fields));
                        $placeholders = implode(', ', array_map(fn($k) => ":$k", array_keys($fields)));
                        $sql = "INSERT INTO project_details ($cols) VALUES ($placeholders)";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute($fields);
                        log_activity($pdo, 'project', "Created detailed info for project ID: " . $projectId);
                    }
                    echo json_encode(['success' => true, 'message' => 'Rich details saved']);

                } elseif ($tab === 'featured') {
                    $propType = $_POST['property_type'] ?? '';
                    $title    = $_POST['title']         ?? '';
                    $propId   = intval($_POST['featured_id'] ?? 0);

                    $data = [
                        $_POST['location']         ?? '',
                        $_POST['price']            ?? '',
                        $_POST['area']             ?? '',
                        intval($_POST['bedrooms']  ?? 0),
                        intval($_POST['bathrooms'] ?? 0),
                        $_POST['status']           ?? '',
                        $_POST['image']            ?? '',
                        $_POST['overview']         ?? '',
                        $_POST['amenities']        ?? '',
                        $_POST['floor_plans']      ?? '',
                        $_POST['location_details'] ?? '',
                        $_POST['documents']        ?? '',
                    ];

                    if ($propId) {
                        $stmt = $pdo->prepare("UPDATE featured_properties SET location=?, price=?, area=?, bedrooms=?, bathrooms=?, status=?, image=?, overview=?, amenities=?, floor_plans=?, location_details=?, documents=? WHERE id=?");
                        $stmt->execute(array_merge($data, [$propId]));
                        log_activity($pdo, 'project', "Updated featured property: " . $title);
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO featured_properties (project_id, property_type, title, location, price, area, bedrooms, bathrooms, status, image, overview, amenities, floor_plans, location_details, documents) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute(array_merge([$projectId, $propType, $title], $data));
                        log_activity($pdo, 'project', "Added new featured property: " . $title);
                    }
                    echo json_encode(['success' => true, 'id' => $propId ?: $pdo->lastInsertId()]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Unknown tab: ' . $tab]);
                }
            }
            break;

        // ─── IMAGE UPLOAD ─────────────────────────────────────────────────────
        case 'upload_image':
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
                $subfolder  = $_POST['subfolder'] ?? 'misc';
                $uploadDir  = 'uploads/' . $subfolder . '/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

                $file     = $_FILES['image'];
                $fileName = time() . '_' . basename($file['name']);
                $targetPath = $uploadDir . $fileName;

                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    echo json_encode(['success' => true, 'path' => 'api/' . $targetPath]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Upload failed']);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'No file provided']);
            }
            break;

        // ─── DELETE GALLERY IMAGE ─────────────────────────────────────────────
        case 'delete_gallery_image':
            $id = intval($_GET['id'] ?? 0);
            $stmt = $pdo->prepare("SELECT image_path FROM project_gallery WHERE id = ?");
            $stmt->execute([$id]);
            $img = $stmt->fetch();
            if ($img) {
                $localPath  = str_replace('admin/api/', '', $img['image_path']);
                $serverRoot = dirname(dirname(__DIR__));
                $fullPath   = $serverRoot . '/' . $localPath;
                if (file_exists($fullPath)) unlink($fullPath);
                $pdo->prepare("DELETE FROM project_gallery WHERE id = ?")->execute([$id]);
                log_activity($pdo, 'project', "Deleted gallery image ID: " . $id);
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Image not found']);
            }
            break;

        case 'delete_featured':
            $id = intval($_GET['id'] ?? 0);
            $pdo->prepare("DELETE FROM featured_properties WHERE id = ?")->execute([$id]);
            log_activity($pdo, 'project', "Deleted featured property ID: " . $id);
            echo json_encode(['success' => true]);
            break;

        case 'delete_plan':
            $projectId = intval($_GET['project_id'] ?? 0);
            $planType  = $_GET['plan_type'] ?? '';
            if ($projectId && $planType) {
                $pdo->prepare("DELETE FROM project_apartment_plans WHERE project_id = ? AND plan_type = ?")->execute([$projectId, $planType]);
                log_activity($pdo, 'project', "Deleted " . strtoupper($planType) . " plan for project ID: " . $projectId);
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Missing project_id or plan_type']);
            }
            break;

        case 'delete_pricing':
            $projectId = intval($_GET['project_id'] ?? 0);
            $planType  = $_GET['plan_type'] ?? '';
            if ($projectId && $planType) {
                $pdo->prepare("DELETE FROM project_pricing WHERE project_id = ? AND plan_type = ?")->execute([$projectId, $planType]);
                log_activity($pdo, 'project', "Deleted " . strtoupper($planType) . " pricing for project ID: " . $projectId);
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Missing project_id or plan_type']);
            }
            break;

        case 'clear_rich_details':
            $projectId = intval($_GET['project_id'] ?? 0);
            if ($projectId) {
                $pdo->prepare("DELETE FROM project_details WHERE project_id = ?")->execute([$projectId]);
                log_activity($pdo, 'project', "Cleared all rich details for project ID: " . $projectId);
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Missing project_id']);
            }
            break;

        // ─── TESTIMONIALS ─────────────────────────────────────────────────────
        case 'get_testimonials':
            $stmt = $pdo->query("SELECT * FROM testimonials ORDER BY created_at DESC");
            echo json_encode($stmt->fetchAll());
            break;

        case 'save_testimonial':
            $id      = intval($_POST['id']      ?? $_GET['id']      ?? $input_data['id'] ?? 0);
            $name    = $_POST['name']    ?? $input_data['name']    ?? '';
            $project = $_POST['project'] ?? $input_data['project'] ?? '';
            $content = $_POST['content'] ?? $input_data['content'] ?? '';
            $rating  = intval($_POST['rating'] ?? $input_data['rating'] ?? 5);

            if ($method === 'POST' && !$id) {
                $stmt = $pdo->prepare("INSERT INTO testimonials (name, project, content, rating) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $project, $content, $rating]);
                log_activity($pdo, 'testimonial', "Added new testimonial from " . $name);
                echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
            } elseif (($method === 'PUT' || $method === 'PATCH' || $method === 'POST') && $id) {
                $stmt = $pdo->prepare("UPDATE testimonials SET name=?, project=?, content=?, rating=? WHERE id=?");
                $stmt->execute([$name, $project, $content, $rating, $id]);
                log_activity($pdo, 'testimonial', "Updated testimonial from " . $name);
                echo json_encode(['success' => true, 'id' => $id]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Invalid method for testimonial save']);
            }
            break;

        case 'delete_testimonial':
            $id = intval($_GET['id'] ?? $input_data['id'] ?? 0);
            if ($id) {
                $stmt = $pdo->prepare("DELETE FROM testimonials WHERE id = ?");
                $stmt->execute([$id]);
                log_activity($pdo, 'testimonial', "Deleted testimonial ID: " . $id);
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'No ID provided']);
            }
            break;

        // ─── INQUIRIES ────────────────────────────────────────────────────────
        case 'get_inquiries':
            $stmt = $pdo->query("SELECT * FROM inquiries ORDER BY created_at DESC");
            echo json_encode($stmt->fetchAll());
            break;

        case 'save_inquiry':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $name    = $_POST['name']    ?? '';
                $email   = $_POST['email']   ?? '';
                $phone   = $_POST['phone']   ?? '';
                $project = $_POST['project'] ?? '';
                $message = $_POST['message'] ?? '';
                $stmt = $pdo->prepare("INSERT INTO inquiries (name, email, phone, project, message) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $email, $phone, $project, $message]);
                log_activity($pdo, 'inquiry', "New inquiry from " . $name . " for " . $project);
                echo json_encode(['success' => true]);
            }
            break;

        case 'delete_inquiry':
            $id = intval($_GET['id'] ?? $input_data['id'] ?? 0);
            if ($id) {
                $stmt = $pdo->prepare("DELETE FROM inquiries WHERE id = ?");
                $stmt->execute([$id]);
                log_activity($pdo, 'inquiry', "Deleted inquiry ID: " . $id);
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'No ID provided']);
            }
            break;

        case 'get_advantages':
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
            $stmt = $pdo->prepare("SELECT * FROM advantages ORDER BY id ASC LIMIT ?");
            $stmt->execute([$limit]);
            echo json_encode($stmt->fetchAll());
            break;

        // ─── BLOGS ──────────────────────────────────────────────────────────
        case 'get_blogs':
            $limit  = isset($_GET['limit']) ? intval($_GET['limit']) : 0;
            $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
            
            $query = "SELECT * FROM blogs WHERE status = 'Published' ORDER BY created_at DESC";
            if ($limit > 0) {
                $query .= " LIMIT ? OFFSET ?";
            }
            
            $stmt = $pdo->prepare($query);
            if ($limit > 0) {
                $stmt->bindValue(1, $limit, PDO::PARAM_INT);
                $stmt->bindValue(2, $offset, PDO::PARAM_INT);
            }
            $stmt->execute();
            echo json_encode($stmt->fetchAll());
            break;

        case 'get_blog':
            $id = intval($_GET['id'] ?? 0);
            if ($id) {
                $stmt = $pdo->prepare("SELECT * FROM blogs WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode($stmt->fetch());
            } else {
                echo json_encode(['error' => 'No ID provided']);
            }
            break;

        case 'save_blog':
            $id = intval($_POST['id'] ?? $input_data['id'] ?? 0);
            $title             = $_POST['title']             ?? $input_data['title']             ?? '';
            $short_description = $_POST['short_description'] ?? $input_data['short_description'] ?? '';
            $content           = $_POST['content']           ?? $input_data['content']           ?? '';
            $author            = $_POST['author']            ?? $input_data['author']            ?? 'Admin';
            $status            = $_POST['status']            ?? $input_data['status']            ?? 'Published';
            $image             = $_POST['image']             ?? $input_data['image']             ?? '';

            if ($method === 'POST' && !$id) {
                $stmt = $pdo->prepare("INSERT INTO blogs (title, short_description, content, author, status, image) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $short_description, $content, $author, $status, $image]);
                log_activity($pdo, 'blog', "Added new blog: " . $title);
                echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
            } elseif ($id) {
                $stmt = $pdo->prepare("UPDATE blogs SET title=?, short_description=?, content=?, author=?, status=?, image=? WHERE id=?");
                $stmt->execute([$title, $short_description, $content, $author, $status, $image, $id]);
                log_activity($pdo, 'blog', "Updated blog: " . $title);
                echo json_encode(['success' => true, 'id' => $id]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
            }
            break;

        case 'delete_blog':
            $id = intval($_GET['id'] ?? $input_data['id'] ?? 0);
            if ($id) {
                $stmt = $pdo->prepare("DELETE FROM blogs WHERE id = ?");
                $stmt->execute([$id]);
                log_activity($pdo, 'blog', "Deleted blog ID: " . $id);
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'No ID provided']);
            }
            break;

        // ─── CAREERS ────────────────────────────────────────────────────────
        case 'get_jobs':
            $stmt = $pdo->prepare("SELECT * FROM jobs WHERE status = 'Active' ORDER BY created_at DESC");
            $stmt->execute();
            echo json_encode($stmt->fetchAll());
            break;

        case 'save_job':
            $id               = intval($_POST['id'] ?? 0);
            $title            = $_POST['title'] ?? '';
            $experience       = $_POST['experience'] ?? '';
            $description      = $_POST['description'] ?? '';
            $responsibilities = $_POST['responsibilities'] ?? '';
            $qualifications   = $_POST['qualifications'] ?? '';
            $job_type         = $_POST['job_type'] ?? 'Full-time';
            $location         = $_POST['location'] ?? 'Ahmedabad';
            $status           = $_POST['status'] ?? 'Active';

            if (!$id) {
                $stmt = $pdo->prepare("INSERT INTO jobs (title, experience, description, responsibilities, qualifications, job_type, location, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $experience, $description, $responsibilities, $qualifications, $job_type, $location, $status]);
                log_activity($pdo, 'career', "Added new job opening: " . $title);
            } else {
                $stmt = $pdo->prepare("UPDATE jobs SET title=?, experience=?, description=?, responsibilities=?, qualifications=?, job_type=?, location=?, status=? WHERE id=?");
                $stmt->execute([$title, $experience, $description, $responsibilities, $qualifications, $job_type, $location, $status, $id]);
                log_activity($pdo, 'career', "Updated job opening: " . $title);
            }
            echo json_encode(['success' => true]);
            break;

        case 'delete_job':
            $id = intval($_GET['id'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM jobs WHERE id = ?");
            $stmt->execute([$id]);
            log_activity($pdo, 'career', "Deleted job opening ID: " . $id);
            echo json_encode(['success' => true]);
            break;

        case 'submit_application':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $job_id      = intval($_POST['job_id'] ?? 0);
                $name        = $_POST['name'] ?? '';
                $email       = $_POST['email'] ?? '';
                $phone       = $_POST['phone'] ?? '';
                $designation = $_POST['designation'] ?? '';
                $message     = $_POST['message'] ?? '';
                $resume_path = $_POST['resume_path'] ?? '';

                $stmt = $pdo->prepare("INSERT INTO job_applications (job_id, name, email, phone, designation, message, resume_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$job_id > 0 ? $job_id : null, $name, $email, $phone, $designation, $message, $resume_path]);
                log_activity($pdo, 'career', "New application from " . $name);
                echo json_encode(['success' => true]);
            }
            break;

        case 'get_applications':
            $stmt = $pdo->prepare("SELECT a.*, j.title as job_title FROM job_applications a LEFT JOIN jobs j ON a.job_id = j.id ORDER BY a.submitted_at DESC");
            $stmt->execute();
            echo json_encode($stmt->fetchAll());
            break;

        case 'delete_application':
            $id = intval($_GET['id'] ?? 0);
            if ($id) {
                $pdo->prepare("DELETE FROM job_applications WHERE id = ?")->execute([$id]);
                log_activity($pdo, 'career', "Deleted application ID: " . $id);
                echo json_encode(['success' => true]);
            }
            break;

        // ─── TEAM ──────────────────────────────────────────────────────────
        case 'get_team':
            $stmt = $pdo->prepare("SELECT * FROM team ORDER BY display_order ASC, id ASC");
            $stmt->execute();
            echo json_encode($stmt->fetchAll());
            break;

        case 'save_team_member':
            $id          = intval($_POST['id'] ?? 0);
            $name        = $_POST['name'] ?? '';
            $designation = $_POST['designation'] ?? '';
            $image       = $_POST['image'] ?? '';
            $facebook    = $_POST['facebook'] ?? '';
            $instagram   = $_POST['instagram'] ?? '';
            $linkedin    = $_POST['linkedin'] ?? '';
            $twitter     = $_POST['twitter'] ?? '';
            $order       = intval($_POST['display_order'] ?? 0);

            if (!$id) {
                $stmt = $pdo->prepare("INSERT INTO team (name, designation, image, facebook, instagram, linkedin, twitter, display_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $designation, $image, $facebook, $instagram, $linkedin, $twitter, $order]);
                log_activity($pdo, 'team', "Added new team member: " . $name);
            } else {
                $stmt = $pdo->prepare("UPDATE team SET name=?, designation=?, image=?, facebook=?, instagram=?, linkedin=?, twitter=?, display_order=? WHERE id=?");
                $stmt->execute([$name, $designation, $image, $facebook, $instagram, $linkedin, $twitter, $order, $id]);
                log_activity($pdo, 'team', "Updated team member: " . $name);
            }
            echo json_encode(['success' => true]);
            break;

        case 'delete_team_member':
            $id = intval($_GET['id'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM team WHERE id = ?");
            $stmt->execute([$id]);
            log_activity($pdo, 'team', "Removed team member ID: " . $id);
            echo json_encode(['success' => true]);
            break;

        // ─── GENERIC IMAGE UPLOAD ─────────────────────────────────────────────
        case 'upload_image':
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $subfolder = preg_replace('/[^a-zA-Z0-9_\-]/', '', $_POST['subfolder'] ?? 'general');
                $uploadDir = __DIR__ . '/uploads/' . $subfolder . '/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                $ext      = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowed  = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx'];
                if (!in_array($ext, $allowed)) {
                    echo json_encode(['success' => false, 'error' => 'Invalid file type']);
                    break;
                }
                // Sanitize original filename part to avoid spaces/special chars
                $safeName = preg_replace('/[^a-zA-Z0-9]/', '_', pathinfo($_FILES['image']['name'], PATHINFO_FILENAME));
                $fileName = time() . '_' . substr($safeName, 0, 50) . '.' . $ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $fileName)) {
                    // Return path relative to the admin folder
                    $path = 'api/uploads/' . $subfolder . '/' . $fileName;
                    echo json_encode(['success' => true, 'path' => $path]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Move failed']);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'No file or upload error']);
            }
            break;

        default:
            echo json_encode(['error' => 'Invalid action', 'action' => $action]);
            break;
    }
} catch (Throwable $e) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => $e->getMessage(), 'file' => basename($e->getFile()), 'line' => $e->getLine()]);
}

ob_end_flush();
