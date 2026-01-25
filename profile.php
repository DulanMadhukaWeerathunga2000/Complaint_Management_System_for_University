<?php
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Handle profile update
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $student_id = $_POST['student_id'];
    $faculty = isset($_POST['faculty']) ? $_POST['faculty'] : '';
    $year = isset($_POST['year']) ? $_POST['year'] : '';
    $req_num = isset($_POST['req_num']) ? $_POST['req_num'] : '';
    
    // Handle password update if provided
    $password_update = '';
    if (!empty($_POST['new_password'])) {
        if ($_POST['new_password'] === $_POST['confirm_password']) {
            $hashed_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $password_update = ", password = ?";
        } else {
            $error = "New passwords do not match!";
        }
    }
    
    // Update profile
    if (empty($error)) {
        try {
            if (!empty($password_update)) {
                // Update with password
                $sql = "UPDATE users SET full_name = ?, email = ?, student_id = ?, faculty = ?, year = ?, req_num = ? $password_update WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssssi", $full_name, $email, $student_id, $faculty, $year, $req_num, $hashed_password, $user_id);
            } else {
                // Update without password
                $sql = "UPDATE users SET full_name = ?, email = ?, student_id = ?, faculty = ?, year = ?, req_num = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssssi", $full_name, $email, $student_id, $faculty, $year, $req_num, $user_id);
            }
            
            if ($stmt->execute()) {
                $_SESSION['full_name'] = $full_name;
                $success = "Profile updated successfully!";
            } else {
                $error = "Failed to update profile: " . $stmt->error;
            }
            
            if (isset($stmt)) {
                $stmt->close();
            }
        } catch (Exception $e) {
            $error = "Database error: " . $e->getMessage() . ". Please check if database columns exist.";
        }
    }
}

// Get current user data
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Complaint Management System</title>
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
                    <li><a href="#"><i class="fas fa-user"></i> <?php echo $_SESSION['full_name']; ?></a></li>
                </ul>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <div class="card form-container">
            <h2><i class="fas fa-user-edit"></i> Edit Profile</h2>
            
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
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username"><i class="fas fa-user-circle"></i> Username</label>
                    <input type="text" id="username" class="form-control" disabled 
                           value="<?php echo htmlspecialchars($user['username']); ?>">
                    <small>Username cannot be changed</small>
                </div>
                
                <div class="form-group">
                    <label for="full_name"><i class="fas fa-user"></i> Full Name</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" required 
                           value="<?php echo isset($user['full_name']) ? htmlspecialchars($user['full_name']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="student_id"><i class="fas fa-id-card"></i> Student ID</label>
                    <input type="text" id="student_id" name="student_id" class="form-control" 
                           value="<?php echo isset($user['student_id']) ? htmlspecialchars($user['student_id']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" id="email" name="email" class="form-control" required 
                           value="<?php echo isset($user['email']) ? htmlspecialchars($user['email']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="faculty"><i class="fas fa-university"></i> Faculty</label>
                    <select id="faculty" name="faculty" class="form-control">
                        <option value="">Select Faculty</option>
                        <option value="Faculty of Technological Studies" <?php echo (isset($user['faculty']) && $user['faculty'] == 'Faculty of Technological Studies') ? 'selected' : ''; ?>>Faculty of Technological Studies</option>
                        <option value="Faculty of Business Studies" <?php echo (isset($user['faculty']) && $user['faculty'] == 'Faculty of Business Studies') ? 'selected' : ''; ?>>Faculty of Business Studies</option>
                        <option value="Faculty of Applied Sciences" <?php echo (isset($user['faculty']) && $user['faculty'] == 'Faculty of Applied Sciences') ? 'selected' : ''; ?>>Faculty of Applied Sciences</option>
                        <option value="Faculty of Humanities" <?php echo (isset($user['faculty']) && $user['faculty'] == 'Faculty of Humanities') ? 'selected' : ''; ?>>Faculty of Humanities</option>
                        <option value="Faculty of Medicine" <?php echo (isset($user['faculty']) && $user['faculty'] == 'Faculty of Medicine') ? 'selected' : ''; ?>>Faculty of Medicine</option>
                        <option value="Faculty of Engineering" <?php echo (isset($user['faculty']) && $user['faculty'] == 'Faculty of Engineering') ? 'selected' : ''; ?>>Faculty of Engineering</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="year"><i class="fas fa-calendar-alt"></i> Academic Year</label>
                    <select id="year" name="year" class="form-control">
                        <option value="">Select Year</option>
                        <option value="1st Year" <?php echo (isset($user['year']) && $user['year'] == '1st Year') ? 'selected' : ''; ?>>1st Year</option>
                        <option value="2nd Year" <?php echo (isset($user['year']) && $user['year'] == '2nd Year') ? 'selected' : ''; ?>>2nd Year</option>
                        <option value="3rd Year" <?php echo (isset($user['year']) && $user['year'] == '3rd Year') ? 'selected' : ''; ?>>3rd Year</option>
                        <option value="4th Year" <?php echo (isset($user['year']) && $user['year'] == '4th Year') ? 'selected' : ''; ?>>4th Year</option>
                        <option value="5th Year" <?php echo (isset($user['year']) && $user['year'] == '5th Year') ? 'selected' : ''; ?>>5th Year</option>
                        <option value="Graduate" <?php echo (isset($user['year']) && $user['year'] == 'Graduate') ? 'selected' : ''; ?>>Graduate</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="req_num"><i class="fas fa-hashtag"></i> Registration Number</label>
                    <input type="text" id="req_num" name="req_num" class="form-control" 
                           value="<?php echo isset($user['req_num']) ? htmlspecialchars($user['req_num']) : ''; ?>">
                </div>
                
                <hr style="margin: 20px 0;">
                
                <h3><i class="fas fa-key"></i> Change Password (Optional)</h3>
                
                <div class="form-group">
                    <label for="new_password"><i class="fas fa-lock"></i> New Password</label>
                    <input type="password" id="new_password" name="new_password" class="form-control">
                    <small>Leave blank to keep current password</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password"><i class="fas fa-lock"></i> Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-success" style="width: 100%;">
                        <i class="fas fa-save"></i> Save Changes
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