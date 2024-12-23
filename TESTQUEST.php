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

        echo '<pre>';
        print_r($message);
        echo '</pre>';

        file_put_contents('mesageTEST.log', "СООБЩЕНИЕ: '$searchText'. \n", FILE_APPEND);
    });
});
$discord->run();
