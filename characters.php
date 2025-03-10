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
        characters(sort: [ROLE, RELEVANCE]) {
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
    <nav class="text-sm mb-6">
        <ol class="flex items-center space-x-2">
            <li><a href="index.php" class="text-indigo-600 hover:text-indigo-800">Accueil</a></li>
            <li class="flex items-center space-x-2">
                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>
                <a href="details.php?id=<?= $id ?>" class="text-indigo-600 hover:text-indigo-800">
                    <?= htmlspecialchars($anime['title']['romaji']) ?>
                </a>
            </li>
            <li class="flex items-center space-x-2">
                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-gray-500">Personnages</span>
            </li>
        </ol>
    </nav>

    <h1 class="text-3xl font-bold mb-8">Personnages de <?= htmlspecialchars($anime['title']['romaji']) ?></h1>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
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
        ?>
            <div class="<?= $cardClass ?> rounded-xl shadow-sm overflow-hidden hover:shadow-lg transition-shadow cursor-pointer group"
                onclick="showCharacterDetails(<?= htmlspecialchars(json_encode($character)) ?>, '<?= $edge['role'] ?>')">
                <div class="relative">
                    <img src="<?= $character['image']['medium'] ?>"
                        alt="<?= htmlspecialchars($character['name']['full']) ?>"
                        class="w-full aspect-[3/4] object-cover transition duration-300 group-hover:scale-105">
                    <?php if ($isMain): ?>
                        <div class="absolute top-2 right-2">
                            <span class="px-2 py-1 bg-gradient-to-r from-purple-500 to-indigo-500 text-white text-xs rounded-full shadow-lg">
                                Principal
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 truncate mb-1">
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

<script>
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