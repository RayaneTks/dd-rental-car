<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php', 'Accès non autorisé.', 'error');
}

$db = Database::getInstance();

// Get pending reservations
$sql = "SELECT r.*, v.name as vehicle_name 
        FROM reservations r 
        JOIN vehicles v ON r.vehicle_id = v.id 
        WHERE r.status = 'pending' 
        ORDER BY r.created_at DESC";
$pendingReservations = $db->get($sql);

// Get recent reservations
$sql = "SELECT r.*, v.name as vehicle_name 
        FROM reservations r 
        JOIN vehicles v ON r.vehicle_id = v.id 
        WHERE r.status != 'pending' 
        ORDER BY r.updated_at DESC 
        LIMIT 5";
$recentReservations = $db->get($sql);

// Get vehicle statistics
$sql = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
            SUM(CASE WHEN status = 'maintenance' THEN 1 ELSE 0 END) as maintenance,
            SUM(CASE WHEN status = 'reserved' THEN 1 ELSE 0 END) as reserved
        FROM vehicles";
$vehicleStats = $db->single($sql);

// Handle reservation status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservation_id'], $_POST['status'])) {
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
        
        redirect('index.php', 'Statut mis à jour avec succès.', 'success');
    } catch (Exception $e) {
        redirect('index.php', 'Erreur lors de la mise à jour.', 'error');
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - DD RENTAL CAR</title>
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
                    <a href="index.php" class="text-white hover:text-gray-300">Dashboard</a>
                    <a href="vehicles.php" class="text-gray-400 hover:text-white">Véhicules</a>
                    <a href="reservations.php" class="text-gray-400 hover:text-white">Réservations</a>
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
        <!-- Welcome Message -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <h2 class="text-2xl font-bold mb-2">Bienvenue, <?= htmlspecialchars($_SESSION['user_name']) ?></h2>
            <p class="text-gray-600">Voici un aperçu de l'activité récente de votre entreprise.</p>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold mb-2">Total Véhicules</h3>
                <p class="text-3xl font-bold text-purple-600"><?= $vehicleStats['total'] ?></p>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold mb-2">Disponibles</h3>
                <p class="text-3xl font-bold text-green-600"><?= $vehicleStats['available'] ?></p>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold mb-2">En maintenance</h3>
                <p class="text-3xl font-bold text-yellow-600"><?= $vehicleStats['maintenance'] ?></p>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold mb-2">Réservés</h3>
                <p class="text-3xl font-bold text-blue-600"><?= $vehicleStats['reserved'] ?></p>
            </div>
        </div>

        <!-- Pending Reservations -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <h3 class="text-xl font-bold mb-4">Demandes de réservation en attente</h3>
            
            <?php if (empty($pendingReservations)): ?>
                <p class="text-gray-600">Aucune demande en attente.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-left border-b-2">
                                <th class="pb-3">Client</th>
                                <th class="pb-3">Véhicule</th>
                                <th class="pb-3">Dates</th>
                                <th class="pb-3">Prix Total</th>
                                <th class="pb-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingReservations as $reservation): ?>
                                <tr class="border-b">
                                    <td class="py-3">
                                        <div class="font-semibold"><?= htmlspecialchars($reservation['client_name']) ?></div>
                                        <div class="text-sm text-gray-600"><?= htmlspecialchars($reservation['client_email']) ?></div>
                                    </td>
                                    <td class="py-3"><?= htmlspecialchars($reservation['vehicle_name']) ?></td>
                                    <td class="py-3">
                                        <div>Du: <?= htmlspecialchars($reservation['start_date']) ?></div>
                                        <div>Au: <?= htmlspecialchars($reservation['end_date']) ?></div>
                                    </td>
                                    <td class="py-3"><?= formatPrice($reservation['total_price']) ?></td>
                                    <td class="py-3">
                                        <form method="POST" class="inline-block">
                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                            <input type="hidden" name="reservation_id" value="<?= $reservation['id'] ?>">
                                            <input type="hidden" name="status" value="confirmed">
                                            <button type="submit" class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">
                                                Accepter
                                            </button>
                                        </form>
                                        <form method="POST" class="inline-block ml-2">
                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                            <input type="hidden" name="reservation_id" value="<?= $reservation['id'] ?>">
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
                                                Refuser
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-xl font-bold mb-4">Activité récente</h3>
            
            <?php if (empty($recentReservations)): ?>
                <p class="text-gray-600">Aucune activité récente.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($recentReservations as $reservation): ?>
                        <div class="flex items-center justify-between border-b pb-4">
                            <div>
                                <div class="font-semibold"><?= htmlspecialchars($reservation['vehicle_name']) ?></div>
                                <div class="text-sm text-gray-600">
                                    Réservé par <?= htmlspecialchars($reservation['client_name']) ?>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="<?= $reservation['status'] === 'confirmed' ? 'text-green-600' : 'text-red-600' ?>">
                                    <?= $reservation['status'] === 'confirmed' ? 'Confirmé' : 'Refusé' ?>
                                </div>
                                <div class="text-sm text-gray-600">
                                    <?= date('d/m/Y H:i', strtotime($reservation['updated_at'])) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
