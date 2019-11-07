<?php
require __DIR__ . '/vendor/autoload.php';

// Check if on Heroku otherwise assume its local
if (getenv('TOKEN')) {
    define('TOKEN', getenv('TOKEN'));
} else {
    $env = new \Symfony\Component\Dotenv\Dotenv();
    $env->load(__DIR__ . '/.env');
    define('TOKEN', $_ENV['TOKEN']);
}

var_dump(TOKEN);

# Grab some of the values from the slash command, create vars for post back to Slack
$command = !empty($_REQUEST['command']) ? $_REQUEST['command'] : false;
$text = !empty($_REQUEST['text']) ? htmlspecialchars($_REQUEST['text']) : false;
$token = !empty($_REQUEST['token']) ? $_REQUEST['token'] : false;
$output = [];
$result = false;

// Check the token
if ($token != TOKEN) {
    $msg = "The token for the slash command doesn't match. Check your script.";
    die($msg);
}

if ($text) {
    $split_string = str_split($text);

    foreach ($split_string as $key => $character) {
        if ($key % 2 !== 0) {
            $char = strtolower($character);
            $output[] = $char;
        } else {
            $char = strtoupper($character);
            $output[] = $char;
        }
    }

    $result = implode($output);
}

echo $result;
