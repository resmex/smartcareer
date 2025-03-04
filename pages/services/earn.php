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
    <title>Income Opportunities - 100+ Ways to Earn Online</title>
    <link rel="stylesheet" href="../../assets/css/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen">
    <?php include '../../includes/header.php'; ?>

    
    <!-- Header Section -->
    <header class="bg-gradient-to-r from-orange-600 to-pink-600 text-white py-16 relative overflow-hidden">
        <!-- Animated Background Shapes -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute w-64 h-64 bg-white rounded-full -top-32 -left-32"></div>
            <div class="absolute w-48 h-48 bg-white rounded-full bottom-0 right-0"></div>
        </div>
    
        <div class="container mx-auto px-6 relative z-10">
            <div class="bg-white bg-opacity-90 backdrop-blur-md rounded-xl p-8 shadow-2xl transform transition-all hover:scale-105 duration-300">
                <h1 class="text-5xl font-extrabold text-orange-600 mb-2 leading-tight">
                    100+ Ways to Make Money Online
                    <!-- <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-600 to-pink-600">Make Money Online</span> -->
                </h1>
                <p class="text-xl font-bold text-gray-700 mb-8">
                    Discover legitimate opportunities to earn while building your career. Start your journey to financial freedom today!
                </p>
    
                <!-- Quick Stats -->
                <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-shadow duration-300 border border-gray-200">
                        <div class="text-4xl font-bold text-orange-600 mb-2">100+</div>
                        <div class="text-lg text-gray-800 font-semibold">Income Sources</div>
                        <p class="text-base text-gray-700 mt-2">Explore a wide range of opportunities tailored for you.</p>
                    </div>
                    <div class="bg-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-shadow duration-300 border border-gray-200">
                        <div class="text-4xl font-bold text-pink-600 mb-2">$0</div>
                        <div class="text-lg text-gray-800 font-semibold">Initial Investment</div>
                        <p class="text-base text-gray-700 mt-2">Start earning with no upfront costs.</p>
                    </div>
                    <div class="bg-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-shadow duration-300 border border-gray-200">
                        <div class="text-4xl font-bold text-purple-600 mb-2">24/7</div>
                        <div class="text-lg text-gray-800 font-semibold">Earning Potential</div>
                        <p class="text-base text-gray-700 mt-2">Work anytime, anywhere, and earn on your own terms.</p>
                    </div>
                </div>
                
    
                <!-- Call to Action -->
                <div class="mt-12 text-center">
                    <a href="#get-started" 
                       class="inline-block bg-transparent text-orange-600 border-2 border-orange-600 px-8 py-4 rounded-full font-semibold hover:bg-orange-600 hover:text-white hover:shadow-lg hover:scale-105 transition-all duration-300">
                        Get Started Now
                    </a>
                </div
                
                
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main id="get-started" class="container mx-auto px-4 py-12">
        <!-- Category Navigation -->
        <div class="flex flex-wrap gap-4 mb-8">
            <button onclick="filterCategory('all')" class="px-4 py-2 rounded-full bg-orange-600 text-white hover:bg-orange-700">All Opportunities</button>
            <button onclick="filterCategory('freelance')" class="px-4 py-2 rounded-full bg-gray-200 hover:bg-gray-300">Freelancing</button>
            <button onclick="filterCategory('content')" class="px-4 py-2 rounded-full bg-gray-200 hover:bg-gray-300">Content Creation</button>
            <button onclick="filterCategory('passive')" class="px-4 py-2 rounded-full bg-gray-200 hover:bg-gray-300">Passive Income</button>
            <button onclick="filterCategory('micro')" class="px-4 py-2 rounded-full bg-gray-200 hover:bg-gray-300">Micro Tasks</button>
            <button onclick="filterCategory('teaching')" class="px-4 py-2 rounded-full bg-gray-200 hover:bg-gray-300">Teaching & Coaching</button>
            <button onclick="filterCategory('investment')" class="px-4 py-2 rounded-full bg-gray-200 hover:bg-gray-300">Investing</button>
            <button onclick="filterCategory('social')" class="px-4 py-2 rounded-full bg-gray-200 hover:bg-gray-300">Social Earning</button>
            <button onclick="filterCategory('other')" class="px-4 py-2 rounded-full bg-gray-200 hover:bg-gray-300">Other Ways</button>
        </div>

        <!-- Opportunities Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Freelancing Section -->
            <div class="category-section" data-category="freelance">
                <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition">
                    <div class="text-2xl text-orange-600 mb-4"><i class="fas fa-laptop-code"></i></div>
                    <h2 class="text-xl font-bold mb-4">Freelancing Platforms</h2>
                    <ul class="space-y-3">
                        <li><a href="https://www.upwork.com" target="_blank" class="text-blue-600 hover:underline">Upwork</a> - General freelancing</li>
                        <li><a href="https://www.fiverr.com" target="_blank" class="text-blue-600 hover:underline">Fiverr</a> - Service-based freelancing</li>
                        <li><a href="https://www.toptal.com" target="_blank" class="text-blue-600 hover:underline">Toptal</a> - Elite freelancing</li>
                        <li><a href="https://www.peopleperhour.com" target="_blank" class="text-blue-600 hover:underline">PeoplePerHour</a> - Hourly projects</li>
                        <li><a href="https://www.guru.com" target="_blank" class="text-blue-600 hover:underline">Guru</a> - Professional freelancing</li>
                    </ul>
                </div>
            </div>

            <!-- Virtual Assistance -->
            <div class="category-section" data-category="freelance">
                <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition">
                    <div class="text-2xl text-orange-600 mb-4"><i class="fas fa-tasks"></i></div>
                    <h2 class="text-xl font-bold mb-4">Virtual Assistance</h2>
                    <ul class="space-y-3">
                        <li><a href="https://www.fancy-hands.com" target="_blank" class="text-blue-600 hover:underline">Fancy Hands</a> - Task-based VA work</li>
                        <li><a href="https://www.belay.com" target="_blank" class="text-blue-600 hover:underline">Belay</a> - Professional VA services</li>
                        <li><a href="https://www.woodbows.com" target="_blank" class="text-blue-600 hover:underline">WoodBows</a> - Full-time VA positions</li>
                        <li><a href="https://www.equivity.com" target="_blank" class="text-blue-600 hover:underline">Equivity</a> - Legal & professional VA</li>
                        <li><a href="https://www.fancyhands.com" target="_blank" class="text-blue-600 hover:underline">Fancy Hands</a> - Micro-tasks VA</li>
                    </ul>
                </div>
            </div>

            <!-- Content Creation -->
            <div class="category-section" data-category="content">
                <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition">
                    <div class="text-2xl text-orange-600 mb-4"><i class="fas fa-pen-fancy"></i></div>
                    <h2 class="text-xl font-bold mb-4">Content Creation</h2>
                    <ul class="space-y-3">
                        <li><a href="https://medium.com/creators" target="_blank" class="text-blue-600 hover:underline">Medium</a> - Article writing</li>
                        <li><a href="https://www.youtube.com/creators" target="_blank" class="text-blue-600 hover:underline">YouTube</a> - Video content</li>
                        <li><a href="https://anchor.fm" target="_blank" class="text-blue-600 hover:underline">Anchor</a> - Podcast creation</li>
                        <li><a href="https://www.substack.com" target="_blank" class="text-blue-600 hover:underline">Substack</a> - Newsletter monetization</li>
                        <li><a href="https://www.patreon.com" target="_blank" class="text-blue-600 hover:underline">Patreon</a> - Creator subscriptions</li>
                    </ul>
                </div>
            </div>

            <!-- Teaching & Coaching -->
            <div class="category-section" data-category="teaching">
                <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition">
                    <div class="text-2xl text-orange-600 mb-4"><i class="fas fa-chalkboard-teacher"></i></div>
                    <h2 class="text-xl font-bold mb-4">Teaching & Coaching</h2>
                    <ul class="space-y-3">
                        <li><a href="https://www.udemy.com/teaching" target="_blank" class="text-blue-600 hover:underline">Udemy</a> - Course creation</li>
                        <li><a href="https://www.teachable.com" target="_blank" class="text-blue-600 hover:underline">Teachable</a> - Online school platform</li>
                        <li><a href="https://www.italki.com/teacher/apply" target="_blank" class="text-blue-600 hover:underline">iTalki</a> - Language teaching</li>
                        <li><a href="https://www.verbling.com/teach" target="_blank" class="text-blue-600 hover:underline">Verbling</a> - Language tutoring</li>
                        <li><a href="https://www.preply.com/en/teach" target="_blank" class="text-blue-600 hover:underline">Preply</a> - Online tutoring</li>
                    </ul>
                </div>
            </div>

            <!-- Micro Tasks -->
            <div class="category-section" data-category="micro">
                <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition">
                    <div class="text-2xl text-orange-600 mb-4"><i class="fas fa-tasks"></i></div>
                    <h2 class="text-xl font-bold mb-4">Micro Tasks & Surveys</h2>
                    <ul class="space-y-3">
                        <li><a href="https://www.mturk.com" target="_blank" class="text-blue-600 hover:underline">Amazon MTurk</a> - Micro tasks</li>
                        <li><a href="https://www.clickworker.com" target="_blank" class="text-blue-600 hover:underline">Clickworker</a> - Data tasks</li>
                        <li><a href="https://www.appen.com" target="_blank" class="text-blue-600 hover:underline">Appen</a> - Data collection</li>
                        <li><a href="https://www.prolific.co" target="_blank" class="text-blue-600 hover:underline">Prolific</a> - Research studies</li>
                        <li><a href="https://www.usertesting.com" target="_blank" class="text-blue-600 hover:underline">UserTesting</a> - Website testing</li>
                    </ul>
                </div>
            </div>

            <!-- Passive Income -->
            <div class="category-section" data-category="passive">
                <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition">
                    <div class="text-2xl text-orange-600 mb-4"><i class="fas fa-money-bill-wave"></i></div>
                    <h2 class="text-xl font-bold mb-4">Passive Income</h2>
                    <ul class="space-y-3">
                        <li><a href="https://www.redbubble.com" target="_blank" class="text-blue-600 hover:underline">Redbubble</a> - Print on demand</li>
                        <li><a href="https://kdp.amazon.com" target="_blank" class="text-blue-600 hover:underline">Amazon KDP</a> - eBook publishing</li>
                        <li><a href="https://www.etsy.com" target="_blank" class="text-blue-600 hover:underline">Etsy</a> - Digital products</li>
                        <li><a href="https://www.shutterstock.com" target="_blank" class="text-blue-600 hover:underline">Shutterstock</a> - Stock media</li>
                        <li><a href="https://www.envato.com" target="_blank" class="text-blue-600 hover:underline">Envato Market</a> - Digital assets</li>
                    </ul>
                </div>
            </div>

        <!-- Affiliate Marketing -->
<div class="category-section" data-category="passive">
    <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition">
        <div class="text-2xl text-orange-600 mb-4"><i class="fas fa-link"></i></div>
        <h2 class="text-xl font-bold mb-4">Affiliate Marketing</h2>
        <ul class="space-y-3">
            <li><a href="https://affiliate-program.amazon.com" target="_blank" class="text-blue-600 hover:underline">Amazon Associates</a> - Earn from product referrals</li>
            <li><a href="https://partnerize.com" target="_blank" class="text-blue-600 hover:underline">Partnerize</a> - Advanced affiliate marketing</li>
            <li><a href="https://www.shareasale.com" target="_blank" class="text-blue-600 hover:underline">ShareASale</a> - Multiple brand partnerships</li>
            <li><a href="https://www.clickbank.com" target="_blank" class="text-blue-600 hover:underline">ClickBank</a> - Digital product promotion</li>
            <li><a href="https://cj.com" target="_blank" class="text-blue-600 hover:underline">CJ Affiliate</a> - Wide network of brands</li>
        </ul>
    </div>
</div>

<!-- Crypto Investment -->
<div class="category-section" data-category="investment">
    <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition">
        <div class="text-2xl text-orange-600 mb-4"><i class="fas fa-coins"></i></div>
        <h2 class="text-xl font-bold mb-4">Crypto Investment</h2>
        <ul class="space-y-3">
            <li><a href="https://www.binance.com" target="_blank" class="text-blue-600 hover:underline">Binance</a> - Crypto trading & investment</li>
            <li><a href="https://www.coinbase.com" target="_blank" class="text-blue-600 hover:underline">Coinbase</a> - Easy crypto investing</li>
            <li><a href="https://crypto.com" target="_blank" class="text-blue-600 hover:underline">Crypto.com</a> - Buy, sell, and earn rewards</li>
            <li><a href="https://www.kraken.com" target="_blank" class="text-blue-600 hover:underline">Kraken</a> - Secure cryptocurrency trading</li>
            <li><a href="https://www.blockfi.com" target="_blank" class="text-blue-600 hover:underline">BlockFi</a> - Earn interest on crypto</li>
        </ul>
    </div>
</div>

<!-- Forex Trading -->
<div class="category-section" data-category="investment">
    <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition">
        <div class="text-2xl text-orange-600 mb-4"><i class="fas fa-chart-line"></i></div>
        <h2 class="text-xl font-bold mb-4">Forex Trading</h2>
        <ul class="space-y-3">
            <li><a href="https://www.forex.com" target="_blank" class="text-blue-600 hover:underline">Forex.com</a> - Forex trading platform</li>
            <li><a href="https://www.etoro.com" target="_blank" class="text-blue-600 hover:underline">eToro</a> - Social trading</li>
            <li><a href="https://www.oanda.com" target="_blank" class="text-blue-600 hover:underline">OANDA</a> - Forex & CFD trading</li>
            <li><a href="https://www.ig.com" target="_blank" class="text-blue-600 hover:underline">IG</a> - Forex trading and education</li>
            <li><a href="https://www.fxcm.com" target="_blank" class="text-blue-600 hover:underline">FXCM</a> - Professional forex services</li>
        </ul>
    </div>
</div>

<!-- BNB (Binance Coin) -->
<div class="category-section" data-category="investment">
    <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition">
        <div class="text-2xl text-orange-600 mb-4"><i class="fas fa-wallet"></i></div>
        <h2 class="text-xl font-bold mb-4">BNB (Binance Coin)</h2>
        <ul class="space-y-3">
            <li><a href="https://www.binance.com/en/bnb" target="_blank" class="text-blue-600 hover:underline">Binance BNB</a> - Invest & trade with BNB</li>
            <li><a href="https://trustwallet.com" target="_blank" class="text-blue-600 hover:underline">Trust Wallet</a> - Secure BNB storage</li>
            <li><a href="https://www.pancakeswap.finance" target="_blank" class="text-blue-600 hover:underline">PancakeSwap</a> - Swap BNB & earn rewards</li>
            <li><a href="https://launchpad.binance.com" target="_blank" class="text-blue-600 hover:underline">Binance Launchpad</a> - Invest in new BNB projects</li>
            <li><a href="https://www.binance.com/en/staking" target="_blank" class="text-blue-600 hover:underline">BNB Staking</a> - Earn passive income</li>
        </ul>
    </div>
</div>

<!-- Chatting & Social Earning -->
<div class="category-section" data-category="social">
    <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition">
        <div class="text-2xl text-orange-600 mb-4"><i class="fas fa-comments"></i></div>
        <h2 class="text-xl font-bold mb-4">Chatting & Social Earning</h2>
        <ul class="space-y-3">
            <li><a href="https://www.chatgig.com" target="_blank" class="text-blue-600 hover:underline">ChatGig</a> - Chat & earn</li>
            <li><a href="https://www.paltalk.com" target="_blank" class="text-blue-600 hover:underline">Paltalk</a> - Social video chat rewards</li>
            <li><a href="https://www.y99.in" target="_blank" class="text-blue-600 hover:underline">Y99</a> - Free chat & earning opportunities</li>
            <li><a href="https://www.whispr.tech" target="_blank" class="text-blue-600 hover:underline">Whispr</a> - Voice chat monetization</li>
            <li><a href="https://www.bigo.tv" target="_blank" class="text-blue-600 hover:underline">Bigo Live</a> - Earn from live streaming</li>
        </ul>
    </div>
</div>
</div>

        <!-- Tips Section -->
        <section class="mt-12 bg-white rounded-xl shadow-sm p-8">
            <h2 class="text-2xl font-bold mb-6">Pro Tips for Success</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="p-4 border rounded-lg">
                    <h3 class="font-semibold mb-2">Getting Started</h3>
                    <ul class="space-y-2 text-gray-600">
                        <li>• Start with skills you already have</li>
                        <li>• Create professional profiles</li>
                        <li>• Build a portfolio</li>
                        <li>• Start small and scale up</li>
                    </ul>
                </div>
                <div class="p-4 border rounded-lg">
                    <h3 class="font-semibold mb-2">Growing Your Income</h3>
                    <ul class="space-y-2 text-gray-600">
                        <li>• Diversify income streams</li>
                        <li>• Reinvest in your skills</li>
                        <li>• Build long-term relationships</li>
                        <li>• Focus on quality over quantity</li>
                    </ul>
                </div>
                <div class="p-4 border rounded-lg">
                    <h3 class="font-semibold mb-2">Time Management</h3>
                    <ul class="space-y-2 text-gray-600">
                        <li>• Set realistic goals</li>
                        <li>• Create a schedule</li>
                        <li>• Use productivity tools</li>
                        <li>• Take regular breaks</li>
                    </ul>
                </div>
            </div>
        </section>
    </main>

    <script>
    function filterCategory(category) {
        const sections = document.querySelectorAll('.category-section');
        sections.forEach(section => {
            if (category === 'all' || section.dataset.category === category) {
                section.style.display = 'block';
            } else {
                section.style.display = 'none';
            }
        });

        // Update active button state
        const buttons = document.querySelectorAll('button');
        buttons.forEach(button => {
            if (button.textContent.toLowerCase().includes(category)) {
                button.classList.remove('bg-gray-200');
                button.classList.add('bg-orange-600', 'text-white');
            } else {
                button.classList.remove('bg-orange-600', 'text-white');
                button.classList.add('bg-gray-200');
            }
        });
    }
    </script>

</body>
</html>