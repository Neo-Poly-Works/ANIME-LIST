<?php
require_once __DIR__ . '/includes/api.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header('Location: index.php');
    exit;
}

$query = '
query ($id: Int) {
    Media(id: $id) {
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
        description
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
        characters(sort: ROLE) {
            nodes {
                name {
                    full
                }
                image {
                    medium
                }
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
$anime = $response['data']['Media'];

require_once 'includes/header.php';
?>

<div class="relative">
    <?php if ($anime['bannerImage']): ?>
    <div class="h-64 w-full bg-cover bg-center" style="background-image: url('<?= $anime['bannerImage'] ?>');">
        <div class="absolute inset-0 bg-black bg-opacity-50"></div>
    </div>
    <?php endif; ?>

    <div class="container mx-auto px-4">
        <div class="relative <?= $anime['bannerImage'] ? '-mt-32' : 'mt-8' ?>">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                <div class="md:flex">
                    <div class="md:w-1/3">
                        <img src="<?= $anime['coverImage']['extraLarge'] ?>" 
                             alt="<?= htmlspecialchars($anime['title']['romaji']) ?>"
                             class="w-full h-auto">
                    </div>
                    <div class="p-6 md:w-2/3">
                        <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-4">
                            <?= htmlspecialchars($anime['title']['romaji']) ?>
                        </h1>
                        
                        <?php if ($anime['title']['english'] && $anime['title']['english'] !== $anime['title']['romaji']): ?>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">
                            <?= htmlspecialchars($anime['title']['english']) ?>
                        </p>
                        <?php endif; ?>

                        <div class="flex flex-wrap gap-2 mb-4">
                            <?php foreach ($anime['genres'] as $genre): ?>
                                <span class="px-3 py-1 bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 rounded-full">
                                    <?= $genre ?>
                                </span>
                            <?php endforeach; ?>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                            <div class="text-center p-4 bg-gray-100 dark:bg-gray-700 rounded">
                                <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400"><?= $anime['averageScore'] ?>%</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Score</div>
                            </div>
                            <div class="text-center p-4 bg-gray-100 dark:bg-gray-700 rounded">
                                <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400"><?= $anime['episodes'] ?? '?' ?></div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Épisodes</div>
                            </div>
                            <div class="text-center p-4 bg-gray-100 dark:bg-gray-700 rounded">
                                <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400"><?= $anime['duration'] ?? '?' ?></div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Minutes</div>
                            </div>
                            <div class="text-center p-4 bg-gray-100 dark:bg-gray-700 rounded">
                                <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400"><?= $anime['popularity'] ?></div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Popularité</div>
                            </div>
                        </div>

                        <div class="prose dark:prose-invert max-w-none">
                            <?= nl2br(htmlspecialchars($anime['description'])) ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($anime['characters']['nodes'])): ?>
            <div class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-4">Personnages</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <?php foreach (array_slice($anime['characters']['nodes'], 0, 12) as $character): ?>
                    <div class="text-center">
                        <img src="<?= $character['image']['medium'] ?>" 
                             alt="<?= htmlspecialchars($character['name']['full']) ?>"
                             class="w-full h-40 object-cover rounded mb-2">
                        <div class="text-sm font-medium text-gray-800 dark:text-white">
                            <?= htmlspecialchars($character['name']['full']) ?>
                        </div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">
                            <?= $character['role'] ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
