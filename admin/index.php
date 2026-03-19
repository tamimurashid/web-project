<?php
/**
 * Ujamaa Hostel - Admin Dashboard
 * Complete admin panel with full management capabilities
 */

session_start();

require_once '../includes/db.php';

$isLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$pdo = getDBConnection();

// Handle login
if (isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT id, username, password_hash, role, full_name FROM admins WHERE username = ? AND is_active = TRUE");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_role'] = $user['role'];
            $_SESSION['admin_name'] = $user['full_name'];
            $isLoggedIn = true;
            
            $updateStmt = $pdo->prepare("UPDATE admins SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
            $updateStmt->execute([$user['id']]);
        } else {
            $loginError = 'Invalid username or password';
        }
    } else {
        $loginError = 'Database connection failed. Please check setup.';
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Get current tab
$tab = $_GET['tab'] ?? 'dashboard';

// Initialize variables
$stats = [];
$bookings = [];
$contacts = [];
$rooms = [];
$testimonials = [];

if ($isLoggedIn && $pdo) {
    try {
        // Get statistics
        $stmt = $pdo->query("
            SELECT 
                COUNT(*) as total_bookings,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_bookings,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
                COALESCE(SUM(CASE WHEN status = 'confirmed' THEN total_price ELSE 0 END), 0) as total_revenue
            FROM bookings
        ");
        $stats = $stmt->fetch();
        
        // Get unread contacts count
        $stmt = $pdo->query("SELECT COUNT(*) as unread FROM contacts WHERE status = 'unread'");
        $unreadContacts = $stmt->fetch()['unread'];
        
    } catch (PDOException $e) {
        error_log("Admin Error: " . $e->getMessage());
    }
}

// Handle CRUD operations
$message = '';
$error = '';

// Update booking status
if ($isLoggedIn && isset($_POST['action']) && $_POST['action'] === 'update_booking_status') {
    $bookingId = $_POST['booking_id'] ?? 0;
    $newStatus = $_POST['status'] ?? '';
    
    if ($bookingId && in_array($newStatus, ['pending', 'confirmed', 'cancelled', 'completed'])) {
        try {
            $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $bookingId]);
            $message = 'Booking status updated successfully';
        } catch (PDOException $e) {
            $error = 'Failed to update booking status';
        }
    }
}

// Delete booking
if ($isLoggedIn && isset($_POST['action']) && $_POST['action'] === 'delete_booking') {
    $bookingId = $_POST['booking_id'] ?? 0;
    
    if ($bookingId) {
        try {
            $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
            $stmt->execute([$bookingId]);
            $message = 'Booking deleted successfully';
        } catch (PDOException $e) {
            $error = 'Failed to delete booking';
        }
    }
}

// Update contact status
if ($isLoggedIn && isset($_POST['action']) && $_POST['action'] === 'update_contact_status') {
    $contactId = $_POST['contact_id'] ?? 0;
    $newStatus = $_POST['status'] ?? '';
    
    if ($contactId && in_array($newStatus, ['unread', 'read', 'replied'])) {
        try {
            $stmt = $pdo->prepare("UPDATE contacts SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $contactId]);
            $message = 'Contact status updated';
        } catch (PDOException $e) {
            $error = 'Failed to update contact';
        }
    }
}

// Delete contact
if ($isLoggedIn && isset($_POST['action']) && $_POST['action'] === 'delete_contact') {
    $contactId = $_POST['contact_id'] ?? 0;
    
    if ($contactId) {
        try {
            $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = ?");
            $stmt->execute([$contactId]);
            $message = 'Contact deleted successfully';
        } catch (PDOException $e) {
            $error = 'Failed to delete contact';
        }
    }
}

// Add/Update room
if ($isLoggedIn && isset($_POST['action']) && $_POST['action'] === 'save_room') {
    $roomId = $_POST['room_id'] ?? 0;
    $roomType = strtolower(trim($_POST['room_type']));
    $roomName = trim($_POST['room_name']);
    $pricePerNight = floatval($_POST['price_per_night']);
    $capacity = intval($_POST['capacity']);
    $description = trim($_POST['description']);
    $amenities = isset($_POST['amenities']) ? json_encode($_POST['amenities']) : json_encode([]);
    $isAvailable = isset($_POST['is_available']) ? 1 : 0;
    
    try {
        if ($roomId) {
            $stmt = $pdo->prepare("
                UPDATE rooms SET room_type = ?, room_name = ?, price_per_night = ?, 
                capacity = ?, description = ?, amenities = ?, is_available = ? 
                WHERE id = ?
            ");
            $stmt->execute([$roomType, $roomName, $pricePerNight, $capacity, $description, $amenities, $isAvailable, $roomId]);
            $message = 'Room updated successfully';
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO rooms (room_type, room_name, price_per_night, capacity, description, amenities, is_available)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$roomType, $roomName, $pricePerNight, $capacity, $description, $amenities, $isAvailable]);
            $message = 'Room added successfully';
        }
    } catch (PDOException $e) {
        $error = 'Failed to save room: ' . $e->getMessage();
    }
}

// Delete room
if ($isLoggedIn && isset($_POST['action']) && $_POST['action'] === 'delete_room') {
    $roomId = $_POST['room_id'] ?? 0;
    
    if ($roomId) {
        try {
            $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
            $stmt->execute([$roomId]);
            $message = 'Room deleted successfully';
        } catch (PDOException $e) {
            $error = 'Failed to delete room';
        }
    }
}

// Add/Update testimonial
if ($isLoggedIn && isset($_POST['action']) && $_POST['action'] === 'save_testimonial') {
    $testimonialId = $_POST['testimonial_id'] ?? 0;
    $guestName = trim($_POST['guest_name']);
    $country = trim($_POST['country']);
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    try {
        if ($testimonialId) {
            $stmt = $pdo->prepare("
                UPDATE testimonials SET guest_name = ?, country = ?, rating = ?, comment = ?, is_active = ?
                WHERE id = ?
            ");
            $stmt->execute([$guestName, $country, $rating, $comment, $isActive, $testimonialId]);
            $message = 'Testimonial updated successfully';
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO testimonials (guest_name, country, rating, comment, is_active)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$guestName, $country, $rating, $comment, $isActive]);
            $message = 'Testimonial added successfully';
        }
    } catch (PDOException $e) {
        $error = 'Failed to save testimonial';
    }
}

// Delete testimonial
if ($isLoggedIn && isset($_POST['action']) && $_POST['action'] === 'delete_testimonial') {
    $testimonialId = $_POST['testimonial_id'] ?? 0;
    
    if ($testimonialId) {
        try {
            $stmt = $pdo->prepare("DELETE FROM testimonials WHERE id = ?");
            $stmt->execute([$testimonialId]);
            $message = 'Testimonial deleted successfully';
        } catch (PDOException $e) {
            $error = 'Failed to delete testimonial';
        }
    }
}

// Add new admin
if ($isLoggedIn && isset($_POST['action']) && $_POST['action'] === 'add_admin') {
    $newUsername = trim($_POST['new_username'] ?? '');
    $newEmail = trim($_POST['new_email'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $newFullName = trim($_POST['new_full_name'] ?? '');
    $newRole = $_POST['new_role'] ?? 'staff';
    
    if ($newUsername && $newEmail && $newPassword) {
        try {
            // Check if username or email already exists
            $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ? OR email = ?");
            $stmt->execute([$newUsername, $newEmail]);
            if ($stmt->fetch()) {
                $error = 'Username or email already exists';
            } else {
                $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    INSERT INTO admins (username, email, password_hash, full_name, role, is_active)
                    VALUES (?, ?, ?, ?, ?, TRUE)
                ");
                $stmt->execute([$newUsername, $newEmail, $passwordHash, $newFullName, $newRole]);
                $message = 'Admin added successfully';
            }
        } catch (PDOException $e) {
            $error = 'Failed to add admin: ' . $e->getMessage();
        }
    } else {
        $error = 'Please fill all required fields';
    }
}

// Delete admin
if ($isLoggedIn && isset($_POST['action']) && $_POST['action'] === 'delete_admin') {
    $adminId = $_POST['admin_id'] ?? 0;
    
    if ($adminId) {
        try {
            // Prevent deleting yourself
            if ($adminId == $_SESSION['admin_id']) {
                $error = 'You cannot delete your own account';
            } else {
                $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
                $stmt->execute([$adminId]);
                $message = 'Admin deleted successfully';
            }
        } catch (PDOException $e) {
            $error = 'Failed to delete admin';
        }
    }
}

// Change password
if ($isLoggedIn && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if ($currentPassword && $newPassword && $confirmPassword) {
        if ($newPassword !== $confirmPassword) {
            $error = 'New passwords do not match';
        } else {
            try {
                // Verify current password
                $stmt = $pdo->prepare("SELECT password_hash FROM admins WHERE id = ?");
                $stmt->execute([$_SESSION['admin_id']]);
                $admin = $stmt->fetch();
                
                if ($admin && password_verify($currentPassword, $admin['password_hash'])) {
                    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE admins SET password_hash = ? WHERE id = ?");
                    $stmt->execute([$newHash, $_SESSION['admin_id']]);
                    $message = 'Password changed successfully';
                } else {
                    $error = 'Current password is incorrect';
                }
            } catch (PDOException $e) {
                $error = 'Failed to change password';
            }
        }
    } else {
        $error = 'Please fill all password fields';
    }
}

// Get data based on tab
if ($isLoggedIn && $pdo) {
    try {
        if ($tab === 'bookings') {
            $statusFilter = $_GET['status'] ?? '';
            $query = "SELECT * FROM bookings";
            if ($statusFilter) {
                $query .= " WHERE status = ?";
                $stmt = $pdo->prepare($query . " ORDER BY created_at DESC");
                $stmt->execute([$statusFilter]);
            } else {
                $stmt = $pdo->query($query . " ORDER BY created_at DESC");
            }
            $bookings = $stmt->fetchAll();
            
        } elseif ($tab === 'contacts') {
            $statusFilter = $_GET['status'] ?? '';
            $query = "SELECT * FROM contacts";
            if ($statusFilter) {
                $query .= " WHERE status = ?";
                $stmt = $pdo->prepare($query . " ORDER BY created_at DESC");
                $stmt->execute([$statusFilter]);
            } else {
                $stmt = $pdo->query($query . " ORDER BY created_at DESC");
            }
            $contacts = $stmt->fetchAll();
            
        } elseif ($tab === 'rooms') {
            $stmt = $pdo->query("SELECT * FROM rooms ORDER BY price_per_night ASC");
            $rooms = $stmt->fetchAll();
            
        } elseif ($tab === 'testimonials') {
            $stmt = $pdo->query("SELECT * FROM testimonials ORDER BY created_at DESC");
            $testimonials = $stmt->fetchAll();
            
        } elseif ($tab === 'admins') {
            $stmt = $pdo->query("SELECT id, username, email, full_name, role, is_active, last_login, created_at FROM admins ORDER BY created_at DESC");
            $admins = $stmt->fetchAll();
            
        } elseif ($tab === 'profile') {
            // Get current admin info
            $stmt = $pdo->prepare("SELECT id, username, email, full_name, role, last_login FROM admins WHERE id = ?");
            $stmt->execute([$_SESSION['admin_id']]);
            $currentAdmin = $stmt->fetch();
        }
    } catch (PDOException $e) {
        error_log("Tab data error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ujamaa Hostel - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #059669;
            --primary-dark: #047857;
            --secondary: #fbbf24;
            --dark: #0f172a;
            --light: #f8fafc;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: #f1f5f9;
        }
        .sidebar {
            min-height: 100vh;
            background: var(--dark);
        }
        .nav-link {
            color: rgba(255,255,255,0.7);
            border-radius: 8px;
            margin: 2px 0;
        }
        .nav-link:hover, .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .table-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-confirmed { background: #d1fae5; color: #065f46; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        .status-completed { background: #e0e7ff; color: #3730a3; }
        .status-unread { background: #fee2e2; color: #991b1b; }
        .status-read { background: #d1fae5; color: #065f46; }
        .status-replied { background: #e0e7ff; color: #3730a3; }
    </style>
</head>
<body>
    <?php if (!$isLoggedIn): ?>
    <!-- Login Page -->
    <div class="min-vh-100 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #0f172a, #1e3a5f);">
        <div class="card border-0 shadow-lg rounded-4 p-5" style="width: 100%; max-width: 400px;">
            <div class="text-center mb-4">
                <i class="bi bi-building fs-1 text-primary"></i>
                <h3 class="mt-3 fw-bold">Ujamaa Hostel</h3>
                <p class="text-muted">Admin Login</p>
            </div>
            <?php if (isset($loginError)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($loginError) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control form-control-lg" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control form-control-lg" required>
                </div>
                <button type="submit" name="login" class="btn btn-primary btn-lg w-100 rounded-pill">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Login
                </button>
            </form>
            <div class="text-center mt-4">
                <a href="../index.php" class="text-muted"><i class="bi bi-arrow-left me-1"></i>Back to Website</a>
            </div>
        </div>
    </div>
    <?php else: ?>
    <!-- Admin Dashboard -->
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar p-3" style="width: 260px; position: fixed; height: 100vh; overflow-y: auto;">
            <div class="text-center mb-4 pb-3 border-bottom border-secondary">
                <i class="bi bi-building fs-3 text-white"></i>
                <h5 class="text-white mt-2 mb-0">Ujamaa Hostel</h5>
                <small class="text-white-50">Admin Panel</small>
            </div>
            
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?= $tab === 'dashboard' ? 'active' : '' ?>" href="?tab=dashboard">
                        <i class="bi bi-speedometer2 me-2"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $tab === 'bookings' ? 'active' : '' ?>" href="?tab=bookings">
                        <i class="bi bi-calendar-check me-2"></i>Bookings
                        <?php if(isset($stats['pending_bookings']) && $stats['pending_bookings'] > 0): ?>
                            <span class="badge bg-warning float-end"><?= $stats['pending_bookings'] ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $tab === 'contacts' ? 'active' : '' ?>" href="?tab=contacts">
                        <i class="bi bi-envelope me-2"></i>Messages
                        <?php if(isset($unreadContacts) && $unreadContacts > 0): ?>
                            <span class="badge bg-danger float-end"><?= $unreadContacts ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $tab === 'rooms' ? 'active' : '' ?>" href="?tab=rooms">
                        <i class="bi bi-door-open me-2"></i>Rooms
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $tab === 'testimonials' ? 'active' : '' ?>" href="?tab=testimonials">
                        <i class="bi bi-chat-quote me-2"></i>Testimonials
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $tab === 'admins' ? 'active' : '' ?>" href="?tab=admins">
                        <i class="bi bi-people me-2"></i>Admins
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $tab === 'profile' ? 'active' : '' ?>" href="?tab=profile">
                        <i class="bi bi-person-gear me-2"></i>Profile
                    </a>
                </li>
            </ul>
            
            <div class="mt-auto pt-3 border-top border-secondary">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary rounded-circle p-2 me-2">
                        <i class="bi bi-person text-white"></i>
                    </div>
                    <div>
                        <small class="text-white-50">Logged in as</small>
                        <div class="text-white fw-bold"><?= htmlspecialchars($_SESSION['admin_name'] ?? $_SESSION['admin_username']) ?></div>
                    </div>
                </div>
                <a href="?logout=1" class="btn btn-outline-light w-100 rounded-pill btn-sm">
                    <i class="bi bi-box-arrow-left me-2"></i>Logout
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-grow-1 p-4" style="margin-left: 260px;">
            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($tab === 'dashboard'): ?>
            <!-- Dashboard Tab -->
            <h4 class="mb-4 fw-bold">Dashboard Overview</h4>
            
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1">Total Bookings</p>
                                <h2 class="fw-bold mb-0"><?= number_format($stats['total_bookings'] ?? 0) ?></h2>
                            </div>
                            <div class="bg-primary-subtle p-3 rounded">
                                <i class="bi bi-calendar-check fs-4 text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1">Confirmed</p>
                                <h2 class="fw-bold mb-0"><?= number_format($stats['confirmed_bookings'] ?? 0) ?></h2>
                            </div>
                            <div class="bg-success-subtle p-3 rounded">
                                <i class="bi bi-check-circle fs-4 text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1">Pending</p>
                                <h2 class="fw-bold mb-0"><?= number_format($stats['pending_bookings'] ?? 0) ?></h2>
                            </div>
                            <div class="bg-warning-subtle p-3 rounded">
                                <i class="bi bi-hourglass-split fs-4 text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1">Total Revenue</p>
                                <h2 class="fw-bold mb-0">$<?= number_format($stats['total_revenue'] ?? 0) ?></h2>
                            </div>
                            <div class="bg-info-subtle p-3 rounded">
                                <i class="bi bi-currency-dollar fs-4 text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="table-card">
                        <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold">Recent Bookings</h6>
                            <a href="?tab=bookings" class="btn btn-sm btn-primary rounded-pill">View All</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Reference</th>
                                        <th>Guest</th>
                                        <th>Room</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($pdo) {
                                        $stmt = $pdo->query("SELECT * FROM bookings ORDER BY created_at DESC LIMIT 5");
                                        $recentBookings = $stmt->fetchAll();
                                        foreach ($recentBookings as $booking): ?>
                                        <tr>
                                            <td><small><?= htmlspecialchars($booking['booking_reference']) ?></small></td>
                                            <td><?= htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']) ?></td>
                                            <td><?= htmlspecialchars(ucfirst($booking['room_type'])) ?></td>
                                            <td><span class="badge status-<?= $booking['status'] ?>"><?= ucfirst($booking['status']) ?></span></td>
                                        </tr>
                                        <?php endforeach;
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="table-card">
                        <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold">Recent Messages</h6>
                            <a href="?tab=contacts" class="btn btn-sm btn-primary rounded-pill">View All</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>From</th>
                                        <th>Subject</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($pdo) {
                                        $stmt = $pdo->query("SELECT * FROM contacts ORDER BY created_at DESC LIMIT 5");
                                        $recentContacts = $stmt->fetchAll();
                                        foreach ($recentContacts as $contact): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']) ?></td>
                                            <td><small><?= htmlspecialchars($contact['subject']) ?></small></td>
                                            <td><span class="badge status-<?= $contact['status'] ?>"><?= ucfirst($contact['status']) ?></span></td>
                                        </tr>
                                        <?php endforeach;
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php elseif ($tab === 'bookings'): ?>
            <!-- Bookings Tab -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0 fw-bold">Bookings Management</h4>
                <div class="btn-group">
                    <a href="?tab=bookings" class="btn btn-sm btn-outline-secondary <?= !$_GET['status'] ? 'active' : '' ?>">All</a>
                    <a href="?tab=bookings&status=pending" class="btn btn-sm btn-outline-secondary <?= ($_GET['status'] ?? '') === 'pending' ? 'active' : '' ?>">Pending</a>
                    <a href="?tab=bookings&status=confirmed" class="btn btn-sm btn-outline-secondary <?= ($_GET['status'] ?? '') === 'confirmed' ? 'active' : '' ?>">Confirmed</a>
                    <a href="?tab=bookings&status=cancelled" class="btn btn-sm btn-outline-secondary <?= ($_GET['status'] ?? '') === 'cancelled' ? 'active' : '' ?>">Cancelled</a>
                </div>
            </div>
            
            <div class="table-card">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Ref#</th>
                                <th>Guest Details</th>
                                <th>Room</th>
                                <th>Dates</th>
                                <th>Guests</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($bookings)): ?>
                            <tr><td colspan="8" class="text-center py-4 text-muted">No bookings found</td></tr>
                            <?php else: ?>
                                <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($booking['booking_reference']) ?></strong><br><small class="text-muted"><?= date('M d, Y', strtotime($booking['created_at'])) ?></small></td>
                                    <td>
                                        <strong><?= htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']) ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($booking['email']) ?></small><br>
                                        <small><?= htmlspecialchars($booking['phone']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars(ucfirst($booking['room_type'])) ?></td>
                                    <td>
                                        <small>In: <?= date('M d', strtotime($booking['checkin_date'])) ?></small><br>
                                        <small>Out: <?= date('M d', strtotime($booking['checkout_date'])) ?></small>
                                    </td>
                                    <td><?= $booking['guests'] ?></td>
                                    <td><strong>$<?= number_format($booking['total_price']) ?></strong></td>
                                    <td><span class="badge status-<?= $booking['status'] ?>"><?= ucfirst($booking['status']) ?></span></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="update_booking_status">
                                                <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="width: auto; display: inline-block;">
                                                    <option value="pending" <?= $booking['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                                    <option value="confirmed" <?= $booking['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                                    <option value="completed" <?= $booking['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                                    <option value="cancelled" <?= $booking['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                                </select>
                                            </form>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this booking?');">
                                                <input type="hidden" name="action" value="delete_booking">
                                                <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <?php elseif ($tab === 'contacts'): ?>
            <!-- Contacts Tab -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0 fw-bold">Messages</h4>
                <div class="btn-group">
                    <a href="?tab=contacts" class="btn btn-sm btn-outline-secondary <?= !$_GET['status'] ? 'active' : '' ?>">All</a>
                    <a href="?tab=contacts&status=unread" class="btn btn-sm btn-outline-secondary <?= ($_GET['status'] ?? '') === 'unread' ? 'active' : '' ?>">Unread</a>
                    <a href="?tab=contacts&status=read" class="btn btn-sm btn-outline-secondary <?= ($_GET['status'] ?? '') === 'read' ? 'active' : '' ?>">Read</a>
                    <a href="?tab=contacts&status=replied" class="btn btn-sm btn-outline-secondary <?= ($_GET['status'] ?? '') === 'replied' ? 'active' : '' ?>">Replied</a>
                </div>
            </div>
            
            <div class="row g-4">
                <?php if (empty($contacts)): ?>
                <div class="col-12"><div class="table-card p-4 text-center text-muted">No messages found</div></div>
                <?php else: ?>
                    <?php foreach ($contacts as $contact): ?>
                    <div class="col-md-6">
                        <div class="table-card p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h6 class="fw-bold mb-0"><?= htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']) ?></h6>
                                    <small class="text-muted"><?= htmlspecialchars($contact['email']) ?></small>
                                    <?php if($contact['phone']): ?>
                                        <br><small><?= htmlspecialchars($contact['phone']) ?></small>
                                    <?php endif; ?>
                                </div>
                                <span class="badge status-<?= $contact['status'] ?>"><?= ucfirst($contact['status']) ?></span>
                            </div>
                            <h6 class="fw-bold"><?= htmlspecialchars($contact['subject']) ?></h6>
                            <p class="mb-3 text-muted"><?= nl2br(htmlspecialchars($contact['message'])) ?></p>
                            <small class="text-muted"><?= date('M d, Y g:i A', strtotime($contact['created_at'])) ?></small>
                            <div class="mt-3 pt-3 border-top d-flex gap-2">
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="update_contact_status">
                                    <input type="hidden" name="contact_id" value="<?= $contact['id'] ?>">
                                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <option value="unread" <?= $contact['status'] === 'unread' ? 'selected' : '' ?>>Unread</option>
                                        <option value="read" <?= $contact['status'] === 'read' ? 'selected' : '' ?>>Read</option>
                                        <option value="replied" <?= $contact['status'] === 'replied' ? 'selected' : '' ?>>Replied</option>
                                    </select>
                                </form>
                                <form method="POST" onsubmit="return confirm('Delete this message?');">
                                    <input type="hidden" name="action" value="delete_contact">
                                    <input type="hidden" name="contact_id" value="<?= $contact['id'] ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <?php elseif ($tab === 'rooms'): ?>
            <!-- Rooms Tab -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0 fw-bold">Room Management</h4>
                <button class="btn btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#roomModal">
                    <i class="bi bi-plus-lg me-2"></i>Add New Room
                </button>
            </div>
            
            <div class="row g-4">
                <?php if (empty($rooms)): ?>
                <div class="col-12"><div class="table-card p-4 text-center text-muted">No rooms found</div></div>
                <?php else: ?>
                    <?php foreach ($rooms as $room): 
                        $amenities = json_decode($room['amenities'] ?? '[]', true) ?: [];
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="table-card p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="fw-bold mb-0"><?= htmlspecialchars($room['room_name']) ?></h5>
                                    <small class="text-muted"><?= htmlspecialchars($room['room_type']) ?></small>
                                </div>
                                <h5 class="text-primary fw-bold mb-0">$<?= number_format($room['price_per_night']) ?><small class="text-muted">/night</small></h5>
                            </div>
                            <p class="text-muted mb-3"><?= htmlspecialchars($room['description']) ?></p>
                            <div class="mb-3">
                                <small class="text-muted">Capacity: <?= $room['capacity'] ?> guests</small><br>
                                <span class="badge <?= $room['is_available'] ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= $room['is_available'] ? 'Available' : 'Unavailable' ?>
                                </span>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#roomModal" 
                                    data-id="<?= $room['id'] ?>" data-type="<?= htmlspecialchars($room['room_type']) ?>"
                                    data-name="<?= htmlspecialchars($room['room_name']) ?>" data-price="<?= $room['price_per_night'] ?>"
                                    data-capacity="<?= $room['capacity'] ?>" data-desc="<?= htmlspecialchars($room['description']) ?>"
                                    data-available="<?= $room['is_available'] ?>">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <form method="POST" onsubmit="return confirm('Delete this room?');">
                                    <input type="hidden" name="action" value="delete_room">
                                    <input type="hidden" name="room_id" value="<?= $room['id'] ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Room Modal -->
            <div class="modal fade" id="roomModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST">
                            <div class="modal-header">
                                <h5 class="modal-title">Add/Edit Room</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="action" value="save_room">
                                <input type="hidden" name="room_id" id="roomId">
                                <div class="mb-3">
                                    <label class="form-label">Room Type (slug)</label>
                                    <input type="text" name="room_type" id="roomType" class="form-control" required placeholder="e.g., standard, deluxe, family">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Room Name</label>
                                    <input type="text" name="room_name" id="roomName" class="form-control" required>
                                </div>
                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <label class="form-label">Price per Night ($)</label>
                                        <input type="number" name="price_per_night" id="roomPrice" class="form-control" required min="1">
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label class="form-label">Capacity</label>
                                        <input type="number" name="capacity" id="roomCapacity" class="form-control" required min="1" max="20">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" id="roomDesc" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="is_available" id="roomAvailable" class="form-check-input" checked>
                                    <label class="form-check-label">Available for booking</label>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Room</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <?php elseif ($tab === 'testimonials'): ?>
            <!-- Testimonials Tab -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0 fw-bold">Testimonials</h4>
                <button class="btn btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#testimonialModal">
                    <i class="bi bi-plus-lg me-2"></i>Add New
                </button>
            </div>
            
            <div class="row g-4">
                <?php if (empty($testimonials)): ?>
                <div class="col-12"><div class="table-card p-4 text-center text-muted">No testimonials found</div></div>
                <?php else: ?>
                    <?php foreach ($testimonials as $testimonial): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="table-card p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h6 class="fw-bold mb-0"><?= htmlspecialchars($testimonial['guest_name']) ?></h6>
                                    <small class="text-muted"><?= htmlspecialchars($testimonial['country'] ?? '') ?></small>
                                </div>
                                <div>
                                    <?php for($i = 0; $i < $testimonial['rating']; $i++): ?>
                                        <i class="bi bi-star-fill text-warning"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <p class="mb-3">"<?= htmlspecialchars($testimonial['comment']) ?>"</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge <?= $testimonial['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= $testimonial['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#testimonialModal"
                                        data-id="<?= $testimonial['id'] ?>" data-name="<?= htmlspecialchars($testimonial['guest_name']) ?>"
                                        data-country="<?= htmlspecialchars($testimonial['country'] ?? '') ?>" data-rating="<?= $testimonial['rating'] ?>"
                                        data-comment="<?= htmlspecialchars($testimonial['comment']) ?>" data-active="<?= $testimonial['is_active'] ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" onsubmit="return confirm('Delete this testimonial?');">
                                        <input type="hidden" name="action" value="delete_testimonial">
                                        <input type="hidden" name="testimonial_id" value="<?= $testimonial['id'] ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Testimonial Modal -->
            <div class="modal fade" id="testimonialModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST">
                            <div class="modal-header">
                                <h5 class="modal-title">Add/Edit Testimonial</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="action" value="save_testimonial">
                                <input type="hidden" name="testimonial_id" id="testimonialId">
                                <div class="mb-3">
                                    <label class="form-label">Guest Name</label>
                                    <input type="text" name="guest_name" id="testimonialName" class="form-control" required>
                                </div>
                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <label class="form-label">Country</label>
                                        <input type="text" name="country" id="testimonialCountry" class="form-control">
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label class="form-label">Rating</label>
                                        <select name="rating" id="testimonialRating" class="form-select">
                                            <option value="5">5 Stars</option>
                                            <option value="4">4 Stars</option>
                                            <option value="3">3 Stars</option>
                                            <option value="2">2 Stars</option>
                                            <option value="1">1 Star</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Comment</label>
                                    <textarea name="comment" id="testimonialComment" class="form-control" rows="3" required></textarea>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="is_active" id="testimonialActive" class="form-check-input" checked>
                                    <label class="form-check-label">Active (show on website)</label>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Testimonial</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Admins Tab -->
            <?php if ($tab === 'admins'): ?>
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="mb-0"><i class="bi bi-people me-2"></i>Manage Admins</h4>
                            <button class="btn btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                                <i class="bi bi-plus-lg me-2"></i>Add New Admin
                            </button>
                        </div>
                        
                        <?php if ($message): ?>
                            <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($message) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                        <?php endif; ?>
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                        <?php endif; ?>
                        
                        <div class="table-card">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Full Name</th>
                                            <th>Role</th>
                                            <th>Status</th>
                                            <th>Last Login</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($admins)): ?>
                                            <?php foreach ($admins as $admin): ?>
                                                <tr>
                                                    <td class="fw-bold"><?= htmlspecialchars($admin['username']) ?></td>
                                                    <td><?= htmlspecialchars($admin['email']) ?></td>
                                                    <td><?= htmlspecialchars($admin['full_name'] ?? '-') ?></td>
                                                    <td><span class="badge bg-<?= $admin['role'] === 'admin' ? 'primary' : ($admin['role'] === 'manager' ? 'info' : 'secondary') ?>"><?= htmlspecialchars($admin['role']) ?></span></td>
                                                    <td><span class="badge bg-<?= $admin['is_active'] ? 'success' : 'danger' ?>"><?= $admin['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                                                    <td><?= $admin['last_login'] ? date('M j, Y g:i A', strtotime($admin['last_login'])) : 'Never' ?></td>
                                                    <td>
                                                        <?php if ($admin['id'] != $_SESSION['admin_id']): ?>
                                                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this admin?');">
                                                                <input type="hidden" name="action" value="delete_admin">
                                                                <input type="hidden" name="admin_id" value="<?= $admin['id'] ?>">
                                                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                                            </form>
                                                        <?php else: ?>
                                                            <span class="text-muted"><i class="bi bi-person-check"></i> You</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="7" class="text-center text-muted py-4">No admins found</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Add Admin Modal -->
                <div class="modal fade" id="addAdminModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Add New Admin</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST">
                                <div class="modal-body">
                                    <input type="hidden" name="action" value="add_admin">
                                    <div class="mb-3">
                                        <label class="form-label">Username *</label>
                                        <input type="text" name="new_username" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email *</label>
                                        <input type="email" name="new_email" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" name="new_full_name" class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Role *</label>
                                        <select name="new_role" class="form-select" required>
                                            <option value="staff">Staff</option>
                                            <option value="manager">Manager</option>
                                            <option value="admin">Admin</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Password *</label>
                                        <input type="password" name="new_password" class="form-control" required minlength="6">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Add Admin</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Profile Tab (Change Password) -->
            <?php if ($tab === 'profile'): ?>
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <h4 class="mb-4"><i class="bi bi-person-gear me-2"></i>Profile Settings</h4>
                                
                                <?php if ($message): ?>
                                    <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($message) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                                <?php endif; ?>
                                <?php if ($error): ?>
                                    <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                                <?php endif; ?>
                                
                                <div class="mb-4 p-3 bg-light rounded">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <small class="text-muted">Username</small>
                                            <div class="fw-bold"><?= htmlspecialchars($currentAdmin['username'] ?? '') ?></div>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted">Email</small>
                                            <div class="fw-bold"><?= htmlspecialchars($currentAdmin['email'] ?? '') ?></div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <small class="text-muted">Role</small>
                                            <div class="fw-bold text-capitalize"><?= htmlspecialchars($currentAdmin['role'] ?? '') ?></div>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted">Last Login</small>
                                            <div class="fw-bold"><?= $currentAdmin['last_login'] ? date('M j, Y g:i A', strtotime($currentAdmin['last_login'])) : 'Never' ?></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <h5 class="mb-3">Change Password</h5>
                                <form method="POST">
                                    <input type="hidden" name="action" value="change_password">
                                    <div class="mb-3">
                                        <label class="form-label">Current Password *</label>
                                        <input type="password" name="current_password" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">New Password *</label>
                                        <input type="password" name="new_password" class="form-control" required minlength="6">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Confirm New Password *</label>
                                        <input type="password" name="confirm_password" class="form-control" required minlength="6">
                                    </div>
                                    <button type="submit" class="btn btn-primary rounded-pill">
                                        <i class="bi bi-key me-2"></i>Change Password
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Room Modal populate
        document.getElementById('roomModal').addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            if (button.dataset.id) {
                document.getElementById('roomId').value = button.dataset.id;
                document.getElementById('roomType').value = button.dataset.type;
                document.getElementById('roomName').value = button.dataset.name;
                document.getElementById('roomPrice').value = button.dataset.price;
                document.getElementById('roomCapacity').value = button.dataset.capacity;
                document.getElementById('roomDesc').value = button.dataset.desc || '';
                document.getElementById('roomAvailable').checked = button.dataset.available == '1';
            } else {
                this.querySelector('form').reset();
                document.getElementById('roomId').value = '';
            }
        });

        // Testimonial Modal populate
        document.getElementById('testimonialModal').addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            if (button.dataset.id) {
                document.getElementById('testimonialId').value = button.dataset.id;
                document.getElementById('testimonialName').value = button.dataset.name;
                document.getElementById('testimonialCountry').value = button.dataset.country || '';
                document.getElementById('testimonialRating').value = button.dataset.rating;
                document.getElementById('testimonialComment').value = button.dataset.comment || '';
                document.getElementById('testimonialActive').checked = button.dataset.active == '1';
            } else {
                this.querySelector('form').reset();
                document.getElementById('testimonialId').value = '';
            }
        });
    </script>
</body>
</html>