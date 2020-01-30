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

$result = array();

$comments = new Comments($pdo);

if(isset($request['user_id'])) {

	$user_id = (int) $request['user_id'];

	if (isset($request['name'])) {
		$name = strip_tags($request['name']);
		$stmt = $pdo->prepare('UPDATE users SET name = :name WHERE id = :id');
		$stmt->execute(array('id' => $user_id, 'name' => $name));
		$result['name'] = $name;
	}

	if (isset($request['game_id']) && isset($request['comment'])) {
		$game_id = (int) $request['game_id'];
		$text = strip_tags($request['comment']);
		if(isset($request['comment_id'])) {
			$comment_id = $request['comment_id'];
			$stmt = $pdo->prepare('UPDATE comments SET comment = :comment WHERE id = :id');
			$stmt->execute(array('id' => $comment_id, 'comment' => $text));
		} else {
			$stmt = $pdo->prepare('INSERT INTO comments SET comment = :comment, user_id = :user_id, game_id = :game_id');
			$stmt->execute(array('user_id' => $user_id, 'comment' => $text, 'game_id' => $game_id));
			$comment_id = $pdo->lastInsertId();
		}
		$result['comment_id'] = $comment_id;
		$result['comment'] = $text;
	}

}

header('Content-type: application/json');
echo json_encode($result); die();