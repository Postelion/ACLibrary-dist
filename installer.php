<?php
// Obsługa AJAX request - na samej górze
if (isset($_POST['ajax']) && $_POST['ajax'] === '1' && isset($_POST['download']) && $_POST['download'] === '1'):
        // Pobierz dane o releases ponownie dla AJAX
        $releases = fetchGitHubReleases('Postelion/ACLibrary-dist');
        
        // Znajdź wybrany release
        $selectedRelease = null;
        foreach ($releases as $release) {
            if ($release['tag_name'] === $_POST['version']) {
                $selectedRelease = $release;
                break;
            }
        }
        
        $url = 'https://github.com/Postelion/ACLibrary-dist/releases/download/'. $_POST['version'] .'/aura-custom-lib.js';

        $destination = sfConfig::get('sf_upload_dir') . '/static/ACL/aura-custom-lib.js';

        $dir = dirname($destination);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true)) {
                die('Nie udało się utworzyć katalogu: ' . $dir);
            }
        }

        $fp = fopen($destination, 'w+');
        if ($fp === false) {
            die('Nie można otworzyć pliku do zapisu.');
        }

        $ch = curl_init($url);

        // Ustawienia cURL
        curl_setopt($ch, CURLOPT_FILE, $fp);            // Zapisuj bezpośrednio do pliku
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Śledź przekierowania
        curl_setopt($ch, CURLOPT_TIMEOUT, 50);          // Timeout (opcjonalnie)
        curl_setopt($ch, CURLOPT_FAILONERROR, true);    // Zgłaszaj błędy HTTP

        // Wykonaj zapytanie
        $result = curl_exec($ch);

        $babelUrl = 'https://github.com/Postelion/ACLibrary-dist/releases/download/' . $_POST['version'] . '/babel.min.js';
        $babelDestination = sfConfig::get('sf_upload_dir') . '/static/ACL/babel.min.js';

        $fpBabel = fopen($babelDestination, 'w+');
        if ($fpBabel === false) {
            die('Nie można otworzyć pliku babel.min.js do zapisu.');
        }

        $chBabel = curl_init($babelUrl);
        curl_setopt($chBabel, CURLOPT_FILE, $fpBabel);
        curl_setopt($chBabel, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($chBabel, CURLOPT_TIMEOUT, 50);
        curl_setopt($chBabel, CURLOPT_FAILONERROR, true);

        $resultBabel = curl_exec($chBabel);

        // Sprawdź, czy wystąpił błąd
        if ($result === false) {
            echo 'Błąd cURL: ' . curl_error($ch);
        } else {
            echo '<h3>✅ Pobrano wersję: ' . htmlspecialchars($_POST['version']) . '</h3>';
                        
            echo '<p><strong>Plik został pobrany pomyślnie!</strong></p>';
            echo "Aby zaimplementować ACLibrary, dodaj poniższy kod do <a href=\"/settings/tab/name/menu_parametry_systemu\">parametrów systemu</a>" . '<br><br>';
            echo "Dodatkowy kod źródłowy w sekcji HEAD:" . '<br>';
            echo '<pre>';
            echo '<code>';
            echo htmlspecialchars('<script defer src="/uploads/localhost/static/ACL/aura-custom-lib.js"></script>');
            echo '</code>';
            echo '</pre>';
            echo "Dodatkowy kod źródłowy w sekcji BODY: :". '<br>';
            echo '<pre>';
            echo '<code>';
            echo htmlspecialchars('<div id="aura-custom-lib"></div>');
            echo '</code>';
            echo '</pre>';
        }

        // Zamknij cURL i plik
        curl_close($ch);
        fclose($fp);

    exit; // Zakończ skrypt dla AJAX request
endif;

// Funkcja pomocnicza do pobierania danych z GitHub API
function fetchGitHubReleases($repo) {
    $url = "https://api.github.com/repos/$repo/releases";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Simple PHP Client'); // wymagane przez GitHub API
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

// Pobieranie danych
$releases = fetchGitHubReleases('Postelion/ACLibrary-dist');
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Wybór wersji ACLibrary</title>
    <style>
        .version-form {
            max-width: 400px;
            margin: 20px 0;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
        }

        .version-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }

        .version-select {
            width: 100%;
            padding: 12px;
            border: 2px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
            background-color: white;
            cursor: pointer;
            transition: border-color 0.3s ease;
        }

        .version-select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        .version-select:hover {
            border-color: #007bff;
        }

        .submit-btn {
            margin-top: 15px;
            padding: 12px 24px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            background-color: #0056b3;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .submit-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .selected-version {
            margin-top: 20px;
            padding: 15px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 6px;
        }

        .error-message {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 15px;
            border-radius: 6px;
            margin-top: 20px;
        }

        #version-info {
            margin-top: 20px;
            padding: 15px;
            background-color: #e9ecef;
            color: #333;
            border: 1px solid #ced4da;
            border-radius: 6px;
        }

        #version-info h3 {
            margin-top: 0;
        }
    </style>
    <script>
        // Dane o releases dla JavaScript
        const releasesData = <?= json_encode($releases) ?>;
        
        function showVersionInfo(selectedVersion) {
            const infoDiv = document.getElementById('version-info');
            
            if (!selectedVersion) {
                infoDiv.style.display = 'none';
                return;
            }
            
            const release = releasesData.find(r => r.tag_name === selectedVersion);
            
            if (release) {
                let html = '<h3>📦 Informacje o wersji: ' + release.tag_name + '</h3>';
                
                if (release.name && release.name !== release.tag_name) {
                    html += '<p><strong>Nazwa:</strong> ' + release.name + '</p>';
                }
                
                if (release.published_at) {
                    const date = new Date(release.published_at).toLocaleDateString('pl-PL');
                    html += '<p><strong>Data wydania:</strong> ' + date + '</p>';
                }
                
                if (release.body) {
                    html += '<div style="background: #f8f9fa; padding: 10px; border-radius: 4px; margin: 10px 0;">';
                    html += '<strong>Opis zmian:</strong><br>';
                    html += '<div style="white-space: pre-wrap; font-size: 0.9em;">' + release.body + '</div>';
                    html += '</div>';
                }
                
                infoDiv.innerHTML = html;
                infoDiv.style.display = 'block';
            } else {
                infoDiv.style.display = 'none';
            }
        }
        
        function handleSubmit(event) {
            event.preventDefault(); // Zapobiega przeładowaniu strony
            
            const form = event.target;
            const versionSelect = form.querySelector('#version');
            const submitBtn = form.querySelector('.submit-btn');
            const resultDiv = document.getElementById('download-result');
            
            if (!versionSelect.value) {
                alert('Proszę wybrać wersję');
                return;
            }
            
            // Pokaż wskaźnik ładowania
            submitBtn.disabled = true;
            submitBtn.textContent = 'Pobieranie...';
            resultDiv.innerHTML = '<p>Przetwarzanie żądania...</p>';
            resultDiv.style.display = 'block';
            
            // Przygotuj dane do wysłania
            const formData = new FormData();
            formData.append('version', versionSelect.value);
            formData.append('download', '1');
            formData.append('ajax', '1');
            
            // Wyślij AJAX request
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                // Wyświetl odpowiedź
                resultDiv.innerHTML = data;
                
                // Przywróć przycisk
                submitBtn.disabled = false;
                submitBtn.textContent = 'Pobierz wersję';
            })
            .catch(error => {
                console.error('Error:', error);
                resultDiv.innerHTML = '<p style="color: red;">Wystąpił błąd podczas pobierania.</p>';
                
                // Przywróć przycisk
                submitBtn.disabled = false;
                submitBtn.textContent = 'Pobierz wersję';
            });
        }
        
        // Dodaj listener do selecta
        document.addEventListener('DOMContentLoaded', function() {
            const versionSelect = document.getElementById('version');
            versionSelect.addEventListener('change', function() {
                showVersionInfo(this.value);
            });
        });
    </script>
</head>
<body>
    <h1>Wybierz wersję ACLibrary</h1>

    <?php if (!empty($releases)): ?>
        <form method="GET" class="version-form" onsubmit="handleSubmit(event)">
            <label for="version">Dostępne wersje:</label>
            <select name="version" id="version" class="version-select">
                <option value="">-- Wybierz wersję --</option>
                <?php foreach ($releases as $release): ?>
                    <option value="<?= htmlspecialchars($release['tag_name']) ?>" 
                            <?= (isset($_GET['version']) && $_GET['version'] === $release['tag_name']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($release['tag_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="submit-btn">Pobierz wersję</button>
        </form>

        <div id="version-info" class="selected-version" style="display: none;">
            <!-- Tutaj pojawi się informacja o wybranej wersji -->
        </div>

        <div id="download-result" class="selected-version" style="display: none;">
            <!-- Tutaj pojawi się odpowiedź z serwera -->
        </div>

        <?php if (isset($_GET['version']) && !empty($_GET['version']) && !isset($_GET['download'])): ?>
            <div class="selected-version">
                <h2>Wybrana wersja: <?= htmlspecialchars($_GET['version']) ?></h2>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <p class="error-message">Nie udało się pobrać wersji z GitHuba.</p>
    <?php endif; ?>
</body>
</html>
