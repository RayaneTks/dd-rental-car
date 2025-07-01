<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DD RENTAL CAR - Location de Voitures Premium à Marseille</title>
    <meta name="description" content="Location de voitures premium à Marseille et livraison dans toute la région PACA. Large gamme de véhicules haut de gamme disponibles.">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="/public/css/styles.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.min.js" defer></script>
    <link rel="icon" type="image/png" href="/public/images/favicon.png">
</head>
<body class="bg-white font-sans">
    <!-- Header -->
    <header class="bg-black text-white">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center">
                <h1 class="text-2xl font-bold">DD RENTAL CAR</h1>
            </div>
            <div class="hidden md:flex items-center space-x-8">
                <a href="catalog.php" class="hover:text-gray-300">Véhicules</a>
                <a href="catalog.php" class="hover:text-gray-300">Réservation</a>
                <a href="#contact" class="hover:text-gray-300">Contact</a>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="relative h-screen">
        <div class="absolute inset-0 hero-overlay z-10"></div>
        <div class="absolute inset-0">
            <img src="https://img.sixt.com/1600/68ca1403-ad7d-4421-a2d7-2ed76213d1ed.jpg" alt="Luxury car" class="w-full h-full object-cover">
        </div>
        <div class="relative z-20 container mx-auto px-6 h-full flex items-center">
            <div class="text-white max-w-2xl">
                <h2 class="text-5xl font-bold mb-6">Location Premium à Prix Attractif</h2>
                <p class="text-xl mb-8">Découvrez notre gamme de véhicules haut de gamme disponibles à Marseille et dans toute la région PACA.</p>
                <a href="catalog.php" class="btn-primary animate-fade-in">
                    Réserver maintenant
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 bg-gray-100">
        <div class="container mx-auto px-6">
            <h2 class="text-4xl font-bold text-center mb-16">Pourquoi choisir DD RENTAL CAR ?</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                <div class="text-center">
                    <div class="feature-icon mx-auto mb-6">
                        <span class="text-2xl">🚗</span>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Flotte Premium</h3>
                    <p class="text-gray-600">Véhicules haut de gamme BMW, Mercedes, Audi entretenus avec le plus grand soin pour votre confort</p>
                </div>
                <div class="text-center">
                    <div class="feature-icon mx-auto mb-6">
                        <span class="text-2xl">🚚</span>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Livraison PACA</h3>
                    <p class="text-gray-600">Service de livraison gratuit dans toute la région PACA pour votre plus grande commodité</p>
                </div>
                <div class="text-center">
                    <div class="feature-icon mx-auto mb-6">
                        <span class="text-2xl">⚡</span>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Réservation Express</h3>
                    <p class="text-gray-600">Processus de réservation simplifié en quelques clics avec confirmation rapide</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-20">
        <div class="container mx-auto px-6">
            <h2 class="text-4xl font-bold text-center mb-16">Ce que disent nos clients</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="testimonial-card">
                    <div class="mb-4">
                        <div class="flex text-yellow-400 mb-2">
                            <span>★★★★★</span>
                        </div>
                        <p class="text-gray-600 italic">"Service exceptionnel ! La BMW X5 était impeccable et la livraison à l'heure. Je recommande vivement DD RENTAL CAR pour leur professionnalisme."</p>
                    </div>
                    <div class="font-semibold">— Marie Dubois, Marseille</div>
                </div>
                <div class="testimonial-card">
                    <div class="mb-4">
                        <div class="flex text-yellow-400 mb-2">
                            <span>★★★★★</span>
                        </div>
                        <p class="text-gray-600 italic">"Véhicules de luxe à prix raisonnable. L'équipe est très réactive et le processus de réservation est simple. Parfait pour mes déplacements professionnels."</p>
                    </div>
                    <div class="font-semibold">— Pierre Martin, Nice</div>
                </div>
                <div class="testimonial-card">
                    <div class="mb-4">
                        <div class="flex text-yellow-400 mb-2">
                            <span>★★★★★</span>
                        </div>
                        <p class="text-gray-600 italic">"Excellent service client ! J'ai pu modifier ma réservation facilement et l'Audi Q8 était parfaite pour mon week-end en famille."</p>
                    </div>
                    <div class="font-semibold">— Sophie Laurent, Cannes</div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-20 bg-gray-100">
        <div class="container mx-auto px-6">
            <h2 class="text-4xl font-bold text-center mb-16">Questions Fréquentes</h2>
            <div class="max-w-4xl mx-auto">
                <div class="space-y-6">
                    <div class="bg-white rounded-lg p-6 shadow-md">
                        <h3 class="text-xl font-bold mb-3">Comment effectuer une réservation ?</h3>
                        <p class="text-gray-600">Sélectionnez votre véhicule, remplissez le formulaire de réservation avec vos dates souhaitées. Vous recevrez un numéro de réservation par email que vous devez conserver.</p>
                    </div>
                    <div class="bg-white rounded-lg p-6 shadow-md">
                        <h3 class="text-xl font-bold mb-3">Quels sont les délais de confirmation ?</h3>
                        <p class="text-gray-600">Nous traitons toutes les demandes dans les 2 heures ouvrables. Vous recevrez un email de confirmation ou de refus avec les détails.</p>
                    </div>
                    <div class="bg-white rounded-lg p-6 shadow-md">
                        <h3 class="text-xl font-bold mb-3">La livraison est-elle gratuite ?</h3>
                        <p class="text-gray-600">Oui, nous livrons gratuitement dans toute la région PACA. Pour les autres régions, contactez-nous pour un devis personnalisé.</p>
                    </div>
                    <div class="bg-white rounded-lg p-6 shadow-md">
                        <h3 class="text-xl font-bold mb-3">Puis-je modifier ma réservation ?</h3>
                        <p class="text-gray-600">Contactez-nous sur WhatsApp avec votre numéro de réservation. Les modifications sont possibles selon disponibilité.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-20">
        <div class="container mx-auto px-6">
            <h2 class="text-4xl font-bold text-center mb-12">Contactez-nous</h2>
            <div class="max-w-lg mx-auto text-center">
                <p class="text-xl mb-8">Pour toute question ou demande spécifique, contactez-nous sur WhatsApp</p>
                <a href="https://wa.me/33663187902" class="bg-green-500 text-white px-8 py-4 rounded-md text-lg font-semibold hover:bg-green-600 transition duration-300 inline-flex items-center">
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
                        <li><a href="#vehicles" class="hover:text-gray-300">Véhicules</a></li>
                        <li><a href="#reservation" class="hover:text-gray-300">Réservation</a></li>
                        <li><a href="#contact" class="hover:text-gray-300">Contact</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-12 pt-8 text-center">
                <p>&copy; 2024 DD RENTAL CAR. Tous droits réservés.</p>
            </div>
        </div>
    </footer>
</body>
</html>
