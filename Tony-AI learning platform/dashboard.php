<?php
//BC105164 Jasper
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$pageTitle = "Learning Dashboard";
include 'header.php';
require_once 'database.php';

$userId = $_SESSION['user_id'];

// Get not started resources
$notStartedSql = "SELECT r.Title as title, r.URL as url, r.Format as type, r.Topic as topic, r.Difficulty as difficulty, ls.status, ls.resource_id
                FROM learning_status ls
                JOIN resources r ON ls.resource_id = r.resource_id
                WHERE ls.user_id = ? AND ls.status = 'added'";
$notStartedStmt = $pdo->prepare($notStartedSql);
$notStartedStmt->execute([$userId]);
$notStartedResult = $notStartedStmt->fetchAll();

// Get in-progress resources
$learningSql = "SELECT r.Title as title, r.URL as url, r.Format as type, r.Topic as topic, r.Difficulty as difficulty, ls.status, ls.resource_id
                FROM learning_status ls
                JOIN resources r ON ls.resource_id = r.resource_id
                WHERE ls.user_id = ? AND ls.status = 'in_progress'";
$learningStmt = $pdo->prepare($learningSql);
$learningStmt->execute([$userId]);
$learningResult = $learningStmt->fetchAll();

// Get completed resources
$completedSql = "SELECT r.Title as title, r.URL as url, r.Format as type, r.Topic as topic, r.Difficulty as difficulty, ls.status, ls.resource_id
                 FROM learning_status ls
                 JOIN resources r ON ls.resource_id = r.resource_id
                 WHERE ls.user_id = ? AND ls.status = 'completed'";
$completedStmt = $pdo->prepare($completedSql);
$completedStmt->execute([$userId]);
$completedResult = $completedStmt->fetchAll();

// Count total learning resources
$totalNotStarted = count($notStartedResult);
$totalLearning = count($learningResult);
$totalCompleted = count($completedResult);
$totalResources = $totalNotStarted + $totalLearning + $totalCompleted;

// Calculate topic distribution
$topics = [];
foreach (array_merge($notStartedResult, $learningResult, $completedResult) as $resource) {
    $topicList = explode('/', $resource['topic']); // Split topics by slash
    foreach ($topicList as $topic) {
        $topic = trim($topic); // Remove extra spaces
        if (!isset($topics[$topic])) {
            $topics[$topic] = 0;
        }
        $topics[$topic]++;
    }
}

// Calculate difficulty distribution
$difficulties = [
    'Beginner' => 0,
    'Intermediate' => 0,
    'Advanced' => 0,
    'Various' => 0
];

foreach (array_merge($notStartedResult, $learningResult, $completedResult) as $resource) {
    $difficultyStr = $resource['difficulty'];
    
    if (strpos($difficultyStr, 'Beginner') !== false) {
        $difficulties['Beginner']++;
    } elseif (strpos($difficultyStr, 'Intermediate') !== false) {
        $difficulties['Intermediate']++;
    } elseif (strpos($difficultyStr, 'Advanced') !== false) {
        $difficulties['Advanced']++;
    } else {
        $difficulties['Various']++;
    }
}

// Calculate learning status distribution by topic
$topicStatusData = [];
$mainTopics = array_keys($topics);
sort($mainTopics);

// Only select the top 5 most common topics to avoid chart complexity
if (count($mainTopics) > 5) {
    // Sort topics by occurrence count
    arsort($topics);
    $mainTopics = array_slice(array_keys($topics), 0, 5);
}

// Initialize data structure
foreach ($mainTopics as $topic) {
    $topicStatusData[$topic] = [
        'added' => 0,
        'in_progress' => 0,
        'completed' => 0
    ];
}

// Count learning status distribution by topic
function countTopicStatus($resources, &$topicStatusData, $mainTopics, $status) {
    foreach ($resources as $resource) {
        $resourceTopics = explode('/', $resource['topic']);
        foreach ($resourceTopics as $topic) {
            $topic = trim($topic);
            if (in_array($topic, $mainTopics)) {
                $topicStatusData[$topic][$status]++;
            }
        }
    }
}

countTopicStatus($notStartedResult, $topicStatusData, $mainTopics, 'added');
countTopicStatus($learningResult, $topicStatusData, $mainTopics, 'in_progress');
countTopicStatus($completedResult, $topicStatusData, $mainTopics, 'completed');

// Calculate learning status distribution by difficulty
$difficultyStatusData = [
    'Beginner' => ['added' => 0, 'in_progress' => 0, 'completed' => 0],
    'Intermediate' => ['added' => 0, 'in_progress' => 0, 'completed' => 0],
    'Advanced' => ['added' => 0, 'in_progress' => 0, 'completed' => 0],
    'Various' => ['added' => 0, 'in_progress' => 0, 'completed' => 0]
];

function countDifficultyStatus($resources, &$difficultyStatusData, $status) {
    foreach ($resources as $resource) {
        $difficultyStr = $resource['difficulty'];
        
        if (strpos($difficultyStr, 'Beginner') !== false) {
            $difficultyStatusData['Beginner'][$status]++;
        } elseif (strpos($difficultyStr, 'Intermediate') !== false) {
            $difficultyStatusData['Intermediate'][$status]++;
        } elseif (strpos($difficultyStr, 'Advanced') !== false) {
            $difficultyStatusData['Advanced'][$status]++;
        } else {
            $difficultyStatusData['Various'][$status]++;
        }
    }
}

countDifficultyStatus($notStartedResult, $difficultyStatusData, 'added');
countDifficultyStatus($learningResult, $difficultyStatusData, 'in_progress');
countDifficultyStatus($completedResult, $difficultyStatusData, 'completed');

// Get status display name
function getStatusDisplayName($status) {
    switch ($status) {
        case 'added':
            return "Favorite";
        case 'in_progress':
            return "In Progress";
        case 'completed':
            return "Completed";
        default:
            return $status;
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
    <div class="dashboard-header">
        <h2><i class="fas fa-chart-line"></i> My Learning Dashboard</h2>
        <p>Track your learning progress and manage your resources here</p>
    </div>

    <!-- Statistics cards -->
    <div class="stats-cards">
        <div class="stats-card total">
            <div class="stats-icon"><i class="fas fa-book"></i></div>
            <div class="stats-info">
                <h3>Total Resources</h3>
                <div class="stats-value"><?php echo $totalResources; ?></div>
            </div>
        </div>
        <div class="stats-card tolearn">
            <div class="stats-icon"><i class="fas fa-list"></i></div>
            <div class="stats-info">
                <h3>Favorite</h3>
                <div class="stats-value"><?php echo $totalNotStarted; ?></div>
            </div>
        </div>
        <div class="stats-card inprogress">
            <div class="stats-icon"><i class="fas fa-spinner"></i></div>
            <div class="stats-info">
                <h3>In Progress</h3>
                <div class="stats-value"><?php echo $totalLearning; ?></div>
            </div>
        </div>
        <div class="stats-card completed">
            <div class="stats-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stats-info">
                <h3>Completed</h3>
                <div class="stats-value"><?php echo $totalCompleted; ?></div>
            </div>
        </div>
    </div>

    <!-- Visualization section -->
    <div class="visualization-section">
        <div class="row">
            <!-- Learning status pie chart -->
            <div class="col-md-6">
                <div class="dashboard-card">
                    <h3><i class="fas fa-tasks"></i> Learning Status Distribution</h3>
                    <div class="chart-container">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Difficulty distribution bar chart -->
            <div class="col-md-6">
                <div class="dashboard-card">
                    <h3><i class="fas fa-chart-line"></i> Difficulty Level Distribution</h3>
                    <div class="chart-container">
                        <canvas id="difficultyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Learning progress overview -->
        <div class="dashboard-card">
            <h3><i class="fas fa-graduation-cap"></i> Learning Progress Overview</h3>
            <div class="progress-overview">
                <div class="progress" style="height: 25px;">
                    <?php if ($totalResources > 0): ?>
                        <div class="progress-bar bg-primary" role="progressbar" 
                             style="width: <?php echo round(($totalNotStarted / $totalResources) * 100); ?>%" 
                             aria-valuenow="<?php echo $totalNotStarted; ?>" aria-valuemin="0" 
                             aria-valuemax="<?php echo $totalResources; ?>">
                            Favorite (<?php echo $totalNotStarted; ?>)
                        </div>
                        <div class="progress-bar bg-warning" role="progressbar" 
                             style="width: <?php echo round(($totalLearning / $totalResources) * 100); ?>%" 
                             aria-valuenow="<?php echo $totalLearning; ?>" aria-valuemin="0" 
                             aria-valuemax="<?php echo $totalResources; ?>">
                            In Progress (<?php echo $totalLearning; ?>)
                        </div>
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: <?php echo round(($totalCompleted / $totalResources) * 100); ?>%" 
                             aria-valuenow="<?php echo $totalCompleted; ?>" aria-valuemin="0" 
                             aria-valuemax="<?php echo $totalResources; ?>">
                            Completed (<?php echo $totalCompleted; ?>)
                        </div>
                    <?php else: ?>
                        <div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                            No learning resources
                        </div>
                    <?php endif; ?>
                </div>
                <div class="progress-legend mt-2">
                    <span class="badge bg-primary">Favorite</span>
                    <span class="badge bg-warning">In Progress</span>
                    <span class="badge bg-success">Completed</span>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Difficulty-Learning status cross analysis -->
            <div class="col-md-6">
                <div class="dashboard-card">
                    <h3><i class="fas fa-layer-group"></i> Difficulty & Learning Status</h3>
                    <div class="chart-container">
                        <canvas id="difficultyStatusChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Topic-Learning status cross analysis -->
            <div class="col-md-6">
                <div class="dashboard-card">
                    <h3><i class="fas fa-th"></i> Topic & Learning Status</h3>
                    <div class="chart-container">
                        <canvas id="topicStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Learning status tabs -->
    <div class="learning-tabs">
        <ul class="tabs-nav">
            <li class="tab-active" data-tab="tab-learning" id="learning-tab"><i class="fas fa-spinner"></i> In Progress (<?php echo $totalLearning; ?>)</li>
            <li data-tab="tab-tolearn" id="favorites-tab"><i class="fas fa-list"></i> Favorite (<?php echo $totalNotStarted; ?>)</li>
            <li data-tab="tab-completed" id="completed-tab"><i class="fas fa-check-circle"></i> Completed (<?php echo $totalCompleted; ?>)</li>
        </ul>

        <div class="tab-content" id="tab-learning">
            <?php if ($totalLearning > 0): ?>
                <div class="resources-grid">
                <?php foreach ($learningResult as $row): ?>
                    <div class="resource-card">
                        <h3><a href="<?php echo $row['url']; ?>" target="_blank"><?php echo $row['title']; ?></a></h3>
                        <div class="resource-meta">
                            <span class="topic-badge"><?php echo $row['topic']; ?></span>
                            <span class="difficulty-badge <?php 
                                echo strpos($row['difficulty'], 'Beginner') !== false ? 'difficulty-beginner' : 
                                    (strpos($row['difficulty'], 'Intermediate') !== false ? 'difficulty-intermediate' : 
                                    (strpos($row['difficulty'], 'Advanced') !== false ? 'difficulty-advanced' : 'difficulty-various')); 
                            ?>"><?php echo $row['difficulty']; ?></span>
                            <span class="resource-type"><i class="fas fa-bookmark"></i> <?php echo $row['type']; ?></span>
                        </div>
                        <div class="resource-actions" style="display: flex; justify-content: space-between;">
                            <div style="display: flex; gap: 8px;">
                                <form method="post" action="update_status.php">
                                    <input type="hidden" name="resource_id" value="<?php echo $row['resource_id']; ?>">
                                    <input type="hidden" name="from_page" value="dashboard">
                                    <input type="hidden" name="status" value="completed">
                                    <button type="submit" class="btn btn-success btn-sm" style="width: 100px;">
                                        <i class="fas fa-check"></i> Complete
                                    </button>
                                </form>
                                <form method="post" action="update_status.php">
                                    <input type="hidden" name="resource_id" value="<?php echo $row['resource_id']; ?>">
                                    <input type="hidden" name="from_page" value="dashboard">
                                    <input type="hidden" name="status" value="not_started">
                                    <button type="submit" class="btn btn-warning btn-sm" style="width: 100px;">
                                        <i class="fas fa-pause"></i> Cancel
                                    </button>
                                </form>
                            </div>
                            <a href="<?php echo $row['url']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-external-link-alt"></i> Visit
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-spinner fa-3x"></i>
                    <p>You don't have any resources in progress.</p>
                    <a href="resources.php" class="btn btn-primary">Browse Learning Resources</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="tab-content" id="tab-tolearn" style="display: none;">
            <?php if ($totalNotStarted > 0): ?>
                <div class="resources-grid">
                <?php foreach ($notStartedResult as $row): ?>
                    <div class="resource-card">
                        <h3><a href="<?php echo $row['url']; ?>" target="_blank"><?php echo $row['title']; ?></a></h3>
                        <div class="resource-meta">
                            <span class="topic-badge"><?php echo $row['topic']; ?></span>
                            <span class="difficulty-badge <?php 
                                echo strpos($row['difficulty'], 'Beginner') !== false ? 'difficulty-beginner' : 
                                    (strpos($row['difficulty'], 'Intermediate') !== false ? 'difficulty-intermediate' : 
                                    (strpos($row['difficulty'], 'Advanced') !== false ? 'difficulty-advanced' : 'difficulty-various')); 
                            ?>"><?php echo $row['difficulty']; ?></span>
                            <span class="resource-type"><i class="fas fa-bookmark"></i> <?php echo $row['type']; ?></span>
                        </div>
                        <div class="resource-actions" style="display: flex; justify-content: space-between;">
                            <div style="display: flex; gap: 8px;">
                                <form method="post" action="update_status.php">
                                    <input type="hidden" name="resource_id" value="<?php echo $row['resource_id']; ?>">
                                    <input type="hidden" name="from_page" value="dashboard">
                                    <input type="hidden" name="status" value="in_progress">
                                    <button type="submit" class="btn btn-primary btn-sm" style="width: 100px;">
                                        <i class="fas fa-play"></i> Start
                                    </button>
                                </form>
                                <form method="post" action="update_status.php">
                                    <input type="hidden" name="resource_id" value="<?php echo $row['resource_id']; ?>">
                                    <input type="hidden" name="from_page" value="dashboard">
                                    <input type="hidden" name="status" value="not_started">
                                    <button type="submit" class="btn btn-outline-secondary btn-sm" style="width: 100px;">
                                        <i class="fas fa-heart-broken"></i> Unfav
                                    </button>
                                </form>
                            </div>
                            <a href="<?php echo $row['url']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-external-link-alt"></i> Visit
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-list fa-3x"></i>
                    <p>Your favorite list is empty.</p>
                    <a href="resources.php" class="btn btn-primary">Browse Learning Resources</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="tab-content" id="tab-completed" style="display: none;">
            <?php if ($totalCompleted > 0): ?>
                <div class="resources-grid">
                <?php foreach ($completedResult as $row): ?>
                    <div class="resource-card">
                        <h3><a href="<?php echo $row['url']; ?>" target="_blank"><?php echo $row['title']; ?></a></h3>
                        <div class="resource-meta">
                            <span class="topic-badge"><?php echo $row['topic']; ?></span>
                            <span class="difficulty-badge <?php 
                                echo strpos($row['difficulty'], 'Beginner') !== false ? 'difficulty-beginner' : 
                                    (strpos($row['difficulty'], 'Intermediate') !== false ? 'difficulty-intermediate' : 
                                    (strpos($row['difficulty'], 'Advanced') !== false ? 'difficulty-advanced' : 'difficulty-various')); 
                            ?>"><?php echo $row['difficulty']; ?></span>
                            <span class="resource-type"><i class="fas fa-bookmark"></i> <?php echo $row['type']; ?></span>
                        </div>
                        <div class="resource-actions" style="display: flex; justify-content: space-between;">
                            <div style="display: flex; gap: 8px;">
                                <form method="post" action="update_status.php">
                                    <input type="hidden" name="resource_id" value="<?php echo $row['resource_id']; ?>">
                                    <input type="hidden" name="from_page" value="dashboard">
                                    <input type="hidden" name="status" value="delete">
                                    <button type="submit" class="btn btn-danger btn-sm" style="width: 100px;">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                            <a href="<?php echo $row['url']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-external-link-alt"></i> Visit
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle fa-3x"></i>
                    <p>You haven't completed any learning resources yet.</p>
                    <a href="resources.php" class="btn btn-primary">Browse Learning Resources</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.dashboard-header {
    margin-bottom: 30px;
    text-align: center;
}

.dashboard-header h2 {
    font-weight: 700;
    margin-bottom: 10px;
    color: #1a237e;
}

.dashboard-header p {
    color: #666;
}

.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stats-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    display: flex;
    align-items: center;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-5px);
}

.stats-icon {
    width: 50px;
    height: 50px;
    background: #f5f7ff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    font-size: 20px;
}

.stats-card.total .stats-icon {
    color: #6200ea;
    background: #ede7f6;
}

.stats-card.tolearn .stats-icon {
    color: #0091ea;
    background: #e1f5fe;
}

.stats-card.inprogress .stats-icon {
    color: #ff6d00;
    background: #fff3e0;
}

.stats-card.completed .stats-icon {
    color: #00c853;
    background: #e8f5e9;
}

.stats-value {
    font-size: 24px;
    font-weight: 700;
    margin-top: 5px;
}

.visualization-section {
    margin-bottom: 30px;
}

.dashboard-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    padding: 20px;
    margin-bottom: 20px;
    position: relative;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.dashboard-card:hover {
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.dashboard-card h3 {
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
    font-size: 1.2rem;
    color: #1a237e;
}

.progress-overview {
    margin-top: 15px;
}

.progress {
    height: 25px;
    border-radius: 5px;
    overflow: hidden;
}

.progress-bar {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    font-weight: 600;
    transition: width 0.6s ease;
}

.progress-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 10px;
    justify-content: center;
}

.badge {
    padding: 5px 10px;
    border-radius: 4px;
    font-weight: 500;
}

.learning-tabs {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    margin-bottom: 30px;
}

.tabs-nav {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0;
    background: #f8f9fa;
    flex-wrap: wrap;
}

.tabs-nav li {
    padding: 15px 20px;
    cursor: pointer;
    transition: 0.3s;
    font-weight: 600;
}

.tabs-nav li:hover {
    background: #e9ecef;
}

.tabs-nav li.tab-active {
    background: white;
    border-bottom: 3px solid #6200ea;
}

.tab-content {
    padding: 20px;
}

.resources-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.resource-card {
    border: 1px solid #eee;
    border-radius: 8px;
    padding: 15px;
    transition: 0.3s;
}

.resource-card:hover {
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transform: translateY(-3px);
}

.resource-card h3 {
    font-size: 1.1rem;
    margin-bottom: 15px;
    border-bottom: none;
    padding-bottom: 0;
}

.resource-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 15px;
}

.topic-badge, .difficulty-badge, .resource-type {
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
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

.resource-type {
    background: #f5f5f5;
    color: #616161;
}

.resource-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
}

.resource-actions div {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 5px;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #666;
}

.empty-state i {
    margin-bottom: 15px;
    color: #ddd;
}

.d-inline {
    display: inline-block;
}

.ml-2 {
    margin-left: 0.5rem;
}

/* Ensure chart containers have appropriate size and fixed height to prevent layout movement */
canvas {
    max-width: 100%;
    margin: 5px 0;
    min-height: 280px;
}

.row {
    display: flex;
    flex-wrap: wrap;
    margin-right: -15px;
    margin-left: -15px;
}

.col-md-6 {
    flex: 0 0 50%;
    max-width: 50%;
    padding-right: 15px;
    padding-left: 15px;
    box-sizing: border-box;
}

/* Media query - Responsive layout enhancement */
@media (max-width: 991px) {
    .col-md-6 {
        margin-bottom: 20px;
    }
}

@media (max-width: 768px) {
    .col-md-6 {
        flex: 0 0 100%;
        max-width: 100%;
    }
    
    .stats-cards {
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    }
    
    .tabs-nav li {
        padding: 10px 15px;
        font-size: 0.9rem;
    }
    
    .tab-content {
        padding: 15px;
    }
    
    .dashboard-card {
        padding: 15px;
    }
}

/* Ensure charts maintain stable height before loading */
.chart-container {
    position: relative;
    height: 280px;
    width: 100%;
}

.floating-message {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
    padding: 10px 20px;
    border-radius: 4px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
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
    border: none;
}

@keyframes highlight {
    0% { box-shadow: 0 0 0 rgba(76, 175, 80, 0.1); }
    50% { box-shadow: 0 0 5px rgba(76, 175, 80, 0.15); }
    100% { box-shadow: 0 0 0 rgba(76, 175, 80, 0.1); }
}
</style>

<script>
// Simplified tag switching functionality
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.tabs-nav li');
    const tabContents = document.querySelectorAll('.tab-content');
    
    // Only retain basic tag switching functionality
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const tabId = tab.getAttribute('data-tab');
            
            // Remove active state from all active tags
            tabs.forEach(t => t.classList.remove('tab-active'));
            
            // Hide all tag contents
            tabContents.forEach(content => {
                content.style.display = 'none';
            });
            
            // Activate current tag and its content
            tab.classList.add('tab-active');
            document.getElementById(tabId).style.display = 'block';
        });
    });
    
    // Highlight recently updated resource card
    <?php if ($highlightResourceId): ?>
    const resourceId = <?php echo $highlightResourceId; ?>;
    const resourceCard = $('input[name="resource_id"][value="' + resourceId + '"]').closest('.resource-card');
    if (resourceCard.length) {
        resourceCard.addClass('highlight-card');
    }
    <?php endif; ?>
    
    // Automatically hide status message
    setTimeout(function() {
        $('.status-message').fadeOut(500);
    }, 3000);
    
    // Ensure stats data is up-to-date after initial load
    updateStatsCards();
});
</script>

<!-- Chart scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Update statistics card display
function updateStatsCards() {
    $.ajax({
        url: 'get_chart_data.php',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data.success) {
                // Update statistics card values
                $('.stats-card.total .stats-value').text(data.totalResources);
                $('.stats-card.tolearn .stats-value').text(data.totalNotStarted);
                $('.stats-card.inprogress .stats-value').text(data.totalLearning);
                $('.stats-card.completed .stats-value').text(data.totalCompleted);
            }
        }
    });
}

// Global variable to store chart instances
var statusChart, difficultyChart, topicStatusChart, difficultyStatusChart;

// Initialize charts after entire page DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    initCharts();
});

// Initialize all charts
function initCharts() {
    // Learning status chart
    if(document.getElementById('statusChart')) {
        statusChart = new Chart(document.getElementById('statusChart'), {
            type: 'pie',
            data: {
                labels: ['Favorite', 'In Progress', 'Completed'],
                datasets: [{
                    data: [
                        <?php echo $totalNotStarted; ?>,
                        <?php echo $totalLearning; ?>,
                        <?php echo $totalCompleted; ?>
                    ],
                    backgroundColor: [
                        '#2196F3', '#FF9800', '#4CAF50'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            boxWidth: 12
                        }
                    }
                }
            }
        });
    }

    // Difficulty distribution chart
    if(document.getElementById('difficultyChart')) {
        difficultyChart = new Chart(document.getElementById('difficultyChart'), {
            type: 'bar',
            data: {
                labels: ['Beginner', 'Intermediate', 'Advanced', 'Various'],
                datasets: [{
                    label: 'Resource Count',
                    data: [
                        <?php echo $difficulties['Beginner']; ?>,
                        <?php echo $difficulties['Intermediate']; ?>,
                        <?php echo $difficulties['Advanced']; ?>,
                        <?php echo $difficulties['Various']; ?>
                    ],
                    backgroundColor: [
                        '#4CAF50', '#FFC107', '#F44336', '#9C27B0'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }

    // Topic-Learning status relationship chart
    if(document.getElementById('topicStatusChart')) {
        topicStatusChart = new Chart(document.getElementById('topicStatusChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($mainTopics); ?>,
                datasets: [
                    {
                        label: 'Favorite',
                        data: <?php echo json_encode(array_map(function($topic) use ($topicStatusData) { 
                            return $topicStatusData[$topic]['added']; 
                        }, $mainTopics)); ?>,
                        backgroundColor: '#2196F3'
                    },
                    {
                        label: 'In Progress',
                        data: <?php echo json_encode(array_map(function($topic) use ($topicStatusData) { 
                            return $topicStatusData[$topic]['in_progress']; 
                        }, $mainTopics)); ?>,
                        backgroundColor: '#FF9800'
                    },
                    {
                        label: 'Completed',
                        data: <?php echo json_encode(array_map(function($topic) use ($topicStatusData) { 
                            return $topicStatusData[$topic]['completed']; 
                        }, $mainTopics)); ?>,
                        backgroundColor: '#4CAF50'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    title: {
                        display: true,
                        text: 'Distribution of learning states per topic'
                    }
                },
                scales: {
                    x: {
                        stacked: true,
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }

    // Difficulty-Learning status relationship chart
    if(document.getElementById('difficultyStatusChart')) {
        difficultyStatusChart = new Chart(document.getElementById('difficultyStatusChart'), {
            type: 'bar',
            data: {
                labels: ['Beginner', 'Intermediate', 'Advanced', 'Various'],
                datasets: [
                    {
                        label: 'Favorite',
                        data: [
                            <?php echo $difficultyStatusData['Beginner']['added']; ?>,
                            <?php echo $difficultyStatusData['Intermediate']['added']; ?>,
                            <?php echo $difficultyStatusData['Advanced']['added']; ?>,
                            <?php echo $difficultyStatusData['Various']['added']; ?>
                        ],
                        backgroundColor: '#2196F3'
                    },
                    {
                        label: 'In Progress',
                        data: [
                            <?php echo $difficultyStatusData['Beginner']['in_progress']; ?>,
                            <?php echo $difficultyStatusData['Intermediate']['in_progress']; ?>,
                            <?php echo $difficultyStatusData['Advanced']['in_progress']; ?>,
                            <?php echo $difficultyStatusData['Various']['in_progress']; ?>
                        ],
                        backgroundColor: '#FF9800'
                    },
                    {
                        label: 'Completed',
                        data: [
                            <?php echo $difficultyStatusData['Beginner']['completed']; ?>,
                            <?php echo $difficultyStatusData['Intermediate']['completed']; ?>,
                            <?php echo $difficultyStatusData['Advanced']['completed']; ?>,
                            <?php echo $difficultyStatusData['Various']['completed']; ?>
                        ],
                        backgroundColor: '#4CAF50'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    title: {
                        display: true,
                        text: 'Distribution of Learning States at Different Levels of Difficulty'
                    }
                },
                scales: {
                    x: {
                        stacked: true,
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }
}

// Update all charts data function
function updateCharts() {
    $.ajax({
        url: 'get_chart_data.php',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data.success) {
                // Update status pie chart
                if (statusChart) {
                    statusChart.data.datasets[0].data = [
                        data.totalNotStarted,
                        data.totalLearning,
                        data.totalCompleted
                    ];
                    statusChart.update();
                }
                
                // Update difficulty distribution chart
                if (difficultyChart) {
                    difficultyChart.data.datasets[0].data = [
                        data.difficulties.Beginner,
                        data.difficulties.Intermediate,
                        data.difficulties.Advanced,
                        data.difficulties.Various
                    ];
                    difficultyChart.update();
                }
                
                // Update topic status chart
                if (topicStatusChart) {
                    // Update labels (topic order might change)
                    topicStatusChart.data.labels = data.mainTopics;
                    
                    // Update data for each status
                    topicStatusChart.data.datasets[0].data = data.mainTopics.map(topic => 
                        data.topicStatusData[topic].added);
                    topicStatusChart.data.datasets[1].data = data.mainTopics.map(topic => 
                        data.topicStatusData[topic].in_progress);
                    topicStatusChart.data.datasets[2].data = data.mainTopics.map(topic => 
                        data.topicStatusData[topic].completed);
                    
                    topicStatusChart.update();
                }
                
                // Update difficulty status chart
                if (difficultyStatusChart) {
                    difficultyStatusChart.data.datasets[0].data = [
                        data.difficultyStatusData.Beginner.added,
                        data.difficultyStatusData.Intermediate.added,
                        data.difficultyStatusData.Advanced.added,
                        data.difficultyStatusData.Various.added
                    ];
                    difficultyStatusChart.data.datasets[1].data = [
                        data.difficultyStatusData.Beginner.in_progress,
                        data.difficultyStatusData.Intermediate.in_progress,
                        data.difficultyStatusData.Advanced.in_progress,
                        data.difficultyStatusData.Various.in_progress
                    ];
                    difficultyStatusChart.data.datasets[2].data = [
                        data.difficultyStatusData.Beginner.completed,
                        data.difficultyStatusData.Intermediate.completed,
                        data.difficultyStatusData.Advanced.completed,
                        data.difficultyStatusData.Various.completed
                    ];
                    difficultyStatusChart.update();
                }
            }
        }
    });
}
</script>

<script>
$(document).ready(function() {
    // Bind event handlers to all existing cards
    $('.resource-card').each(function() {
        bindCardFormEvents($(this));
    });
    
    // Ensure stats card displays correct data
    updateStatsCards();
});

// Intercept form submissions from all resource cards to update without refreshing
function bindCardFormEvents(card) {
    card.find('form').submit(function(e) {
        e.preventDefault();
        
        var form = $(this);
        var resourceId = form.find('input[name="resource_id"]').val();
        var status = form.find('input[name="status"]').val();
        var fromPage = form.find('input[name="from_page"]').val();
        var card = form.closest('.resource-card');
        var tabId = card.closest('.tab-content').attr('id');
        
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
                    showMessage('Status update successful', 'success');
                    
                    if (tabId === 'tab-learning' && (status === 'not_started' || status === 'completed')) {
                        card.fadeOut(300, function() {
                            $(this).remove();
                            
                            if (status === 'completed') {
                                var newCard = createCardFromResponse(response);
                                
                                if ($('#tab-completed .resources-grid').length === 0) {
                                    $('#tab-completed').html('<div class="resources-grid"></div>');
                                }
                                
                                $('#tab-completed .resources-grid').prepend(newCard);
                                newCard.addClass('highlight-card');
                                bindCardFormEvents(newCard);
                            }
                            
                            updateTabCounts();
                            updateCharts();
                            updateStatsCards();
                        });
                    } else if (tabId === 'tab-tolearn' && (status === 'in_progress' || status === 'completed' || status === 'not_started')) {
                        // If removed from "Favorite" tab, hide card and update count
                        if (status === 'not_started') {
                            // If removed from favorites, completely remove card
                            card.fadeOut(300, function() {
                                $(this).remove();
                                updateTabCounts();
                                updateCharts();
                                updateStatsCards();
                            });
                        } else {
                            // If starting learning or completing, move to corresponding tab
                            card.fadeOut(300, function() {
                                $(this).remove();
                                
                                // Create new card clone
                                var newCard = createCardFromResponse(response);
                                
                                // Add to corresponding tab
                                if (status === 'in_progress') {
                                    if ($('#tab-learning .resources-grid').length === 0) {
                                        // If tab is empty, create new grid
                                        $('#tab-learning').html('<div class="resources-grid"></div>');
                                    }
                                    $('#tab-learning .resources-grid').prepend(newCard);
                                    newCard.addClass('highlight-card');
                                } else if (status === 'completed') {
                                    if ($('#tab-completed .resources-grid').length === 0) {
                                        // If tab is empty, create new grid
                                        $('#tab-completed').html('<div class="resources-grid"></div>');
                                    }
                                    $('#tab-completed .resources-grid').prepend(newCard);
                                    newCard.addClass('highlight-card');
                                }
                                
                                // Rebind event handlers to new added card
                                bindCardFormEvents(newCard);
                                
                                updateTabCounts();
                                updateCharts();
                                updateStatsCards();
                            });
                        }
                    } else if (tabId === 'tab-completed' && (status !== 'completed' || status === 'delete')) {
                        card.fadeOut(300, function() {
                            $(this).remove();
                            updateTabCounts();
                            updateCharts();
                            updateStatsCards();
                            
                            // Check if all completed resources are removed
                            if ($('#tab-completed .resource-card').length === 0) {
                                $('#tab-completed').html(`
                                    <div class="empty-state">
                                        <i class="fas fa-check-circle fa-3x"></i>
                                        <p>You haven't completed any learning resources yet.</p>
                                        <a href="resources.php" class="btn btn-primary">Browse Learning Resources</a>
                                    </div>
                                `);
                            }
                        });
                    }
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

// Create new card from response
function createCardFromResponse(response) {
    var resourceInfo = response.resource;
    var difficultyClass = '';
    
    if (resourceInfo.difficulty.indexOf('Beginner') !== -1) {
        difficultyClass = 'difficulty-beginner';
    } else if (resourceInfo.difficulty.indexOf('Intermediate') !== -1) {
        difficultyClass = 'difficulty-intermediate';
    } else if (resourceInfo.difficulty.indexOf('Advanced') !== -1) {
        difficultyClass = 'difficulty-advanced';
    } else {
        difficultyClass = 'difficulty-various';
    }
    
    var cardHeader = '';
    if (response.status !== 'not_started' && response.headerClass) {
        cardHeader = '<div class="card-header ' + response.headerClass + ' text-white">' + response.statusDisplay + '</div>';
    }
    
    var actionButtons = '';
    if (response.status === 'in_progress') {
        actionButtons = `
            <div style="display: flex; gap: 8px;">
                <form method="post" action="update_status.php">
                    <input type="hidden" name="resource_id" value="${resourceInfo.resource_id}">
                    <input type="hidden" name="from_page" value="dashboard">
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" class="btn btn-success btn-sm" style="width: 100px;">
                        <i class="fas fa-check"></i> Complete
                    </button>
                </form>
                <form method="post" action="update_status.php">
                    <input type="hidden" name="resource_id" value="${resourceInfo.resource_id}">
                    <input type="hidden" name="from_page" value="dashboard">
                    <input type="hidden" name="status" value="not_started">
                    <button type="submit" class="btn btn-warning btn-sm" style="width: 100px;">
                        <i class="fas fa-pause"></i> Cancel
                    </button>
                </form>
            </div>
        `;
    } else if (response.status === 'added') {
        actionButtons = `
            <div style="display: flex; gap: 8px;">
                <form method="post" action="update_status.php">
                    <input type="hidden" name="resource_id" value="${resourceInfo.resource_id}">
                    <input type="hidden" name="from_page" value="dashboard">
                    <input type="hidden" name="status" value="in_progress">
                    <button type="submit" class="btn btn-primary btn-sm" style="width: 100px;">
                        <i class="fas fa-play"></i> Start
                    </button>
                </form>
                <form method="post" action="update_status.php">
                    <input type="hidden" name="resource_id" value="${resourceInfo.resource_id}">
                    <input type="hidden" name="from_page" value="dashboard">
                    <input type="hidden" name="status" value="not_started">
                    <button type="submit" class="btn btn-outline-secondary btn-sm" style="width: 100px;">
                        <i class="fas fa-heart-broken"></i> Unfav
                    </button>
                </form>
            </div>
        `;
    } else if (response.status === 'completed') {
        actionButtons = `
            <div style="display: flex; gap: 8px;">
                <form method="post" action="update_status.php">
                    <input type="hidden" name="resource_id" value="${resourceInfo.resource_id}">
                    <input type="hidden" name="from_page" value="dashboard">
                    <input type="hidden" name="status" value="delete">
                    <button type="submit" class="btn btn-danger btn-sm" style="width: 100px;">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
            </div>
        `;
    }
    
    var card = $(`
        <div class="resource-card">
            ${cardHeader}
            <h3><a href="${resourceInfo.url}" target="_blank">${resourceInfo.title}</a></h3>
            <div class="resource-meta">
                <span class="topic-badge">${resourceInfo.topic}</span>
                <span class="difficulty-badge ${difficultyClass}">${resourceInfo.difficulty}</span>
                <span class="resource-type"><i class="fas fa-bookmark"></i> ${resourceInfo.type}</span>
            </div>
            <div class="resource-actions" style="display: flex; justify-content: space-between;">
                ${actionButtons}
                <a href="${resourceInfo.url}" target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-external-link-alt"></i> Visit
                </a>
            </div>
        </div>
    `);
    
    return card;
}

// Update tab counts display
function updateTabCounts() {
    $.ajax({
        url: 'get_chart_data.php',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data.success) {
                $('#learning-tab').html('<i class="fas fa-spinner"></i> In Progress (' + data.totalLearning + ')');
                $('#favorites-tab').html('<i class="fas fa-list"></i> Favorite (' + data.totalNotStarted + ')');
                $('#completed-tab').html('<i class="fas fa-check-circle"></i> Completed (' + data.totalCompleted + ')');
                
                // Update progress bars
                var totalResources = data.totalResources;
                if (totalResources > 0) {
                    var notStartedWidth = Math.round((data.totalNotStarted / totalResources) * 100);
                    var learningWidth = Math.round((data.totalLearning / totalResources) * 100);
                    var completedWidth = Math.round((data.totalCompleted / totalResources) * 100);
                    
                    $('.progress-bar.bg-primary').css('width', notStartedWidth + '%')
                        .attr('aria-valuenow', data.totalNotStarted)
                        .text('Favorite (' + data.totalNotStarted + ')');
                        
                    $('.progress-bar.bg-warning').css('width', learningWidth + '%')
                        .attr('aria-valuenow', data.totalLearning)
                        .text('In Progress (' + data.totalLearning + ')');
                        
                    $('.progress-bar.bg-success').css('width', completedWidth + '%')
                        .attr('aria-valuenow', data.totalCompleted)
                        .text('Completed (' + data.totalCompleted + ')');
                } else {
                    // If no resources, set all progress bars to 0
                    $('.progress-bar.bg-primary, .progress-bar.bg-warning, .progress-bar.bg-success').css('width', '0%')
                        .attr('aria-valuenow', 0);
                    $('.progress-bar').first().text('No learning resources');
                }
                
                // Check if empty state needs to be displayed
                if (data.totalLearning === 0) {
                    if ($('#tab-learning .resources-grid').length > 0) {
                        $('#tab-learning .resources-grid').remove();
                        $('#tab-learning').append(`
                            <div class="empty-state">
                                <i class="fas fa-spinner fa-3x"></i>
                                <p>You don't have any resources in progress.</p>
                                <a href="resources.php" class="btn btn-primary">Browse Learning Resources</a>
                            </div>
                        `);
                    }
                }
                
                if (data.totalNotStarted === 0) {
                    if ($('#tab-tolearn .resources-grid').length > 0) {
                        $('#tab-tolearn .resources-grid').remove();
                        $('#tab-tolearn').append(`
                            <div class="empty-state">
                                <i class="fas fa-list fa-3x"></i>
                                <p>Your favorite list is empty.</p>
                                <a href="resources.php" class="btn btn-primary">Browse Learning Resources</a>
                            </div>
                        `);
                    }
                }
                
                if (data.totalCompleted === 0) {
                    if ($('#tab-completed .resources-grid').length > 0) {
                        $('#tab-completed .resources-grid').remove();
                        $('#tab-completed').append(`
                            <div class="empty-state">
                                <i class="fas fa-check-circle fa-3x"></i>
                                <p>You haven't completed any learning resources yet.</p>
                                <a href="resources.php" class="btn btn-primary">Browse Learning Resources</a>
                            </div>
                        `);
                    }
                }
            }
        }
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
</script>

<?php include 'footer.php'; ?>