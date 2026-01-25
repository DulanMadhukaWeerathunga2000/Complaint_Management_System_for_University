<?php
require_once 'config/database.php';
$conn = getDBConnection();

$error = '';
$success = '';

// Handle registration
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = $_POST['email'];
    $full_name = $_POST['full_name'];
    $student_id = $_POST['student_id'];
    $faculty = $_POST['faculty'];
    $year = $_POST['year'];
    $req_num = $_POST['req_num'];
    
    // Check if username already exists
    $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $username, $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $error = "Username or email already exists!";
    } else {
        // Insert new user
        $insert_sql = "INSERT INTO users (username, password, email, full_name, student_id, faculty, year, req_num, user_type) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'student')";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ssssssss", $username, $password, $email, $full_name, $student_id, $faculty, $year, $req_num);
        
        if ($insert_stmt->execute()) {
            $success = "Registration successful! You can now login.";
        } else {
            $error = "Registration failed. Please try again.";
        }
        
        $insert_stmt->close();
    }
    
    $check_stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complaint Management System - Register</title>
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
                    <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="index.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                </ul>
            </nav>
        </div>
    </header>
    
    <div class="container auth-container">
        <div class="auth-box">
            <h2 class="auth-title"><i class="fas fa-user-plus"></i> Student Registration</h2>
            
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
                    <label for="full_name"><i class="fas fa-user"></i> Full Name</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="student_id"><i class="fas fa-id-card"></i> Student ID</label>
                    <input type="text" id="student_id" name="student_id" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="faculty"><i class="fas fa-university"></i> Faculty</label>
                    <select id="faculty" name="faculty" class="form-control" required>
                        <option value="">Select Faculty</option>
                        <option value="Faculty of Technological Studies">Faculty of Technological Studies</option>
                        <option value="Faculty of Business Studies">Faculty of Business Studies</option>
                        <option value="Faculty of Applied Sciences">Faculty of Applied Sciences</option>
                        <option value="Faculty of Humanities">Faculty of Humanities</option>
                        <option value="Faculty of Medicine">Faculty of Medicine</option>
                        <option value="Faculty of Engineering">Faculty of Engineering</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="year"><i class="fas fa-calendar-alt"></i> Academic Year</label>
                    <select id="year" name="year" class="form-control" required>
                        <option value="">Select Year</option>
                        <option value="1st Year">1st Year</option>
                        <option value="2nd Year">2nd Year</option>
                        <option value="3rd Year">3rd Year</option>
                        <option value="4th Year">4th Year</option>
                        <option value="5th Year">5th Year</option>
                        <option value="Graduate">Graduate</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="req_num"><i class="fas fa-hashtag"></i> Registration Number</label>
                    <input type="text" id="req_num" name="req_num" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="username"><i class="fas fa-user-circle"></i> Username</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                    <input type="password" id="password" name="password" class="form-control" required minlength="6">
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-success" style="width: 100%;">
                        <i class="fas fa-user-plus"></i> Register
                    </button>
                </div>
            </form>
            
            <div class="auth-links">
                <p>Already have an account? <a href="index.php">Login here</a></p>
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