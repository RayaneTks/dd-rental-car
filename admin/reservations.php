<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php', 'Accès non autorisé.', 'error');
}

$db = Database::getInstance();
$message = '';
$errors = [];

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }

    $reservationId = (int)$_POST['reservation_id'];
    $status = sanitize($_POST['status']);

    try {
        $db->update('reservations', 
            ['status' => $status], 
            'id = ?', 
            [$reservationId]
        );

        // Get reservation details for email
        $sql = "SELECT r.*, v.name as vehicle_name 
                FROM reservations r 
                JOIN vehicles v ON r.vehicle_id = v.id 
                WHERE r.id = ?";
        $reservation = $db->single($sql, [$reservationId]);

        // Send email notification
        $subject = "Mise à jour de votre réservation - DD RENTAL CAR";
        $message = "Votre réservation pour {$reservation['vehicle_name']} a été " . 
                  ($status === 'confirmed' ? 'confirmée' : 'refusée') . ".";

        sendEmail($reservation['client_email'], $subject, $message);

        $message = 'Statut de la réservation mis à jour avec succès.';
    } catch (Exception $e) {
        $errors[] = 'Une erreur est survenue lors de la mise à jour.';
    }
}

// Get all reservations with vehicle details
$sql = "SELECT r.*, v.name as vehicle_name, v.brand, v.model 
        FROM reservations r 
        JOIN vehicles v ON r.vehicle_id = v.id 
        ORDER BY r.created_at DESC";
$reservations = $db->get($sql);

// Group reservations by status
$groupedReservations = [
    'pending' => [],
    'confirmed' => [],
    'completed' => [],
    'cancelled' => [],
    'rejected' => []
];

foreach ($reservations as $reservation) {
    $groupedReservations[$reservation['status']][] = $reservation;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Réservations - DD RENTAL CAR</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-black text-white">
        <div class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold">DD RENTAL CAR</h1>
                    <span class="ml-4 text-gray-400">Administration</span>
                </div>
                <nav class="hidden md:flex items-center space-x-6">
                    <a href="index.php" class="text-gray-400 hover:text-white">Dashboard</a>
                    <a href="vehicles.php" class="text-gray-400 hover:text-white">Véhicules</a>
                    <a href="reservations.php" class="text-white hover:text-gray-300">Réservations</a>
                    <form method="POST" action="logout.php" class="inline">
                        <button type="submit" class="text-gray-400 hover:text-white">
                            Déconnexion
                        </button>
                    </form>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-6 py-8">
        <!-- Messages -->
        <?php if (!empty($message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <ul class="list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Tabs -->
        <div class="mb-8" x-data="{ activeTab: 'pending' }">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <button 
                        @click="activeTab = 'pending'"
                        :class="{'border-purple-500 text-purple-600': activeTab === 'pending'}"
                        class="py-4 px-1 border-b-2 font-medium text-sm hover:text-gray-700 hover:border-gray-300"
                    >
                        En attente (<?= count($groupedReservations['pending']) ?>)
                    </button>
                    <button 
                        @click="activeTab = 'confirmed'"
                        :class="{'border-purple-500 text-purple-600': activeTab === 'confirmed'}"
                        class="py-4 px-1 border-b-2 font-medium text-sm hover:text-gray-700 hover:border-gray-300"
                    >
                        Confirmées (<?= count($groupedReservations['confirmed']) ?>)
                    </button>
                    <button 
                        @click="activeTab = 'completed'"
                        :class="{'border-purple-500 text-purple-600': activeTab === 'completed'}"
                        class="py-4 px-1 border-b-2 font-medium text-sm hover:text-gray-700 hover:border-gray-300"
                    >
                        Terminées (<?= count($groupedReservations['completed']) ?>)
                    </button>
                    <button 
                        @click="activeTab = 'cancelled'"
                        :class="{'border-purple-500 text-purple-600': activeTab === 'cancelled'}"
                        class="py-4 px-1 border-b-2 font-medium text-sm hover:text-gray-700 hover:border-gray-300"
                    >
                        Annulées (<?= count($groupedReservations['cancelled']) + count($groupedReservations['rejected']) ?>)
                    </button>
                </nav>
            </div>

            <!-- Reservations Tables -->
            <?php 
            $statuses = [
                'pending' => 'En attente',
                'confirmed' => 'Confirmées',
                'completed' => 'Terminées',
                'cancelled' => 'Annulées/Refusées'
            ];

            foreach ($statuses as $status => $title): 
                $reservationsToShow = $status === 'cancelled' 
                    ? array_merge($groupedReservations['cancelled'], $groupedReservations['rejected'])
                    : $groupedReservations[$status];
            ?>
                <div x-show="activeTab === '<?= $status ?>'" class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <?php if (empty($reservationsToShow)): ?>
                        <div class="p-6 text-center text-gray-500">
                            Aucune réservation <?= strtolower($title) ?>.
                        </div>
                    <?php else: ?>
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-50 text-left">
                                    <th class="px-6 py-3">Client</th>
                                    <th class="px-6 py-3">Véhicule</th>
                                    <th class="px-6 py-3">Dates</th>
                                    <th class="px-6 py-3">Prix Total</th>
                                    <th class="px-6 py-3">Statut</th>
                                    <?php if ($status === 'pending'): ?>
                                        <th class="px-6 py-3">Actions</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reservationsToShow as $reservation): ?>
                                    <tr class="border-t">
                                        <td class="px-6 py-4">
                                            <div class="font-semibold">
                                                <?= htmlspecialchars($reservation['client_name']) ?>
                                            </div>
                                            <div class="text-sm text-gray-600">
                                                <?= htmlspecialchars($reservation['client_email']) ?>
                                            </div>
                                            <div class="text-sm text-gray-600">
                                                <?= htmlspecialchars($reservation['client_phone']) ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="font-semibold">
                                                <?= htmlspecialchars($reservation['vehicle_name']) ?>
                                            </div>
                                            <div class="text-sm text-gray-600">
                                                <?= htmlspecialchars($reservation['brand']) ?> 
                                                <?= htmlspecialchars($reservation['model']) ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div>Du: <?= date('d/m/Y', strtotime($reservation['start_date'])) ?></div>
                                            <div>Au: <?= date('d/m/Y', strtotime($reservation['end_date'])) ?></div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?= formatPrice($reservation['total_price']) ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 rounded text-sm 
                                                <?= $reservation['status'] === 'confirmed' ? 'bg-green-100 text-green-800' : 
                                                    ($reservation['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                                    'bg-red-100 text-red-800') ?>">
                                                <?= htmlspecialchars($reservation['status']) ?>
                                            </span>
                                        </td>
                                        <?php if ($status === 'pending'): ?>
                                            <td class="px-6 py-4">
                                                <form method="POST" class="inline-block">
                                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                    <input type="hidden" name="reservation_id" value="<?= $reservation['id'] ?>">
                                                    <input type="hidden" name="status" value="confirmed">
                                                    <button type="submit" class="text-green-600 hover:text-green-800 mr-3">
                                                        Confirmer
                                                    </button>
                                                </form>
                                                <form method="POST" class="inline-block">
                                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                    <input type="hidden" name="reservation_id" value="<?= $reservation['id'] ?>">
                                                    <input type="hidden" name="status" value="rejected">
                                                    <button type="submit" class="text-red-600 hover:text-red-800">
                                                        Refuser
                                                    </button>
                                                </form>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.8.2/dist/alpine.min.js" defer></script>
</body>
</html>
