<?php
require_once 'includes/api.php';

$search = isset($_GET['q']) ? $_GET['q'] : '';

if ($search) {
    $query = '
    query ($search: String) {
        Page(page: 1, perPage: 20) {
            media(search: $search, type: ANIME) {
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

    $variables = ['search' => $search];
    $animes = fetchFromAniList($query, $variables);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche - AniList Explorer</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <header class="mb-8">
            <a href="index.php" class="text-indigo-600 hover:text-indigo-800">← Retour</a>
            <h1 class="text-3xl font-bold text-center text-indigo-600 mt-4">Résultats pour "<?php echo htmlspecialchars($search); ?>"</h1>
        </header>

        <?php if (isset($animes) && !empty($animes['data']['Page']['media'])): ?>
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
        <?php else: ?>
        <p class="text-center text-gray-600">Aucun résultat trouvé.</p>
        <?php endif; ?>
    </div>
</body>
</html>
