<?php
// BC106294 Tracy
session_start();
$pageTitle = "Home";
include 'header.php'; // Include common header
?>

<div class="hero-section">
    <div class="container">
        <div class="hero-content">
            <h1>Discover Your AI Learning Journey</h1>
            <p class="hero-subtitle">Personalized AI learning resources, progress tracking, and professional skill development</p>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="cta-buttons">
                    <a href="dashboard.php" class="btn btn-primary btn-lg"><i class="fas fa-chart-line"></i> My Dashboard</a>
                    <a href="recommendations.php" class="btn btn-outline-light btn-lg"><i class="fas fa-lightbulb"></i> Get Recommendations</a>
                </div>
            <?php else: ?>
                <div class="cta-buttons">
                    <a href="login.php" class="btn btn-primary btn-lg"><i class="fas fa-sign-in-alt"></i> Log In</a>
                    <a href="register.php" class="btn btn-secondary btn-lg"><i class="fas fa-user-plus"></i> Sign Up</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="features-section">
    <div class="container">
        <h2 class="section-title"><i class="fas fa-star"></i> Platform Features</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-laptop-code"></i></div>
                <h3>Rich AI Learning Resources</h3>
                <p>Curated high-quality courses, articles, and datasets covering NLP, CV, and machine learning</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-brain"></i></div>
                <h3>Personalized Recommendations</h3>
                <p>Get learning resources tailored to your interests and skill level</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-tasks"></i></div>
                <h3>Learning Progress Tracking</h3>
                <p>Easily mark and track your learning status: Favorite, In Progress, and Completed</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-chart-bar"></i></div>
                <h3>Data Visualization</h3>
                <p>Understand your learning progress through intuitive charts and analytics</p>
            </div>
        </div>
    </div>
</div>

<div class="topics-section">
    <div class="container">
        <h2 class="section-title"><i class="fas fa-hashtag"></i> Popular Topics</h2>
        <div class="topics-grid">
            <a href="resources.php?topics%5B%5D=Natural+Language+Processing&search=" class="topic-card nlp">
                <h3><i class="fas fa-comment-alt"></i> Natural Language Processing</h3>
                <p>Explore text analysis, language understanding and generation</p>
            </a>
            <a href="resources.php?topics%5B%5D=Computer+Vision&search=" class="topic-card cv">
                <h3><i class="fas fa-eye"></i> Computer Vision</h3>
                <p>Learn image recognition, object detection and visual understanding</p>
            </a>
            <a href="resources.php?topics%5B%5D=Machine+Learning&search=" class="topic-card ml">
                <h3><i class="fas fa-cogs"></i> Machine Learning</h3>
                <p>Master machine learning algorithms, model training and optimization</p>
            </a>
            <a href="resources.php?topics%5B%5D=Deep+Learning&search=" class="topic-card dl">
                <h3><i class="fas fa-network-wired"></i> Deep Learning</h3>
                <p>Discover neural networks architectures and applications</p>
            </a>
            <a href="resources.php?topics%5B%5D=Reinforcement+Learning&search=" class="topic-card rl">
                <h3><i class="fas fa-robot"></i> Reinforcement Learning</h3>
                <p>Explore how AI agents learn through interaction with environments</p>
            </a>
            <a href="resources.php?topics%5B%5D=AI+Ethics&search=" class="topic-card ethics">
                <h3><i class="fas fa-balance-scale"></i> AI Ethics</h3>
                <p>Understand the ethical considerations and responsible AI use</p>
            </a>
        </div>
    </div>
</div>

<div class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2><i class="fas fa-rocket"></i> Ready to Start Learning?</h2>
            <p>Join our platform today and gain access to curated AI resources tailored to your needs</p>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <div class="cta-buttons">
                    <a href="register.php" class="btn btn-primary btn-lg">Sign Up Now</a>
                    <a href="resources.php" class="btn btn-outline-light btn-lg">Browse as Guest</a>
                </div>
            <?php else: ?>
                <div class="cta-buttons">
                    <a href="recommendations.php" class="btn btn-primary btn-lg">Get Personalized Recommendations</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.hero-section {
    background: linear-gradient(135deg, #1a237e 0%, #4a6fdc 100%);
    color: white;
    padding: 120px 0 100px;
    text-align: center;
    margin-bottom: 60px;
    position: relative;
    overflow: hidden;
}

.hero-section:before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('um.jpg') no-repeat center center;
    background-size: cover;
    opacity: 0.15;
    z-index: 0;
}

.hero-content {
    position: relative;
    z-index: 1;
    max-width: 800px;
    margin: 0 auto;
}

.hero-content h1 {
    font-size: 3.5rem;
    font-weight: 700;
    margin-bottom: 25px;
    text-shadow: 0 2px 10px rgba(0,0,0,0.2);
    animation: fadeInDown 1s ease-out;
}

.hero-subtitle {
    font-size: 1.4rem;
    margin-bottom: 40px;
    opacity: 0.9;
    max-width: 700px;
    margin-left: auto;
    margin-right: auto;
    line-height: 1.6;
    animation: fadeInUp 1s ease-out 0.3s both;
}

.cta-buttons {
    display: flex;
    justify-content: center;
    gap: 20px;
    flex-wrap: wrap;
    animation: fadeInUp 1s ease-out 0.6s both;
}

.btn-lg {
    padding: 12px 30px;
    font-size: 1.1rem;
    border-radius: 30px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
}

.btn-lg:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
}

.btn-primary {
    background: linear-gradient(to right, #4a6fdc, #6a8dff);
    border-color: transparent;
}

.btn-secondary {
    background: linear-gradient(to right, #6c757d, #868e96);
    border-color: transparent;
}

.section-title {
    text-align: center;
    margin-bottom: 50px;
    position: relative;
    padding-bottom: 15px;
    font-weight: 700;
    font-size: 2.5rem;
    color: #333;
}

.section-title:after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 4px;
    background: linear-gradient(to right, #4a6fdc, #6a8dff);
    border-radius: 2px;
}

.features-section {
    padding: 80px 0;
    background-color: #f8f9fa;
    margin-bottom: 60px;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 30px;
}

.feature-card {
    background: white;
    border-radius: 15px;
    padding: 40px 30px;
    text-align: center;
    box-shadow: 0 5px 30px rgba(0,0,0,0.05);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-top: 4px solid #4a6fdc;
}

.feature-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.1);
}

.feature-icon {
    font-size: 3rem;
    color: #4a6fdc;
    margin-bottom: 25px;
    background: #f0f4ff;
    width: 90px;
    height: 90px;
    line-height: 90px;
    border-radius: 50%;
    margin: 0 auto 25px;
}

.feature-card h3 {
    margin-bottom: 15px;
    font-weight: 600;
    color: #333;
}

.feature-card p {
    color: #666;
    line-height: 1.6;
}

.topics-section {
    padding: 80px 0;
    margin-bottom: 60px;
}

.topics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 30px;
}

.topic-card {
    border-radius: 15px;
    padding: 35px;
    color: white;
    text-decoration: none;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
    overflow: hidden;
    min-height: 200px;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
}

.topic-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.15);
    text-decoration: none;
}

.topic-card h3 {
    color: white;
    margin-bottom: 10px;
    position: relative;
    z-index: 1;
    font-weight: 600;
    font-size: 1.5rem;
}

.topic-card p {
    color: rgba(255,255,255,0.9);
    margin-bottom: 15px;
    position: relative;
    z-index: 1;
}

.topic-card:before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, rgba(0,0,0,0.1), rgba(0,0,0,0.6));
    z-index: 0;
}

.topic-card.nlp {
    background: linear-gradient(135deg, #0288d1 0%, #26c6da 100%);
}

.topic-card.cv {
    background: linear-gradient(135deg, #7b1fa2 0%, #ba68c8 100%);
}

.topic-card.ml {
    background: linear-gradient(135deg, #388e3c 0%, #81c784 100%);
}

.topic-card.dl {
    background: linear-gradient(135deg, #d32f2f 0%, #ff7043 100%);
}

.topic-card.rl {
    background: linear-gradient(135deg, #f57c00 0%, #ffca28 100%);
}

.topic-card.ethics {
    background: linear-gradient(135deg, #455a64 0%, #90a4ae 100%);
}

.cta-section {
    background: linear-gradient(135deg, #4a6fdc 0%, #6a8dff 100%);
    color: white;
    padding: 80px 0;
    text-align: center;
    margin-bottom: 60px;
    position: relative;
    overflow: hidden;
}

.cta-section:before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('https://images.unsplash.com/photo-1558494949-ef010cbdcc31?ixlib=rb-4.0.3&auto=format&fit=crop&w=1950&q=80') no-repeat center center;
    background-size: cover;
    opacity: 0.15;
    z-index: 0;
}

.cta-content {
    position: relative;
    z-index: 1;
    max-width: 800px;
    margin: 0 auto;
}

.cta-content h2 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 20px;
    text-shadow: 0 2px 10px rgba(0,0,0,0.2);
}

.cta-content p {
    font-size: 1.2rem;
    margin-bottom: 30px;
    opacity: 0.9;
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* 响应式调整 */
@media (max-width: 991px) {
    .hero-content h1 {
        font-size: 2.8rem;
    }
    
    .hero-subtitle {
        font-size: 1.2rem;
    }
    
    .section-title {
        font-size: 2rem;
    }
}

@media (max-width: 767px) {
    .hero-section {
        padding: 80px 0 70px;
    }
    
    .hero-content h1 {
        font-size: 2.3rem;
    }
    
    .hero-subtitle {
        font-size: 1.1rem;
    }
    
    .btn-lg {
        padding: 10px 20px;
        font-size: 1rem;
    }
    
    .features-grid, .topics-grid {
        grid-template-columns: 1fr;
    }
    
    .feature-card {
        padding: 30px 20px;
    }
    
    .topic-card {
        min-height: 180px;
        padding: 25px;
    }
}
</style>

<?php include 'footer.php'; // Include common footer ?>