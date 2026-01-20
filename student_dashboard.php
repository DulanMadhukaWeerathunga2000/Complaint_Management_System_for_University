<?php
require_once 'config/database.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'student') {
    header("Location: index.php");
    exit();
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Get user's complaints
$sql = "SELECT * FROM complaints WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$complaints = $result->fetch_all(MYSQLI_ASSOC);

// Count complaints by status
$count_sql = "SELECT status, COUNT(*) as count FROM complaints WHERE user_id = ? GROUP BY status";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();

$status_counts = [
    'pending' => 0,
    'in_progress' => 0,
    'resolved' => 0,
    'rejected' => 0
];

while ($row = $count_result->fetch_assoc()) {
    $status_counts[$row['status']] = $row['count'];
}

$stmt->close();
$count_stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Complaint Management System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="container header-container">
            <div class="logo">
                <i class="fas fa-graduation-cap logo-icon"></i>
                <span>University Complaint System</span>
            </div>
            <nav>
                <ul>
                    <li><a href="student_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="submit_complaint.php"><i class="fas fa-plus-circle"></i> New Complaint</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    <li><a href="#"><i class="fas fa-user"></i> <?php echo $_SESSION['full_name']; ?></a></li>
                </ul>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <h1 style="margin: 20px 0;">Student Dashboard</h1>
        
        <!-- Stats Cards -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($complaints); ?></div>
                <div class="stat-label">Total Complaints</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $status_counts['pending']; ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $status_counts['in_progress']; ?></div>
                <div class="stat-label">In Progress</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $status_counts['resolved']; ?></div>
                <div class="stat-label">Resolved</div>
            </div>
        </div>
        
        <!-- My Complaints Table -->
        <div class="card">
            <h2><i class="fas fa-list-alt"></i> My Complaints</h2>
            
            <?php if (count($complaints) > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($complaints as $complaint): ?>
                                <tr>
                                    <td>#<?php echo $complaint['complaint_id']; ?></td>
                                    <td><?php echo htmlspecialchars($complaint['title']); ?></td>
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
                                        <a href="view_complaint.php?id=<?php echo $complaint['complaint_id']; ?>" class="btn btn-sm">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>You haven't submitted any complaints yet.</p>
                <a href="submit_complaint.php" class="btn">
                    <i class="fas fa-plus-circle"></i> Submit Your First Complaint
                </a>
            <?php endif; ?>
        </div>
        
        <!-- Quick Actions -->
        <div class="card">
            <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
            <div style="display: flex; gap: 15px; margin-top: 15px;">
                <a href="submit_complaint.php" class="btn btn-success">
                    <i class="fas fa-plus-circle"></i> Submit New Complaint
                </a>
                <a href="profile.php" class="btn">
                    <i class="fas fa-user-edit"></i> Edit Profile
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