<?php
require_once 'config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: index.php");
    exit();
}

$conn = getDBConnection();
$admin_id = $_SESSION['user_id'];

// Filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$category_filter = isset($_GET['category']) ? $_GET['category'] : 'all';
$priority_filter = isset($_GET['priority']) ? $_GET['priority'] : 'all';

// Build query
$sql = "SELECT c.*, u.full_name, u.student_id, u.email 
        FROM complaints c 
        JOIN users u ON c.user_id = u.id 
        WHERE 1=1";

$params = [];
$types = "";

if ($status_filter != 'all') {
    $sql .= " AND c.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if ($category_filter != 'all') {
    $sql .= " AND c.category = ?";
    $params[] = $category_filter;
    $types .= "s";
}

if ($priority_filter != 'all') {
    $sql .= " AND c.priority = ?";
    $params[] = $priority_filter;
    $types .= "s";
}

$sql .= " ORDER BY c.created_at DESC";

// Prepare and execute
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$complaints = $result->fetch_all(MYSQLI_ASSOC);

// Get unique categories for filter
$categories_sql = "SELECT DISTINCT category FROM complaints ORDER BY category";
$categories_result = $conn->query($categories_sql);
$categories = $categories_result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Complaints - Complaint Management System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="container header-container">
            <div class="logo">
                <i class="fas fa-graduation-cap logo-icon"></i>
                <span>University Complaint System - Admin Panel</span>
            </div>
            <nav>
                <ul>
                    <li><a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="manage_complaints.php"><i class="fas fa-tasks"></i> Manage Complaints</a></li>
                    <li><a href="manage_users.php"><i class="fas fa-users"></i> Manage Users</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <h1 style="margin: 20px 0;">Manage Complaints</h1>
        
        <!-- Filters -->
        <div class="card">
            <h2><i class="fas fa-filter"></i> Filter Complaints</h2>
            <form method="GET" action="" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Status</option>
                        <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="in_progress" <?php echo $status_filter == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="resolved" <?php echo $status_filter == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                        <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category" class="form-control">
                        <option value="all" <?php echo $category_filter == 'all' ? 'selected' : ''; ?>>All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['category']; ?>" 
                                <?php echo $category_filter == $cat['category'] ? 'selected' : ''; ?>>
                                <?php echo $cat['category']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="priority">Priority</label>
                    <select id="priority" name="priority" class="form-control">
                        <option value="all" <?php echo $priority_filter == 'all' ? 'selected' : ''; ?>>All Priorities</option>
                        <option value="low" <?php echo $priority_filter == 'low' ? 'selected' : ''; ?>>Low</option>
                        <option value="medium" <?php echo $priority_filter == 'medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="high" <?php echo $priority_filter == 'high' ? 'selected' : ''; ?>>High</option>
                    </select>
                </div>
                
                <div class="form-group" style="align-self: end;">
                    <button type="submit" class="btn" style="width: 100%;">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Complaints Table -->
        <div class="card">
            <h2><i class="fas fa-list-alt"></i> Complaints (<?php echo count($complaints); ?>)</h2>
            
            <?php if (count($complaints) > 0): ?>
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
                            <?php foreach ($complaints as $complaint): ?>
                                <tr>
                                    <td>#<?php echo $complaint['complaint_id']; ?></td>
                                    <td><?php echo htmlspecialchars($complaint['title']); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($complaint['full_name']); ?></strong><br>
                                        <small><?php echo $complaint['student_id']; ?></small><br>
                                        <small><?php echo $complaint['email']; ?></small>
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
                <p>No complaints found with the selected filters.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> University Complaint Management System | Group 10</p>
        </div>
    </footer>
</body>
</html>