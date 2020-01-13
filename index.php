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
$user = !empty($_REQUEST['user_id']) ? htmlspecialchars($_REQUEST['user_id']) : false;
$user_name = !empty($_REQUEST['user_name']) ? htmlspecialchars($_REQUEST['user_name']) : false;
$token = !empty($_REQUEST['token']) ? $_REQUEST['token'] : false;
$response_url = !empty($_REQUEST['response_url']) ? $_REQUEST['response_url'] : false;
$output = [];
$result = false;

// Check the token
if ($token !== TOKEN) {
    $msg = "The token for the slash command doesn't match. Check your script.";
    $result = [
        'response_type' => 'ephemeral',
        'text' => $msg
    ];

    header('Content-type: application/json');
    echo json_encode($result);
}

if ($command === '/sarcasm' && $text) {
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

    // Prepare result
    $result = [
        'replace_original' => 'true',
        'response_type' => 'in_channel',
        'text' => implode($output)
    ];
}

if ($command === '/insult') {
    if (empty($user)) {
        $output = 'You need to @someone!';
    } else {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://insult.mattbas.org/api/en/insult.json?who=$user");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        $response = curl_exec($ch);
        $response = json_decode($response);
        curl_close($ch);

        if (!empty($response->error)) {
            $output = $response->error;
        } elseif (isset($response->insult)) {
            $output = $response->insult;
        }
    }

    // Prepare result
    $result = [
        'replace_original' => 'true',
        'response_type' => 'in_channel',
        'text' => $output
    ];
}

if ($command === '/breakfastclub') {
    // Prepare result
    $result = [
        'replace_original' => 'true',
        'response_type' => 'in_channel',
        'text' => 'Test'
    ];
}

// Send `message_response`
if ($response_url && $result) {
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $response_url,
        CURLOPT_POST => 1,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => json_encode($result)
    ]);

    $resp = curl_exec($curl);
    curl_close($curl);
}
