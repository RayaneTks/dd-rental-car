<?php
$errorCodes = [
    400 => 'Mauvaise requête',
    401 => 'Non autorisé',
    403 => 'Accès interdit',
    404 => 'Page non trouvée',
    500 => 'Erreur serveur interne'
];

$errorCode = $_SERVER['REDIRECT_STATUS'] ?? 404;
$errorMessage = $errorCodes[$errorCode] ?? 'Une erreur est survenue';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erreur <?= $errorCode ?> - DD RENTAL CAR</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4 text-center">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-6xl font-bold text-purple-600 mb-4"><?= $errorCode ?></h1>
            <h2 class="text-2xl font-bold mb-4"><?= htmlspecialchars($errorMessage) ?></h2>
            <p class="text-gray-600 mb-8">Désolé, une erreur s'est produite. Veuillez réessayer ou retourner à la page d'accueil.</p>
            <a 
                href="index.php" 
                class="inline-block bg-purple-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-purple-700 transition duration-300"
            >
                Retour à l'accueil
            </a>
        </div>
    </div>
</body>
</html>
