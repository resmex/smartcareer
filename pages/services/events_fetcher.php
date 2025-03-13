<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');
error_reporting(E_ALL);

try {
    require __DIR__ . '/../../vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->load();
} catch (Exception $e) {
    die(json_encode(["error" => "Failed to load environment: " . $e->getMessage()]));
}

// Configuration
$cacheTime = 300; // 5 minutes
$dataDir = __DIR__ . '/data';
$cacheFile = $dataDir . '/events.json';
$logFile = $dataDir . '/api_errors.log';

// Load credentials
$privateToken = $_ENV['EVENTBRITE_PRIVATE_TOKEN'] ?? '';
$publicToken = $_ENV['EVENTBRITE_PUBLIC_TOKEN'] ?? '';
$apiKey = $_ENV['EVENTBRITE_API_KEY'] ?? '';
$clientSecret = $_ENV['EVENTBRITE_CLIENT_SECRET'] ?? '';
$clientId = $_ENV['EVENTBRITE_CLIENT_ID'] ?? '';
$orgId = $_ENV['EVENTBRITE_ORG_ID'] ?? '';

$fallbackEvents = [
    ["id" => 1, "title" => "Tech Career Expo 2025", "type" => "Career Fair", "date" => "2025-03-25", "time" => "9:00 AM - 5:00 PM", "location" => "Dar es Salaam", "image" => "https://via.placeholder.com/300x200?text=Tech+Career+Expo", "link" => "https://example.com/events/tech-career-expo"],
    ["id" => 2, "title" => "Digital Marketing Workshop", "type" => "Workshop", "date" => "2025-04-10", "time" => "2:00 PM - 6:00 PM", "location" => "Arusha", "image" => "https://via.placeholder.com/300x200?text=Marketing+Workshop", "link" => "https://example.com/events/marketing-workshop"],
    ["id" => 3, "title" => "Tanzania Tech Hackathon", "type" => "Hackathon", "date" => "2025-05-05", "time" => "All day", "location" => "Mwanza", "image" => "https://via.placeholder.com/300x200?text=Tech+Hackathon", "link" => "https://example.com/events/tech-hackathon"]
];

if (!is_dir($dataDir) && !mkdir($dataDir, 0777, true)) {
    die(json_encode(["error" => "Failed to create cache directory"]));
}

$filters = array_map('htmlspecialchars', $_GET);

// Check for user-posted events first
$events = [];
if (file_exists($cacheFile)) {
    $events = json_decode(file_get_contents($cacheFile), true);
    if (!is_array($events)) {
        $events = [];
    }
    $events = filterEvents($events, $filters);
}

if (empty($events) || isset($_GET['refresh'])) {
    $authAttempts = [];
    if (!empty($privateToken)) {
        $authAttempts[] = ["type" => "Private Token (User)", "url" => "https://www.eventbriteapi.com/v3/users/me/events/?token=$privateToken&expand=venue&status=live"];
        if (!empty($orgId)) {
            $authAttempts[] = ["type" => "Private Token (Org)", "url" => "https://www.eventbriteapi.com/v3/organizations/$orgId/events/?token=$privateToken&expand=venue&status=live"];
        }
    }
    if (!empty($publicToken)) {
        $authAttempts[] = ["type" => "Public Token (User)", "url" => "https://www.eventbriteapi.com/v3/users/me/events/?token=$publicToken&expand=venue&status=live"];
    }

    foreach ($authAttempts as $attempt) {
        $eventbriteApiUrl = $attempt['url'];
        if (!empty($filters['location'])) $eventbriteApiUrl .= "&location.address=" . urlencode($filters['location']);
        if (!empty($filters['search'])) $eventbriteApiUrl .= "&q=" . urlencode($filters['search']);
        if (!empty($filters['date'])) {
            switch ($filters['date']) {
                case 'this-week': $start = date('Y-m-d\TH:i:s', strtotime('monday this week')); $end = date('Y-m-d\TH:i:s', strtotime('sunday this week')); break;
                case 'this-month': $start = date('Y-m-d\TH:i:s', strtotime('first day of this month')); $end = date('Y-m-d\TH:i:s', strtotime('last day of this month')); break;
                case 'next-month': $start = date('Y-m-d\TH:i:s', strtotime('first day of next month')); $end = date('Y-m-d\TH:i:s', strtotime('last day of next month')); break;
                default: $start = $end = null;
            }
            if ($start && $end) $eventbriteApiUrl .= "&start_date.range_start=$start&start_date.range_end=$end";
        }

        $apiEvents = fetchEventsFromSource($eventbriteApiUrl, $attempt['type']);
        if (!isset($apiEvents['error'])) {
            $events = array_merge($events, $apiEvents);
            break;
        }
    }

    if (empty($events) || isset($events['error'])) {
        $events = $fallbackEvents;
    }

    if (file_exists($cacheFile)) {
        $userEvents = json_decode(file_get_contents($cacheFile), true);
        if (is_array($userEvents)) {
            $events = array_merge($userEvents, $events);
        }
    }
}

$events = enrichEventData($events);

// Determine the "popular" event (e.g., the most recent one)
$popularEvent = !empty($events) ? $events[0] : $fallbackEvents[0]; // First event as "popular" for simplicity
$allEvents = $events;

// Output structure with popular event and full list
$output = [
    "popular_event" => $popularEvent,
    "events" => $allEvents
];

echo json_encode($output);
exit;

function fetchEventsFromSource($url, $authType) {
    global $logFile;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36 Edg/129.0.0.0");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Accept: application/json"]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "[$authType] API URL: $url\nHTTP Code: $httpCode\nResponse: " . substr($response, 0, 500) . "...\n", FILE_APPEND);

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        return ["error" => "cURL failed: $error"];
    }

    curl_close($ch);
    if ($httpCode != 200) {
        return ["error" => "HTTP error: $httpCode"];
    }

    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ["error" => "JSON parse error: " . json_last_error_msg()];
    }
    if (empty($data['events'])) {
        return [];
    }

    return parseEventbriteEvents($data['events']);
}

function parseEventbriteEvents($events) {
    $parsedEvents = [];
    foreach ($events as $index => $event) {
        $startDate = $event['start']['local'] ?? 'TBD';
        $time = $startDate !== 'TBD' ? (new DateTime($startDate))->format('h:i A') : 'TBD';
        $date = $startDate !== 'TBD' ? (new DateTime($startDate))->format('Y-m-d') : 'TBD';
        $imageUrl = $event['logo']['url'] ?? "https://via.placeholder.com/300x200?text=" . urlencode(substr($event['name']['text'] ?? 'Event', 0, 20));
        $parsedEvents[] = [
            "id" => $event['id'] ?? $index + 1,
            "title" => $event['name']['text'] ?? 'Unknown Event',
            "type" => determineEventType($event['name']['text'] ?? ''),
            "date" => $date,
            "time" => $time,
            "location" => isset($event['venue']['address']['localized_multi_line_address_display']) 
                ? implode(', ', $event['venue']['address']['localized_multi_line_address_display']) 
                : 'TBD',
            "image" => filter_var($imageUrl, FILTER_VALIDATE_URL) ? $imageUrl : "https://via.placeholder.com/300x200?text=Event",
            "link" => $event['url'] ?? '#'
        ];
    }
    return $parsedEvents;
}

function determineEventType($title) {
    $title = strtolower($title);
    if (strpos($title, 'fair') !== false || strpos($title, 'expo') !== false) return "Career Fair";
    if (strpos($title, 'workshop') !== false || strpos($title, 'training') !== false) return "Workshop";
    if (strpos($title, 'hackathon') !== false || strpos($title, 'competition') !== false) return "Hackathon";
    if (strpos($title, 'webinar') !== false || strpos($title, 'online') !== false) return "Webinar";
    if (strpos($title, 'networking') !== false || strpos($title, 'meetup') !== false) return "Networking";
    return "Event";
}

function enrichEventData($events) {
    foreach ($events as &$event) {
        if ($event['date'] !== "TBD") {
            try {
                $dateObj = new DateTime($event['date']);
                $event['formatted_date'] = $dateObj->format('F j, Y');
                $event['sort_date'] = $dateObj->format('Y-m-d');
            } catch (Exception $e) {
                $event['formatted_date'] = $event['date'];
                $event['sort_date'] = $event['date'];
            }
        } else {
            $event['formatted_date'] = "TBD";
            $event['sort_date'] = "9999-12-31";
        }
        $event['description'] = $event['description'] ?? "Join us for this exciting " . strtolower($event['type']) . " in " . $event['location'] . ".";
    }
    usort($events, fn($a, $b) => strcmp($a['sort_date'], $b['sort_date']));
    return $events;
}

function filterEvents($events, $filters) {
    return array_filter($events, function($event) use ($filters) {
        $matchesSearch = empty($filters['search']) || stripos($event['title'], $filters['search']) !== false || stripos($event['description'], $filters['search']) !== false;
        $matchesType = empty($filters['type']) || stripos($event['type'], $filters['type']) !== false;
        $matchesLocation = empty($filters['location']) || stripos($event['location'], $filters['location']) !== false;
        $matchesDate = true;
        if (!empty($filters['date']) && $event['sort_date'] !== "9999-12-31") {
            $eventDate = new DateTime($event['sort_date']);
            switch ($filters['date']) {
                case 'this-week':
                    $start = new DateTime('monday this week');
                    $end = new DateTime('sunday this week');
                    break;
                case 'this-month':
                    $start = new DateTime('first day of this month');
                    $end = new DateTime('last day of this month');
                    break;
                case 'next-month':
                    $start = new DateTime('first day of next month');
                    $end = new DateTime('last day of next month');
                    break;
                default:
                    $start = $end = null;
            }
            $matchesDate = $start && $end ? ($eventDate >= $start && $eventDate <= $end) : true;
        }
        return $matchesSearch && $matchesType && $matchesLocation && $matchesDate;
    });
}
?>