<?php
// BC104782 Seven
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$pageTitle = "User Profile";
include 'header.php';

$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "isom3012";
// Connect to database
$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $interestedTopics = isset($_POST['interested_topics']) ? implode(",", $_POST['interested_topics']) : "";
    $skillLevel = $_POST['skill_level'];

    // Check if user preferences exist, update if they do, otherwise insert
    $sqlCheck = "SELECT profile_id FROM user_profiles WHERE user_id = ?";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bind_param("i", $_SESSION['user_id']);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows > 0) {
        $sqlUpdate = "UPDATE user_profiles SET interested_topics = ?, skill_level = ? WHERE user_id = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param("ssi", $interestedTopics, $skillLevel, $_SESSION['user_id']);
        if ($stmtUpdate->execute()) {
            $profileUpdateMessage = "User profile updated successfully!";
        } else {
            $profileUpdateError = "Failed to update user profile!";
        }
        $stmtUpdate->close();
    } else {
        $sqlInsert = "INSERT INTO user_profiles (user_id, interested_topics, skill_level) VALUES (?, ?, ?)";
        $stmtInsert = $conn->prepare($sqlInsert);
        $stmtInsert->bind_param("iss", $_SESSION['user_id'], $interestedTopics, $skillLevel);
        if ($stmtInsert->execute()) {
            $profileUpdateMessage = "User profile saved successfully!";
        } else {
            $profileUpdateError = "Failed to save user profile!";
        }
        $stmtInsert->close();
    }
    $stmtCheck->close();
}

// Get user's current preferences
$sqlProfile = "SELECT interested_topics, skill_level FROM user_profiles WHERE user_id = ?";
$stmtProfile = $conn->prepare($sqlProfile);
$stmtProfile->bind_param("i", $_SESSION['user_id']);
$stmtProfile->execute();
$resultProfile = $stmtProfile->get_result();
$userProfile = $resultProfile->fetch_assoc();

// Define topics list
$availableTopics = [
    'Artificial Intelligence', 
    'Machine Learning', 
    'Deep Learning',
    'Natural Language Processing',
    'Computer Vision',
    'Reinforcement Learning',
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

$conn->close();

$interestedTopicsArray = isset($userProfile['interested_topics']) ? explode(",", $userProfile['interested_topics']) : [];
$skillLevel = isset($userProfile['skill_level']) ? $userProfile['skill_level'] : '';
?>

<div class="profile-container">
    <div class="profile-header">
        <div class="container">
            <h1><i class="fas fa-user-cog"></i> User Profile</h1>
            <p>Customize your profile and learning preferences to get personalized recommendations</p>
        </div>
    </div>

    <div class="container">
        <?php if (isset($profileUpdateMessage)): ?>
            <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeIn">
                <i class="fas fa-check-circle"></i> <?php echo $profileUpdateMessage; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($profileUpdateError)): ?>
            <div class="alert alert-danger alert-dismissible fade show animate__animated animate__headShake">
                <i class="fas fa-exclamation-circle"></i> <?php echo $profileUpdateError; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="profile-content">
            <div class="card profile-card">
                <div class="card-body">
                    <div class="profile-section">
                        <div class="section-header">
                            <h2><i class="fas fa-sliders-h"></i> Learning Preferences</h2>
                            <p>Tell us about your interests to get personalized recommendations</p>
                        </div>
                        
                        <form method="post" class="profile-form">
                            <div class="form-group skill-level-group">
                                <label for="skill_level" class="form-label">Experience Level</label>
                                <div class="skill-level-selector">
                                    <div class="form-check skill-level-option">
                                        <input class="form-check-input" type="radio" name="skill_level" value="Beginner" id="level_beginner" <?php echo $skillLevel === 'Beginner' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="level_beginner">
                                            <div class="skill-icon beginner">
                                                <i class="fas fa-seedling"></i>
                                            </div>
                                            <span>Beginner</span>
                                            <small>New to AI concepts</small>
                                        </label>
                                    </div>
                                    
                                    <div class="form-check skill-level-option">
                                        <input class="form-check-input" type="radio" name="skill_level" value="Intermediate" id="level_intermediate" <?php echo $skillLevel === 'Intermediate' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="level_intermediate">
                                            <div class="skill-icon intermediate">
                                                <i class="fas fa-tree"></i>
                                            </div>
                                            <span>Intermediate</span>
                                            <small>Familiar with basics</small>
                                        </label>
                                    </div>
                                    
                                    <div class="form-check skill-level-option">
                                        <input class="form-check-input" type="radio" name="skill_level" value="Advanced" id="level_advanced" <?php echo $skillLevel === 'Advanced' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="level_advanced">
                                            <div class="skill-icon advanced">
                                                <i class="fas fa-brain"></i>
                                            </div>
                                            <span>Advanced</span>
                                            <small>Proficient in AI</small>
                                        </label>
                                    </div>
                                    
                                    <div class="form-check skill-level-option">
                                        <input class="form-check-input" type="radio" name="skill_level" value="Various" id="level_various" <?php echo $skillLevel === 'Various' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="level_various">
                                            <div class="skill-icon various">
                                                <i class="fas fa-layer-group"></i>
                                            </div>
                                            <span>Various</span>
                                            <small>Mixed knowledge</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group topics-group">
                                <label class="form-label">Topics of Interest</label>
                                <p class="form-text text-muted">Select topics you want to learn more about</p>
                                
                                <div class="topics-section">
                                    <div class="topics-header">
                                        <h3>Primary Topics</h3>
                                    </div>
                                    <div class="topics-container">
                                        <?php 
                                        // Common topics in specified order
                                        $common_topics = [
                                            'Artificial Intelligence', 
                                            'Machine Learning', 
                                            'Deep Learning', 
                                            'Natural Language Processing', 
                                            'Computer Vision', 
                                            'Reinforcement Learning'
                                        ];
                                        
                                        foreach ($common_topics as $topic): ?>
                                            <div class="topic-checkbox">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       name="interested_topics[]" 
                                                       value="<?php echo $topic; ?>" 
                                                       id="topic_<?php echo str_replace(' ', '_', $topic); ?>"
                                                       <?php echo in_array($topic, $interestedTopicsArray) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="topic_<?php echo str_replace(' ', '_', $topic); ?>">
                                                    <?php echo $topic; ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <div class="topics-section more-topics" id="more_topics">
                                    <div class="topics-header">
                                        <h3>Specialized Topics</h3>
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="toggle_more_topics">
                                            <span class="show-text">Show More <i class="fas fa-chevron-down"></i></span>
                                            <span class="hide-text">Hide <i class="fas fa-chevron-up"></i></span>
                                        </button>
                                    </div>
                                    <div class="topics-container" style="display:none;">
                                        <?php 
                                        // Other topics in specified order
                                        $other_topics = [
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
                                        
                                        foreach ($other_topics as $topic): ?>
                                            <div class="topic-checkbox">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       name="interested_topics[]" 
                                                       value="<?php echo $topic; ?>" 
                                                       id="topic_<?php echo str_replace(['(', ')', ' '], ['', '', '_'], $topic); ?>"
                                                       <?php echo in_array($topic, $interestedTopicsArray) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="topic_<?php echo str_replace(['(', ')', ' '], ['', '', '_'], $topic); ?>">
                                                    <?php echo $topic; ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" name="update_profile" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save"></i> Save Preferences
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.profile-container {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: calc(100vh - 160px);
}

.profile-header {
    background: linear-gradient(135deg, #1a237e 0%, #4a6fdc 100%);
    color: white;
    padding: 40px 0 30px;
    margin-bottom: 30px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.profile-header h1 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 10px;
}

.profile-header p {
    font-size: 1.1rem;
    opacity: 0.9;
}

.profile-content {
    padding-bottom: 60px;
}

.profile-card {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    margin-bottom: 30px;
    border: none;
}

.section-header {
    margin-bottom: 30px;
}

.section-header h2 {
    color: #1a237e;
    font-size: 1.8rem;
    font-weight: 600;
    margin-bottom: 10px;
}

.section-header p {
    color: #666;
    font-size: 1.1rem;
}

.profile-section {
    padding: 20px;
}

.skill-level-group {
    margin-bottom: 40px;
}

.skill-level-selector {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-top: 15px;
}

.skill-level-option {
    flex: 1;
    min-width: 170px;
}

.skill-level-option .form-check-input {
    display: none;
}

.skill-level-option .form-check-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px 15px;
    border-radius: 10px;
    border: 2px solid #e9ecef;
    cursor: pointer;
    transition: all 0.2s;
    text-align: center;
}

.skill-level-option .form-check-input:checked + .form-check-label {
    border-color: #4a6fdc;
    box-shadow: 0 5px 15px rgba(74, 111, 220, 0.1);
    background-color: #f8f9ff;
}

.skill-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
    font-size: 1.8rem;
    color: white;
}

.skill-icon.beginner {
    background: linear-gradient(135deg, #4caf50, #81c784);
}

.skill-icon.intermediate {
    background: linear-gradient(135deg, #ff9800, #ffb74d);
}

.skill-icon.advanced {
    background: linear-gradient(135deg, #f44336, #e57373);
}

.skill-icon.various {
    background: linear-gradient(135deg, #9c27b0, #ba68c8);
}

.skill-level-option span {
    font-size: 1.05rem;
    font-weight: 600;
    margin-bottom: 5px;
}

.skill-level-option small {
    color: #6c757d;
    font-size: 0.85rem;
}

.topics-group {
    margin-bottom: 40px;
}

.topics-section {
    margin-bottom: 30px;
    background-color: #f8f9fa;
    border-radius: 10px;
    overflow: hidden;
}

.topics-header {
    padding: 15px 20px;
    background-color: #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.topics-header h3 {
    margin: 0;
    font-size: 1.3rem;
    color: #495057;
}

.topics-container {
    padding: 20px;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.topic-checkbox {
    background: white;
    border-radius: 30px;
    padding: 8px 15px;
    display: inline-flex;
    align-items: center;
    border: 1px solid #e9ecef;
    transition: all 0.2s;
}

.topic-checkbox:hover {
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
}

.topic-checkbox .form-check-input {
    margin-right: 8px;
}

.topic-checkbox .form-check-input:checked + .form-check-label {
    color: #4a6fdc;
    font-weight: 500;
}

#toggle_more_topics {
    padding: 5px 10px;
    font-size: 0.85rem;
}

#toggle_more_topics .hide-text {
    display: none;
}

#toggle_more_topics.active .show-text {
    display: none;
}

#toggle_more_topics.active .hide-text {
    display: inline;
}

.form-actions {
    margin-top: 40px;
    text-align: center;
}

.form-actions .btn {
    padding: 12px 30px;
    font-size: 1.1rem;
    transition: all 0.3s;
    background: linear-gradient(135deg, #1a237e 0%, #4a6fdc 100%);
    border: none;
}

.form-actions .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(26, 35, 126, 0.2);
}

.alert {
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 25px;
}

.alert-success {
    background-color: #f0fff4;
    border-left: 4px solid #28a745;
    color: #28a745;
}

.alert-danger {
    background-color: #fff6f6;
    border-left: 4px solid #dc3545;
    color: #dc3545;
}

/* Responsive design */
@media (max-width: 768px) {
    .skill-level-option {
        min-width: 130px;
    }
    
    .skill-icon {
        width: 50px;
        height: 50px;
        font-size: 1.5rem;
    }
}
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

<script>
$(document).ready(function() {
    // Show/hide more topics
    $('#toggle_more_topics').click(function() {
        $(this).toggleClass('active');
        $('#more_topics .topics-container').slideToggle();
    });
    
    // Highlight selected topics
    $('.topic-checkbox input').change(function() {
        if($(this).is(':checked')) {
            $(this).parent().css('background-color', '#eef2ff').css('border-color', '#4a6fdc');
        } else {
            $(this).parent().css('background-color', '').css('border-color', '');
        }
    });
    
    // Initialize highlighting of already selected topics
    $('.topic-checkbox input:checked').each(function() {
        $(this).parent().css('background-color', '#eef2ff').css('border-color', '#4a6fdc');
    });
});
</script>

<?php include 'footer.php'; ?>