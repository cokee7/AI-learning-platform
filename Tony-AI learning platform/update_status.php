<?php
//BC210759 Lillian
session_start();
require_once 'database.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    // If user is not logged in, redirect to login page or return JSON error (for AJAX requests)
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        // AJAX request returns JSON error
        echo json_encode(['success' => false, 'message' => 'Please login to continue']);
        exit;
    } else {
        // Regular request redirects
        $_SESSION['message'] = "Please login to continue";
        $_SESSION['message_type'] = "warning";
        header("Location: login.php");
        exit;
    }
}

$user_id = $_SESSION['user_id'];

// Handle favorite/unfavorite actions
if (isset($_POST['resource_id']) && isset($_POST['action']) && $_POST['action'] == 'toggle_favorite') {
    $resource_id = $_POST['resource_id'];
    
    // Check if resource is already favorited
    $stmt = $pdo->prepare("SELECT * FROM user_resources WHERE user_id = ? AND resource_id = ?");
    $stmt->execute([$user_id, $resource_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        // Resource is already favorited, unfavorite it
        if ($result['is_favorite'] == 1) {
            // Update to unfavorite
            $updateStmt = $pdo->prepare("UPDATE user_resources SET is_favorite = 0 WHERE user_id = ? AND resource_id = ?");
            $updateStmt->execute([$user_id, $resource_id]);
            echo json_encode(['status' => 'unfavorited']);
        } else {
            // Update to favorite
            $updateStmt = $pdo->prepare("UPDATE user_resources SET is_favorite = 1 WHERE user_id = ? AND resource_id = ?");
            $updateStmt->execute([$user_id, $resource_id]);
            echo json_encode(['status' => 'favorited']);
        }
    } else {
        // Resource is not favorited, add favorite
        $stmt = $pdo->prepare("INSERT INTO user_resources (user_id, resource_id, is_favorite) VALUES (?, ?, 1)");
        $stmt->execute([$user_id, $resource_id]);
        echo json_encode(['status' => 'favorited']);
    }
    exit;
}

// Handle learning status changes (used by resources.php and dashboard.php)
if (isset($_POST['resource_id']) && isset($_POST['status'])) {
    $resource_id = $_POST['resource_id'];
    $status = $_POST['status'];
    $from_page = isset($_POST['from_page']) ? $_POST['from_page'] : 'dashboard';
    
    // Handle delete operation
    if ($status === 'delete') {
        $deleteStmt = $pdo->prepare("DELETE FROM learning_status WHERE user_id = ? AND resource_id = ?");
        $deleteStmt->execute([$user_id, $resource_id]);
        
        // Query resource information (for response)
        $resourceStmt = $pdo->prepare("SELECT Title as title, URL as url, Format as type, Topic as topic, Difficulty as difficulty, resource_id FROM resources WHERE resource_id = ?");
        $resourceStmt->execute([$resource_id]);
        $resourceInfo = $resourceStmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if it's an AJAX request
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            // Return JSON response
            echo json_encode([
                'success' => true,
                'message' => 'Resource deleted successfully',
                'status' => 'delete',
                'resource' => $resourceInfo
            ]);
            exit;
        } else {
            // Regular request redirect
            $_SESSION['message'] = "Resource deleted successfully";
            $_SESSION['message_type'] = "success";
            
            // Redirect back to original page
            if ($from_page == 'resources') {
                header("Location: resources.php");
            } elseif ($from_page == 'recommendations') {
                header("Location: recommendations.php");
            } else {
                header("Location: dashboard.php");
            }
            exit;
        }
    }
    
    // First check if there is already a status record for the resource
    $checkStmt = $pdo->prepare("SELECT * FROM learning_status WHERE user_id = ? AND resource_id = ?");
    $checkStmt->execute([$user_id, $resource_id]);
    $existingStatus = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingStatus) {
        // Update existing record
        $updateStmt = $pdo->prepare("UPDATE learning_status SET status = ?, updated_at = NOW() WHERE user_id = ? AND resource_id = ?");
        $updateStmt->execute([$status, $user_id, $resource_id]);
    } else {
        // Create new record
        $insertStmt = $pdo->prepare("INSERT INTO learning_status (user_id, resource_id, status, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
        $insertStmt->execute([$user_id, $resource_id, $status]);
    }
    
    // Query resource information
    $resourceStmt = $pdo->prepare("SELECT Title as title, URL as url, Format as type, Topic as topic, Difficulty as difficulty, resource_id FROM resources WHERE resource_id = ?");
    $resourceStmt->execute([$resource_id]);
    $resourceInfo = $resourceStmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if it's an AJAX request
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        // Return JSON response
        $headerClass = '';
        $statusDisplay = '';
        
        // Get status display information
        switch ($status) {
            case 'added':
                $headerClass = "bg-primary";
                $statusDisplay = "Favorite";
                break;
            case 'in_progress':
                $headerClass = "bg-warning";
                $statusDisplay = "In Progress";
                break;
            case 'completed':
                $headerClass = "bg-success";
                $statusDisplay = "Completed";
                break;
            case 'not_started':
                $headerClass = "";
                $statusDisplay = "";
                break;
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Resource status updated',
            'status' => $status,
            'headerClass' => $headerClass,
            'statusDisplay' => $statusDisplay,
            'resource' => $resourceInfo
        ]);
        exit;
    } else {
        // Regular request redirect
        $_SESSION['message'] = "Resource status updated successfully";
        $_SESSION['message_type'] = "success";
        $_SESSION['updated_resource_id'] = $resource_id; // Record updated resource ID
        
        // Redirect back to original page
        if ($from_page == 'resources') {
            header("Location: resources.php");
        } elseif ($from_page == 'recommendations') {
            header("Location: recommendations.php");
        } else {
            header("Location: dashboard.php");
        }
        exit;
    }
}

// If no matching operation, return error
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    echo json_encode(['success' => false, 'message' => 'Invalid operation']);
} else {
    $_SESSION['message'] = "Invalid operation";
    $_SESSION['message_type'] = "danger";
    header("Location: dashboard.php");
}
exit;
?>