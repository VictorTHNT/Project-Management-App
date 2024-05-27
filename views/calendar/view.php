<?php
session_start();
require_once '../../includes/connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

function getProjects($pdo, $user_id) {
    $stmt = $pdo->prepare("
        SELECT p.title, DATE_FORMAT(p.start_date, '%Y-%m-%d') as start, 
               DATE_FORMAT(p.end_date, '%Y-%m-%d') as end, p.color
        FROM projects p
        WHERE p.manager_id = :user_id
    ");
    $stmt->execute(['user_id' => $user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$projects = getProjects($pdo, $user_id);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Calendrier des Projets</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.0/fullcalendar.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.0/fullcalendar.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa; /* Light grey background for a softer look */
            font-family: 'Arial', sans-serif; /* Simple and modern font */
        }
        #calendar {
            max-width: 800px;
            margin: 40px auto;
            padding: 15px;
            background-color: #ffffff; /* White background for the calendar */
            box-shadow: 0 4px 8px rgba(0,0,0,0.1); /* Subtle shadow for depth */
            border-radius: 10px; /* Rounded corners */
        }
        .fc-toolbar h2 {
            font-size: 1.5rem; /* Larger text for clarity */
            color: #333333; /* Dark grey for better readability */
        }
        .fc-button {
            background-color: #ffffff; /* Buttons that blend into the toolbar */
            color: #333333;
            border: 1px solid #dddddd; /* Slight border to distinguish the buttons */
            margin-left: 5px;
        }
        .fc-button:hover {
            background-color: #eeeeee; /* Light grey background on hover for button interaction */
        }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; // Include the navigation bar ?>
    <div class="container">
        <h2 class="text-center mb-4">Calendrier des Projets</h2> <!-- Centered heading for the calendar page -->
        <div id="calendar"></div>
    </div>
    <script>
    $(document).ready(function() {
        $('#calendar').fullCalendar({
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            defaultView: 'month',
            defaultDate: moment().format('YYYY-MM-DD'),
            editable: true,
            eventLimit: true, // allow "more" link when too many events
            events: <?php echo json_encode($projects); ?>
        });
    });
    </script>
</body>
</html>
