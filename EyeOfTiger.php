<?php
session_start();

function authenticate() {
	if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
		if ($_SERVER['PHP_AUTH_USER'] !== 'admin' || $_SERVER['PHP_AUTH_PW'] !== 'admin') {
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
	<style>
		body {
			font-family: Arial, sans-serif;
			background-color: #f0f8ff;
			color: #333;
			margin: 0;
			padding: 20px;
		}
		h1 {
			color: #4682b4;
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
			border: 1px solid #4682b4;
		}
		th, td {
			padding: 10px;
			text-align: left;
		}
	</style>
</head>
<body>
	<h1>EyeOfTiger Dashboard</h1>
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
			$entry = [];
			foreach ($logs as $line) {
				if (trim($line) === '') {
					if (!empty($entry)) {
						echo '<tr>';
						foreach ($entry as $value) {
							echo '<td>' . htmlspecialchars($value) . '</td>';
						}
						echo '</tr>';
						$entry = [];
					}
				} else {
					$entry[] = explode('=', $line)[1];
				}
			}
			if (!empty($entry)) {
				echo '<tr>';
				foreach ($entry as $value) {
					echo '<td>' . htmlspecialchars($value) . '</td>';
				}
				echo '</tr>';
			}
			?>
		</tbody>
	</table>
</body>
</html>
