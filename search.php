<?php
require_once 'includes/api.php';

$search = isset($_GET['q']) ? $_GET['q'] : '';

if ($search) {
    $query = '
    query ($search: String) {
        Page(page: 1, perPage: 24) {
            media(search: $search, type: ANIME) {
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
    }';

    $variables = ['search' => $search];
    $animes = fetchFromAniList($query, $variables);
}

require_once 'includes/header.php';
?>

<div class="container mx-auto px-4">
    <div class="max-w-4xl mx-auto">
        <?php if (isset($animes) && !empty($animes['data']['Page']['media'])): ?>
            <div class="carousel-3d relative h-[600px]">
                <div class="swiper w-full">
                    <div class="swiper-wrapper">
                        <?php foreach ($animes['data']['Page']['media'] as $anime): ?>
                        <div class="swiper-slide" style="width: 300px;">
                            <div class="carousel-item group bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-xl transition-all duration-300">
                                <div class="relative">
                                    <img src="<?= $anime['coverImage']['large'] ?>" 
                                         alt="<?= htmlspecialchars($anime['title']['romaji']) ?>"
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
                                                <?php foreach (array_slice($anime['genres'], 0, 2) as $genre): ?>
                                                    <span class="px-2 py-1 text-xs bg-white/20 text-white backdrop-blur-sm rounded-full">
                                                        <?= $genre ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                            <a href="details.php?id=<?= $anime['id'] ?>" 
                                               class="inline-block w-full text-center bg-white/90 backdrop-blur-sm text-gray-800 px-4 py-2 rounded-lg hover:bg-white transition">
                                                Voir les détails
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <h2 class="text-lg font-semibold text-gray-800 line-clamp-1 mb-2">
                                        <?= htmlspecialchars($anime['title']['romaji']) ?>
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
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <?php if ($search): ?>
                    <div class="mb-4">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-xl text-gray-600">Aucun résultat trouvé pour "<?= htmlspecialchars($search) ?>"</p>
                        <p class="text-gray-500 mt-2">Essayez avec des mots-clés différents</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
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
    // Initialize Swiper
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
        observer: true,
        observeParents: true
    });

    // Initialize favorite buttons
    const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
    favorites.forEach(animeId => {
        updateFavoriteButton(animeId);
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
