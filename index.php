<?php
 // Démarrer la session
 include 'includes/connect.php';
 include 'includes/navbar.php';
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['nom']) || !isset($_SESSION['prenom'])) {
    // Rediriger vers la page de connexion
    header('Location: /Project-Management-App/views/auth/login.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Home - Project Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background: url('./assets/images/fond.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #333;
        }
        .btn-custom {
            background-color: #696969;
            border-color: #696969;
            color: #fff;
        }

        .btn-custom:hover {
            background-color: #696969
            border-color: #696969
        }

        .container {
            width: 80%;
            margin: 0 auto;
        }
        .hero {
            
            color: #fff;
            text-align: center;
            padding: 100px 0;
        }
        .hero h1 {
            font-size: 48px;
            margin-bottom: 20px;
        }
        .hero p {
            font-size: 24px;
            margin-bottom: 40px;
        }
        .hero-buttons .btn {
            padding: 15px 30px;
            border: 1px solid #fff;
            color: #fff;
            text-decoration: none;
            margin: 5px;
            border-radius: 3px;
        }
        .features, .pricing {
            padding: 60px 0;
        }
        .features h2, .pricing h2 {
            text-align: center;
            font-size: 36px;
            margin-bottom: 40px;
        }
        .feature {
            text-align: center;
            margin-bottom: 20px;
            color: white;
        }
        .feature img {
            width: 50px;
            margin-bottom: 20px;
        }
        .feature h3 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .feature p {
            font-size: 16px;
        }
        .pricing {
            background-color: #f4f5f7;
        }
        .pricing-plan {
            text-align: center;
            background-color: #fff;
            padding: 40px;
            margin: 0 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: inline-block;
            vertical-align: top;
            width: calc(33% - 40px);
        }
        .pricing-plan h3 {
            font-size: 24px;
            margin-bottom: 20px;
        }
        .pricing-plan p {
            font-size: 20px;
            margin-bottom: 20px;
        }
        .pricing-plan ul {
            list-style: none;
            padding: 0;
            margin: 0 0 20px 0;
        }
        .pricing-plan ul li {
            font-size: 16px;
            color: #555;
            margin-bottom: 10px;
        }
        .pricing-plan .btn {
            padding: 10px 20px;
            border: 1px solid #696969;
            background-color: #696969;
            color: #fff;
            text-decoration: none;
            border-radius: 3px;
        }
        .card-body {
            background-color: #fff;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .feature-section {
            background-color: #fff;
            padding: 60px 0;
        }
        .feature-section .feature-card {
            border: 1px solid #696969;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        .feature-section .feature-card img {
            width: 80px;
            margin-bottom: 20px;
        }
        .feature-section .feature-card h3 {
            font-size: 20px;
            margin-bottom: 10px;
        }
        .feature-section .feature-card p {
            font-size: 16px;
            color: #555;
            margin-bottom: 20px;
        }
        .feature-section .feature-card .btn {
            padding: 10px 20px;
            border: 1px solid #696969;
            background-color: #696969;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Projects</h5>
                        <p class="card-text">Manage your projects and track progress.</p>
                        <a href="./views/projects" class="btn btn-primary btn-custom">View Projects</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Tasks</h5>
                        <p class="card-text">Keep track of your tasks and manage them efficiently.</p>
                        <a href="./views/tasks" class="btn btn-primary btn-custom">View Tasks</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Team</h5>
                        <p class="card-text">Collaborate with your team and share updates.</p>
                        <a href="./views/" class="btn btn-primary btn-custom">View Team</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Calendar</h5>
                        <p class="card-text">Keep track of important deadlines and events.</p>
                        <a href="./views/calendar" class="btn btn-primary btn-custom">View Calendar</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Reports</h5>
                        <p class="card-text">Generate and view reports on project progress.</p>
                        <a href="./views" class="btn btn-primary btn-custom">View Reports</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Settings</h5>
                        <p class="card-text">Manage your account and application settings.</p>
                        <a href="./views" class="btn btn-primary btn-custom">View Settings</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="hero">
        <div class="container">
            <h1>The best project management solution</h1>
            <p>Organize, plan and collaborate on all your projects with our all-in-one platform.</p>
            <div class="hero-buttons">
                <a href="./views/projects" class="btn primary">Start now</a>
                
            </div>
        </div>
    </section>

    <section class="feature-section">
        <div class="container">
            <h2 class="text-center">Features to evolve efficiently</h2>
            <h3 class="text-center">Do more with Project Management</h3>
            <p class="text-center">ProjectManagement's intuitive features allow all teams to quickly configure and customize workflows for all their activities.</p>
            <div class="row">
                <div class="col-md-4">
                    <div class="feature-card">
                        <img src="./assets/images/integration.png" alt="Intégrations">
                        <h3>Integrations</h3>
                        <p>Connect the apps your team already uses to your workflow or add a Power-Up tailored to your specific needs.</p>
                        <a href="#" class="btn">Browse integrations</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <img src="./assets/images/intégration.jpeg" alt="Automatisation Butler">
                        <h3>Butler Automation</h3>
                        <p>Each board has code-free automation built into it. Focus on the most important tasks and let robots take care of the rest.</p>
                        <a href="#" class="btn">Discover automation</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <img src="./assets/images/gestion.png" alt="GestionProjet Enterprise">
                        <h3>GestionProjet Enterprise</h3>
                        <p>The productivity tool favored by teams, complete with the features and security required to scale.</p>
                        <a href="#" class="btn">Discover Enterprise</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="features">
        <div class="container">
            <div class="feature">
                <h2>Features</h2>
                <img src="./assets/images/taches.png" alt="Icone Fonctionnalité 1">
                <h3>Task management</h3>
                <p>Create, assign and track tasks efficiently.</p>
            </div>
            <div class="feature">
                <img src="./assets/images/calendrier.jpg" alt="Icone Fonctionnalité 2">
                <h3>Project schedule</h3>
                <p>Visualize deadlines and plan your projects on an intuitive calendar.</p>
            </div>
            <div class="feature">
                <img src="./assets/images/collaboration.png" alt="Icone Fonctionnalité 3">
                <h3>Real-time collaboration</h3>
                <p>Work together in real time with your team.</p>
            </div>
        </div>
    </section>

    <section class="pricing">
        <div class="container">
            <h2>Prices</h2>
            <div class="d-flex justify-content-center flex-wrap">
                <div class="pricing-plan">
                    <h3>Basic</h3>
                    <p>Free</p>
                    <p>For small teams</p>
                    <ul>
                        <li>Up to 10 projects</li>
                        <li>5 users</li>
                        <li>Basic features</li>
                    </ul>
                    <a href="./views/auth/register.php" class="btn primary">Register</a>
                </div>
                <div class="pricing-plan">
                    <h3>Professional</h3>
                    <p>29€/mois</p>
                    <p>For growing teams</p>
                    <ul>
                        <li>Unlimited projects</li>
                        <li>Unlimited users</li>
                        <li>Advanced Features</li>
                    </ul>
                    <a href="./views/auth/register.php" class="btn primary">Register</a>
                </div>
            </div>
        </div>
    </section>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
</body>
</html>
