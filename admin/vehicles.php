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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
        case 'edit':
            $vehicleData = [
                'name' => sanitize($_POST['name']),
                'brand' => sanitize($_POST['brand']),
                'model' => sanitize($_POST['model']),
                'year' => (int)$_POST['year'],
                'category' => sanitize($_POST['category']),
                'daily_rate' => (float)$_POST['daily_rate'],
                'description' => sanitize($_POST['description']),
                'status' => sanitize($_POST['status'])
            ];

            // Validate data
            if (empty($vehicleData['name']) || empty($vehicleData['brand']) || empty($vehicleData['model'])) {
                $errors[] = 'Tous les champs obligatoires doivent être remplis.';
            }

            if (empty($errors)) {
                try {
                    // Handle image upload
                    if (!empty($_FILES['image']['name'])) {
                        $vehicleData['image_url'] = uploadImage($_FILES['image'], 'vehicles/');
                    }

                    if ($action === 'add') {
                        $db->insert('vehicles', $vehicleData);
                        $message = 'Véhicule ajouté avec succès.';
                    } else {
                        $vehicleId = (int)$_POST['vehicle_id'];
                        $db->update('vehicles', $vehicleData, 'id = ?', [$vehicleId]);
                        $message = 'Véhicule mis à jour avec succès.';
                    }
                } catch (Exception $e) {
                    $errors[] = 'Une erreur est survenue: ' . $e->getMessage();
                }
            }
            break;

        case 'delete':
            $vehicleId = (int)$_POST['vehicle_id'];
            try {
                $db->delete('vehicles', 'id = ?', [$vehicleId]);
                $message = 'Véhicule supprimé avec succès.';
            } catch (Exception $e) {
                $errors[] = 'Impossible de supprimer ce véhicule.';
            }
            break;
    }
}

// Get all vehicles
$sql = "SELECT * FROM vehicles ORDER BY created_at DESC";
$vehicles = $db->get($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Véhicules - DD RENTAL CAR</title>
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
                    <a href="vehicles.php" class="text-white hover:text-gray-300">Véhicules</a>
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

        <!-- Add Vehicle Button -->
        <div class="mb-8">
            <button 
                onclick="document.getElementById('addVehicleModal').classList.remove('hidden')"
                class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition duration-300"
            >
                Ajouter un véhicule
            </button>
        </div>

        <!-- Vehicles Table -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left">
                        <th class="px-6 py-3">Image</th>
                        <th class="px-6 py-3">Nom</th>
                        <th class="px-6 py-3">Marque/Modèle</th>
                        <th class="px-6 py-3">Catégorie</th>
                        <th class="px-6 py-3">Prix/Jour</th>
                        <th class="px-6 py-3">Statut</th>
                        <th class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vehicles as $vehicle): ?>
                        <tr class="border-t">
                            <td class="px-6 py-4">
                                <img 
                                    src="<?= htmlspecialchars($vehicle['image_url'] ?? 'https://via.placeholder.com/150') ?>" 
                                    alt="<?= htmlspecialchars($vehicle['name']) ?>"
                                    class="w-20 h-20 object-cover rounded"
                                >
                            </td>
                            <td class="px-6 py-4"><?= htmlspecialchars($vehicle['name']) ?></td>
                            <td class="px-6 py-4">
                                <?= htmlspecialchars($vehicle['brand']) ?> <?= htmlspecialchars($vehicle['model']) ?>
                                <div class="text-sm text-gray-600"><?= $vehicle['year'] ?></div>
                            </td>
                            <td class="px-6 py-4"><?= htmlspecialchars($vehicle['category']) ?></td>
                            <td class="px-6 py-4"><?= formatPrice($vehicle['daily_rate']) ?></td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded text-sm 
                                    <?= $vehicle['status'] === 'available' ? 'bg-green-100 text-green-800' : 
                                        ($vehicle['status'] === 'maintenance' ? 'bg-yellow-100 text-yellow-800' : 
                                        'bg-red-100 text-red-800') ?>">
                                    <?= htmlspecialchars($vehicle['status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <button 
                                    onclick="editVehicle(<?= htmlspecialchars(json_encode($vehicle)) ?>)"
                                    class="text-blue-600 hover:text-blue-800 mr-3"
                                >
                                    Modifier
                                </button>
                                <form method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce véhicule ?')">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="vehicle_id" value="<?= $vehicle['id'] ?>">
                                    <button type="submit" class="text-red-600 hover:text-red-800">
                                        Supprimer
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Add Vehicle Modal -->
    <div id="addVehicleModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full">
                <div class="p-6">
                    <h2 class="text-2xl font-bold mb-6">Ajouter un véhicule</h2>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="action" value="add">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Nom</label>
                                <input type="text" name="name" required class="w-full px-4 py-2 border rounded">
                            </div>
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Marque</label>
                                <input type="text" name="brand" required class="w-full px-4 py-2 border rounded">
                            </div>
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Modèle</label>
                                <input type="text" name="model" required class="w-full px-4 py-2 border rounded">
                            </div>
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Année</label>
                                <input type="number" name="year" required class="w-full px-4 py-2 border rounded">
                            </div>
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Catégorie</label>
                                <select name="category" required class="w-full px-4 py-2 border rounded">
                                    <option value="SUV">SUV</option>
                                    <option value="Berline">Berline</option>
                                    <option value="Sport">Sport</option>
                                    <option value="Luxe">Luxe</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Prix par jour</label>
                                <input type="number" name="daily_rate" required class="w-full px-4 py-2 border rounded">
                            </div>
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Image</label>
                                <input type="file" name="image" accept="image/*" class="w-full">
                            </div>
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Statut</label>
                                <select name="status" required class="w-full px-4 py-2 border rounded">
                                    <option value="available">Disponible</option>
                                    <option value="maintenance">En maintenance</option>
                                    <option value="reserved">Réservé</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-gray-700 font-semibold mb-2">Description</label>
                            <textarea name="description" rows="4" class="w-full px-4 py-2 border rounded"></textarea>
                        </div>

                        <div class="flex justify-end space-x-4">
                            <button 
                                type="button"
                                onclick="document.getElementById('addVehicleModal').classList.add('hidden')"
                                class="px-6 py-2 border rounded hover:bg-gray-100"
                            >
                                Annuler
                            </button>
                            <button 
                                type="submit"
                                class="px-6 py-2 bg-purple-600 text-white rounded hover:bg-purple-700"
                            >
                                Ajouter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Vehicle Modal -->
    <div id="editVehicleModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full">
                <div class="p-6">
                    <h2 class="text-2xl font-bold mb-6">Modifier le véhicule</h2>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="vehicle_id" id="edit_vehicle_id">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Nom</label>
                                <input type="text" name="name" id="edit_name" required class="w-full px-4 py-2 border rounded">
                            </div>
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Marque</label>
                                <input type="text" name="brand" id="edit_brand" required class="w-full px-4 py-2 border rounded">
                            </div>
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Modèle</label>
                                <input type="text" name="model" id="edit_model" required class="w-full px-4 py-2 border rounded">
                            </div>
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Année</label>
                                <input type="number" name="year" id="edit_year" required class="w-full px-4 py-2 border rounded">
                            </div>
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Catégorie</label>
                                <select name="category" id="edit_category" required class="w-full px-4 py-2 border rounded">
                                    <option value="SUV">SUV</option>
                                    <option value="Berline">Berline</option>
                                    <option value="Sport">Sport</option>
                                    <option value="Luxe">Luxe</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Prix par jour</label>
                                <input type="number" name="daily_rate" id="edit_daily_rate" required class="w-full px-4 py-2 border rounded">
                            </div>
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Image</label>
                                <input type="file" name="image" accept="image/*" class="w-full">
                            </div>
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Statut</label>
                                <select name="status" id="edit_status" required class="w-full px-4 py-2 border rounded">
                                    <option value="available">Disponible</option>
                                    <option value="maintenance">En maintenance</option>
                                    <option value="reserved">Réservé</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-gray-700 font-semibold mb-2">Description</label>
                            <textarea name="description" id="edit_description" rows="4" class="w-full px-4 py-2 border rounded"></textarea>
                        </div>

                        <div class="flex justify-end space-x-4">
                            <button 
                                type="button"
                                onclick="document.getElementById('editVehicleModal').classList.add('hidden')"
                                class="px-6 py-2 border rounded hover:bg-gray-100"
                            >
                                Annuler
                            </button>
                            <button 
                                type="submit"
                                class="px-6 py-2 bg-purple-600 text-white rounded hover:bg-purple-700"
                            >
                                Mettre à jour
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    function editVehicle(vehicle) {
        document.getElementById('edit_vehicle_id').value = vehicle.id;
        document.getElementById('edit_name').value = vehicle.name;
        document.getElementById('edit_brand').value = vehicle.brand;
        document.getElementById('edit_model').value = vehicle.model;
        document.getElementById('edit_year').value = vehicle.year;
        document.getElementById('edit_category').value = vehicle.category;
        document.getElementById('edit_daily_rate').value = vehicle.daily_rate;
        document.getElementById('edit_status').value = vehicle.status;
        document.getElementById('edit_description').value = vehicle.description;
        
        document.getElementById('editVehicleModal').classList.remove('hidden');
    }
    </script>
</body>
</html>
