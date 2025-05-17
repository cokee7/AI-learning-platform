<?php
// BC105164 Japser
session_start();
require_once 'database.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to continue']);
    exit();
}

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

// Return JSON data
echo json_encode([
    'success' => true,
    'totalNotStarted' => $totalNotStarted,
    'totalLearning' => $totalLearning, 
    'totalCompleted' => $totalCompleted,
    'totalResources' => $totalResources,
    'difficulties' => $difficulties,
    'mainTopics' => $mainTopics,
    'topicStatusData' => $topicStatusData,
    'difficultyStatusData' => $difficultyStatusData
]);
?> 