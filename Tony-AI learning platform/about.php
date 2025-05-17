<?php
session_start();
$pageTitle = "About Us";
include 'header.php';
?>
<!-- Main container for about page content -->
<div class="about-container">
    <div class="about-hero">
        <div class="hero-content">
            <h1><i class="fas fa-info-circle"></i> About TonyAI</h1>
            <p class="lead">Making AI learning accessible and personalized</p>
        </div>
    </div>

    <div class="about-content">
        <div class="about-card">
            <h2><i class="fas fa-rocket"></i> Our Purpose</h2>
            <p>TonyAI was created to solve the problem of information overload in AI education. We help learners cut through the noise and find resources that match their skill level and interests.</p>
        </div>
 <!-- Three-column content section (becomes single column on mobile) -->
        <div class="about-card">
            <h2><i class="fas fa-cogs"></i> How It Works</h2>
            <!-- Feature list with checkmark icons -->
            <ul class="feature-list">
                <li><i class="fas fa-check-circle"></i> Personalized recommendations based on your goals</li>
                <li><i class="fas fa-check-circle"></i> Simple progress tracking (Start/In Progress/Completed)</li>
                <li><i class="fas fa-check-circle"></i> Quality-curated resources from trusted sources</li>
            </ul>
        </div>

        <div class="about-card">
            <h2><i class="fas fa-users"></i> Who It's For</h2>
            <div class="audience-grid">
                <div class="audience-item">
                    <i class="fas fa-graduation-cap"></i>
                    <h3>Students</h3>
                </div>
                <div class="audience-item">
                    <i class="fas fa-briefcase"></i>
                    <h3>Professionals</h3>
                </div>
                <div class="audience-item">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <h3>Educators</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.about-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.about-hero {
    background: linear-gradient(135deg, #4a6fdc 0%, #1a237e 100%);
    color: white;
    padding: 80px 20px;
    text-align: center;
    border-radius: 10px;
    margin-bottom: 40px;
}

.about-hero h1 {
    font-size: 2.5rem;
    margin-bottom: 15px;
}

.about-hero .lead {
    font-size: 1.3rem;
    opacity: 0.9;
}

.about-content {
    display: grid;
    gap: 30px;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
}

.about-card {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.about-card h2 {
    color: #1a237e;
    margin-bottom: 20px;
    font-size: 1.5rem;
}

.feature-list {
    list-style: none;
    padding: 0;
}

.feature-list li {
    margin-bottom: 15px;
    padding-left: 30px;
    position: relative;
}

.feature-list i {
    color: #4a6fdc;
    position: absolute;
    left: 0;
    top: 3px;
}

.audience-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    text-align: center;
}

.audience-item i {
    font-size: 2rem;
    color: #4a6fdc;
    margin-bottom: 10px;
}

.audience-item h3 {
    font-size: 1.1rem;
    color: #333;
}

@media (max-width: 768px) {
    .about-hero {
        padding: 60px 20px;
    }
    
    .audience-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include 'footer.php'; ?>