<?php
require_once 'config/database.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'student') {
    header("Location: index.php");
    exit();
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Get current user data for profile info
$user_sql = "SELECT * FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();
$user_stmt->close();

// Check if $user_data exists
if (!$user_data) {
    // Handle case where user data couldn't be fetched
    $user_data = [
        'full_name' => $_SESSION['full_name'] ?? 'User',
        'email' => 'Not available',
        'student_id' => '',
        'faculty' => '',
        'year' => '',
        'req_num' => ''
    ];
}

// Get user's complaints for dashboard
$sql = "SELECT * FROM complaints WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$recent_complaints = $result->fetch_all(MYSQLI_ASSOC);

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

$total_complaints = array_sum($status_counts);

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
                <img src="css/uniLogoi.png" alt="University Logo" class="logo-img">
                <span>University Complaint System</span>
            </div>
            <nav>
                <ul>
                    <li><a href="student_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="my_complaints.php"><i class="fas fa-list"></i> My Complaints</a></li>
                    <li><a href="submit_complaint.php"><i class="fas fa-plus-circle"></i> New Complaint</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    <li><a href="#"><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?></a></li>
                </ul>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <h1 style="margin: 20px 0;">Welcome, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?>!</h1>
        
        <!-- Profile Information -->
        <div class="card" style="margin-bottom: 20px;">
            <h2><i class="fas fa-user-circle"></i> Profile Information</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                <div>
                    <strong><i class="fas fa-user"></i> Name:</strong> 
                    <?php echo isset($user_data['full_name']) ? htmlspecialchars($user_data['full_name']) : 'Not set'; ?>
                </div>
                <div>
                    <strong><i class="fas fa-id-card"></i> Student ID:</strong> 
                    <?php echo isset($user_data['student_id']) && !empty($user_data['student_id']) ? htmlspecialchars($user_data['student_id']) : 'Not set'; ?>
                </div>
                <div>
                    <strong><i class="fas fa-envelope"></i> Email:</strong> 
                    <?php echo isset($user_data['email']) ? htmlspecialchars($user_data['email']) : 'Not set'; ?>
                </div>
                <div>
                    <strong><i class="fas fa-university"></i> Faculty:</strong> 
                    <?php echo isset($user_data['faculty']) && !empty($user_data['faculty']) ? htmlspecialchars($user_data['faculty']) : 'Not set'; ?>
                </div>
                <div>
                    <strong><i class="fas fa-calendar-alt"></i> Year:</strong> 
                    <?php echo isset($user_data['year']) && !empty($user_data['year']) ? htmlspecialchars($user_data['year']) : 'Not set'; ?>
                </div>
                <div>
                    <strong><i class="fas fa-hashtag"></i> Registration No:</strong> 
                    <?php echo isset($user_data['req_num']) && !empty($user_data['req_num']) ? htmlspecialchars($user_data['req_num']) : 'Not set'; ?>
                </div>
            </div>
           
        </div>
        
        <!-- Stats Cards -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_complaints; ?></div>
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
                                <th>Category</th>
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
                                    <td><?php echo htmlspecialchars($complaint['category']); ?></td>
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
                <div style="text-align: center; margin-top: 15px;">
                    <a href="my_complaints.php" class="btn">View All Complaints</a>
                </div>
            <?php else: ?>
                <p>You haven't submitted any complaints yet.</p>
                <div style="text-align: center; margin-top: 20px;">
                    <a href="submit_complaint.php" class="btn btn-success">
                        <i class="fas fa-plus-circle"></i> Submit Your First Complaint
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Quick Actions -->
        <div class="card">
            <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
            <div style="display: flex; gap: 15px; margin-top: 15px;">
                <a href="submit_complaint.php" class="btn btn-success" style="flex: 1;">
                    <i class="fas fa-plus-circle"></i> Submit New Complaint
                </a>
                <a href="profile.php" class="btn" style="flex: 1;">
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