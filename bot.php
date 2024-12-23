<?php

require 'vendor/autoload.php'; // Подключение автозагрузчика Composer

use Discord\Discord;
use Discord\WebSockets\Intents;

$discordToken = file_get_contents('yoken.txt');

// Инициализация клиента Discord
$discord = new Discord([
    'token' => $discordToken,
    'intents' => Intents::ALL,  // Устанавливаем все необходимые интенты
]);

// Когда бот подключается
$discord->on('ready', function (Discord $discord) {
    echo "Bot is ready!", PHP_EOL;

    // По команде !fetch будет получать данные с API
    $discord->on('message', function ($message) use ($discord) {

        /*
        if (strpos($message->content, '!fetch') === 0) {
            // Получаем данные с внешнего API
            $apiUrl = 'https://api.example.com/data';
            $response = file_get_contents($apiUrl);
            
            if ($response === false) {
                $message->channel->sendMessage('Error fetching data from API.');
            } else {
                $data = json_decode($response, true); // Преобразуем JSON-ответ в массив
                $message->channel->sendMessage("API Data: " . json_encode($data, JSON_PRETTY_PRINT));
            }
        }
        */

        // По команде !createchannel создадим новый канал
        if (strpos($message->content, '!createchannel') === 0) {
            $guild = $message->guild;
            $guild->channels->create([
                'name' => 'new-channel',
                'type' => 0,
            ]);
            $message->channel->sendMessage("New channel 'new-channel' created!");
        }
    });
});

// Запускаем бота
$discord->run();
