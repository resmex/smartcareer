<?php
session_start();
include '../../includes/connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.php");
    exit();
}

// Optional: Fetch events server-side for initial load
$stmt = $con->prepare("SELECT * FROM events ORDER BY date ASC");
$stmt->execute();
$initialEvents = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$popularEvent = !empty($initialEvents) ? $initialEvents[0] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartCareer | Events</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .event-card { transition: transform 0.2s ease-in-out; border-radius: 12px; }
        .event-card:hover { transform: translateY(-4px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        #refreshBtn:hover { color: #2563EB; }
    </style>
</head>
<body class="bg-gray-50">
    <?php include '../../includes/header.php'; ?>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <div id="popularEventBanner" class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl p-8 text-white mb-8 <?php echo $popularEvent ? '' : 'hidden'; ?>">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div>
                    <h1 id="popularTitle" class="text-3xl font-bold mb-2"><?php echo $popularEvent ? htmlspecialchars($popularEvent['title']) : ''; ?></h1>
                    <p id="popularDescription" class="mb-4"><?php echo $popularEvent ? htmlspecialchars($popularEvent['description']) : ''; ?></p>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center"><i class="fas fa-calendar-alt mr-2"></i><span id="popularDate"><?php echo $popularEvent ? htmlspecialchars($popularEvent['date']) : ''; ?></span></div>
                        <div class="flex items-center"><i class="fas fa-map-marker-alt mr-2"></i><span id="popularLocation"><?php echo $popularEvent ? htmlspecialchars($popularEvent['location']) : ''; ?></span></div>
                    </div>
                </div>
                <a id="popularLink" href="<?php echo $popularEvent ? htmlspecialchars($popularEvent['link']) : '#'; ?>" target="_blank" class="mt-4 md:mt-0 px-6 py-3 bg-white text-blue-600 rounded-lg hover:bg-gray-100">Register Now</a>
            </div>
        </div>

        <div class="mb-6 flex flex-col sm:flex-row justify-between items-center">
            <h2 class="text-2xl font-semibold"><span id="totalEvents" class="text-blue-600"><?php echo count($initialEvents); ?></span> Events</h2>
            <div class="mt-4 sm:mt-0 flex items-center space-x-4">
                <a href="post_event.php" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center">
                    <i class="fas fa-plus mr-2"></i> Post an Event
                </a>
                <a href="manage_event.php" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 flex items-center">
                    <i class="fas fa-cog mr-2"></i> Manage Your Events
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <div class="flex flex-col md:flex-row md:items-center gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <input type="text" id="searchInput" placeholder="Search events..." class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <select id="typeFilter" class="border rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500">
                        <option value="">All Types</option>
                        <option value="Career Fair">Career Fair</option>
                        <option value="Workshop">Workshop</option>
                        <option value="Hackathon">Hackathon</option>
                        <option value="Webinar">Webinar</option>
                        <option value="Networking">Networking</option>
                    </select>
                    <select id="locationFilter" class="border rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500">
                        <option value="">All Locations</option>
                        <option value="Dar es Salaam">Dar es Salaam</option>
                        <option value="Arusha">Arusha</option>
                        <option value="Mwanza">Mwanza</option>
                    </select>
                    <select id="dateFilter" class="border rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500">
                        <option value="">All Dates</option>
                        <option value="this-week">This Week</option>
                        <option value="this-month">This Month</option>
                        <option value="next-month">Next Month</option>
                    </select>
                    <button id="refreshBtn" class="px-4 py-2 border rounded-lg text-gray-600 hover:bg-gray-100">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
        </div>

        <div id="errorMessage" class="bg-red-100 text-red-700 p-4 rounded-lg mb-8 hidden"></div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="eventsContainer">
            <?php if (empty($initialEvents)): ?>
                <p class="text-center text-gray-600 py-8">No events found.</p>
            <?php else: ?>
                <?php foreach ($initialEvents as $event): ?>
                    <div class="event-card bg-white shadow-sm overflow-hidden">
                        <img src="<?php echo htmlspecialchars($event['image'] ?: 'https://via.placeholder.com/300x200'); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" class="w-full h-48 object-cover">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm"><?php echo htmlspecialchars($event['type']); ?></span>
                                <button class="text-gray-400 hover:text-blue-600" onclick="addToCalendar('<?php echo $event['id']; ?>')">
                                    <i class="fas fa-calendar-plus"></i>
                                </button>
                            </div>
                            <h3 class="text-lg font-semibold mb-2"><?php echo htmlspecialchars($event['title']); ?></h3>
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-calendar-alt w-5"></i><span><?php echo htmlspecialchars($event['date']); ?></span>
                                </div>
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-clock w-5"></i><span><?php echo htmlspecialchars($event['time']); ?></span>
                                </div>
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-map-marker-alt w-5"></i><span><?php echo htmlspecialchars($event['location']); ?></span>
                                </div>
                            </div>
                            <button onclick="openRegistration('<?php echo $event['id']; ?>')" class="w-full block text-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                Register Now
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div id="registrationModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
            <div class="bg-white rounded-lg p-8 max-w-md w-full">
                <h2 class="text-2xl font-bold mb-4">Event Registration</h2>
                <form id="registrationForm">
                    <input type="hidden" id="eventId" name="event_id">
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Full Name</label>
                        <input type="text" name="full_name" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Phone</label>
                        <input type="tel" name="phone" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500" required>
                    </div>
                    <div class="flex justify-end space-x-4">
                        <button type="button" class="px-4 py-2 text-gray-600 hover:text-gray-800" onclick="closeModal()">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Register</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function createEventCard(event) {
            return `
                <div class="event-card bg-white shadow-sm overflow-hidden">
                    <img src="${event.image || 'https://via.placeholder.com/300x200'}" alt="${event.title}" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">${event.type}</span>
                            <button class="text-gray-400 hover:text-blue-600" onclick="addToCalendar('${event.id}')">
                                <i class="fas fa-calendar-plus"></i>
                            </button>
                        </div>
                        <h3 class="text-lg font-semibold mb-2">${event.title}</h3>
                        <div class="space-y-2 mb-4">
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-calendar-alt w-5"></i><span>${event.date}</span>
                            </div>
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-clock w-5"></i><span>${event.time}</span>
                            </div>
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-map-marker-alt w-5"></i><span>${event.location}</span>
                            </div>
                        </div>
                        <button onclick="openRegistration('${event.id}')" class="w-full block text-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Register Now
                        </button>
                    </div>
                </div>
            `;
        }

        async function fetchEvents() {
            const container = document.getElementById('eventsContainer');
            const errorMessage = document.getElementById('errorMessage');
            const totalEvents = document.getElementById('totalEvents');
            const popularBanner = document.getElementById('popularEventBanner');
            const popularTitle = document.getElementById('popularTitle');
            const popularDescription = document.getElementById('popularDescription');
            const popularDate = document.getElementById('popularDate');
            const popularLocation = document.getElementById('popularLocation');
            const popularLink = document.getElementById('popularLink');
            const search = document.getElementById('searchInput').value;
            const type = document.getElementById('typeFilter').value;
            const location = document.getElementById('locationFilter').value;
            const date = document.getElementById('dateFilter').value;

            container.innerHTML = '<div class="loading text-center py-8"><i class="fas fa-spinner fa-spin fa-2x text-blue-600"></i><p class="mt-2 text-gray-600">Loading events...</p></div>';
            errorMessage.classList.add('hidden');
            popularBanner.classList.add('hidden');

            try {
                const url = `events_fetcher.php?search=${encodeURIComponent(search)}&type=${encodeURIComponent(type)}&location=${encodeURIComponent(location)}&date=${encodeURIComponent(date)}`;
                const response = await fetch(url);
                if (!response.ok) throw new Error(`HTTP error: ${response.status}`);
                const data = await response.json();

                if (data.error) throw new Error(data.error);

                if (data.popular_event) {
                    popularTitle.textContent = data.popular_event.title;
                    popularDescription.textContent = data.popular_event.description || 'No description available';
                    popularDate.textContent = data.popular_event.date;
                    popularLocation.textContent = data.popular_event.location;
                    popularLink.href = data.popular_event.link;
                    popularBanner.classList.remove('hidden');
                }

                const eventsData = data.events || [];
                container.innerHTML = eventsData.length ? eventsData.map(event => createEventCard(event)).join('') : '<p class="text-center text-gray-600 py-8">No events found.</p>';
                totalEvents.textContent = eventsData.length;
            } catch (error) {
                console.error('Error fetching events:', error);
                errorMessage.textContent = `Error loading events: ${error.message}. Please try again later.`;
                errorMessage.classList.remove('hidden');
                container.innerHTML = '';
            }
        }

        function openRegistration(eventId) {
            document.getElementById('eventId').value = eventId;
            document.getElementById('registrationModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('registrationModal').classList.add('hidden');
        }

        function addToCalendar(eventId) {
            alert(`Event ${eventId} added to your calendar!`);
        }

        document.addEventListener('DOMContentLoaded', () => {
            let refreshInterval = setInterval(fetchEvents, 300000);

            ['searchInput', 'typeFilter', 'locationFilter', 'dateFilter'].forEach(id => {
                document.getElementById(id).addEventListener('input', fetchEvents);
                document.getElementById(id).addEventListener('change', fetchEvents);
            });

            document.getElementById('refreshBtn').addEventListener('click', () => {
                fetchEvents();
                clearInterval(refreshInterval);
                refreshInterval = setInterval(fetchEvents, 300000);
            });

            document.getElementById('registrationModal').addEventListener('click', (e) => {
                if (e.target === e.currentTarget) closeModal();
            });

            document.getElementById('registrationForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(e.target);
                try {
                    const response = await fetch('register_event.php', {
                        method: 'POST',
                        body: formData
                    });
                    if (!response.ok) throw new Error('Failed to register');
                    const result = await response.json();
                    if (result.error) throw new Error(result.error);
                    alert('Registration successful!');
                    closeModal();
                } catch (error) {
                    alert('Error: ' + error.message);
                }
            });
        });
    </script>
</body>
</html>