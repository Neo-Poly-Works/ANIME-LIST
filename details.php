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
        format
        startDate {
            year
            month
            day
        }
        endDate {
            year
            month
            day
        }
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
                isAnimationStudio
            }
        }
        characters(sort: [ROLE, RELEVANCE], perPage: 8) {
            nodes {
                name {
                    full
                    native
                }
                image {
                    medium
                    large
                }
                age
                gender
                description
                bloodType
                favourites
                dateOfBirth {
                    year
                    month
                    day
                }
            }
            edges {
                role
                voiceActors(language: JAPANESE) {
                    name {
                        full
                    }
                    image {
                        medium
                    }
                }
            }
        }
        streamingEpisodes {
            title
            thumbnail
            url
            site
        }
        recommendations(sort: RATING_DESC, perPage: 1000) {
            nodes {
                mediaRecommendation {
                    id
                    title {
                        romaji
                    }
                    coverImage {
                        medium
                    }
                    averageScore
                }
            }
        }
        relations {
            edges {
                relationType
                node {
                    id
                    title {
                        romaji
                    }
                    coverImage {
                        medium
                    }
                    type
                    format
                }
            }
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

// Traduire le statut
$statusTranslations = [
    'FINISHED' => 'Terminé',
    'RELEASING' => 'En cours',
    'NOT_YET_RELEASED' => 'À venir',
    'CANCELLED' => 'Annulé',
    'HIATUS' => 'En pause'
];

$formatTranslations = [
    'TV' => 'Série TV',
    'MOVIE' => 'Film',
    'OVA' => 'OVA',
    'ONA' => 'ONA',
    'SPECIAL' => 'Spécial',
    'MUSIC' => 'Clip Musical',
    'MANGA' => 'Manga',
    'NOVEL' => 'Roman',
    'ONE_SHOT' => 'One Shot'
];

$seasonTranslations = [
    'WINTER' => 'Hiver',
    'SPRING' => 'Printemps',
    'SUMMER' => 'Été',
    'FALL' => 'Automne'
];

$relationTranslations = [
    'ADAPTATION' => 'Adaptation',
    'PREQUEL' => 'Préquel',
    'SEQUEL' => 'Suite',
    'PARENT' => 'Œuvre principale',
    'SIDE_STORY' => 'Histoire parallèle',
    'CHARACTER' => 'Personnage commun',
    'SUMMARY' => 'Résumé',
    'ALTERNATIVE' => 'Alternative',
    'SPIN_OFF' => 'Spin-off',
    'OTHER' => 'Autre'
];

$status = isset($statusTranslations[$anime['status']]) ? $statusTranslations[$anime['status']] : $anime['status'];
$format = isset($formatTranslations[$anime['format']]) ? $formatTranslations[$anime['format']] : $anime['format'];
$season = isset($seasonTranslations[$anime['season']]) ? $seasonTranslations[$anime['season']] : $anime['season'];

function formatDate($date)
{
    if (!$date['year']) return 'Inconnue';

    if ($date['month'] && $date['day']) {
        return sprintf('%02d/%02d/%04d', $date['day'], $date['month'], $date['year']);
    } elseif ($date['month']) {
        return sprintf('%02d/%04d', $date['month'], $date['year']);
    } else {
        return $date['year'];
    }
}

$startDate = formatDate($anime['startDate']);
$endDate = formatDate($anime['endDate']);
$airing = $startDate . ($anime['status'] === 'FINISHED' ? ' au ' . $endDate : '');

require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <nav class="text-sm mb-6">
        <ol class="flex items-center space-x-2">
            <li><a href="index.php" class="text-indigo-600 hover:text-indigo-800">Accueil</a></li>
            <li class="flex items-center space-x-2">
                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>
                <a href="search.php?type=ANIME" class="text-indigo-600 hover:text-indigo-800">Animes</a>
            </li>
            <li class="flex items-center space-x-2">
                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-gray-500 truncate max-w-xs"><?= htmlspecialchars($anime['title']['romaji']) ?></span>
            </li>
        </ol>
    </nav>

    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        <?php if ($anime['bannerImage']): ?>
            <div class="h-80 md:h-96 relative">
                <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('<?= $anime['bannerImage'] ?>');"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-black/90 to-black/20"></div>
            </div>
        <?php endif; ?>

        <div class="relative <?= $anime['bannerImage'] ? '-mt-48' : '' ?> px-6 py-8">
            <div class="flex flex-col md:flex-row gap-8">
                <div class="md:w-1/3 lg:w-1/4 flex-shrink-0">
                    <div class="relative group rounded-lg overflow-hidden shadow-2xl">
                        <img src="<?= $anime['coverImage']['extraLarge'] ?>"
                            alt="<?= htmlspecialchars($anime['title']['romaji']) ?>"
                            class="w-full transition duration-300 group-hover:scale-105">
                        <div class="absolute bottom-0 inset-x-0 h-16 bg-gradient-to-t from-black to-transparent"></div>
                        <button onclick="toggleFavorite(<?= $anime['id'] ?>)"
                            class="favorite-btn absolute top-4 right-4 p-2 rounded-full bg-white/90 hover:bg-white shadow-lg transition transform hover:scale-110"
                            data-id="<?= $anime['id'] ?>">
                            <svg class="w-6 h-6 favorite-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"></svg>
                        </button>
                        <div class="absolute bottom-4 left-4">
                            <span class="px-3 py-1 bg-indigo-600 text-white text-sm rounded-full shadow-lg">
                                <?= $format ?>
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-col space-y-3 mt-4">
                        <button onclick="toggleFavorite(<?= $anime['id'] ?>)" id="watchlistBtn" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow transition flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            <span>Ajouter à mes favoris</span>
                        </button>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-4 mt-4">
                        <h3 class="text-lg font-semibold mb-3">Informations</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Format</span>
                                <span class="font-medium"><?= $format ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Statut</span>
                                <span class="font-medium"><?= $status ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Diffusion</span>
                                <span class="font-medium"><?= $airing ?></span>
                            </div>
                            <?php if ($anime['season'] && $anime['seasonYear']): ?>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Saison</span>
                                    <span class="font-medium"><?= $season ?> <?= $anime['seasonYear'] ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ($anime['episodes']): ?>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Épisodes</span>
                                    <span class="font-medium"><?= $anime['episodes'] ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ($anime['duration']): ?>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Durée</span>
                                    <span class="font-medium"><?= $anime['duration'] ?> min</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($anime['studios']['nodes'])): ?>
                        <div class="bg-gray-50 rounded-xl p-4 mt-4">
                            <h3 class="text-lg font-semibold mb-3">Studios</h3>
                            <div class="space-y-2">
                                <?php
                                foreach ($anime['studios']['nodes'] as $studio):
                                    $isMain = isset($studio['isAnimationStudio']) && $studio['isAnimationStudio'];
                                ?>
                                    <div class="flex items-center">
                                        <span class="<?= $isMain ? 'text-indigo-600 font-medium' : 'text-gray-700' ?>">
                                            <?= $studio['name'] ?>
                                        </span>
                                        <?php if ($isMain): ?>
                                            <span class="ml-2 px-2 py-0.5 bg-indigo-100 text-indigo-700 text-xs rounded-full">
                                                Principal
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="flex-1">
                    <h1 class="text-4xl font-bold text-gray-900 mb-2">
                        <?= htmlspecialchars($anime['title']['romaji']) ?>
                    </h1>
                    <?php if ($anime['title']['english'] && $anime['title']['english'] !== $anime['title']['romaji']): ?>
                        <h2 class="text-2xl text-gray-600 mb-1">
                            <?= htmlspecialchars($anime['title']['english']) ?>
                        </h2>
                    <?php endif; ?>
                    <?php if ($anime['title']['native']): ?>
                        <h3 class="text-xl text-gray-500 mb-4">
                            <?= htmlspecialchars($anime['title']['native']) ?>
                        </h3>
                    <?php endif; ?>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                        <div class="bg-gradient-to-br from-indigo-50 to-purple-50 p-4 rounded-xl text-center">
                            <div class="text-2xl font-bold text-indigo-600">
                                <?= $anime['averageScore'] ?? '?' ?>%
                            </div>
                            <div class="text-sm text-gray-600">Note</div>
                        </div>
                        <div class="bg-gradient-to-br from-indigo-50 to-purple-50 p-4 rounded-xl text-center">
                            <div class="text-2xl font-bold text-indigo-600">
                                <?= $anime['episodes'] ?? '?' ?>
                            </div>
                            <div class="text-sm text-gray-600">Épisodes</div>
                        </div>
                        <div class="bg-gradient-to-br from-indigo-50 to-purple-50 p-4 rounded-xl text-center">
                            <div class="text-2xl font-bold text-indigo-600">
                                <?= $anime['duration'] ?? '?' ?>
                            </div>
                            <div class="text-sm text-gray-600">Minutes</div>
                        </div>
                        <div class="bg-gradient-to-br from-indigo-50 to-purple-50 p-4 rounded-xl text-center">
                            <div class="text-2xl font-bold text-indigo-600"><?= $anime['popularity'] ?></div>
                            <div class="text-sm text-gray-600">Popularité</div>
                        </div>
                    </div>

                    <div class="prose max-w-none mb-8">
                        <h3 class="text-xl font-semibold mb-4">Synopsis</h3>
                        <div class="text-gray-600 leading-relaxed markdown-content bg-gray-50 p-6 rounded-xl">
                            <?= $parsedown->text($anime['description']); ?>
                        </div>
                    </div>

                    <div class="mb-8">
                        <h3 class="text-xl font-semibold mb-4">Genres</h3>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($anime['genres'] as $genre): ?>
                                <a href="index.php?genre=<?= urlencode($genre) ?>" class="px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-full text-sm transition transform hover:scale-105 hover:shadow-md">
                                    <?= $genre ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <?php if (!empty($anime['tags'])): ?>
                        <div class="mb-8">
                            <h3 class="text-xl font-semibold mb-4">Tags</h3>
                            <div class="flex flex-wrap gap-2">
                                <?php
                                usort($anime['tags'], function ($a, $b) {
                                    return $b['rank'] - $a['rank'];
                                });

                                foreach ($anime['tags'] as $tag):
                                    $intensity = intval($tag['rank'] / 10);
                                    $bgClass = "bg-indigo-{$intensity}0";
                                    if ($intensity < 3) {
                                        $bgClass = "bg-gray-100";
                                        $textClass = "text-gray-700";
                                    } else {
                                        $textClass = "text-indigo-700";
                                    }
                                ?>
                                    <span class="px-3 py-1 <?= $bgClass ?> <?= $textClass ?> rounded-full text-sm flex items-center">
                                        <?= $tag['name'] ?>
                                        <span class="ml-2 px-1.5 py-0.5 text-xs bg-white/60 text-indigo-800 rounded-full"><?= $tag['rank'] ?>%</span>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($anime['relations']['edges'])): ?>
                        <div class="mb-8">
                            <h3 class="text-xl font-semibold mb-4">Œuvres liées</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                <?php foreach ($anime['relations']['edges'] as $relation):
                                    $relatedMedia = $relation['node'];
                                    $relationType = isset($relationTranslations[$relation['relationType']]) ?
                                        $relationTranslations[$relation['relationType']] : $relation['relationType'];
                                ?>
                                    <a href="details.php?id=<?= $relatedMedia['id'] ?>" class="flex bg-white rounded-lg shadow-sm hover:shadow-md p-3 transition">
                                        <img src="<?= $relatedMedia['coverImage']['medium'] ?>"
                                            alt="<?= htmlspecialchars($relatedMedia['title']['romaji']) ?>"
                                            class="w-16 h-24 object-cover rounded">
                                        <div class="ml-3 flex-1">
                                            <span class="block text-xs font-medium text-indigo-600 mb-1"><?= $relationType ?></span>
                                            <h4 class="font-medium text-sm text-gray-800 line-clamp-2">
                                                <?= htmlspecialchars($relatedMedia['title']['romaji']) ?>
                                            </h4>
                                            <span class="text-xs text-gray-500 mt-1 block">
                                                <?= isset($formatTranslations[$relatedMedia['format']]) ?
                                                    $formatTranslations[$relatedMedia['format']] : $relatedMedia['format'] ?>
                                            </span>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($anime['characters']['nodes'])): ?>
        <div class="mt-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Personnages</h2>
                <a href="characters.php?id=<?= $anime['id'] ?>" class="text-indigo-600 hover:text-indigo-800 flex items-center">
                    <span>Voir tous</span>
                    <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
                <?php
                $characters = $anime['characters']['nodes'];
                $edges = $anime['characters']['edges'];
                for ($i = 0; $i < count($characters); $i++):
                    $character = $characters[$i];
                    $edge = $edges[$i];
                    $isMain = $edge['role'] === 'MAIN';
                    $cardClass = $isMain ? 'bg-gradient-to-br from-indigo-50 to-purple-50 ring-1 ring-purple-200' : 'bg-white';
                    $roleClass = $isMain ? 'text-purple-600 font-medium' : 'text-gray-500';

                    if (!empty($character['description'])) {
                        $character['description'] = $parsedown->text($character['description']);
                    }

                    if (!empty($edge['voiceActors'])) {
                        $character['voiceActor'] = $edge['voiceActors'][0];
                    }
                ?>
                    <div class="<?= $cardClass ?> rounded-xl shadow-sm overflow-hidden hover:shadow-lg transition-shadow cursor-pointer group"
                        onclick="showCharacterDetails(<?= htmlspecialchars(json_encode($character)) ?>, '<?= $edge['role'] ?>')">
                        <div class="relative overflow-hidden">
                            <img src="<?= $character['image']['medium'] ?>"
                                alt="<?= htmlspecialchars($character['name']['full']) ?>"
                                class="w-full h-48 object-cover transition duration-300 group-hover:scale-105">
                            <?php if ($isMain): ?>
                                <div class="absolute top-2 right-2">
                                    <span class="px-2 py-1 bg-gradient-to-r from-purple-500 to-indigo-500 text-white text-xs rounded-full shadow-lg">
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

    <div id="characterModal" class="fixed inset-0 bg-black bg-opacity-75 hidden items-center justify-center z-50 transition duration-300 opacity-0">
        <div class="bg-white rounded-2xl max-w-3xl w-full mx-4 max-h-[90vh] overflow-y-auto shadow-2xl transform scale-95 transition duration-300">
            <div class="p-6">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-2xl font-bold text-gray-900" id="modalCharacterName"></h3>
                    <button onclick="closeCharacterModal()" class="text-gray-500 hover:text-gray-700 transition transform hover:rotate-90">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="flex flex-col md:flex-row gap-6">
                    <div class="w-full md:w-1/3">
                        <div class="bg-gray-100 rounded-xl p-2">
                            <img id="modalCharacterImage" class="w-full rounded-lg shadow-inner" src="" alt="">
                        </div>
                        <div id="modalVoiceActor" class="mt-4 hidden">
                            <h4 class="text-lg font-semibold mb-2">Doubleur</h4>
                            <div class="flex items-center bg-gray-50 p-3 rounded-lg">
                                <img id="modalVoiceActorImage" class="w-12 h-12 rounded-full object-cover" src="" alt="">
                                <div class="ml-3">
                                    <div id="modalVoiceActorName" class="font-medium"></div>
                                    <div class="text-sm text-gray-500">Japonais</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex-1">
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <div class="text-sm text-gray-600">Âge</div>
                                <div id="modalCharacterAge" class="font-semibold"></div>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <div class="text-sm text-gray-600">Genre</div>
                                <div id="modalCharacterGender" class="font-semibold"></div>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <div class="text-sm text-gray-600">Groupe sanguin</div>
                                <div id="modalCharacterBloodType" class="font-semibold"></div>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <div class="text-sm text-gray-600">Favoris</div>
                                <div id="modalCharacterFavorites" class="font-semibold"></div>
                            </div>
                        </div>

                        <div id="modalCharacterNativeName" class="bg-indigo-50 p-3 rounded-lg mb-6 hidden">
                            <div class="text-sm text-indigo-600">Nom original</div>
                            <div class="font-medium text-gray-800"></div>
                        </div>

                        <div id="modalCharacterBirthday" class="bg-indigo-50 p-3 rounded-lg mb-6 hidden">
                            <div class="text-sm text-indigo-600">Date de naissance</div>
                            <div class="font-medium text-gray-800"></div>
                        </div>

                        <div id="modalCharacterRole" class="mb-4 flex items-center">
                            <div id="roleIcon" class="w-6 h-6 mr-2 text-purple-600"></div>
                            <div id="roleName" class="font-medium text-lg"></div>
                        </div>

                        <div>
                            <h4 class="text-lg font-semibold mb-2">Description</h4>
                            <div id="modalCharacterDescription" class="text-gray-600 markdown-content bg-gray-50 p-4 rounded-lg overflow-auto max-h-64"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($anime['streamingEpisodes'])): ?>
    <div class="mt-8">
        <h2 class="text-2xl font-bold mb-6">Episodes disponibles (<?= count($anime['streamingEpisodes']) ?>)</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($anime['streamingEpisodes'] as $episode): ?>
                <a href="<?= $episode['url'] ?>" target="_blank"
                    class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-lg transition-shadow group">
                    <div class="relative">
                        <img src="<?= $episode['thumbnail'] ?>"
                            alt="<?= htmlspecialchars($episode['title']) ?>"
                            class="w-full h-32 object-cover transition duration-300 group-hover:scale-105">
                        <div class="absolute inset-0 bg-black/40 group-hover:bg-black/20 transition-colors"></div>
                        <div class="absolute bottom-2 right-2">
                            <span class="px-2 py-1 bg-white/90 text-gray-900 text-xs rounded-full">
                                <?= $episode['site'] ?>
                            </span>
                        </div>
                    </div>
                    <div class="p-4">
                        <h3 class="font-medium text-gray-800 text-sm line-clamp-2">
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

    document.addEventListener('DOMContentLoaded', () => {
        const animeId = <?= $id ?>;
        updateFavoriteButton(animeId);
    });

    function showCharacterDetails(character, role) {
        const modal = document.getElementById('characterModal');
        const modalContent = modal.querySelector('.bg-white');

        document.getElementById('modalCharacterName').textContent = character.name.full;
        document.getElementById('modalCharacterImage').src = character.image.large || character.image.medium;
        document.getElementById('modalCharacterAge').textContent = character.age || 'Inconnu';
        document.getElementById('modalCharacterGender').textContent = character.gender || 'Inconnu';
        document.getElementById('modalCharacterBloodType').textContent = character.bloodType || 'Inconnu';
        document.getElementById('modalCharacterFavorites').textContent = character.favourites.toLocaleString() || '0';

        const nativeNameDiv = document.getElementById('modalCharacterNativeName');
        if (character.name.native) {
            nativeNameDiv.querySelector('div:last-child').textContent = character.name.native;
            nativeNameDiv.classList.remove('hidden');
        } else {
            nativeNameDiv.classList.add('hidden');
        }

        const birthdayDiv = document.getElementById('modalCharacterBirthday');
        if (character.dateOfBirth && character.dateOfBirth.month) {
            const date = new Date(
                character.dateOfBirth.year || 2000,
                character.dateOfBirth.month - 1,
                character.dateOfBirth.day || 1
            );
            const options = {
                day: 'numeric',
                month: 'long'
            };
            if (character.dateOfBirth.year) options.year = 'numeric';
            birthdayDiv.querySelector('div:last-child').textContent = date.toLocaleDateString('fr-FR', options);
            birthdayDiv.classList.remove('hidden');
        } else {
            birthdayDiv.classList.add('hidden');
        }

        const roleDiv = document.getElementById('modalCharacterRole');
        const roleIcon = document.getElementById('roleIcon');
        const roleName = document.getElementById('roleName');

        if (role === 'MAIN') {
            roleIcon.innerHTML = `
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>`;
            roleName.textContent = 'Personnage Principal';
        } else {
            roleIcon.innerHTML = `
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                <path d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z"/>
            </svg>`;
            roleName.textContent = 'Personnage Secondaire';
        }

        const voiceActorDiv = document.getElementById('modalVoiceActor');
        if (character.voiceActor) {
            document.getElementById('modalVoiceActorName').textContent = character.voiceActor.name.full;
            document.getElementById('modalVoiceActorImage').src = character.voiceActor.image.medium;
            voiceActorDiv.classList.remove('hidden');
        } else {
            voiceActorDiv.classList.add('hidden');
        }

        document.getElementById('modalCharacterDescription').innerHTML = character.description || 'Aucune description disponible.';

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modalContent.classList.remove('scale-95');
            modalContent.classList.add('scale-100');
        }, 10);
    }

    function closeCharacterModal() {
        const modal = document.getElementById('characterModal');
        const modalContent = modal.querySelector('.bg-white');

        modal.classList.add('opacity-0');
        modalContent.classList.remove('scale-100');
        modalContent.classList.add('scale-95');

        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }, 300);
    }

    document.getElementById('characterModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeCharacterModal();
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>