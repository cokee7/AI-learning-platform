<?php
// BC210143 Dolly
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TonyAI Learning platform</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
        }
        .navbar-brand {
            font-weight: bold;
        }
        .navbar-nav .nav-link {
            margin-right: 15px;
        }
        .btn-outline-primary {
            border-color: #0d6efd;
            color: #0d6efd;
        }
        .btn-outline-primary:hover {
            background-color: #0d6efd;
            color: white;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="index.php">Tony-AI Learning Platform</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Homepage</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="resources.php">Learning Resources</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Study Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="recommendations.php">Recommendations</a>
                    </li>

                </ul>
                <div class="d-flex">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="profile.php" class="btn btn-outline-primary me-2">Personal Preference</a>
                        <a href="logout.php" class="btn btn-outline-danger">Log out</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline-primary me-2">Log in</a>
                        <a href="register.php" class="btn btn-outline-primary">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['message']; 
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>