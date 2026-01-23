<?php
require_once 'config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: index.php");
    exit();
}

$conn = getDBConnection();
$admin_id = $_SESSION['user_id'];

// Get statistics
$total_complaints_sql = "SELECT COUNT(*) as total FROM complaints";
$total_result = $conn->query($total_complaints_sql);
$total_complaints = $total_result->fetch_assoc()['total'];

$pending_sql = "SELECT COUNT(*) as pending FROM complaints WHERE status = 'pending'";
$pending_result = $conn->query($pending_sql);
$pending_complaints = $pending_result->fetch_assoc()['pending'];

$resolved_sql = "SELECT COUNT(*) as resolved FROM complaints WHERE status = 'resolved'";
$resolved_result = $conn->query($resolved_sql);
$resolved_complaints = $resolved_result->fetch_assoc()['resolved'];

$users_sql = "SELECT COUNT(*) as users FROM users WHERE user_type = 'student'";
$users_result = $conn->query($users_sql);
$total_users = $users_result->fetch_assoc()['users'];

// Get recent complaints
$recent_sql = "SELECT c.*, u.full_name, u.student_id 
               FROM complaints c 
               JOIN users u ON c.user_id = u.id 
               ORDER BY c.created_at DESC 
               LIMIT 10";
$recent_result = $conn->query($recent_sql);
$recent_complaints = $recent_result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Complaint Management System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="container header-container">
            <div class="logo">
<<<<<<< HEAD
                <img src="css/uniLogoi.png" alt="University Logo" class="logo-img">
=======
                <i class="fas fa-graduation-cap logo-icon"></i>
>>>>>>> 7c4024f80d6443629376d0d22a61c3d00caf73d9
                <span>University Complaint System - Admin Panel</span>
            </div>
            <nav>
                <ul>
                    <li><a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="manage_complaints.php"><i class="fas fa-tasks"></i> Manage Complaints</a></li>
                    <li><a href="manage_users.php"><i class="fas fa-users"></i> Manage Users</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    <li><a href="#"><i class="fas fa-user-shield"></i> <?php echo $_SESSION['full_name']; ?></a></li>
                </ul>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <h1 style="margin: 20px 0;">Admin Dashboard</h1>
        
        <!-- Stats Cards -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_complaints; ?></div>
                <div class="stat-label">Total Complaints</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $pending_complaints; ?></div>
                <div class="stat-label">Pending Complaints</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $resolved_complaints; ?></div>
                <div class="stat-label">Resolved Complaints</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_users; ?></div>
                <div class="stat-label">Registered Students</div>
            </div>
        </div>
        
        <!-- Recent Complaints -->
        <div class="card">
            <h2><i class="fas fa-history"></i> Recent Complaints</h2>
            
            <?php if (count($recent_complaints) > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Student</th>
                                <th>Category</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_complaints as $complaint): ?>
                                <tr>
                                    <td>#<?php echo $complaint['complaint_id']; ?></td>
                                    <td><?php echo htmlspecialchars($complaint['title']); ?></td>
                                    <td><?php echo htmlspecialchars($complaint['full_name']); ?><br>
                                        <small><?php echo $complaint['student_id']; ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($complaint['category']); ?></td>
                                    <td>
                                        <span class="priority-badge priority-<?php echo $complaint['priority']; ?>">
                                            <?php echo ucfirst($complaint['priority']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo str_replace('_', '-', $complaint['status']); ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($complaint['created_at'])); ?></td>
                                    <td>
                                        <a href="manage_complaint.php?id=<?php echo $complaint['complaint_id']; ?>" class="btn btn-sm">
                                            <i class="fas fa-edit"></i> Manage
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>No complaints found.</p>
            <?php endif; ?>
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="manage_complaints.php" class="btn">
                    <i class="fas fa-tasks"></i> View All Complaints
                </a>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card">
            <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
            <div style="display: flex; gap: 15px; margin-top: 15px; flex-wrap: wrap;">
                <a href="manage_complaints.php?status=pending" class="btn btn-warning">
                    <i class="fas fa-clock"></i> View Pending Complaints
                </a>
                <a href="manage_users.php" class="btn">
                    <i class="fas fa-users-cog"></i> Manage Users
                </a>
                <a href="reports.php" class="btn btn-success">
                    <i class="fas fa-chart-bar"></i> Generate Reports
                </a>
            </div>
        </div>
    </div>
    
    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> University Complaint Management System | Group 10</p>
        </div>
    </footer>
</body>
</html>