<?php

require_once 'vendor/autoload.php';
require_once 'loadEnv.php';

use Discord\Discord;
use Discord\WebSockets\Event;
use Discord\Parts\Channel\Message;
use Discord\Parts\Channel\Channel;

$discord_token = getenv('DISCORD_TOKEN');
$ytb_api_key = getenv('YTB_API_KEY');

$search_channel = '1306223296416841728';

$messages = array();

$discord = new Discord([
    'token' => $discord_token,
]);

$channels_test = ['first', 'sec', 'thrd'];

// Слушаем событие, когда бот подключается к Discord
$discord->on('ready', function (Discord $discord) use ($search_channel, &$messages, $channels_test) {
    echo "Bot is ready!", PHP_EOL;

    // $guild = $discord->guilds->first(); // Берем первый сервер, на котором состоит бот
    // // Получаем все каналы на сервере
    // foreach ($guild->channels as $channel) {
    //     echo "Channel Name: " . $channel->name . PHP_EOL;
    //     echo "Channel ID: " . $channel->id . PHP_EOL;
    //     // Дополнительная информация о канале
    // }

    $discord->on('message', function (Message $message) use ($search_channel, &$messages, $discord, $channels_test) {
        if ($message->author->bot) {
            return;
        }
        
        $searchText = $message->content;
        file_put_contents('mesageTEST.log', "СООБЩЕНИЕ: '$searchText'. \n", FILE_APPEND);

        if ($message->channel_id === $search_channel) {
            $messages[] = $message->content;

            // Создаем канал с таким названием
            $discord->guilds->fetch($message->guild_id)->then(function ($guild) use ($searchText) {
                // Создаем текстовый канал с именем, совпадающим с текстом сообщения
                $guild->channels->create([
                    'name' => $searchText,
                    'type' => Channel::TYPE_TEXT, // Тип канала (текстовый)
                ]);
                echo "Канал с именем '$searchText' был создан.", PHP_EOL;
            });
            
            // Пытаемся найти совпадение с любым из сообщений в массиве
            if (in_array($searchText, $channels_test)) {
            } else {
                $message->channel->sendMessage('Нет совпадений');
                echo "Нет совпадений для сообщения: '$searchText'.", PHP_EOL;
            }
        }
    });
});

$discord->run();



