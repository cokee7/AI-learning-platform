<?php
// BC104782 Seven
session_start();
require_once 'database.php';
$pageTitle = "Personalized Recommendations";
include 'header.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user preferences - retrieve skill_level and interested_topics
$stmt = $pdo->prepare("SELECT skill_level, interested_topics FROM user_profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$profile = $stmt->fetch();

if (!$profile) {
    $skill_level = 'Beginner';  
    $interested_topics = '';
} else {
    $skill_level = $profile['skill_level'] ?? 'Beginner'; // Default to Beginner
    $interested_topics = $profile['interested_topics'] ?? '';
}

// Convert interest topics to array
$interested_topics_array = !empty($interested_topics) ? explode(",", $interested_topics) : [];

// Get recommended resources based on user experience level and interest topics
$sql = "SELECT * FROM resources WHERE 1=1";

// Exclude resources the user has completed
$sql .= " AND resource_id NOT IN (
    SELECT resource_id FROM learning_status 
    WHERE user_id = :user_id AND status = 'completed'
)";

// Add difficulty filter conditions
if (!empty($skill_level) && $skill_level != 'Various') {
    // For Beginner users, only select Beginner resources
    if ($skill_level == 'Beginner') {
        $sql .= " AND (Difficulty = 'Beginner' OR Difficulty LIKE 'Beginner/%')";
    }
    // For Intermediate users, only select Intermediate resources
    else if ($skill_level == 'Intermediate') {
        $sql .= " AND (Difficulty = 'Intermediate' OR Difficulty LIKE 'Intermediate/%' OR Difficulty LIKE '%/Intermediate')";
    }
    // For Advanced users, only select Advanced resources
    else if ($skill_level == 'Advanced') {
        $sql .= " AND (Difficulty = 'Advanced' OR Difficulty LIKE 'Advanced/%' OR Difficulty LIKE '%/Advanced')";
    }
}

// Add interest topics filter conditions (if user has selected interests)
if (!empty($interested_topics_array)) {
    $topic_conditions = [];
    foreach ($interested_topics_array as $topic) {
        $topic_conditions[] = "Topic LIKE :topic_" . md5($topic);
    }
    if (!empty($topic_conditions)) {
        $sql .= " AND (" . implode(" OR ", $topic_conditions) . ")";
    }
}

$sql .= " ORDER BY RAND() LIMIT 12"; // Randomly select 12 resources

$stmt = $pdo->prepare($sql);

// Bind user_id parameter
$stmt->bindValue(":user_id", $user_id);

// Bind topic parameters
if (!empty($interested_topics_array)) {
    foreach ($interested_topics_array as $topic) {
        $param_name = ":topic_" . md5($topic);
        $stmt->bindValue($param_name, "%$topic%");
    }
}

$stmt->execute();
$result = $stmt->fetchAll();

// Get user learning status
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
?>

<div class="container mt-4">
    <div class="recommendation-header text-center mb-4">
        <h2>Personalized Learning Recommendations</h2>
        <p class="lead">Based on your experience level (<?php echo $skill_level; ?>) 
            <?php if (!empty($interested_topics_array)): ?>
                and interest topics (<?php echo implode(', ', $interested_topics_array); ?>)
            <?php endif; ?>
        </p>
    </div>
    
    <?php if (empty($result)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> No resources matching your preferences were found. Please try <a href="profile.php" class="alert-link">updating your preferences</a> or <a href="resources.php" class="alert-link">browse all resources</a>.
        </div>
    <?php else: ?>
        <div class="resource-grid">
            <?php foreach ($result as $resource): ?>
                <div class="resource-card" style="display: flex; flex-direction: column; min-height: 340px;">
                    <?php
                    // Get current resource learning status
                    $resourceId = $resource['resource_id'];
                    $status = isset($userLearningStatus[$resourceId]) ? $userLearningStatus[$resourceId] : null;
                    // Based on status set card header style
                    if (!empty($status)) {
                        $headerClass = getStatusClass($status);
                        $statusDisplay = getStatusDisplayName($status);
                        echo '<div class="card-header ' . $headerClass . ' text-white">' . $statusDisplay . '</div>';
                    }
                    ?>
                    <div class="card-body" style="flex: 1 1 auto; display: flex; flex-direction: column;">
                        <h3 style="flex-shrink:0;"><a href="<?php echo $resource['URL']; ?>" target="_blank"><?php echo $resource['Title']; ?></a></h3>
                        <p class="resource-description" style="flex-shrink:0;">Source: <?php echo $resource['Source']; ?></p>
                        <div class="resource-meta" style="flex-shrink:0;">
                            <span class="topic-badge">
                                <?php echo $resource['Topic']; ?>
                            </span>
                            <span class="difficulty-badge <?php 
                                echo strpos($resource['Difficulty'], 'Beginner') !== false ? 'difficulty-beginner' : 
                                    (strpos($resource['Difficulty'], 'Intermediate') !== false ? 'difficulty-intermediate' : 
                                    (strpos($resource['Difficulty'], 'Advanced') !== false ? 'difficulty-advanced' : 'difficulty-various')); 
                            ?>"><?php echo $resource['Difficulty']; ?></span>
                            <span class="format-badge"><i class="fas fa-file-alt"></i> <?php echo $resource['Format']; ?></span>
                        </div>
                        <div class="resource-actions" style="margin-top:auto; display:flex; gap:10px; flex-wrap:wrap; justify-content: space-between;">
                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                <!-- Favorite/Unfavorite button -->
                                <?php if (empty($status) || $status === 'not_started'): ?>
                                    <form method="post" action="update_status.php">
                                        <input type="hidden" name="resource_id" value="<?php echo $resourceId; ?>">
                                        <input type="hidden" name="from_page" value="recommendations">
                                        <input type="hidden" name="status" value="added">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" style="width: 100px;">
                                            <i class="fas fa-heart"></i> Favorite
                                        </button>
                                    </form>
                                <?php elseif ($status === 'added'): ?>
                                    <form method="post" action="update_status.php">
                                        <input type="hidden" name="resource_id" value="<?php echo $resourceId; ?>">
                                        <input type="hidden" name="from_page" value="recommendations">
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
                                        <input type="hidden" name="from_page" value="recommendations">
                                        <input type="hidden" name="status" value="not_started">
                                        <button type="submit" class="btn btn-sm btn-warning" style="width: 100px;">
                                            <i class="fas fa-pause"></i> Cancel
                                        </button>
                                    </form>
                                <?php elseif ($status !== 'completed'): ?>
                                    <form method="post" action="update_status.php">
                                        <input type="hidden" name="resource_id" value="<?php echo $resourceId; ?>">
                                        <input type="hidden" name="from_page" value="recommendations">
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
                                        <input type="hidden" name="from_page" value="recommendations">
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit" class="btn btn-sm btn-success" style="width: 100px;">
                                            <i class="fas fa-check"></i> Complete
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Visit resource button -->
                            <a href="<?php echo $resource['URL']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-external-link-alt"></i> Visit
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <div class="mt-4 text-center">
        <a href="profile.php" class="btn btn-secondary">
            <i class="fas fa-user-cog"></i> Update Your Preferences
        </a>
        <a href="resources.php" class="btn btn-outline-primary">
            <i class="fas fa-list"></i> Browse All Resources
        </a>
    </div>
</div>

<script>
$(document).ready(function() {
    // Intercept resource status form submission, change to AJAX request
    $('.resource-actions form').submit(function(e) {
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
                    // Update card display
                    updateCardStatus(card, response);
                    
                    // Show temporary success message
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
                    <input type="hidden" name="from_page" value="recommendations">
                    <input type="hidden" name="status" value="not_started">
                    <button type="submit" class="btn btn-sm btn-outline-secondary" style="width: 100px;">
                        <i class="fas fa-heart-broken"></i> Unfav
                    </button>
                </form>
                <form method="post" action="update_status.php">
                    <input type="hidden" name="resource_id" value="${response.resource.resource_id}">
                    <input type="hidden" name="from_page" value="recommendations">
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
                    <input type="hidden" name="from_page" value="recommendations">
                    <input type="hidden" name="status" value="not_started">
                    <button type="submit" class="btn btn-sm btn-warning" style="width: 100px;">
                        <i class="fas fa-pause"></i> Cancel
                    </button>
                </form>
                <form method="post" action="update_status.php">
                    <input type="hidden" name="resource_id" value="${response.resource.resource_id}">
                    <input type="hidden" name="from_page" value="recommendations">
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
                    <input type="hidden" name="from_page" value="recommendations">
                    <input type="hidden" name="status" value="added">
                    <button type="submit" class="btn btn-sm btn-outline-danger" style="width: 100px;">
                        <i class="fas fa-heart"></i> Favorite
                    </button>
                </form>
                <form method="post" action="update_status.php">
                    <input type="hidden" name="resource_id" value="${response.resource.resource_id}">
                    <input type="hidden" name="from_page" value="recommendations">
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
    
    // Show floating message
    function showFloatingMessage(message, type) {
        // Create message element
        var messageEl = $('<div class="floating-message alert alert-' + type + '">' + message + '</div>');
        
        // Add to page
        $('body').append(messageEl);
        
        // Automatically disappear after 2 seconds
        setTimeout(function() {
            messageEl.fadeOut(500, function() {
                $(this).remove();
            });
        }, 2000);
    }

    // Show message function
    function showMessage(message, type) {
        // Remove existing message
        $('.status-message').remove();
        
        // Create new message
        var messageEl = $('<div class="alert alert-' + type + ' status-message">' + message + '</div>');
        $('body').append(messageEl);
        
        // Disappear after 3 seconds
        setTimeout(function() {
            messageEl.fadeOut(500, function() {
                $(this).remove();
            });
        }, 3000);
    }
});
</script>

<style>
.recommendation-header {
    padding: 20px 0;
    margin-bottom: 30px;
}

.resource-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 25px;
    margin-top: 20px;
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

.card-header {
    padding: 10px 15px;
}

.alert-info {
    border-left: 4px solid #17a2b8;
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

<?php include 'footer.php'; ?>