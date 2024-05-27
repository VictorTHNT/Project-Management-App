<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Footer Example</title>
    <!-- Latest Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Latest FontAwesome CSS -->
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
            background-color: #0080FF ;
            color: #fff;
            padding: 20px 0; /* Reduce padding */
        }
        .footer a {
            color: #fff;
            text-decoration: none;
            margin: 0 5px; /* Reduce margin between links */
        }
        .footer a:hover {
            color: black ;
        }
        .social-icons a {
            margin: 0 5px; /* Reduce margin between social icons */
            font-size: 24px;
        }
        .app-badges img {
            height: 40px;
            margin: 0 5px; /* Reduce margin between app badges */
        }
        .footer .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap; /* Wrap to ensure it fits smaller screens */
            max-width: 100%; /* Remove max-width */
            margin: 0 10px; /* Equal margin left and right */
        }
        .footer .container > div {
            flex: 1;
            text-align: center;
            padding: 5px; /* Reduce padding to each section */
        }
    </style>
</head>
<body>

    <div class="content">
        <!-- Your main content goes here -->
    </div>

    <footer class="footer mt-auto">
        <div class="container">
            <div>
                <p class="mb-0">&copy; 2024 Vaal, Inc.</p>
            </div>
            <div>
                <a href="#">Englais</a>
                <a href="#">Conditions & Confidentialité</a>
            </div>
            <div class="social-icons">
                <a href="https://twitter.com" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                <a href="https://linkedin.com" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                <a href="https://instagram.com" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="https://facebook.com" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="https://youtube.com" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
            </div>
            <div class="app-badges">
                <a href="https://www.apple.com/app-store/"><img src="https://developer.apple.com/assets/elements/badges/download-on-the-app-store.svg" alt="App Store"></a>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/js/bootstrap.min.js"></script>
</body>
</html>


