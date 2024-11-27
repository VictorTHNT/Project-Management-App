<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Home - Project Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* Tous les styles précédents restent identiques */
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            color: #333;
        }

        .hero-section {
            background: linear-gradient(135deg, #0061f2 0%, #00ba88 100%);
            padding: 100px 0;
            color: white;
            text-align: center;
        }

        .hero-section h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }

        .hero-section p {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .feature-cards {
            margin-top: -50px;
            padding-bottom: 50px;
        }

        .feature-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-5px);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: #f0f9ff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .stats-section {
            background: #fff;
            padding: 40px 0;
        }

        .stat-item {
            text-align: center;
            padding: 20px;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #0061f2;
            margin-bottom: 10px;
        }

        .workflow-section {
            padding: 80px 0;
            background: #f8f9fa;
        }

        .workflow-step {
            text-align: center;
            padding: 30px;
        }

        .workflow-number {
            width: 40px;
            height: 40px;
            background: #0061f2;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-weight: bold;
        }

        .cta-section {
            background: linear-gradient(135deg, #00ba88 0%, #0061f2 100%);
            padding: 80px 0;
            color: white;
            text-align: center;
        }

        /* Nouveaux styles pour les boutons */
        .btn-custom-primary {
            background: linear-gradient(45deg, #0061f2, #00ba88);
            color: white;
            padding: 15px 35px;
            border-radius: 30px;
            border: none;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 97, 242, 0.3);
            display: inline-block;
        }

        .btn-custom-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 97, 242, 0.4);
            color: white;
        }

        .btn-custom-secondary {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            color: white;
            padding: 15px 35px;
            border-radius: 30px;
            border: 2px solid rgba(255, 255, 255, 0.5);
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .btn-custom-secondary:hover {
            background: white;
            color: #0061f2;
            transform: translateY(-3px);
        }

        /* Styles pour la nouvelle section de tarifs */
        .pricing-section {
            padding: 80px 0;
            background: #f8f9fa;
        }

        .pricing-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            height: 100%;
        }

        .pricing-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        }

        .pricing-card.featured {
            border: 2px solid #0061f2;
        }

        .pricing-card.featured::before {
            content: 'Popular';
            position: absolute;
            top: 20px;
            right: -35px;
            background: #0061f2;
            color: white;
            padding: 5px 40px;
            transform: rotate(45deg);
            font-size: 14px;
            font-weight: 600;
        }

        .price {
            font-size: 3rem;
            font-weight: 700;
            color: #0061f2;
            margin: 5px 0;
        }

        .price span {
            font-size: 1rem;
            color: #6c757d;
        }

        .feature-list {
            list-style: none;
            padding: 0;
            margin: 30px 0;
        }

        .feature-list li {
            padding: 10px 0;
            color: #6c757d;
            display: flex;
            align-items: center;
        }

        .feature-list li::before {
            content: "✓";
            color: #00ba88;
            font-weight: bold;
            margin-right: 10px;
        }

        .btn-pricing {
            width: 100%;
            padding: 15px;
            border-radius: 30px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            display: inline-block;
        }

        .btn-pricing.primary {
            background: linear-gradient(45deg, #0061f2, #00ba88);
            color: white;
            border: none;
        }

        .btn-pricing.secondary {
            background: white;
            color: #0061f2;
            border: 2px solid #0061f2;
        }

        .btn-pricing:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 97, 242, 0.2);
        }
    </style>
</head>
<body>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VAAL - Navbar</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: white; /* Fond blanc */
            color: black; /* Texte noir */
        }

        .navbar .logo-container {
            display: flex;
            align-items: center;
        }

        .navbar .logo-container img {
            width: 150px; /* Taille du logo */
            height: 40px;
            margin-right: 10px; /* Espace entre le logo et le texte */
        }

        .navbar .logo-container .brand-name {
            font-size: 24px;
            font-weight: bold;
        }

        .navbar .btn-register {
            padding: 10px 20px;
            background-color: white;
            color: black;
            border: 2px solid #0061f2; /* Bordure bleue pour plus de contraste */
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none; /* Supprime le soulignement pour le lien */
            transition: 0.3s;
        }

        .navbar .btn-register:hover {
            background-color: #0061f2;
            color: white; /* Texte blanc sur fond bleu au survol */
        }

        #bouton1{
            margin-top: 130px;
        }

        #bouton2{
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="logo-container">
            <img src="assets/images/vaal_logo_noir.png" alt="Logo"> 
        </div>
        <a href="views/auth/register.php" class="btn-register">Inscription</a>
    </div>
</body>
</html>


    
    <section class="hero-section">
        <div class="container">
            <h1>Gérez vos projets avec efficacité</h1>
            <p>Une solution complète pour planifier, suivre et collaborer sur vos projets d'entreprise</p>
            <a href="./views/auth/register.php" class="btn-custom-primary mx-2">Commencer gratuitement</a>
            <a href="" class="btn-custom-secondary mx-2">En savoir plus</a>
        </div>
    </section>

    <section class="feature-cards">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <img src="./assets/images/taches.png" alt="Tasks" width="30">
                        </div>
                        <h3>Gestion des tâches</h3>
                        <p>Organisez et suivez toutes vos tâches avec des tableaux Kanban intuitifs</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <img src="./assets/images/collaboration.png" alt="Team" width="30">
                        </div>
                        <h3>Collaboration d'équipe</h3>
                        <p>Travaillez ensemble en temps réel avec des outils de communication intégrés</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <img src="./assets/images/calendrier.jpg" alt="Calendar" width="30">
                        </div>
                        <h3>Planification avancée</h3>
                        <p>Visualisez et gérez les délais avec notre calendrier interactif</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="workflow-section">
        <div class="container">
            <h2 class="text-center mb-5">Comment ça marche</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="workflow-step">
                        <div class="workflow-number">1</div>
                        <h3>Créez votre projet</h3>
                        <p>Définissez vos objectifs et structurez votre projet en quelques clics</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="workflow-step">
                        <div class="workflow-number">2</div>
                        <h3>Invitez votre équipe</h3>
                        <p>Collaborez facilement avec tous les membres de votre équipe</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="workflow-step">
                        <div class="workflow-number">3</div>
                        <h3>Suivez les progrès</h3>
                        <p>Visualisez l'avancement et optimisez votre productivité</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <section class="cta-section">
        <div class="container">
            <h2 class="mb-4">Prêt à optimiser votre gestion de projet ?</h2>
            <p class="mb-4">Rejoignez des milliers d'entreprises qui font confiance à notre solution</p>
            <a href="" class="btn btn-light btn-lg">Commencer maintenant</a>
        </div>
    </section>
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <div class="stat-item">
                        <div class="stat-number">10K+</div>
                        <p>Projets gérés</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-item">
                        <div class="stat-number">50K+</div>
                        <p>Utilisateurs actifs</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-item">
                        <div class="stat-number">98%</div>
                        <p>Satisfaction client</p>
                    </div>
                </div>
            </div>
        </div>
    </section>


    
    <section class="pricing-section" id="pricing">
        <div class="container">
            <h2 class="text-center mb-5">Choisissez votre plan</h2>
            <div class="row g-4">
                
                <div class="col-md-4">
                    <div class="pricing-card">
                        <h3>Gratuit</h3>
                        <div class="price">0€ <span>/mois</span></div>
                        <p>Parfait pour débuter</p>
                        <ul class="feature-list">
                            <li>Jusqu'à 5 projets</li>
                            <li>2 membres d'équipe</li>
                            <li>Tableau Kanban basique</li>
                            <li>Support par email</li>
                        </ul>
                        <a href="" class="btn-pricing secondary" id="bouton1">Commencer gratuitement</a>
                    </div>
                </div>

            
                <div class="col-md-4">
                    <div class="pricing-card featured">
                        <h3>Pro</h3>
                        <div class="price">29€ <span>/mois</span></div>
                        <p>Pour les équipes en croissance</p>
                        <ul class="feature-list">
                            <li>Projets illimités</li>
                            <li>Jusqu'à 15 membres</li>
                            <li>Tableaux avancés</li>
                            <li>Support prioritaire</li>
                            <li>Rapports personnalisés</li>
                            <li>Intégrations avancées</li>
                        </ul>
                        <a href="" class="btn-pricing primary" id="bouton2">Commencer l'essai Pro</a>
                    </div>
                </div>

                
                <div class="col-md-4">
                    <div class="pricing-card">
                        <h3>Enterprise</h3>
                        <div class="price">99€ <span>/mois</span></div>
                        <p>Pour les grandes entreprises</p>
                        <ul class="feature-list">
                            <li>Tout illimité</li>
                            <li>Membres illimités</li>
                            <li>Support 24/7 dédié</li>
                            <li>Sécurité avancée</li>
                            <li>API personnalisée</li>
                            <li>Formation sur mesure</li>
                            <li>SLA garanti</li>
                        </ul>
                        <a href="" class="btn-pricing secondary">Contacter les ventes</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Footer Example</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
        }

        .content {
            flex: 1;
        }

        .footer {
            background-color: #343a40;
            color: #fff;
            padding: 20px 10px;
        }

        .footer a {
            color: #fff;
            text-decoration: none;
            margin: 0 10px;
        }

        .footer a:hover {
            color: black;
        }

        .social-icons a {
            margin: 0 10px;
            font-size: 24px;
        }

        .app-badges img {
            height: 40px;
            margin-left: 10px;
        }

        .footer .container {
            display: flex;
            justify-content: space-between; /* Répartir les blocs horizontalement */
            align-items: center; /* Centrer verticalement */
            flex-wrap: wrap; /* Empêcher les débordements */
        }

        .footer .container > div {
            flex: 1;
            text-align: center;
        }

        @media (min-width: 768px) {
            .footer .container > div:first-child {
                text-align: left;
            }

            .footer .container > div:last-child {
                text-align: right;
            }
        }
    </style>
</head>
<body>

    <div class="content">
        
    </div>

    <footer class="footer mt-auto">
        <div class="container">
            <!-- Left Section -->
            <div>
                <p class="mb-0">&copy; 2024 Vaal, Inc.</p>
            </div>

            <!-- Middle Section -->
            <div>
                <a href="#">Englais</a>
                <a href="#">Conditions & Confidentialité</a>
            </div>

            <!-- Social Icons -->
            <div class="social-icons">
                <a href="https://twitter.com" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                <a href="https://linkedin.com" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                <a href="https://instagram.com" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="https://facebook.com" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="https://youtube.com" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
            </div>

            <!-- App Store Badge -->
            <div class="app-badges">
                <a href="https://www.apple.com/app-store/">
                    <img src="https://developer.apple.com/assets/elements/badges/download-on-the-app-store.svg" alt="App Store">
                </a>
            </div>
        </div>
    </footer>

</body>
</html>