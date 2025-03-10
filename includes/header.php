<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Primary Meta Tags -->
    <title>AniList Explorer - Découvrez et explorez vos animes préférés</title>
    <meta name="title" content="AniList Explorer - Découvrez et explorez vos animes préférés">
    <meta name="description" content="Explorez une vaste collection d'animes, découvrez de nouvelles séries, gérez vos favoris et restez informé des dernières sorties. La meilleure plateforme pour les fans d'anime.">
    <meta name="keywords" content="anime, manga, japon, animation japonaise, streaming anime, séries animées, AniList, explorer anime">
    <meta name="author" content="AniList Explorer">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://anime-list.neopolyworks.fr">
    <meta property="og:title" content="AniList Explorer - Découvrez et explorez vos animes préférés">
    <meta property="og:description" content="Explorez une vaste collection d'animes, découvrez de nouvelles séries, gérez vos favoris et restez informé des dernières sorties.">
    <meta property="og:image" content="assets/images/og-image.jpg">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://anime-list.neopolyworks.fr">
    <meta property="twitter:title" content="AniList Explorer - Découvrez et explorez vos animes préférés">
    <meta property="twitter:description" content="Explorez une vaste collection d'animes, découvrez de nouvelles séries, gérez vos favoris et restez informé des dernières sorties.">
    <meta property="twitter:image" content="assets/images/og-image.jpg">

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="assets/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/favicon/favicon-16x16.png">
    <link rel="manifest" href="assets/site.webmanifest">
    <link rel="mask-icon" href="assets/favicon/safari-pinned-tab.svg" color="#6366f1">
    <meta name="msapplication-TileColor" content="#6366f1">
    <meta name="theme-color" content="#6366f1">

    <!-- Styles et Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Electrolize:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <link href="https://unpkg.com/swiper/swiper-bundle.min.css" rel="stylesheet">
    <style>
        .markdown-content p {
            margin-bottom: 1rem !important;
            line-height: 1.5 !important;
        }

        .markdown-content strong {
            font-weight: 700 !important;
            color: inherit !important;
        }

        .markdown-content em {
            font-style: italic !important;
        }

        .markdown-content h1 {
            font-size: 2.25rem !important;
            font-weight: 800 !important;
            margin-bottom: 1rem !important;
            margin-top: 2rem !important;
            color: inherit !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
            padding-bottom: 0.5rem !important;
        }

        .markdown-content h2 {
            font-size: 1.75rem !important;
            font-weight: 700 !important;
            margin-bottom: 0.875rem !important;
            margin-top: 1.75rem !important;
            color: inherit !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
            padding-bottom: 0.25rem !important;
        }

        .markdown-content h3 {
            font-size: 1.5rem !important;
            font-weight: 700 !important;
            margin-bottom: 0.75rem !important;
            margin-top: 1.5rem !important;
            color: inherit !important;
        }

        .markdown-content h4 {
            font-size: 1.25rem !important;
            font-weight: 600 !important;
            margin-bottom: 0.5rem !important;
            margin-top: 1.25rem !important;
            color: inherit !important;
        }

        .markdown-content ul,
        .markdown-content ol {
            padding-left: 1.5rem !important;
            margin-bottom: 1rem !important;
        }

        .markdown-content ul {
            list-style-type: disc !important;
        }

        .markdown-content ol {
            list-style-type: decimal !important;
        }

        .markdown-content li {
            margin-bottom: 0.5rem !important;
            line-height: 1.5 !important;
        }

        .markdown-content hr {
            border-top: 1px solid currentColor !important;
            margin: 1.5rem 0 !important;
            opacity: 0.3 !important;
        }

        body {
            font-family: 'Electrolize', sans-serif;
        }

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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </nav>
    <div class="pt-20">