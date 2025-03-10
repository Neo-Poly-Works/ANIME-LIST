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
        <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
            <form action="" method="GET" class="flex gap-2">
                <input type="text" 
                       name="q" 
                       value="<?= htmlspecialchars($search) ?>"
                       placeholder="Rechercher un anime..." 
                       class="flex-1 px-4 py-2 rounded-lg border-2 border-indigo-100 focus:border-indigo-300 focus:outline-none"
                       required>
                <button type="submit" 
                        class="px-6 py-2 bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-lg hover:opacity-90 transition">
                    Rechercher
                </button>
            </form>
        </div>

        <?php if (isset($animes) && !empty($animes['data']['Page']['media'])): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($animes['data']['Page']['media'] as $anime): ?>
                    <div class="group bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-xl transition-all duration-300">
                        <div class="relative">
                            <img src="<?= $anime['coverImage']['large'] ?>" 
                                 alt="<?= htmlspecialchars($anime['title']['romaji']) ?>"
                                 class="w-full h-[250px] object-cover group-hover:scale-105 transition-transform duration-300">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
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
                <?php endforeach; ?>
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

<?php require_once 'includes/footer.php'; ?>
