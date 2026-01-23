<?php
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

$error = '';
$success = '';

// Handle complaint submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
<<<<<<< HEAD
    $faculty = $_POST['faculty'];
=======
>>>>>>> 7c4024f80d6443629376d0d22a61c3d00caf73d9
    $category = $_POST['category'];
    $description = $_POST['description'];
    $priority = $_POST['priority'];
    
    // Handle file upload
    $attachment_path = null;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'application/msword'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (in_array($_FILES['attachment']['type'], $allowed_types) && $_FILES['attachment']['size'] <= $max_size) {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid() . '_' . time() . '.' . $file_extension;
            $file_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $file_path)) {
                $attachment_path = $file_path;
            }
        }
    }
    
    // Insert complaint
<<<<<<< HEAD
    $sql = "INSERT INTO complaints (user_id, title, faculty, category, description, priority, attachment_path) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssss", $user_id, $title, $faculty, $category, $description, $priority, $attachment_path);
=======
    $sql = "INSERT INTO complaints (user_id, title, category, description, priority, attachment_path) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssss", $user_id, $title, $category, $description, $priority, $attachment_path);
>>>>>>> 7c4024f80d6443629376d0d22a61c3d00caf73d9
    
    if ($stmt->execute()) {
        $success = "Complaint submitted successfully! Your complaint ID is #" . $conn->insert_id;
    } else {
        $error = "Failed to submit complaint. Please try again.";
    }
    
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Complaint - Complaint Management System</title>
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
                <span>University Complaint System</span>
            </div>
            <nav>
                <ul>
                    <li><a href="student_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="submit_complaint.php"><i class="fas fa-plus-circle"></i> New Complaint</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <div class="card form-container">
            <h2><i class="fas fa-plus-circle"></i> Submit New Complaint</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title"><i class="fas fa-heading"></i> Complaint Title</label>
                    <input type="text" id="title" name="title" class="form-control" required 
                           placeholder="Brief title of your complaint">
                </div>
                
                <div class="form-group">
<<<<<<< HEAD
                    <label for="faculty"><i class="fas fa-university"></i> Faculty</label>
                    <select id="faculty" name="faculty" class="form-control" required>
                        <option value="">Select Faculty</option>
                        <option value="Faculty of Technological Studies">Faculty of Technological Studies</option>
                        <option value="Faculty of Business Studies">Faculty of Business Studies</option>
                        <option value="Faculty of Applied Sciences">Faculty of Applied Sciences</option>
                    </select>
                </div>
                
                <div class="form-group">
=======
>>>>>>> 7c4024f80d6443629376d0d22a61c3d00caf73d9
                    <label for="category"><i class="fas fa-tags"></i> Category</label>
                    <select id="category" name="category" class="form-control" required>
                        <option value="">Select Category</option>
                        <option value="Academic">Academic</option>
                        <option value="Administrative">Administrative</option>
                        <option value="Facilities">Facilities</option>
                        <option value="Hostel">Hostel</option>
                        <option value="Library">Library</option>
                        <option value="Cafeteria">Cafeteria</option>
                        <option value="Transport">Transport</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="priority"><i class="fas fa-exclamation-circle"></i> Priority</label>
                    <select id="priority" name="priority" class="form-control" required>
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="description"><i class="fas fa-align-left"></i> Description</label>
                    <textarea id="description" name="description" class="form-control" required 
                              placeholder="Provide detailed description of your complaint..."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="attachment"><i class="fas fa-paperclip"></i> Attachment (Optional)</label>
                    <input type="file" id="attachment" name="attachment" class="form-control">
                    <small>Max file size: 5MB. Allowed: images, PDF, Word documents</small>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-success" style="width: 100%;">
                        <i class="fas fa-paper-plane"></i> Submit Complaint
                    </button>
                </div>
            </form>
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="student_dashboard.php" class="btn">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
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