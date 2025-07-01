<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$db = Database::getInstance();

// Get all available vehicles
$sql = "SELECT * FROM vehicles WHERE status = 'available' ORDER BY daily_rate ASC";
$vehicles = $db->get($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalogue de Véhicules - DD RENTAL CAR</title>
    <meta name="description" content="Découvrez notre gamme de véhicules premium disponibles à la location à Marseille et en région PACA.">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="/public/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-black text-white">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center">
                <a href="index.php" class="text-2xl font-bold">DD RENTAL CAR</a>
            </div>
            <div class="hidden md:flex items-center space-x-8">
                <a href="index.php" class="hover:text-gray-300">Accueil</a>
                <a href="catalog.php" class="hover:text-gray-300">Véhicules</a>
                <a href="#contact" class="hover:text-gray-300">Contact</a>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-6 py-12">
        <h1 class="text-4xl font-bold text-center mb-12">Notre Flotte Premium</h1>

        <!-- Filters -->
        <div class="mb-12 flex flex-wrap gap-4 justify-center">
            <button data-filter="all" class="px-6 py-2 bg-purple-600 text-white rounded-full hover:bg-purple-700">Tous</button>
            <button data-filter="SUV" class="px-6 py-2 bg-white text-gray-700 rounded-full hover:bg-gray-100">SUV</button>
            <button data-filter="Berline" class="px-6 py-2 bg-white text-gray-700 rounded-full hover:bg-gray-100">Berline</button>
            <button data-filter="Sport" class="px-6 py-2 bg-white text-gray-700 rounded-full hover:bg-gray-100">Sport</button>
        </div>

        <!-- Vehicle Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($vehicles as $vehicle): ?>
            <div class="bg-white rounded-lg shadow-lg overflow-hidden" data-category="<?= htmlspecialchars($vehicle['category']) ?>">
                <div class="relative pb-[56.25%]">
                    <img 
                        src="<?= htmlspecialchars($vehicle['image_url'] ?? 'https://images.unsplash.com/photo-1555215695-3004980ad54e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80') ?>" 
                        alt="<?= htmlspecialchars($vehicle['name']) ?>"
                        class="absolute top-0 left-0 w-full h-full object-cover"
                    >
                </div>
                <div class="p-6">
                    <h2 class="text-2xl font-bold mb-2"><?= htmlspecialchars($vehicle['name']) ?></h2>
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-gray-600"><?= htmlspecialchars($vehicle['category']) ?></span>
                        <span class="text-purple-600 font-bold"><?= formatPrice($vehicle['daily_rate']) ?> /jour</span>
                    </div>
                    <p class="text-gray-600 mb-6"><?= htmlspecialchars($vehicle['description']) ?></p>
                    <a 
                        href="reservation.php?vehicle_id=<?= $vehicle['id'] ?>" 
                        class="block w-full text-center bg-black text-white py-3 rounded-md hover:bg-gray-800 transition duration-300"
                    >
                        Réserver
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($vehicles)): ?>
        <div class="text-center py-12">
            <p class="text-gray-600 text-lg">Aucun véhicule disponible pour le moment.</p>
        </div>
        <?php endif; ?>
    </main>

    <!-- Contact Section -->
    <section id="contact" class="bg-gray-100 py-20">
        <div class="container mx-auto px-6">
            <h2 class="text-4xl font-bold text-center mb-12">Besoin d'aide ?</h2>
            <div class="max-w-lg mx-auto text-center">
                <p class="text-xl mb-8">Pour toute question ou demande spécifique, contactez-nous sur WhatsApp</p>
                <a 
                    href="https://wa.me/33663187902" 
                    class="bg-green-500 text-white px-8 py-4 rounded-md text-lg font-semibold hover:bg-green-600 transition duration-300 inline-flex items-center"
                >
                    <span>Contacter sur WhatsApp</span>
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-black text-white py-12">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                <div>
                    <h3 class="text-xl font-bold mb-4">DD RENTAL CAR</h3>
                    <p>Location de voitures premium à Marseille</p>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4">Contact</h3>
                    <p>WhatsApp: 06 63 18 79 02</p>
                    <p>Région: PACA</p>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4">Navigation</h3>
                    <ul class="space-y-2">
                        <li><a href="index.php" class="hover:text-gray-300">Accueil</a></li>
                        <li><a href="catalog.php" class="hover:text-gray-300">Véhicules</a></li>
                        <li><a href="#contact" class="hover:text-gray-300">Contact</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-12 pt-8 text-center">
                <p>&copy; 2024 DD RENTAL CAR. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterButtons = document.querySelectorAll('button');
        const vehicles = document.querySelectorAll('[data-category]');
        const vehicleGrid = document.querySelector('.grid');

        // Set initial state
        let currentFilter = 'all';
        updateDisplay();

        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                filterButtons.forEach(btn => {
                    btn.classList.remove('bg-purple-600', 'text-white');
                    btn.classList.add('bg-white', 'text-gray-700');
                });

                // Add active class to clicked button
                this.classList.remove('bg-white', 'text-gray-700');
                this.classList.add('bg-purple-600', 'text-white');

                // Update filter
                currentFilter = this.textContent.trim();
                updateDisplay();
            });
        });

        function updateDisplay() {
            vehicles.forEach(vehicle => {
                const category = vehicle.dataset.category;
                if (currentFilter === 'Tous' || currentFilter === category) {
                    vehicle.classList.remove('hidden');
                    vehicle.classList.add('vehicle-card');
                } else {
                    vehicle.classList.add('hidden');
                    vehicle.classList.remove('vehicle-card');
                }
            });

            // Add animation
            vehicles.forEach(vehicle => {
                if (!vehicle.classList.contains('hidden')) {
                    vehicle.style.opacity = '0';
                    setTimeout(() => {
                        vehicle.style.opacity = '1';
                        vehicle.style.transition = 'opacity 0.3s ease-in-out';
                    }, 50);
                }
            });
        }
    });
    </script>
</body>
</html>
