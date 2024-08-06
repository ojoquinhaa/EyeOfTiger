<?php
include 'config.php';
session_start();

function authenticate() {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        if ($_SERVER['PHP_AUTH_USER'] !== $_ENV['DASHBOARD_USERNAME'] || $_SERVER['PHP_AUTH_PW'] !== $_ENV['DASHBOARD_PASSWORD']) {
            header('WWW-Authenticate: Basic realm="EyeOfTiger"');
            header('HTTP/1.0 401 Unauthorized');
            echo 'Autenticação necessária';
            exit;
        } else {
            $_SESSION['logged_in'] = true;
        }
    }
}

authenticate();

function getBlacklist() {
    return file('data/blacklist.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
}

function addIPToBlacklist($ip) {
    $blacklist = getBlacklist();
    if (!in_array($ip, $blacklist)) {
        file_put_contents('data/blacklist.txt', $ip . PHP_EOL, FILE_APPEND);
    }
}

function removeIPFromBlacklist($ip) {
    $blacklist = getBlacklist();
    $newBlacklist = array_diff($blacklist, [$ip]);
    file_put_contents('data/blacklist.txt', implode(PHP_EOL, $newBlacklist) . PHP_EOL);
}

function getRedirectURL() {
    return trim(file_get_contents('data/redirect.txt'));
}

function setRedirectURL($url) {
    file_put_contents('data/redirect.txt', $url);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_ip'])) {
        addIPToBlacklist($_POST['add_ip']);
    } elseif (isset($_POST['remove_ip'])) {
        removeIPFromBlacklist($_POST['remove_ip']);
    } elseif (isset($_POST['redirect_url'])) {
        setRedirectURL($_POST['redirect_url']);
    }
    header('Location: EyeOfTiger.php');
    exit();
}

$blacklist = getBlacklist();
$logs = file('data/informations.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$redirectURL = getRedirectURL();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EyeOfTiger Dashboard</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #1e1e1e;
            color: #e0e0e0;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
        }
        .container {
            width: 80%;
            max-width: 1200px;
            background-color: #2c2c2c;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
        }
        h1 {
            color: #b0e0e6;
            display: flex;
            align-items: center;
            margin-top: 0;
        }
        h1 img {
            height: 40px;
            margin-right: 10px;
        }
        form {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #b0e0e6;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #4682b4;
            color: #e0e0e0;
        }
        tbody tr:nth-child(even) {
            background-color: #2c2c2c;
        }
        tbody tr:nth-child(odd) {
            background-color: #1e1e1e;
        }
        label {
            color: #b0e0e6;
        }
        input[type="text"], input[type="submit"] {
            font-family: 'Roboto', sans-serif;
        }
        input[type="submit"] {
            background-color: #4682b4;
            color: #e0e0e0;
            border: none;
            padding: 10px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #4169e1;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><img src="img/logo.jpeg" alt="EyeOfTiger Logo"> Eye Of Tiger</h1>
        <h2>Gerenciar Blacklist</h2>
        <form method="POST" action="EyeOfTiger.php">
            <label for="add_ip">Adicionar IP:</label>
            <input type="text" name="add_ip" id="add_ip" required>
            <input type="submit" value="Adicionar">
        </form>
        <form method="POST" action="EyeOfTiger.php">
            <label for="remove_ip">Remover IP:</label>
            <input type="text" name="remove_ip" id="remove_ip" required>
            <input type="submit" value="Remover">
        </form>
        <h2>Blacklist Atual</h2>
        <ul>
            <?php foreach ($blacklist as $ip) { echo '<li>' . htmlspecialchars($ip) . '</li>'; } ?>
        </ul>
        <h2>Configurar Redirecionamento</h2>
        <form method="POST" action="EyeOfTiger.php">
            <label for="redirect_url">URL de Redirecionamento:</label>
            <input type="text" name="redirect_url" id="redirect_url" value="<?php echo htmlspecialchars($redirectURL); ?>" required>
            <input type="submit" value="Atualizar">
        </form>
        <h2>Logs de Acesso</h2>
        <table>
            <thead>
                <tr>
                    <th>IP</th>
                    <th>PORT</th>
                    <th>CITY</th>
                    <th>REGION</th>
                    <th>COUNTRY</th>
                    <th>LOCATION</th>
                    <th>ISP</th>
                    <th>DATE</th>
                    <th>HOST</th>
                    <th>UA</th>
                    <th>METHOD</th>
                    <th>REF</th>
                    <th>COOKIE</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $logsArray = [];
                $logEntries = implode("\n", $logs);
                $entries = explode("\nIP=", $logEntries);
                foreach ($entries as $entry) {
                    if (trim($entry) === '') continue;
                    $lines = explode("\n", $entry);
                    $log = [];
                    foreach ($lines as $line) {
                        list($key, $value) = explode('=', $line, 2) + [NULL, NULL];
                        $log[$key] = $value;
                    }
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($log['IP'] ?? '') . '</td>';
                    echo '<td>' . htmlspecialchars($log['PORT'] ?? '') . '</td>';
                    echo '<td>' . htmlspecialchars($log['CITY'] ?? '') . '</td>';
                    echo '<td>' . htmlspecialchars($log['REGION'] ?? '') . '</td>';
                    echo '<td>' . htmlspecialchars($log['COUNTRY'] ?? '') . '</td>';
                    echo '<td>' . htmlspecialchars($log['LOCATION'] ?? '') . '</td>';
                    echo '<td>' . htmlspecialchars($log['ISP'] ?? '') . '</td>';
                    echo '<td>' . htmlspecialchars($log['DATE'] ?? '') . '</td>';
                    echo '<td>' . htmlspecialchars($log['HOST'] ?? '') . '</td>';
                    echo '<td>' . htmlspecialchars($log['UA'] ?? '') . '</td>';
                    echo '<td>' . htmlspecialchars($log['METHOD'] ?? '') . '</td>';
                    echo '<td>' . htmlspecialchars($log['REF'] ?? '') . '</td>';
                    echo '<td>' . htmlspecialchars($log['COOKIE'] ?? '') . '</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
