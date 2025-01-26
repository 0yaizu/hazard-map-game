<?php
session_start();
$username = $_SESSION['name'];
if (isset($_SESSION['id'])) { //ログインしているとき
	$usrname = htmlspecialchars($username, \ENT_QUOTES, 'UTF-8');
	$msg = 'こんにちは' . htmlspecialchars($username, \ENT_QUOTES, 'UTF-8') . 'さん';
	$link = '<a href="logout.php">ログアウト</a>';
} else { //ログインしていない時
	$usrname = '<a href="login.php">ログイン</a>';
	$msg = 'ログインしていません';
	$link = '<a href="login.php">ログイン</a>';
}
?>

<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!--Bootstrap-->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous"></script>
	
	<!--css-->
	<link rel="stylesheet" href="style/style.css">
	<link rel="stylesheet" href="style/gameroom.css">

	<!-- Google font Icon -->
	<link
		href="https://fonts.googleapis.com/icon?family=Material+Icons"
		rel="stylesheet"
	/>

	<title>ゲームプレイ画面</title>
</head>

<body>
	<!--header-->
	<header>
		<div class="row align-items-center">
			<div class="col-1"><h1><a href="home.php">resQ</a></h1></div>
			<div class="col-6"></div>
			<div class="col-3">
				<span><?php echo $usrname; ?></span>
				<span class="material-icons icon">account_circle</span>
			</div>
			<div class="col-2"><?php echo $link; ?></div>
		</div>
	</header>
	<p>ゲーム画面(仮)</p>
	<table>
		<tr>
			<td>あああ</td>
			<td>あああ</td>
			<td>あああ</td>
			<td>あああ</td>
			<td>あああ</td>
		</tr>
		<tr>
			<td>あああ</td>
			<td>あああ</td>
			<td>あああ</td>
			<td>あああ</td>
			<td>あああ</td>
		</tr>
		<tr>
			<td>あああ</td>
			<td>あああ</td>
			<td>あああ</td>
			<td>あああ</td>
			<td>あああ</td>
		</tr>
		<tr>
			<td>あああ</td>
			<td>あああ</td>
			<td>あああ</td>
			<td>あああ</td>
			<td>あああ</td>
		</tr>
		<tr>
			<td>あああ</td>
			<td>あああ</td>
			<td>あああ</td>
			<td>あああ</td>
			<td>あああ</td>
		</tr>
	</table>
</body>

</html>