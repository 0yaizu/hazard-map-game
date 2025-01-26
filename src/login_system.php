<?php
session_start();
$mail = $_POST['mail'];
$pass = $_POST['pass'];
$host = "docker_hazard_map_db";
	$port = "5432";
	$dbname = "hazard_db";
	$user = "user";
	$password = "16210a0c-d1cd-fd9e-7746-b042e3bfa723";
try {
	$dsn = "pgsql:host=$host;dbname=$dbname";
	$pdo = new PDO($dsn, $user, $password);
} catch (PDOException $e) {
  $msg = $e->getMessage();
}

$sql = "SELECT * FROM users WHERE email = '$mail' AND password = '$pass'";
$stmt = $pdo->query($sql);

if ($stmt->rowCount() == 1) {
	//DBのユーザー情報をセッションに保存
	$result = $stmt->fetch();
	$_SESSION['id'] = $result['user_id'];
	$_SESSION['name'] = $result['name'];
	$msg = 'ログインしました。';
	$link = '<a href="home.php">ホーム</a>';
}
else {
	$msg = 'メールアドレスもしくはパスワードが間違っています。';
	$link = '<a href="login.php">戻る</a>';
}
?>

<h1><?php echo $msg; ?></h1>
<?php echo $link; ?>