<?php
// BC210143 Dolly
session_start();
$pageTitle = "Login";
include 'header.php';

$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "isom3012";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Connect to the database
    $conn = new mysqli($servername, $db_username, $db_password, $dbname);
    if ($conn->connect_error) {
        die("Connection Fail: " . $conn->connect_error);
    }

    $sql = "SELECT user_id, username, password_hash FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        // Verify password
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            // Remove unnecessary password hash storage
            // Set successful login message
            $_SESSION['message'] = "Login successful. Welcome back!";
            $_SESSION['message_type'] = "success";
            
            header("Location: index.php");
            exit();
        } else {
            $loginError = "Invalid password. Please try again.";
        }
    } else {
        $loginError = "Username does not exist. Please try again.";
    }

    $stmt->close();
    $conn->close();
}
?>

<div class="login-container">
    <div class="login-wrapper">
        <div class="login-left">
            <div class="login-info">
                <h1>Welcome to AI Learning Platform</h1>
                <p>Explore personalized AI learning resources, track your progress, and enhance your skills</p>
                <div class="features">
                    <div class="feature-item">
                        <i class="fas fa-book-reader"></i>
                        <span>Curated Learning Paths</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-chart-line"></i>
                        <span>Progress Tracking</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-lightbulb"></i>
                        <span>Personalized Recommendations</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="login-right">
            <div class="login-form-container">
                <div class="form-header">
                    <h2><i class="fas fa-sign-in-alt"></i> Login</h2>
                    <p>Access your personalized learning dashboard</p>
                </div>
                <?php if (isset($loginError)): ?>
                    <div class="alert alert-danger animate__animated animate__headShake"> 
                        <i class="fas fa-exclamation-circle"></i> <?php echo $loginError; ?> 
                    </div>
                <?php endif; ?>
                <?php if (isset($_GET['registered']) && $_GET['registered'] == 'true'): ?>
                    <div class="alert alert-success animate__animated animate__fadeIn"> 
                        <i class="fas fa-check-circle"></i> Registration successful! Please login with your credentials.
                    </div>
                <?php endif; ?>
                <form method="post" class="login-form">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" id="username" name="username" class="form-control" required placeholder="Enter your username">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" id="password" name="password" class="form-control" required placeholder="Enter your password">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block login-btn">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                    <div class="form-footer">
                        <p>Don't have an account? <a href="register.php" class="register-link">Sign up</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.login-container {
    min-height: calc(100vh - 160px);
    padding: 0;
    margin: 0;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.login-wrapper {
    display: flex;
    max-width: 1200px;
    margin: 0 auto;
    background-color: #fff;
    border-radius: 12px;
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    min-height: 600px;
}

.login-left {
    flex: 1;
    background: linear-gradient(135deg, #1a237e 0%, #4a6fdc 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px;
    color: white;
    position: relative;
    overflow: hidden;
}

.login-left::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(rgba(255, 255, 255, 0.1) 8%, transparent 8%);
    background-position: 0 0;
    background-size: 30px 30px;
    opacity: 0.3;
    transform: rotate(30deg);
    z-index: 0;
}

.login-info {
    position: relative;
    z-index: 1;
    text-align: left;
}

.login-info h1 {
    font-size: 2.5rem;
    margin-bottom: 20px;
    color: white;
    font-weight: 700;
}

.login-info p {
    font-size: 1.1rem;
    margin-bottom: 30px;
    opacity: 0.9;
    line-height: 1.6;
}

.features {
    margin-top: 40px;
}

.feature-item {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
    animation: fadeInUp 0.5s ease-out;
}

.feature-item i {
    font-size: 1.5rem;
    margin-right: 15px;
    color: rgba(255, 255, 255, 0.9);
}

.feature-item span {
    font-size: 1.1rem;
    font-weight: 500;
}

.login-right {
    flex: 1;
    padding: 60px 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.login-form-container {
    width: 100%;
    max-width: 400px;
}

.form-header {
    text-align: center;
    margin-bottom: 30px;
}

.form-header h2 {
    font-size: 2rem;
    color: #1a237e;
    margin-bottom: 10px;
}

.form-header p {
    color: #666;
}

.login-form .form-group {
    margin-bottom: 25px;
}

.login-form .form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.login-form .input-group {
    position: relative;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
    border-radius: 8px;
    transition: all 0.3s;
}

.login-form .input-group:focus-within {
    box-shadow: 0 3px 15px rgba(26, 35, 126, 0.1);
}

.login-form .input-group-text {
    background: #f8f9fa;
    border-right: none;
    color: #6c757d;
}

.login-form .form-control {
    border-left: none;
    padding: 12px 15px;
    height: auto;
}

.login-form .form-control:focus {
    box-shadow: none;
}

.login-btn {
    padding: 12px;
    font-size: 1.1rem;
    font-weight: 600;
    transition: all 0.3s;
    background: linear-gradient(135deg, #1a237e 0%, #4a6fdc 100%);
    border: none;
    margin-top: 10px;
}

.login-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(26, 35, 126, 0.2);
}

.form-footer {
    text-align: center;
    margin-top: 30px;
    color: #666;
}

.register-link {
    color: #1a237e;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s;
}

.register-link:hover {
    color: #4a6fdc;
    text-decoration: underline;
}

.alert {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 25px;
    font-weight: 500;
}

.alert-danger {
    background-color: #fff6f6;
    border-left: 4px solid #dc3545;
    color: #dc3545;
}

.alert-success {
    background-color: #f0fff4;
    border-left: 4px solid #28a745;
    color: #28a745;
}

@keyframes fadeInUp {
    from {
        transform: translateY(20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@media (max-width: 992px) {
    .login-wrapper {
        flex-direction: column;
    }
    
    .login-left {
        padding: 40px 20px;
    }
    
    .login-right {
        padding: 40px 20px;
    }
}

@media (max-width: 768px) {
    .login-left {
        display: none;
    }
}
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

<?php include 'footer.php'; ?>