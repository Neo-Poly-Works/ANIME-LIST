<?php
require_once __DIR__ . '/includes/api.php';

$query = '
query {
    Page(page: 1, perPage: 20) {
        media(sort: POPULARITY_DESC, type: ANIME) {
            id
            title {
                romaji
                english
            }
            coverImage {
                medium
            }
            averageScore
        }
    }
}';

$animes = fetchFromAniList($query);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AniList Explorer</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-center text-indigo-600">AniList Explorer</h1>
            <form action="search.php" method="GET" class="mt-4 flex justify-center">
                <input type="text" name="q" placeholder="Rechercher un anime..." 
                       class="w-full max-w-md px-4 py-2 rounded-l border focus:outline-none focus:border-indigo-500">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-r hover:bg-indigo-700">
                    Rechercher
                </button>
            </form>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($animes['data']['Page']['media'] as $anime): ?>
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <img src="<?php echo $anime['coverImage']['medium']; ?>" 
                     alt="<?php echo $anime['title']['romaji']; ?>"
                     class="w-full h-64 object-cover">
                <div class="p-4">
                    <h2 class="text-lg font-semibold mb-2"><?php echo $anime['title']['romaji']; ?></h2>
                    <p class="text-gray-600">Score: <?php echo $anime['averageScore']; ?>%</p>
                    <a href="details.php?id=<?php echo $anime['id']; ?>" 
                       class="mt-4 inline-block bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                        Plus de détails
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
