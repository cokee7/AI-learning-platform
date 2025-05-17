<?php
// BC210143 Dolly
session_start();
$pageTitle = "Register";
include 'header.php';

$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "isom3012";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $email = $_POST['email'];

    if (strlen($password) < 8) {
        $registrationError = "Password must be at least 8 characters!";
    } else if ($password !== $confirmPassword) {
        $registrationError = "Passwords do not match!";
    } else {
        // Connect to database (same connection method as in import_resources.php)
        $conn = new mysqli($servername, $db_username, $db_password, $dbname);
        if ($conn->connect_error) {
            die("Database connection failed: " . $conn->connect_error);
        }

        // Check if username already exists
        $sqlCheck = "SELECT username FROM users WHERE username = ?";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bind_param("s", $username);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();

        if ($resultCheck->num_rows > 0) {
            $registrationError = "Username already exists!";
        } else {
            // Hash the password and insert into database
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $sqlInsert = "INSERT INTO users (username, password_hash, email) VALUES (?, ?, ?)";
            $stmtInsert = $conn->prepare($sqlInsert);
            $stmtInsert->bind_param("sss", $username, $passwordHash, $email);

            if ($stmtInsert->execute()) {
                // Get the user_id of the newly created user
                $user_id = $conn->insert_id;
                
                // Insert into user_profiles table
                $sqlProfile = "INSERT INTO user_profiles (user_id, skill_level, interested_topics) VALUES (?, ?, '')";
                $stmtProfile = $conn->prepare($sqlProfile);
                $stmtProfile->bind_param("is", $user_id, $_POST['skill_level']);
                
                if ($stmtProfile->execute()) {
                    header("Location: login.php?registered=true"); // Redirect to login page after successful registration
                    exit();
                } else {
                    $registrationError = "Profile creation failed, please try again later! " . $stmtProfile->error;
                }
                $stmtProfile->close();
            } else {
                $registrationError = "Registration failed, please try again later! " . $stmtInsert->error;
            }
            $stmtInsert->close();
        }
        $stmtCheck->close();
        $conn->close();
    }
}
?>

<div class="register-container">
    <div class="register-wrapper">
        <div class="register-left">
            <div class="register-info">
                <h1>Join Our AI Learning Community</h1>
                <p>Create an account to access personalized learning resources, track your progress, and connect with other AI enthusiasts</p>
                <div class="benefits">
                    <div class="benefit-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Access to premium AI resources</span>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Personalized learning recommendations</span>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Track your learning progress</span>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Join our growing community</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="register-right">
            <div class="register-form-container">
                <div class="form-header">
                    <h2><i class="fas fa-user-plus"></i> Create Account</h2>
                    <p>Fill in the form below to start your AI learning journey</p>
                </div>
                
                <?php if(isset($registrationError)): ?>
                    <div class="alert alert-danger animate__animated animate__headShake">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $registrationError; ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="" class="register-form">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" name="username" id="username" class="form-control" required 
                                placeholder="Choose a username"
                                value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" name="email" id="email" class="form-control" required 
                                placeholder="Enter your email address"
                                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" name="password" id="password" class="form-control" required 
                                placeholder="At least 8 characters">
                        </div>
                        <div class="password-strength mt-1" id="password-strength"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required 
                                placeholder="Re-enter your password">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="skill_level">Experience Level</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-graduation-cap"></i></span>
                            <select name="skill_level" id="skill_level" class="form-select">
                                <option value="Beginner">Beginner - New to AI</option>
                                <option value="Intermediate">Intermediate - Some AI experience</option>
                                <option value="Advanced">Advanced - Proficient in AI</option>
                                <option value="Various">Various - Varied experience levels</option>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" name="register" class="btn btn-primary btn-block register-btn">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>
                    
                    <div class="form-footer">
                        <p>Already have an account? <a href="login.php" class="login-link">Sign in</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.register-container {
    min-height: calc(100vh - 160px);
    padding: 0;
    margin: 0;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.register-wrapper {
    display: flex;
    max-width: 1200px;
    margin: 0 auto;
    background-color: #fff;
    border-radius: 12px;
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    min-height: 600px;
}

.register-left {
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

.register-left::before {
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

.register-info {
    position: relative;
    z-index: 1;
    text-align: left;
}

.register-info h1 {
    font-size: 2.5rem;
    margin-bottom: 20px;
    color: white;
    font-weight: 700;
}

.register-info p {
    font-size: 1.1rem;
    margin-bottom: 30px;
    opacity: 0.9;
    line-height: 1.6;
}

.benefits {
    margin-top: 40px;
}

.benefit-item {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
    animation: fadeInUp 0.5s ease-out forwards;
    opacity: 0;
}

.benefit-item:nth-child(1) { animation-delay: 0.1s; }
.benefit-item:nth-child(2) { animation-delay: 0.2s; }
.benefit-item:nth-child(3) { animation-delay: 0.3s; }
.benefit-item:nth-child(4) { animation-delay: 0.4s; }

.benefit-item i {
    font-size: 1.5rem;
    margin-right: 15px;
    color: rgba(255, 255, 255, 0.9);
}

.benefit-item span {
    font-size: 1.1rem;
    font-weight: 500;
}

.register-right {
    flex: 1.2;
    padding: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow-y: auto;
}

.register-form-container {
    width: 100%;
    max-width: 500px;
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

.register-form .form-group {
    margin-bottom: 20px;
}

.register-form .form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.register-form .input-group {
    position: relative;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
    border-radius: 8px;
    transition: all 0.3s;
}

.register-form .input-group:focus-within {
    box-shadow: 0 3px 15px rgba(26, 35, 126, 0.1);
}

.register-form .input-group-text {
    background: #f8f9fa;
    border-right: none;
    color: #6c757d;
}

.register-form .form-control,
.register-form .form-select {
    border-left: none;
    padding: 12px 15px;
    height: auto;
}

.register-form .form-control:focus,
.register-form .form-select:focus {
    box-shadow: none;
}

.register-btn {
    padding: 12px;
    font-size: 1.1rem;
    font-weight: 600;
    transition: all 0.3s;
    background: linear-gradient(135deg, #1a237e 0%, #4a6fdc 100%);
    border: none;
    margin-top: 10px;
    width: 100%;
}

.register-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(26, 35, 126, 0.2);
}

.form-footer {
    text-align: center;
    margin-top: 30px;
    color: #666;
}

.login-link {
    color: #1a237e;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s;
}

.login-link:hover {
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

.password-strength {
    height: 5px;
    margin-top: 5px;
    border-radius: 5px;
    transition: all 0.3s;
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
    .register-wrapper {
        flex-direction: column;
    }
    
    .register-left, .register-right {
        padding: 40px 20px;
    }
}

@media (max-width: 768px) {
    .register-left {
        display: none;
    }
}
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

<script>
// password strength checker
$(document).ready(function() {
    $('#password').on('input', function() {
        var password = $(this).val();
        var strength = 0;
        var strengthBar = $('#password-strength');
        
        if (password.length >= 8) strength += 1;
        if (password.match(/[A-Z]/)) strength += 1;
        if (password.match(/[0-9]/)) strength += 1;
        if (password.match(/[^A-Za-z0-9]/)) strength += 1;
        
        switch(strength) {
            case 0:
                strengthBar.css('width', '0%').css('background-color', '');
                break;
            case 1:
                strengthBar.css('width', '25%').css('background-color', '#dc3545');
                break;
            case 2:
                strengthBar.css('width', '50%').css('background-color', '#ffc107');
                break;
            case 3:
                strengthBar.css('width', '75%').css('background-color', '#0dcaf0');
                break;
            case 4:
                strengthBar.css('width', '100%').css('background-color', '#198754');
                break;
        }
    });
    
    // check password match
    $('#confirm_password').on('input', function() {
        var password = $('#password').val();
        var confirmPassword = $(this).val();
        
        if (password === confirmPassword) {
            $(this).css('border-color', '#198754');
        } else {
            $(this).css('border-color', '#dc3545');
        }
    });
});
</script>

<?php include 'footer.php'; ?>