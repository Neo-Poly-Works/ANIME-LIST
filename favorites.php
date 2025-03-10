<?php
require_once 'includes/api.php';

$query = '
query ($ids: [Int]) {
    Page {
        media(id_in: $ids, type: ANIME) {
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
        }
    }
}';

require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Mes Favoris</h1>

    <div id="favorites-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-6">
        <!-- Sera rempli par JavaScript -->
    </div>

    <div id="empty-state" class="hidden text-center py-12">
        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
        </svg>
        <p class="text-xl text-gray-600">Vous n'avez pas encore de favoris</p>
        <a href="index.php" class="mt-4 inline-block px-6 py-2 bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-lg hover:opacity-90 transition">
            Découvrir des animes
        </a>
    </div>
</div>

<script>
async function loadFavorites() {
    const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
    const container = document.getElementById('favorites-container');
    const emptyState = document.getElementById('empty-state');

    if (favorites.length === 0) {
        container.innerHTML = '';
        emptyState.classList.remove('hidden');
        return;
    }

    try {
        const response = await fetch('https://graphql.anilist.co', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                query: document.querySelector('#graphql-query').textContent,
                variables: { ids: favorites }
            })
        });

        if (!response.ok) throw new Error('Network response was not ok');
        
        const data = await response.json();
        if (!data || !data.data || !data.data.Page || !data.data.Page.media) {
            throw new Error('Invalid response format');
        }

        const animes = data.data.Page.media;
        emptyState.classList.add('hidden');

        container.innerHTML = animes.map(anime => `
            <div class="group bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-xl transition-all duration-300">
                <div class="relative">
                    <img src="${anime.coverImage.large}" 
                         alt="${anime.title.romaji}"
                         class="w-full h-[300px] object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <div class="absolute bottom-0 left-0 right-0 p-4">
                            <a href="details.php?id=${anime.id}" 
                               class="inline-block w-full text-center bg-white/90 backdrop-blur-sm text-gray-800 px-4 py-2 rounded-lg hover:bg-white transition">
                                Voir les détails
                            </a>
                        </div>
                    </div>
                </div>
                <div class="p-4">
                    <h2 class="text-lg font-semibold text-gray-800 line-clamp-1">
                        ${anime.title.romaji}
                    </h2>
                    <div class="flex items-center justify-between mt-2 text-sm text-gray-600">
                        <span class="flex items-center">
                            <svg class="w-4 h-4 text-yellow-400 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            ${anime.averageScore}%
                        </span>
                        <button onclick="removeFavorite(${anime.id})" class="text-red-500 hover:text-red-600">
                            Retirer
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
    } catch (error) {
        console.error('Error:', error);
        container.innerHTML = `
            <div class="col-span-full text-center py-8 text-red-500">
                Une erreur est survenue lors du chargement des favoris.
            </div>`;
    }
}

function removeFavorite(animeId) {
    let favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
    const index = favorites.indexOf(animeId);
    if (index > -1) {
        favorites.splice(index, 1);
        localStorage.setItem('favorites', JSON.stringify(favorites));
        loadFavorites();
    }
}

document.addEventListener('DOMContentLoaded', loadFavorites);

// Add this hidden element to store the GraphQL query
document.body.insertAdjacentHTML('beforeend', `
    <script type="text/plain" id="graphql-query">
        ${document.querySelector('script[type="text/plain"]')?.innerHTML || ''}
    </script>
`);
</script>

<?php require_once 'includes/footer.php'; ?>