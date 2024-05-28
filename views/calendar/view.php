<?php
include '../../includes/connect.php';
include '../../includes/navbar.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

function getProjectsAndEvents($pdo, $user_id) {
    // Fetching projects associated with the user
    $stmt = $pdo->prepare("
        SELECT p.id, p.title, DATE_FORMAT(p.start_date, '%Y-%m-%d') as start, 
               DATE_FORMAT(p.end_date, '%Y-%m-%d') as end, p.color
        FROM projects p
        JOIN user_team ut ON p.id = ut.project_id
        WHERE ut.user_id = :user_id
    ");
    $stmt->execute(['user_id' => $user_id]);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetching personal events
    $stmt = $pdo->prepare("
        SELECT e.id, e.title, DATE_FORMAT(e.start_date, '%Y-%m-%dT%H:%i:%s') as start, 
               DATE_FORMAT(e.end_date, '%Y-%m-%dT%H:%i:%s') as end, '#ff00ff' as color
        FROM calendar_events e
        WHERE e.user_id = :user_id
    ");
    $stmt->execute(['user_id' => $user_id]);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_merge($projects, $events);
}

$events = getProjectsAndEvents($pdo, $user_id);
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
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-center mb-0">Calendrier des Projets</h2>
            <a href="create.php" class="btn btn-primary">Créer</a>
        </div>
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
            events: <?php echo json_encode($events); ?>,
            eventClick: function(event) {
                if (event.id) {
                    window.location.href = '../../views/index.php?selected_project=' + event.id;
                }
            },
            eventRender: function(event, element) {
                if (event.allDay === false) {
                    event.start = moment(event.start).format('YYYY-MM-DD');
                    event.end = moment(event.end).format('YYYY-MM-DD');
                }
                element.find('.fc-title').html(event.title);
            }
        });
    });
    </script>

</body>
</html>
