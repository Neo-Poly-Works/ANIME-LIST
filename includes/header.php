<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AniList Explorer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Electrolize:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <link href="https://unpkg.com/swiper/swiper-bundle.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Electrolize', sans-serif; }
        .carousel-3d {
            perspective: 1200px;
        }
        .swiper-slide {
            width: 300px !important;
            margin-right: 30px;
        }
        .swiper-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .swiper {
            width: 100%;
            padding: 50px;
        }
        .swiper-3d .swiper-slide-shadow-left,
        .swiper-3d .swiper-slide-shadow-right {
            background-image: none;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-50 to-purple-50 min-h-screen">
    <nav class="bg-white shadow-lg fixed w-full z-10">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <a href="index.php" class="text-2xl font-bold text-indigo-600">
                    AniList<span class="text-purple-600">Explorer</span>
                </a>
                <form action="search.php" method="GET" class="hidden md:flex items-center flex-1 max-w-2xl mx-8">
                    <input type="text" name="q" placeholder="Rechercher un anime..." 
                           class="w-full px-4 py-2 rounded-lg border-2 border-indigo-100 focus:border-indigo-300 focus:outline-none"
                           required>
                    <button type="submit" class="ml-2 px-6 py-2 bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-lg hover:opacity-90 transition">
                        Rechercher
                    </button>
                </form>
                <div class="flex items-center space-x-4">
                    <a href="favorites.php" class="text-gray-600 hover:text-indigo-600 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </nav>
    <div class="pt-20">
</body>
</html>
