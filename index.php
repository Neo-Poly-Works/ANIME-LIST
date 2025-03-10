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
        <div class="w-full md:w-64 bg-white p-6 rounded-xl shadow-sm">
            <h2 class="text-xl font-bold mb-6 text-gray-800">Filtres</h2>
            <form action="" method="GET">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Genre</label>
                        <select name="genre" class="w-full p-2 border border-gray-200 rounded-lg focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">Tous les genres</option>
                            <?php foreach ($genres as $g): ?>
                                <option value="<?= $g ?>" <?= $genre === $g ? 'selected' : '' ?>><?= $g ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="w-full px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-lg hover:opacity-90 transition">
                        Appliquer les filtres
                    </button>
                </div>
            </form>
        </div>

        <!-- Liste des animes -->
        <div class="flex-1 overflow-x-hidden flex-start">
            <div class="carousel-3d relative h-[600px]">
                <div class="swiper w-full">
                    <div class="swiper-wrapper">
                        <?php foreach ($animes as $anime): ?>
                        <div class="swiper-slide" style="width: 300px;">
                            <div class="carousel-item group bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-xl transition-all duration-300">
                                <div class="relative">
                                    <img src="<?= $anime['coverImage']['large']; ?>" 
                                         alt="<?= htmlspecialchars($anime['title']['romaji']); ?>"
                                         class="w-[300px] h-[400px] object-cover bg-black">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                        <div class="absolute top-4 right-4">
                                            <button onclick="toggleFavorite(<?= $anime['id'] ?>, event)" 
                                                    class="favorite-btn p-2 rounded-full bg-white/90 hover:bg-white shadow-lg transition"
                                                    data-id="<?= $anime['id'] ?>">
                                                <svg class="w-6 h-6 favorite-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                                </svg>
                                            </button>
                                        </div>
                                        <div class="absolute bottom-0 left-0 right-0 p-4">
                                            <div class="flex flex-wrap gap-2 mb-2">
                                                <?php foreach (array_slice($anime['genres'], 0, 3) as $genre): ?>
                                                    <span class="px-2 py-1 text-xs bg-white/20 text-white backdrop-blur-sm rounded-full">
                                                        <?= $genre ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                            <a href="details.php?id=<?= $anime['id']; ?>" 
                                               class="inline-block w-full text-center bg-white/90 backdrop-blur-sm text-gray-800 px-4 py-2 rounded-lg hover:bg-white transition">
                                                Voir les détails
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <h2 class="text-lg font-semibold text-gray-800 line-clamp-2 mb-2">
                                        <?= htmlspecialchars($anime['title']['romaji']); ?>
                                    </h2>
                                    <div class="flex items-center justify-between text-sm text-gray-600">
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 text-yellow-400 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                            <?= $anime['averageScore'] ?>%
                                        </span>
                                        <span><?= $anime['episodes'] ?? '?' ?> épisodes</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="swiper-button-next rounded-lg bg-gray-300 opacity-80 p-4"></div>
                    <div class="swiper-button-prev rounded-lg bg-gray-300 opacity-80 p-4"></div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-8 flex justify-center gap-2">
                <?php if ($pageInfo['currentPage'] > 1): ?>
                    <a href="?page=<?= $pageInfo['currentPage'] - 1 ?>&genre=<?= $genre ?>" 
                       class="px-6 py-2 bg-white text-gray-800 rounded-lg hover:bg-gray-50 transition">
                        ← Précédent
                    </a>
                <?php endif; ?>
                
                <?php if ($pageInfo['hasNextPage']): ?>
                    <a href="?page=<?= $pageInfo['currentPage'] + 1 ?>&genre=<?= $genre ?>" 
                       class="px-6 py-2 bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-lg hover:opacity-90 transition">
                        Suivant →
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function toggleFavorite(animeId, event) {
    event.preventDefault();
    event.stopPropagation();
    
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
    const btns = document.querySelectorAll(`[data-id="${animeId}"] .favorite-icon`);
    
    btns.forEach(btn => {
        if (favorites.includes(animeId)) {
            btn.innerHTML = '<path fill="currentColor" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"/>';
            btn.style.color = '#ec4899';
        } else {
            btn.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>';
            btn.style.color = '#6b7280';
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const swiper = new Swiper('.swiper', {
        effect: 'coverflow',
        grabCursor: true,
        centeredSlides: true,
        slidesPerView: 'auto',
        initialSlide: 2,
        coverflowEffect: {
            rotate: 20,
            stretch: 0,
            depth: 350,
            modifier: 1,
            slideShadows: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        // Ajout de ces paramètres
        observer: true,
        observeParents: true
    });

    // Initialize all favorite buttons
    const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
    favorites.forEach(animeId => {
        updateFavoriteButton(animeId);
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
