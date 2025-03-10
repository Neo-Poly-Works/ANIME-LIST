<?php
require_once __DIR__ . '/includes/api.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$genre = isset($_GET['genre']) ? $_GET['genre'] : null;

$query = '
query ($page: Int, $genre: String) {
    Page(page: $page, perPage: 24) {
        pageInfo {
            total
            currentPage
            lastPage
            hasNextPage
            perPage
        }
        media(sort: POPULARITY_DESC, type: ANIME, genre: $genre) {
            id
            title {
                romaji
                english
            }
            coverImage {
                large
            }
            averageScore
            genres
            episodes
            status
            season
            seasonYear
        }
    }
    GenreCollection
}';

$variables = [
    'page' => $page,
    'genre' => $genre
];

$response = fetchFromAniList($query, $variables);
$animes = $response['data']['Page']['media'];
$genres = $response['data']['GenreCollection'];
$pageInfo = $response['data']['Page']['pageInfo'];

require_once 'includes/header.php';
?>

<div class="container mx-auto px-4">
    <div class="flex flex-col md:flex-row gap-6">
        <!-- Filtres -->
        <div class="w-full md:w-64 bg-white dark:bg-gray-800 p-4 rounded-lg shadow-lg h-fit">
            <h2 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Filtres</h2>
            <form action="" method="GET">
                <select name="genre" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Tous les genres</option>
                    <?php foreach ($genres as $g): ?>
                        <option value="<?= $g ?>" <?= $genre === $g ? 'selected' : '' ?>><?= $g ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="mt-4 w-full bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">
                    Filtrer
                </button>
            </form>
        </div>

        <!-- Liste des animes -->
        <div class="flex-1">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php foreach ($animes as $anime): ?>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden transform hover:scale-105 transition duration-200">
                    <img src="<?= $anime['coverImage']['large']; ?>" 
                         alt="<?= htmlspecialchars($anime['title']['romaji']); ?>"
                         class="w-full h-80 object-cover">
                    <div class="p-4">
                        <h2 class="text-lg font-semibold mb-2 text-gray-800 dark:text-white">
                            <?= htmlspecialchars($anime['title']['romaji']); ?>
                        </h2>
                        <div class="flex flex-wrap gap-2 mb-2">
                            <?php foreach (array_slice($anime['genres'], 0, 3) as $genre): ?>
                                <span class="px-2 py-1 bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 text-sm rounded">
                                    <?= $genre ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                        <div class="flex justify-between items-center text-sm text-gray-600 dark:text-gray-400">
                            <span>Épisodes: <?= $anime['episodes'] ?? '?' ?></span>
                            <span>Score: <?= $anime['averageScore'] ?>%</span>
                        </div>
                        <a href="details.php?id=<?= $anime['id']; ?>" 
                           class="mt-4 inline-block w-full text-center bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">
                            Plus de détails
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <div class="mt-8 flex justify-center gap-2">
                <?php if ($pageInfo['currentPage'] > 1): ?>
                    <a href="?page=<?= $pageInfo['currentPage'] - 1 ?>&genre=<?= $genre ?>" 
                       class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition">
                        Précédent
                    </a>
                <?php endif; ?>
                
                <?php if ($pageInfo['hasNextPage']): ?>
                    <a href="?page=<?= $pageInfo['currentPage'] + 1 ?>&genre=<?= $genre ?>" 
                       class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition">
                        Suivant
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
