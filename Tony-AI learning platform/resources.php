<?php
//BC210759 Lillian
session_start();
$pageTitle = "Learning Resources";
include 'header.php';
// Use database.php for database connection
require_once 'database.php';

// Get filter parameters
$topics = isset($_GET['topics']) ? $_GET['topics'] : [];
$difficulties = isset($_GET['difficulties']) ? $_GET['difficulties'] : [];
$formats = isset($_GET['formats']) ? $_GET['formats'] : [];
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Query all available topics, difficulties and formats
$topicsQuery = "SELECT DISTINCT Topic FROM resources ORDER BY Topic";
$topicsStmt = $pdo->query($topicsQuery);
$availableTopics = $topicsStmt->fetchAll(PDO::FETCH_COLUMN);

$difficultiesQuery = "SELECT 'Beginner' AS Difficulty UNION SELECT 'Intermediate' UNION SELECT 'Advanced' UNION SELECT 'Various' ORDER BY FIELD(Difficulty, 'Beginner', 'Intermediate', 'Advanced', 'Various')";
$difficultiesStmt = $pdo->query($difficultiesQuery);
$availableDifficulties = $difficultiesStmt->fetchAll(PDO::FETCH_COLUMN);

$formatsQuery = "SELECT DISTINCT SUBSTRING_INDEX(Format, '/', 1) AS Format FROM resources 
                UNION 
                SELECT DISTINCT SUBSTRING_INDEX(SUBSTRING_INDEX(Format, '/', 2), '/', -1) AS Format FROM resources 
                WHERE Format LIKE '%/%'
                UNION
                SELECT DISTINCT SUBSTRING_INDEX(Format, '/', -1) AS Format FROM resources 
                WHERE Format LIKE '%/%/%'
                ORDER BY Format";
$formatsStmt = $pdo->query($formatsQuery);
$availableFormats = $formatsStmt->fetchAll(PDO::FETCH_COLUMN);

// Build query conditions
$conditions = [];
$params = [];

if (!empty($topics)) {
    $topicPlaceholders = [];
    foreach ($topics as $topic) {
        $topicPlaceholders[] = "Topic LIKE ?";
        $params[] = "%$topic%";
    }
    $conditions[] = "(" . implode(" OR ", $topicPlaceholders) . ")";
}

if (!empty($difficulties)) {
    $difficultyPlaceholders = [];
    
    // Modify query logic: for each selected difficulty level
    // 1. Show exact matching resources
    // 2. Show mixed difficulty resources that include this level
    // 3. Always include 'Various' difficulty resources
    
    $includeVarious = false; // Track if Various condition has been added
    
    foreach ($difficulties as $difficulty) {
        if ($difficulty == 'Various') {
            $includeVarious = true;
        }
        $difficultyPlaceholders[] = "Difficulty LIKE ?";
        $params[] = "%$difficulty%";
    }
    
    // If user hasn't explicitly selected Various, add it automatically
    if (!$includeVarious) {
        $difficultyPlaceholders[] = "Difficulty = 'Various'";
    }
    
    $conditions[] = "(" . implode(" OR ", $difficultyPlaceholders) . ")";
}

if (!empty($formats)) {
    $formatPlaceholders = [];
    foreach ($formats as $format) {
        $formatPlaceholders[] = "Format LIKE ?";
        $params[] = "%$format%";
    }
    $conditions[] = "(" . implode(" OR ", $formatPlaceholders) . ")";
}

if (!empty($search)) {
    // Modify search condition to search by title
    $conditions[] = "(Title LIKE ?)";
    $params[] = "%$search%";
}

// Build query statement
$query = "SELECT * FROM resources";
if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}
$query .= " ORDER BY resource_id DESC";

$stmt = $pdo->prepare($query);
if (!empty($params)) {
    $stmt->execute($params);
} else {
    $stmt->execute();
}
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user's learning status
$userLearningStatus = [];
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $statusQuery = "SELECT resource_id, status FROM learning_status WHERE user_id = ?";
    $statusStmt = $pdo->prepare($statusQuery);
    $statusStmt->execute([$userId]);
    $statusResult = $statusStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($statusResult as $row) {
        $userLearningStatus[$row['resource_id']] = $row['status'];
    }
}

// Get status display name
function getStatusDisplayName($status) {
    switch ($status) {
        case 'added': return "Favorite";
        case 'in_progress': return "In Progress";
        case 'completed': return "Completed";
        default: return $status;
    }
}

function getStatusClass($status) {
    switch ($status) {
        case 'added': return "bg-primary";
        case 'in_progress': return "bg-warning";
        case 'completed': return "bg-success";
        default: return "";
    }
}

// Show message
if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-' . ($_SESSION['message_type'] ?? 'info') . ' status-message">' . $_SESSION['message'] . '</div>';
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// Get recently updated resource ID
$highlightResourceId = isset($_SESSION['updated_resource_id']) ? $_SESSION['updated_resource_id'] : null;
if (isset($_SESSION['updated_resource_id'])) {
    unset($_SESSION['updated_resource_id']);
}
?>

<div class="container">
    <div class="page-header">
        <h2><i class="fas fa-book"></i> Learning Resources</h2>
        <p>Browse all available AI learning resources and filter content according to your needs</p>
    </div>

    <!-- Filter form -->
    <div class="filter-panel">
        <form method="get" class="filter-form">
            <div class="filter-row">
                <div class="filter-group">
                    <label><i class="fas fa-tag"></i> Topic:</label>
                    <div class="multi-select">
                        <?php 
                        // Display common topics in specified order
                        $commonTopics = [
                            'Artificial Intelligence',
                            'Machine Learning',
                            'Deep Learning',
                            'Natural Language Processing',
                            'Computer Vision',
                            'Reinforcement Learning'
                        ];
                        foreach ($commonTopics as $topic): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="topics[]" value="<?php echo $topic; ?>" id="topic_<?php echo $topic; ?>" <?php echo in_array($topic, $topics) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="topic_<?php echo $topic; ?>">
                                    <?php echo $topic; ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                        
                        <button type="button" class="btn btn-sm btn-link" id="toggleMoreTopics">
                            Show More Topics <i class="fas fa-chevron-down"></i>
                        </button>
                        
                        <div id="moreTopics" style="display:none;">
                            <?php 
                            // Display other topics in specified order
                            $otherTopics = [
                                'Generative AI',
                                'Artificial General Intelligence',
                                'AI Ethics',
                                'AI Strategy',
                                'Data Visualization',
                                'Machine Learning Foundations',
                                'Machine Learning Operations (MLOps)',
                                'Mathematics',
                                'ML Implementation',
                                'Python (Programming Language)',
                                'Statistics',
                                'Structured Query Language (SQL)',
                                'Visualization'
                            ];
                            
                            foreach ($otherTopics as $topic): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="topics[]" value="<?php echo $topic; ?>" id="topic_<?php echo str_replace(['(', ')', ' '], ['', '', '_'], $topic); ?>" <?php echo in_array($topic, $topics) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="topic_<?php echo str_replace(['(', ')', ' '], ['', '', '_'], $topic); ?>">
                                        <?php echo $topic; ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <div class="filter-group">
                    <label><i class="fas fa-chart-line"></i> Difficulty:</label>
                    <div class="multi-select">
                        <?php foreach ($availableDifficulties as $difficulty): 
                            if (!empty($difficulty)): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="difficulties[]" value="<?php echo $difficulty; ?>" id="difficulty_<?php echo $difficulty; ?>" <?php echo in_array($difficulty, $difficulties) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="difficulty_<?php echo $difficulty; ?>">
                                        <?php echo $difficulty; ?>
                                    </label>
                                </div>
                            <?php endif;
                        endforeach; ?>
                    </div>
                </div>
                
                <div class="filter-group">
                    <label><i class="fas fa-file-alt"></i> Format:</label>
                    <div class="multi-select">
                        <?php foreach ($availableFormats as $format): 
                            if (!empty($format)): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="formats[]" value="<?php echo $format; ?>" id="format_<?php echo $format; ?>" <?php echo in_array($format, $formats) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="format_<?php echo $format; ?>">
                                        <?php echo $format; ?>
                                    </label>
                                </div>
                            <?php endif;
                        endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="filter-row mt-3">
                <div class="filter-group">
                    <label for="search"><i class="fas fa-search"></i> Search:</label>
                    <input type="text" id="search" name="search" class="filter-input" placeholder="Search resource title" value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="filter-buttons">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Apply Filters</button>
                    <a href="resources.php" class="btn btn-outline-secondary"><i class="fas fa-undo"></i> Reset</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Resource cards list -->
    <div class="resources-container">
        <?php if (count($result) > 0): ?>
            <div class="resource-count">
                <p>Found <?php echo count($result); ?> resources</p>
            </div>
            <div class="resource-grid">
                <?php foreach ($result as $row): ?>
                    <div class="resource-card" style="display: flex; flex-direction: column; min-height: 340px;">
                        <?php
                        // Get current resource learning status
                        $resourceId = $row['resource_id'];
                        $status = isset($userLearningStatus[$resourceId]) ? $userLearningStatus[$resourceId] : null;
                        // Based on status set card header style
                        if (!empty($status)) {
                            $headerClass = getStatusClass($status);
                            $statusDisplay = getStatusDisplayName($status);
                            echo '<div class="card-header ' . $headerClass . ' text-white">' . $statusDisplay . '</div>';
                        }
                        ?>
                        <div class="card-body" style="flex: 1 1 auto; display: flex; flex-direction: column;">
                            <h3 style="flex-shrink:0;"><a href="<?php echo $row['URL']; ?>" target="_blank"><?php echo $row['Title']; ?></a></h3>
                            <p class="resource-description" style="flex-shrink:0;">Source: <?php echo $row['Source']; ?></p>
                            <div class="resource-meta" style="flex-shrink:0;">
                                <span class="topic-badge">
                                    <?php echo $row['Topic']; ?>
                                </span>
                                <span class="difficulty-badge <?php 
                                    echo strpos($row['Difficulty'], 'Beginner') !== false ? 'difficulty-beginner' : 
                                        (strpos($row['Difficulty'], 'Intermediate') !== false ? 'difficulty-intermediate' : 
                                        (strpos($row['Difficulty'], 'Advanced') !== false ? 'difficulty-advanced' : 'difficulty-various')); 
                                ?>"><?php echo $row['Difficulty']; ?></span>
                                <span class="format-badge"><i class="fas fa-file-alt"></i> <?php echo $row['Format']; ?></span>
                            </div>
                            <div class="resource-actions" style="margin-top:auto; display:flex; gap:10px; flex-wrap:wrap; justify-content: space-between;">
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                        <!-- Favorite/Unfavorite button -->
                                        <?php if (empty($status) || $status === 'not_started'): ?>
                                            <form method="post" action="update_status.php">
                                                <input type="hidden" name="resource_id" value="<?php echo $resourceId; ?>">
                                                <input type="hidden" name="from_page" value="resources">
                                                <input type="hidden" name="status" value="added">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" style="width: 100px;">
                                                    <i class="fas fa-heart"></i> Favorite
                                                </button>
                                            </form>
                                        <?php elseif ($status === 'added'): ?>
                                            <form method="post" action="update_status.php">
                                                <input type="hidden" name="resource_id" value="<?php echo $resourceId; ?>">
                                                <input type="hidden" name="from_page" value="resources">
                                                <input type="hidden" name="status" value="not_started">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary" style="width: 100px;">
                                                    <i class="fas fa-heart-broken"></i> Unfav
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <!-- Start/Cancel button -->
                                        <?php if ($status === 'in_progress'): ?>
                                            <form method="post" action="update_status.php">
                                                <input type="hidden" name="resource_id" value="<?php echo $resourceId; ?>">
                                                <input type="hidden" name="from_page" value="resources">
                                                <input type="hidden" name="status" value="not_started">
                                                <button type="submit" class="btn btn-sm btn-warning" style="width: 100px;">
                                                    <i class="fas fa-pause"></i> Cancel
                                                </button>
                                            </form>
                                        <?php elseif ($status !== 'completed'): ?>
                                            <form method="post" action="update_status.php">
                                                <input type="hidden" name="resource_id" value="<?php echo $resourceId; ?>">
                                                <input type="hidden" name="from_page" value="resources">
                                                <input type="hidden" name="status" value="in_progress">
                                                <button type="submit" class="btn btn-sm btn-success" style="width: 100px;">
                                                    <i class="fas fa-play"></i> Start
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <!-- Complete button (if in progress) -->
                                        <?php if ($status === 'in_progress'): ?>
                                            <form method="post" action="update_status.php">
                                                <input type="hidden" name="resource_id" value="<?php echo $resourceId; ?>">
                                                <input type="hidden" name="from_page" value="resources">
                                                <input type="hidden" name="status" value="completed">
                                                <button type="submit" class="btn btn-sm btn-success" style="width: 100px;">
                                                    <i class="fas fa-check"></i> Complete
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Visit resource button -->
                                    <a href="<?php echo $row['URL']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-external-link-alt"></i> Visit
                                    </a>
                                <?php else: ?>
                                    <a href="login.php" class="btn btn-sm btn-outline-secondary">Login to start learning</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-search fa-3x"></i>
                <p>No resources found matching your criteria.</p>
                <a href="resources.php" class="btn btn-primary">View All Resources</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.filter-panel {
    background-color: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 30px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.filter-form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: flex-start;
}

.filter-group {
    flex: 1;
    min-width: 200px;
}

.filter-buttons {
    display: flex;
    gap: 10px;
    margin-top: 25px;
    align-self: flex-end;
}

.filter-select {
    width: 100%;
    padding: 10px;
    border-radius: 5px;
    border: 1px solid #ddd;
}

.filter-input {
    width: 100%;
    padding: 10px;
    border-radius: 5px;
    border: 1px solid #ddd;
}

.multi-select {
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 10px;
    background-color: white;
}

.resource-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 25px;
    margin-top: 20px;
}

.resource-count {
    margin-bottom: 20px;
    color: #666;
}

.resource-card {
    border: 1px solid #eee;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    transition: transform 0.2s, box-shadow 0.2s;
    background-color: white;
}

.resource-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.card-body {
    padding: 15px;
}

.resource-card h3 {
    font-size: 1.1rem;
    margin-bottom: 12px;
    line-height: 1.4;
}

.resource-card h3 a {
    color: #1a237e;
    text-decoration: none;
    font-weight: 600;
}

.resource-card h3 a:hover {
    color: #3949ab;
    text-decoration: underline;
}

.resource-description {
    color: #555;
    margin-bottom: 15px;
}

.resource-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 15px;
}

.topic-badge, .difficulty-badge, .format-badge {
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 0.85rem;
}

.topic-badge {
    background-color: #e3f2fd;
    color: #0d47a1;
}

.difficulty-beginner {
    background-color: #e8f5e9;
    color: #2e7d32;
}

.difficulty-intermediate {
    background-color: #fff8e1;
    color: #ff8f00;
}

.difficulty-advanced {
    background-color: #ffebee;
    color: #c62828;
}

.difficulty-various {
    background-color: #f3e5f5;
    color: #6a1b9a;
}

.format-badge {
    background-color: #f5f5f5;
    color: #616161;
}

.resource-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 15px;
    align-items: center;
    justify-content: space-between;
}

.resource-actions div {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 5px;
}

.resource-actions .badge {
    padding: 6px 12px;
    font-size: 0.85rem;
    margin-right: 10px;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 0.85rem;
    min-width: 85px;
    text-align: center;
    height: 32px;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
}

.bookmark-button-container {
    min-width: 90px;
    min-height: 32px;
    display: flex;
}

.bookmark-button-container button {
    width: 100%;
}

.empty-state {
    text-align: center;
    padding: 50px 0;
    color: #666;
}

.empty-state i {
    margin-bottom: 20px;
    color: #ddd;
}

.page-header {
    margin-bottom: 30px;
    border-bottom: 1px solid #eee;
    padding-bottom: 15px;
}

.page-header p {
    color: #666;
    margin-top: 5px;
}

.form-check {
    margin-bottom: 8px;
}

.form-check-label {
    margin-left: 5px;
}

.status-form button.dropdown-item {
    background: none;
    border: none;
    text-align: left;
    width: 100%;
    padding: 0.25rem 1.5rem;
    clear: both;
    font-weight: 400;
    color: #212529;
    white-space: nowrap;
}

.status-form button.dropdown-item:hover, 
.status-form button.dropdown-item:focus {
    color: #16181b;
    text-decoration: none;
    background-color: #f8f9fa;
}

.status-message {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1000;
    animation: fadeInOut 4s ease-in-out;
    border-radius: 5px;
    padding: 10px 20px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

@keyframes fadeInOut {
    0% { opacity: 0; transform: translateY(-20px); }
    10% { opacity: 1; transform: translateY(0); }
    70% { opacity: 1; transform: translateY(0); }
    100% { opacity: 0; transform: translateY(-20px); }
}

.highlight-card {
    animation: highlight 2s ease-in-out;
    border: 2px solid #4CAF50;
}

@keyframes highlight {
    0% { box-shadow: 0 0 0 rgba(76, 175, 80, 0.5); }
    50% { box-shadow: 0 0 20px rgba(76, 175, 80, 0.8); }
    100% { box-shadow: 0 0 0 rgba(76, 175, 80, 0.5); }
}
</style>

<script>
$(document).ready(function() {
    // Intercept resource card form submissions, change to AJAX requests
    $('.resource-card form').submit(function(e) {
        e.preventDefault(); // Prevent form regular submission
        
        var form = $(this);
        var resourceId = form.find('input[name="resource_id"]').val();
        var status = form.find('input[name="status"]').val();
        var fromPage = form.find('input[name="from_page"]').val();
        var card = form.closest('.resource-card');
        
        // Send AJAX request
        $.ajax({
            url: 'update_status.php',
            type: 'POST',
            data: {
                resource_id: resourceId,
                status: status,
                from_page: fromPage
            },
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    // Update card status display
                    updateCardStatus(card, response);
                    
                    // Show success message
                    showMessage('Status update successful', 'success');
                } else {
                    // Show error message
                    showMessage(response.message || 'Update failed', 'danger');
                }
            },
            error: function() {
                showMessage('Request failed, please try again', 'danger');
            }
        });
    });

    // Update card status display
    function updateCardStatus(card, response) {
        // Remove existing status header
        card.find('.card-header').remove();
        
        // If there's new status, add status header
        if (response.status !== 'not_started' && response.headerClass) {
            card.prepend('<div class="card-header ' + response.headerClass + ' text-white">' + response.statusDisplay + '</div>');
        }
        
        // Get button container
        var actionDiv = card.find('.resource-actions div').first();
        actionDiv.empty(); // Clear existing buttons
        
        // Based on status add appropriate buttons
        if (response.status === 'added') {
            // Show unfavorite button
            actionDiv.append(`
                <form method="post" action="update_status.php">
                    <input type="hidden" name="resource_id" value="${response.resource.resource_id}">
                    <input type="hidden" name="from_page" value="resources">
                    <input type="hidden" name="status" value="not_started">
                    <button type="submit" class="btn btn-sm btn-outline-secondary" style="width: 100px;">
                        <i class="fas fa-heart-broken"></i> Unfav
                    </button>
                </form>
                <form method="post" action="update_status.php">
                    <input type="hidden" name="resource_id" value="${response.resource.resource_id}">
                    <input type="hidden" name="from_page" value="resources">
                    <input type="hidden" name="status" value="in_progress">
                    <button type="submit" class="btn btn-sm btn-success" style="width: 100px;">
                        <i class="fas fa-play"></i> Start
                    </button>
                </form>
            `);
        } else if (response.status === 'in_progress') {
            // Show cancel and complete buttons
            actionDiv.append(`
                <form method="post" action="update_status.php">
                    <input type="hidden" name="resource_id" value="${response.resource.resource_id}">
                    <input type="hidden" name="from_page" value="resources">
                    <input type="hidden" name="status" value="not_started">
                    <button type="submit" class="btn btn-sm btn-warning" style="width: 100px;">
                        <i class="fas fa-pause"></i> Cancel
                    </button>
                </form>
                <form method="post" action="update_status.php">
                    <input type="hidden" name="resource_id" value="${response.resource.resource_id}">
                    <input type="hidden" name="from_page" value="resources">
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" class="btn btn-sm btn-success" style="width: 100px;">
                        <i class="fas fa-check"></i> Complete
                    </button>
                </form>
            `);
        } else if (response.status === 'completed') {
            // Completed status, no extra buttons
        } else {
            // Default status, show favorite and start buttons
            actionDiv.append(`
                <form method="post" action="update_status.php">
                    <input type="hidden" name="resource_id" value="${response.resource.resource_id}">
                    <input type="hidden" name="from_page" value="resources">
                    <input type="hidden" name="status" value="added">
                    <button type="submit" class="btn btn-sm btn-outline-danger" style="width: 100px;">
                        <i class="fas fa-heart"></i> Favorite
                    </button>
                </form>
                <form method="post" action="update_status.php">
                    <input type="hidden" name="resource_id" value="${response.resource.resource_id}">
                    <input type="hidden" name="from_page" value="resources">
                    <input type="hidden" name="status" value="in_progress">
                    <button type="submit" class="btn btn-sm btn-success" style="width: 100px;">
                        <i class="fas fa-play"></i> Start
                    </button>
                </form>
            `);
        }
        
        // Rebind form submission event
        actionDiv.find('form').submit(function(e) {
            e.preventDefault();
            
            var form = $(this);
            var resourceId = form.find('input[name="resource_id"]').val();
            var status = form.find('input[name="status"]').val();
            var fromPage = form.find('input[name="from_page"]').val();
            var card = form.closest('.resource-card');
            
            $.ajax({
                url: 'update_status.php',
                type: 'POST',
                data: {
                    resource_id: resourceId,
                    status: status,
                    from_page: fromPage
                },
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        updateCardStatus(card, response);
                        showMessage('Status update successful', 'success');
                    } else {
                        showMessage(response.message || 'Update failed', 'danger');
                    }
                },
                error: function() {
                    showMessage('Request failed, please try again', 'danger');
                }
            });
        });
    }
    
    // Show message function
    function showMessage(message, type) {
        var messageHtml = '<div class="alert alert-' + type + ' status-message">' + message + '</div>';
        
        // First remove any existing messages
        $('.status-message').remove();
        
        // Add new message
        $('body').append(messageHtml);
        
        // Automatically hide message
        setTimeout(function() {
            $('.status-message').fadeOut(500, function() {
                $(this).remove();
            });
        }, 3000);
    }
    
    // Show more topics button functionality
    $('#toggleMoreTopics').click(function() {
        $('#moreTopics').slideToggle();
        var icon = $(this).find('i');
        var text = $(this).text();
        
        if (text.indexOf('Show More') !== -1) {
            $(this).html('Show Less Topics <i class="fas fa-chevron-up"></i>');
        } else {
            $(this).html('Show More Topics <i class="fas fa-chevron-down"></i>');
        }
    });
});
</script>

<?php include 'footer.php'; ?>