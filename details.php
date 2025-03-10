<?php
require_once __DIR__ . '/includes/api.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header('Location: index.php');
    exit;
}

$query = '
query ($id: Int) {
    Media(id: $id, type: ANIME) {
        id
        title {
            romaji
            english
            native
        }
        coverImage {
            large
            extraLarge
        }
        bannerImage
        description(asHtml: false)
        episodes
        duration
        status
        season
        seasonYear
        averageScore
        popularity
        genres
        tags {
            name
            rank
        }
        studios {
            nodes {
                name
            }
        }
        characters(sort: [ROLE, RELEVANCE], perPage: 12) {
            nodes {
                name {
                    full
                }
                image {
                    medium
                }
                
            }
          edges {
            role
          }
        }
        streamingEpisodes {
            title
            thumbnail
            url
        }
    }
}';

$variables = ['id' => $id];
$response = fetchFromAniList($query, $variables);

if (!isset($response['data']['Media'])) {
    header('Location: index.php');
    exit;
}

$anime = $response['data']['Media'];

require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        <!-- Bannière avec overlay -->
        <?php if ($anime['bannerImage']): ?>
        <div class="h-96 relative">
            <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('<?= $anime['bannerImage'] ?>');"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-black/90 to-black/20"></div>
        </div>
        <?php endif; ?>

        <div class="relative <?= $anime['bannerImage'] ? '-mt-48' : '' ?> px-6 py-8">
            <div class="flex flex-col md:flex-row gap-8">
                <!-- Image de couverture -->
                <div class="md:w-1/3 lg:w-1/4 flex-shrink-0">
                    <div class="relative group">
                        <img src="<?= $anime['coverImage']['extraLarge'] ?>" 
                             alt="<?= htmlspecialchars($anime['title']['romaji']) ?>"
                             class="w-full rounded-lg shadow-2xl">
                        <button onclick="toggleFavorite(<?= $anime['id'] ?>)" 
                                class="favorite-btn absolute top-4 right-4 p-2 rounded-full bg-white/90 hover:bg-white shadow-lg transition"
                                data-id="<?= $anime['id'] ?>">
                            <svg class="w-6 h-6 favorite-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"></svg>
                        </button>
                    </div>
                </div>

                <!-- Informations -->
                <div class="flex-1">
                    <h1 class="text-4xl font-bold text-gray-900 mb-2">
                        <?= htmlspecialchars($anime['title']['romaji']) ?>
                    </h1>
                    <?php if ($anime['title']['english']): ?>
                    <h2 class="text-2xl text-gray-600 mb-4">
                        <?= htmlspecialchars($anime['title']['english']) ?>
                    </h2>
                    <?php endif; ?>

                    <!-- Stats -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                        <div class="bg-gradient-to-br from-indigo-50 to-purple-50 p-4 rounded-xl text-center">
                            <div class="text-2xl font-bold text-indigo-600"><?= $anime['averageScore'] ?>%</div>
                            <div class="text-sm text-gray-600">Score</div>
                        </div>
                        <div class="bg-gradient-to-br from-indigo-50 to-purple-50 p-4 rounded-xl text-center">
                            <div class="text-2xl font-bold text-indigo-600"><?= $anime['episodes'] ?? '?' ?></div>
                            <div class="text-sm text-gray-600">Épisodes</div>
                        </div>
                        <div class="bg-gradient-to-br from-indigo-50 to-purple-50 p-4 rounded-xl text-center">
                            <div class="text-2xl font-bold text-indigo-600"><?= $anime['duration'] ?? '?' ?></div>
                            <div class="text-sm text-gray-600">Minutes</div>
                        </div>
                        <div class="bg-gradient-to-br from-indigo-50 to-purple-50 p-4 rounded-xl text-center">
                            <div class="text-2xl font-bold text-indigo-600"><?= $anime['popularity'] ?></div>
                            <div class="text-sm text-gray-600">Popularité</div>
                        </div>
                    </div>

                    <!-- Synopsis -->
                    <div class="prose max-w-none mb-8">
                        <h3 class="text-xl font-semibold mb-4">Synopsis</h3>
                        <p class="text-gray-600 leading-relaxed">
                            <?= $anime['description'] ?>
                        </p>
                    </div>

                    <!-- Genres -->
                    <div class="mb-8">
                        <h3 class="text-xl font-semibold mb-4">Genres</h3>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($anime['genres'] as $genre): ?>
                            <span class="px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-full text-sm">
                                <?= $genre ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Personnages -->
    <?php if (!empty($anime['characters']['nodes'])): ?>
    <div class="mt-8">
        <h2 class="text-2xl font-bold mb-6">Personnages Principaux</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
            <?php foreach (array_slice($anime['characters']['nodes'], 0, 12) as $character): ?>
            <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-lg transition-shadow">
                <img src="<?= $character['image']['medium'] ?>" 
                     alt="<?= htmlspecialchars($character['name']['full']) ?>"
                     class="w-full h-48 object-cover">
                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 truncate">
                        <?= htmlspecialchars($character['name']['full']) ?>
                    </h3>
                    <p class="text-sm text-gray-500"><?= $character['role'] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function toggleFavorite(animeId) {
    let favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
    const index = favorites.indexOf(animeId);
    
    if (index === -1) {
        favorites.push(animeId);
    } else {
        favorites.splice(index, 1);
    }
    
    localStorage.setItem('favorites', JSON.stringify(favorites));
    updateFavoriteButton(animeId);
}

function updateFavoriteButton(animeId) {
    const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
    const btn = document.querySelector(`[data-id="${animeId}"] .favorite-icon`);
    
    if (favorites.includes(animeId)) {
        btn.innerHTML = '<path fill="currentColor" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"/>';
        btn.style.color = '#ec4899';
    } else {
        btn.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>';
        btn.style.color = '#6b7280';
    }
}

// Initialize favorite button state
document.addEventListener('DOMContentLoaded', () => {
    const animeId = <?= $id ?>;
    updateFavoriteButton(animeId);
});
</script>

<?php require_once 'includes/footer.php'; ?>
