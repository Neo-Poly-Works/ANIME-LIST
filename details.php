<?php
require_once __DIR__ . '/includes/api.php';
require_once 'Parsedown.php';

use Markdown\Parsedown;

$parsedown = new Parsedown();

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
        characters(sort: [ROLE, RELEVANCE]) {
            nodes {
                name {
                    full
                }
                image {
                    medium
                }
                age
                gender
                description
                bloodType
                favourites
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
                        <p class="text-gray-600 leading-relaxed markdown-content">
                            <?= $parsedown->text($anime['description']); ?>
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

                    <!-- Tags -->
                    <?php if (!empty($anime['tags'])): ?>
                    <div class="mb-8">
                        <h3 class="text-xl font-semibold mb-4">Tags</h3>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($anime['tags'] as $tag): ?>
                            <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm flex items-center">
                                <?= $tag['name'] ?>
                                <span class="ml-2 text-xs text-gray-500"><?= $tag['rank'] ?>%</span>
                            </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Studios -->
                    <?php if (!empty($anime['studios']['nodes'])): ?>
                    <div class="mb-8">
                        <h3 class="text-xl font-semibold mb-4">Studios</h3>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($anime['studios']['nodes'] as $studio): ?>
                            <span class="px-4 py-2 bg-gray-100 text-gray-700 rounded-full text-sm">
                                <?= $studio['name'] ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Personnages -->
    <?php if (!empty($anime['characters']['nodes'])): ?>
    <div class="mt-8">
        <h2 class="text-2xl font-bold mb-6">Personnages</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
            <?php 
            $characters = $anime['characters']['nodes'];
            $edges = $anime['characters']['edges'];
            for($i = 0; $i < count($characters); $i++): 
                $character = $characters[$i];
                $edge = $edges[$i];
                $isMain = $edge['role'] === 'MAIN';
                $cardClass = $isMain ? 'bg-gradient-to-br from-indigo-50 to-purple-50 ring-2 ring-purple-500' : 'bg-white';
                $roleClass = $isMain ? 'text-purple-600 font-medium' : 'text-gray-500';
            ?>
            <div class="<?= $cardClass ?> rounded-xl shadow-sm overflow-hidden hover:shadow-lg transition-shadow cursor-pointer"
                 onclick="showCharacterDetails(<?= htmlspecialchars(json_encode($character)) ?>)">
                <div class="relative">
                    <img src="<?= $character['image']['medium'] ?>" 
                         alt="<?= htmlspecialchars($character['name']['full']) ?>"
                         class="w-full h-48 object-cover">
                    <?php if ($isMain): ?>
                    <div class="absolute top-2 right-2">
                        <span class="px-2 py-1 bg-purple-500 text-white text-xs rounded-full">
                            Principal
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 truncate">
                        <?= htmlspecialchars($character['name']['full']) ?>
                    </h3>
                    <p class="text-sm <?= $roleClass ?>">
                        <?= $edge['role'] === 'MAIN' ? 'Personnage Principal' : 'Personnage Secondaire' ?>
                    </p>
                </div>
            </div>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Character Modal -->
    <div id="characterModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-2xl font-bold text-gray-900" id="modalCharacterName"></h3>
                    <button onclick="closeCharacterModal()" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="flex flex-col md:flex-row gap-6">
                    <div class="w-full md:w-1/3">
                        <img id="modalCharacterImage" class="w-full rounded-lg" src="" alt="">
                    </div>
                    <div class="flex-1">
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <div class="text-sm text-gray-600">Age</div>
                                <div id="modalCharacterAge" class="font-semibold"></div>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <div class="text-sm text-gray-600">Gender</div>
                                <div id="modalCharacterGender" class="font-semibold"></div>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <div class="text-sm text-gray-600">Blood Type</div>
                                <div id="modalCharacterBloodType" class="font-semibold"></div>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <div class="text-sm text-gray-600">Favorites</div>
                                <div id="modalCharacterFavorites" class="font-semibold"></div>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold mb-2">Description</h4>
                            <p id="modalCharacterDescription" class="text-gray-600 markdown-content"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Episodes -->
    <?php if (!empty($anime['streamingEpisodes'])): ?>
    <div class="mt-8">
        <h2 class="text-2xl font-bold mb-6">Episodes disponibles</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($anime['streamingEpisodes'] as $episode): ?>
            <a href="<?= $episode['url'] ?>" target="_blank" 
               class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-lg transition-shadow">
                <img src="<?= $episode['thumbnail'] ?>" 
                     alt="<?= htmlspecialchars($episode['title']) ?>"
                     class="w-full h-32 object-cover">
                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 text-sm">
                        <?= htmlspecialchars($episode['title']) ?>
                    </h3>
                </div>
            </a>
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

function showCharacterDetails(character) {
    document.getElementById('modalCharacterName').textContent = character.name.full;
    document.getElementById('modalCharacterImage').src = character.image.medium;
    document.getElementById('modalCharacterAge').textContent = character.age || 'Unknown';
    document.getElementById('modalCharacterGender').textContent = character.gender || 'Unknown';
    document.getElementById('modalCharacterBloodType').textContent = character.bloodType || 'Unknown';
    document.getElementById('modalCharacterFavorites').textContent = character.favourites || '0';
    document.getElementById('modalCharacterDescription').innerHTML = 
        `${character.description ? <?php echo json_encode($parsedown->text('${character.description}')); ?> : 'No description available'}`;
    
    document.getElementById('characterModal').style.display = 'flex';
}

function closeCharacterModal() {
    document.getElementById('characterModal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('characterModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCharacterModal();
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
