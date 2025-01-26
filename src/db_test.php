<?php
	$host = "docker_hazard_map_db";
	$port = "5432";
	$dbname = "hazard_db";
	$user = "user";
	$password = "16210a0c-d1cd-fd9e-7746-b042e3bfa723";

	try {
		$dsn = "pgsql:host=$host;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password);
    echo "<p>接続成功</p>";
	}catch (PDOException $e) {
    echo "<p style='color: red;'>エラー: " . htmlspecialchars($e->getMessage()) . "</p>";
	}

	$mail = "test@email.com";
	$pass = "test1234";
	$sql = "SELECT * FROM users WHERE email = '$mail' and password = '$pass'";
	echo "<p>SQL: " . htmlspecialchars($sql) . "</p>";
	$stmt = $pdo->query($sql);
	$result = $stmt->fetch();
	echo "<p>" . htmlspecialchars($result['name']) . "</p>";
	foreach ($stmt as $row) {
		echo "<p>" . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') . "</p>";
	}
?>