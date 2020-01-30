<?php
/**
 * Created by PhpStorm.
 * User: lutov
 * Date: 30.01.2020
 * Time: 11:24
 */

require_once(__DIR__."/../vendor/autoload.php");

$dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset='.DB_CHARSET;
$opt = [
	PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
	PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	PDO::ATTR_EMULATE_PREPARES => false,
];
$pdo = new PDO($dsn, DB_USER, DB_PASSWORD, $opt);

$request = $_REQUEST;
$request['game_id'] = 1; // test value

$auth = false;
if(isset($request['user_id'])) {
	$user_id = (int) $request['user_id'];
	$stmt = $pdo->prepare('SELECT id, name FROM users WHERE id = :id LIMIT 1');
	$stmt->execute(array('id' => $user_id));
	$user = $stmt->fetch();
	if(isset($user['id'])) {$auth = true;}
}

$game_id = $request['game_id'];
$stmt = $pdo->prepare('SELECT * FROM games WHERE id = :id LIMIT 1');
$stmt->execute(array('id' => $game_id));
$game = $stmt->fetch();

$comments = new Comments($pdo);

?>
<!doctype html>
<html lang="en">
<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<!-- Bootstrap CSS -->
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">

	<title>Games | <?=$game['name']?></title>
</head>
<body>

<div class="container mt-5">

	<h1><?=$game['name']?></h1>

	<div class="card">
		<div class="card-body">
			<p class="card-text"><?=$game['description']?></p>
			<?php if($auth) { ?>
			<a href="#" class="btn btn-primary">Add comment</a>
			<? } ?>
		</div>
	</div>

</div>

<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
</body>
</html>
