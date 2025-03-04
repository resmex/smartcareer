<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.php");
    exit();
}

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
        .event-card {
            transition: transform 0.2s ease-in-out;
        }
        .event-card:hover {
            transform: translateY(-4px);
        }
        .calendar-day.active {
            background-color: #2563eb;
            color: white;
        }
        .calendar-day.has-event {
            border: 2px solid #2563eb;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    
    <?php include '../../includes/header.php'; ?>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Featured Event Banner -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl p-8 text-white mb-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Tech Career Fair 2025</h1>
                    <p class="mb-4">Connect with top tech companies in Tanzania</p>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            <span>March 15, 2025</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-map-marker-alt mr-2"></i>
                            <span>Diamond Plaza, Dar es Salaam</span>
                        </div>
                    </div>
                </div>
                <button class="mt-4 md:mt-0 px-6 py-3 bg-white text-blue-600 rounded-lg hover:bg-gray-100">
                    Register Now
                </button>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <div class="flex flex-col md:flex-row md:items-center gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <input type="text" placeholder="Search events..." 
                               class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <select class="border rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500">
                        <option value="">All Types</option>
                        <option value="career-fair">Career Fair</option>
                        <option value="workshop">Workshop</option>
                        <option value="seminar">Seminar</option>
                        <option value="networking">Networking</option>
                    </select>
                    <select class="border rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500">
                        <option value="">All Locations</option>
                        <option value="dar-es-salaam">Dar es Salaam</option>
                        <option value="arusha">Arusha</option>
                        <option value="mwanza">Mwanza</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Calendar View -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <div class="grid grid-cols-7 gap-2">
                <div class="text-center text-gray-600 font-semibold">Sun</div>
                <div class="text-center text-gray-600 font-semibold">Mon</div>
                <div class="text-center text-gray-600 font-semibold">Tue</div>
                <div class="text-center text-gray-600 font-semibold">Wed</div>
                <div class="text-center text-gray-600 font-semibold">Thu</div>
                <div class="text-center text-gray-600 font-semibold">Fri</div>
                <div class="text-center text-gray-600 font-semibold">Sat</div>
                <!-- Calendar days will be inserted here -->
            </div>
        </div>

        <!-- Events Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="eventsContainer">
            <!-- Event cards will be dynamically inserted here -->
        </div>
    </div>

    <!-- Registration Modal -->
    <div id="registrationModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-8 max-w-md w-full">
            <h2 class="text-2xl font-bold mb-4">Event Registration</h2>
            <form id="registrationForm">
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Full Name</label>
                    <input type="text" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Email</label>
                    <input type="email" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Phone</label>
                    <input type="tel" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500">
                </div>
                <div class="flex justify-end space-x-4">
                    <button type="button" class="px-4 py-2 text-gray-600 hover:text-gray-800" onclick="closeModal()">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Register
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Simulated events data (replace with actual API calls)
        const eventsData = [
            {
                id: 1,
                title: "Resume Writing Workshop",
                type: "Workshop",
                date: "2025-03-22",
                time: "14:00 - 16:00",
                location: "Innovation Hub, Dar es Salaam",
                organizer: "Career Development Center",
                description: "Learn how to create a compelling resume...",
                capacity: 50,
                registered: 32,
                image: "/api/placeholder/320/180"
            },
            // Add more events...
        ];

        // Function to create event card HTML
        function createEventCard(event) {
            return `
                <div class="event-card bg-white rounded-lg shadow-sm overflow-hidden">
                    <img src="${event.image}" alt="${event.title}" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                                ${event.type}
                            </span>
                            <button class="text-gray-400 hover:text-blue-600" onclick="addToCalendar(${event.id})">
                                <i class="fas fa-calendar-plus"></i>
                            </button>
                        </div>
                        <h3 class="text-lg font-semibold mb-2">${event.title}</h3>
                        <div class="space-y-2 mb-4">
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-calendar-alt w-5"></i>
                                <span>${event.date}</span>
                            </div>
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-clock w-5"></i>
                                <span>${event.time}</span>
                            </div>
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-map-marker-alt w-5"></i>
                                <span>${event.location}</span>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="flex justify-between text-sm text-gray-600 mb-1">
                                <span>Capacity</span>
                                <span>${event.registered}/${event.capacity}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 rounded-full h-2" 
                                     style="width: ${(event.registered/event.capacity)*100}%"></div>
                            </div>
                        </div>
                        <button onclick="openRegistration(${event.id})" 
                                class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Register Now
                        </button>
                    </div>
                </div>
            `;
        }

        // Function to fetch and display events
        async function fetchEvents() {
            const container = document.getElementById('eventsContainer');
            
            try {
                // Simulate API call delay
                await new Promise(resolve => setTimeout(resolve, 1000));

                container.innerHTML = eventsData.map(event => createEventCard(event)).join('');
            } catch (error) {
                console.error('Error fetching events:', error);
                container.innerHTML = '<p class="text-red-500">Error loading events. Please try again later.</p>';
            }
        }

        // Modal functions
        function openRegistration(eventId) {
            document.getElementById('registrationModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('registrationModal').classList.add('hidden');
        }

        // Calendar integration
        function addToCalendar(eventId) {
            // Implement calendar integration logic
            alert('Event added to your calendar!');
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', () => {
            fetchEvents();
            
            // Close modal when clicking outside
            document.getElementById('registrationModal').addEventListener('click', (e) => {
                if (e.target === e.currentTarget) {
                    closeModal();
                }
            });

            // Handle form submission
            document.getElementById('registrationForm').addEventListener('submit', (e) => {
                e.preventDefault();
                // Implement registration logic
                alert('Registration successful!');
                closeModal();
            });
        });
    </script>
</body>
</html>