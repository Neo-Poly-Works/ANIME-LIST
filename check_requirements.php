<?php
$requirements = [
    'curl' => [
        'required' => true,
        'installed' => function_exists('curl_init'),
        'name' => 'cURL Extension',
        'help' => 'Activez l\'extension cURL dans votre php.ini en décommentant la ligne ;extension=curl'
    ]
];

$allOk = true;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification des prérequis - AniList Explorer</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-center text-indigo-600 mb-8">Vérification des prérequis</h1>
        
        <div class="bg-white rounded-lg shadow-lg p-6">
            <?php foreach ($requirements as $requirement): ?>
                <?php $allOk = $allOk && ($requirement['installed'] || !$requirement['required']); ?>
                <div class="mb-4 p-4 rounded <?php echo $requirement['installed'] ? 'bg-green-100' : 'bg-red-100'; ?>">
                    <div class="flex items-center justify-between">
                        <h2 class="font-semibold"><?php echo $requirement['name']; ?></h2>
                        <span class="<?php echo $requirement['installed'] ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo $requirement['installed'] ? '✓ Installé' : '✗ Non installé'; ?>
                        </span>
                    </div>
                    <?php if (!$requirement['installed']): ?>
                        <p class="mt-2 text-sm text-gray-600"><?php echo $requirement['help']; ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <?php if ($allOk): ?>
                <div class="mt-6 text-center">
                    <a href="index.php" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">
                        Accéder au site
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
