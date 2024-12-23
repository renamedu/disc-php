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

$discord->on('ready', function (Discord $discord) use ($search_channel, &$messages, $channels_test) {
    echo "Bot is ready!", PHP_EOL;

    $discord->on('message', function (Message $message) use ($search_channel, &$messages, $discord, $channels_test) {
        if ($message->author->bot) {
            return;
        }

        $searchText = $message->content;

        // Добавьте отладочную информацию
        file_put_contents('mesageTEST.log', "СООБЩЕНИЕ: '$searchText'. \n", FILE_APPEND);

        if ($message->channel_id === $search_channel) {
            $messages[] = $message->content;

            // Убедитесь, что message->content не пустое
            if (!empty($searchText)) {
                if (in_array($searchText, $channels_test)) {
                    // Здесь начинается асинхронная операция
                    $discord->guilds->fetch($message->guild_id)->then(function ($guild) use ($searchText) {
                        // Проверим, что searchText не пустое в контексте промиса
                        if (!empty($searchText)) {
                            $guild->channels->create([
                                'name' => $searchText,
                                'type' => Channel::TYPE_TEXT,
                            ]);
                            echo "Канал с именем '$searchText' был создан.", PHP_EOL;
                        } else {
                            echo "searchText пустой при создании канала.", PHP_EOL;
                        }
                    });
                } else {
                    $message->channel->sendMessage('Нет совпадений');
                    echo "Нет совпадений для сообщения: '$searchText'.", PHP_EOL;
                }
            } else {
                echo "Ошибка: searchText пустой!", PHP_EOL;
            }
        }
    });
});

$discord->run();
