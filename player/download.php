<?php
// Скачивание видео через прокси
header('Content-Type: application/octet-stream');

$video_id = $_GET['id'] ?? '';
$format = $_GET['format'] ?? 'mp4';

if (empty($video_id)) {
    die('Ошибка: не указан ID видео');
}

// Используем youtube-dl для скачивания
$youtube_url = "https://www.youtube.com/watch?v={$video_id}";
$filename = "video_{$video_id}.{$format}";

// Команда для youtube-dl
$command = "youtube-dl --get-url --format 'best[ext={$format}]' '{$youtube_url}'";
$video_url = shell_exec($command);

if (!$video_url) {
    die('Не удалось получить ссылку на видео');
}

// Заголовки для скачивания
header("Content-Disposition: attachment; filename=\"{$filename}\"");
header("Content-Transfer-Encoding: binary");
header("Cache-Control: no-cache");

// Читаем и отправляем видео
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, trim($video_url));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');

$video_data = curl_exec($ch);
curl_close($ch);

echo $video_data;
?>