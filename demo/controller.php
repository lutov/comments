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

$action = (isset($request['action'])) ? strip_tags($request['action']) : '';

$game_id = (isset($request['game_id'])) ? (int) $request['game_id'] : 0;

$comment_id = (isset($request['comment_id'])) ? (int) $request['comment_id'] : 0;

if(isset($request['user_id'])) {

	$user_id = (int) $request['user_id'];
	$result['comment']['user_id'] = $user_id;

	if (isset($request['name'])) {
		$name = strip_tags($request['name']);
		$stmt = $pdo->prepare('UPDATE users SET name = :name WHERE id = :id');
		$stmt->execute(array('id' => $user_id, 'name' => $name));
		$result['comment']['name'] = $name;
	}

	if (0 !== $game_id) {

		if (isset($request['comment'])) {
			$text = strip_tags($request['comment']);
			if (0 !== $comment_id) {
				$comments->update($comment_id, $text);
			} else {
				$comment_id = $comments->add($user_id, $game_id, $text);
			}
			$result['comment']['id'] = $comment_id;
			$result['comment']['comment'] = $text;
		}

		if ('get_user_comment' == $action) {

			$result['comment'] = $comments->getUserCommentByGame($user_id, $game_id);

		}

	}

	if ('delete_comment' == $action) {

		$result['comment']['deleted'] = $comments->delete($user_id, $comment_id);

	}

}

if ('get_comments' == $action) {

	$result['comments'] = $comments->getRandomByGame($game_id);

}

header('Content-type: application/json');
echo json_encode($result); die();