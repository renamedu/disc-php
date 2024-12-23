<?php

require_once 'vendor/autoload.php'; // Подключение библиотек

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Google_Client;
use Google_Service_YouTube;
use Google_Service_YouTube_SearchListResponse;

$discordToken = file_get_contents('yoken.txt');
$youtubeApiKey = file_get_contents('goken.txt');

$discord = new Discord([
    'token' => $discordToken,
]);

// Инициализация Google Client для YouTube API
$googleClient = new Google_Client();
$googleClient->setDeveloperKey($youtubeApiKey);
$youtubeService = new Google_Service_YouTube($googleClient);

$discord->on('ready', function (Discord $discord) use ($youtubeService) {
    echo "Bot is ready!" . PHP_EOL;

    // Прослушка сообщений
    $discord->on('message', function (Message $message) use ($discord, $youtubeService) {
        // Проверяем, что сообщение отправлено в канал "поиск-каналов"
        if ($message->channel->name == 'поиск-каналов') {
            $query = $message->content;

            // Ищем каналы на YouTube
            $searchResponse = searchYouTubeChannels($youtubeService, $query);
            
            if (empty($searchResponse)) {
                $message->reply('Каналы не найдены.');
                return;
            }

            // Отправляем список каналов
            $responseMessage = "Нашли следующие каналы:\n";
            foreach ($searchResponse as $index => $channel) {
                $responseMessage .= ($index + 1) . ". " . $channel['title'] . " (https://youtube.com/channel/" . $channel['id'] . ")\n";
            }
            $message->reply($responseMessage);
            
            // Ожидаем ответа от пользователя для выбора канала
            $discord->on('message', function (Message $responseMessage) use ($discord, $searchResponse) {
                $choiceIndex = (int)$responseMessage->content;

                if ($choiceIndex > 0 && $choiceIndex <= count($searchResponse)) {
                    $selectedChannel = $searchResponse[$choiceIndex - 1];
                    $channelName = $selectedChannel['title'];

                    // Создаем канал на сервере Discord с названием канала YouTube
                    $guild = $responseMessage->channel->guild;
                    $guild->channels->create([
                        'name' => $channelName,
                        'type' => 0, // text channel
                    ]);

                    // Записываем ссылки на видео в канал Discord
                    $videos = getYouTubeVideos($youtubeService, $selectedChannel['id']);
                    $videoLinks = implode("\n", array_map(function ($video) {
                        return "https://youtube.com/watch?v=" . $video['id'];
                    }, $videos));

                    $responseMessage->channel->sendMessage("Ссылки на видео канала {$channelName}:\n" . $videoLinks);
                } else {
                    $responseMessage->reply('Неверный выбор. Попробуйте снова.');
                }
            });
        }
    });
});

// Функция для поиска каналов на YouTube
function searchYouTubeChannels(Google_Service_YouTube $youtubeService, $query) {
    $searchResponse = $youtubeService->search->listSearch('snippet', [
        'q' => $query,
        'type' => 'channel',
        'maxResults' => 5,
    ]);

    $channels = [];
    foreach ($searchResponse['items'] as $searchResult) {
        $channels[] = [
            'title' => $searchResult['snippet']['title'],
            'id' => $searchResult['snippet']['channelId'],
        ];
    }

    return $channels;
}

// Функция для получения видео с канала
function getYouTubeVideos(Google_Service_YouTube $youtubeService, $channelId) {
    $videosResponse = $youtubeService->search->listSearch('snippet', [
        'channelId' => $channelId,
        'maxResults' => 10,
    ]);

    $videos = [];
    foreach ($videosResponse['items'] as $videoItem) {
        $videos[] = [
            'id' => $videoItem['id']['videoId'],
            'title' => $videoItem['snippet']['title'],
        ];
    }

    return $videos;
}

// Запуск бота
$discord->run();
