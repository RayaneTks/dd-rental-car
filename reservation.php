<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$db = Database::getInstance();
$errors = [];
$success = false;

// Get vehicle details
if (isset($_GET['vehicle_id'])) {
    $vehicleId = (int)$_GET['vehicle_id'];
    $sql = "SELECT * FROM vehicles WHERE id = ? AND status = 'available'";
    $vehicle = $db->single($sql, [$vehicleId]);
    
    if (!$vehicle) {
        redirect('catalog.php', 'Véhicule non disponible.', 'error');
    }
} else {
    redirect('catalog.php', 'Aucun véhicule sélectionné.', 'error');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }
    
    // Sanitize inputs
    $clientName = sanitize($_POST['client_name']);
    $clientEmail = sanitize($_POST['client_email']);
    $clientPhone = sanitize($_POST['client_phone']);
    $startDate = sanitize($_POST['start_date']);
    $endDate = sanitize($_POST['end_date']);
    $notes = sanitize($_POST['notes']);
    
    // Validate inputs
    if (empty($clientName)) {
        $errors[] = 'Le nom est requis.';
    }
    
    if (empty($clientEmail) || !isValidEmail($clientEmail)) {
        $errors[] = 'Email invalide.';
    }
    
    if (empty($clientPhone)) {
        $errors[] = 'Le numéro de téléphone est requis.';
    }
    
    // Validate dates
    $dateValidation = validateDateRange($startDate, $endDate);
    if (!$dateValidation['valid']) {
        $errors[] = $dateValidation['message'];
    }
    
    // Check vehicle availability
    if (!isVehicleAvailable($vehicleId, $startDate, $endDate)) {
        $errors[] = 'Le véhicule n\'est pas disponible pour ces dates.';
    }
    
    // If no errors, process the reservation
    if (empty($errors)) {
        try {
            // Calculate total price
            $start = new DateTime($startDate);
            $end = new DateTime($endDate);
            $days = $start->diff($end)->days + 1;
            $totalPrice = $vehicle['daily_rate'] * $days;
            
            // Create reservation
            $reservationData = [
                'vehicle_id' => $vehicleId,
                'client_name' => $clientName,
                'client_email' => $clientEmail,
                'client_phone' => $clientPhone,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_price' => $totalPrice,
                'notes' => $notes,
                'status' => 'pending'
            ];
            
            $db->insert('reservations', $reservationData);
            
            // Send confirmation email
            $emailContent = generateReservationEmail([
                'client_name' => $clientName,
                'vehicle_name' => $vehicle['name'],
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            
            sendEmail($clientEmail, 'Confirmation de demande de réservation - DD RENTAL CAR', $emailContent);
            
            $success = true;
        } catch (Exception $e) {
            $errors[] = 'Une erreur est survenue lors de la réservation.';
            error_log($e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation - <?= htmlspecialchars($vehicle['name']) ?> - DD RENTAL CAR</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
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
        <?php if ($success): ?>
            <div class="max-w-2xl mx-auto bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-8">
                <p>Votre demande de réservation a été envoyée avec succès. Nous vous contacterons rapidement pour confirmer votre réservation.</p>
                <p class="mt-4">
                    <a href="catalog.php" class="text-green-700 underline">Retourner au catalogue</a>
                </p>
            </div>
        <?php else: ?>
            <div class="max-w-2xl mx-auto">
                <h1 class="text-4xl font-bold mb-8">Réservation</h1>

                <!-- Vehicle Info -->
                <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                    <div class="flex items-center">
                        <img 
                            src="<?= htmlspecialchars($vehicle['image_url'] ?? 'https://img.sixt.com/1600/68ca1403-ad7d-4421-a2d7-2ed76213d1ed.jpg') ?>" 
                            alt="<?= htmlspecialchars($vehicle['name']) ?>"
                            class="w-32 h-32 object-cover rounded-lg mr-6"
                        >
                        <div>
                            <h2 class="text-2xl font-bold mb-2"><?= htmlspecialchars($vehicle['name']) ?></h2>
                            <p class="text-gray-600 mb-2"><?= htmlspecialchars($vehicle['category']) ?></p>
                            <p class="text-purple-600 font-bold"><?= formatPrice($vehicle['daily_rate']) ?> /jour</p>
                        </div>
                    </div>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-8">
                        <ul class="list-disc list-inside">
                            <?php foreach ($errors as $error): ?>
                                <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Reservation Form -->
                <form method="POST" class="bg-white rounded-lg shadow-lg p-6">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2" for="client_name">
                                Nom complet
                            </label>
                            <input 
                                type="text" 
                                id="client_name" 
                                name="client_name" 
                                required 
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600"
                                value="<?= isset($_POST['client_name']) ? htmlspecialchars($_POST['client_name']) : '' ?>"
                            >
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2" for="client_email">
                                Email
                            </label>
                            <input 
                                type="email" 
                                id="client_email" 
                                name="client_email" 
                                required 
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600"
                                value="<?= isset($_POST['client_email']) ? htmlspecialchars($_POST['client_email']) : '' ?>"
                            >
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-gray-700 font-semibold mb-2" for="client_phone">
                            Téléphone
                        </label>
                        <input 
                            type="tel" 
                            id="client_phone" 
                            name="client_phone" 
                            required 
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600"
                            value="<?= isset($_POST['client_phone']) ? htmlspecialchars($_POST['client_phone']) : '' ?>"
                        >
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2" for="start_date">
                                Date de début
                            </label>
                            <input 
                                type="date" 
                                id="start_date" 
                                name="start_date" 
                                required 
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600 datepicker"
                                value="<?= isset($_POST['start_date']) ? htmlspecialchars($_POST['start_date']) : '' ?>"
                            >
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2" for="end_date">
                                Date de fin
                            </label>
                            <input 
                                type="date" 
                                id="end_date" 
                                name="end_date" 
                                required 
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600 datepicker"
                                value="<?= isset($_POST['end_date']) ? htmlspecialchars($_POST['end_date']) : '' ?>"
                            >
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-gray-700 font-semibold mb-2" for="notes">
                            Notes / Demandes spéciales
                        </label>
                        <textarea 
                            id="notes" 
                            name="notes" 
                            rows="4" 
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600"
                        ><?= isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : '' ?></textarea>
                    </div>

                    <button 
                        type="submit" 
                        class="w-full bg-purple-600 text-white py-3 rounded-lg font-semibold hover:bg-purple-700 transition duration-300"
                    >
                        Envoyer la demande de réservation
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-black text-white py-12 mt-12">
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
        // Initialize date pickers
        flatpickr(".datepicker", {
            locale: "fr",
            minDate: "today",
            dateFormat: "Y-m-d"
        });

        // Calculate total price
        const startDate = document.getElementById('start_date');
        const endDate = document.getElementById('end_date');
        const dailyRate = <?= $vehicle['daily_rate'] ?>;

        function updateTotalPrice() {
            if (startDate.value && endDate.value) {
                const start = new Date(startDate.value);
                const end = new Date(endDate.value);
                const days = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
                
                if (days > 0) {
                    const total = days * dailyRate;
                    document.getElementById('total_price').textContent = 
                        new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(total);
                }
            }
        }

        startDate.addEventListener('change', updateTotalPrice);
        endDate.addEventListener('change', updateTotalPrice);
    });
    </script>
</body>
</html>
