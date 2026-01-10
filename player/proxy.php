<?php
// ВАЖНО: Этот файл должен быть на сервере ВНЕ России
// Например, в Германии, Финляндии, США и т.д.

header('Content-Type: text/html; charset=utf-8');

// Получаем параметры
$action = $_GET['action'] ?? '';
$video_id = $_GET['id'] ?? '';
$quality = $_GET['quality'] ?? 'best';

if (empty($video_id)) {
    die('Ошибка: не указан ID видео');
}

// Список User-Agent для обхода блокировок
$user_agents = [
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
    'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36',
];

$user_agent = $user_agents[array_rand($user_agents)];

switch ($action) {
    case 'embed':
        // Вариант 1: Встраивание через iframe (самый простой)
        embedVideo($video_id);
        break;
        
    case 'stream':
        // Вариант 2: Прямая трансляция видео (более сложный)
        streamVideo($video_id, $quality);
        break;
        
    default:
        die('Неизвестное действие');
}

function embedVideo($video_id) {
    // Генерируем iframe с видео
    $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>YouTube Video</title>
    <style>
        body { margin: 0; padding: 0; background: #000; }
        iframe { width: 100vw; height: 100vh; border: none; }
    </style>
</head>
<body>
    <iframe src="https://www.youtube-nocookie.com/embed/{$video_id}?autoplay=1&rel=0&modestbranding=1" 
            frameborder="0" 
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
            allowfullscreen>
    </iframe>
</body>
</html>
HTML;
    
    echo $html;
}

function streamVideo($video_id, $quality) {
    // ВАЖНО: Для этого варианта нужно установить youtube-dl на сервере
    // и настроить права доступа
    
    $youtube_url = "https://www.youtube.com/watch?v={$video_id}";
    
    // Получаем прямую ссылку на видео через youtube-dl
    $command = "youtube-dl -g -f {$quality} '{$youtube_url}' 2>&1";
    $video_url = shell_exec($command);
    
    if (!$video_url) {
        die('Не удалось получить ссылку на видео');
    }
    
    // Настраиваем заголовки для потоковой передачи
    header('Content-Type: video/mp4');
    header('Content-Disposition: inline; filename="video.mp4"');
    
    // Проксируем видео
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, trim($video_url));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: */*',
        'Accept-Language: en-US,en;q=0.9',
        'Cache-Control: no-cache',
        'Connection: keep-alive',
        'DNT: 1',
        'Pragma: no-cache',
        'Range: bytes=0-',
        'Referer: https://www.youtube.com/',
        'Sec-Fetch-Dest: video',
        'Sec-Fetch-Mode: no-cors',
        'Sec-Fetch-Site: cross-site',
    ]);
    
    curl_exec($ch);
    curl_close($ch);
}
?>