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

# Grab some of the values from the slash command, create vars for post back to Slack
$command = !empty($_REQUEST['command']) ? $_REQUEST['command'] : false;
$text = !empty($_REQUEST['text']) ? htmlspecialchars($_REQUEST['text']) : false;
$token = !empty($_REQUEST['token']) ? $_REQUEST['token'] : false;
$response_url = !empty($_REQUEST['response_url']) ? $_REQUEST['response_url'] : false;
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

//    $result = [
//        'response_type' => 'ephemeral',
//        'text' => implode($output)
//    ];
//
//    header('Content-type: application/json');
//    echo json_encode($result);

    if ($response_url) {
        $result = [
            'replace_original' => 'true',
            'response_type' => 'ephemeral',
            'text' => implode($output)
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $response_url,
            CURLOPT_POST => 1,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => $result
        ]);

        $resp = curl_exec($curl);
        var_dump($resp);
        curl_close($curl);
    }
}
