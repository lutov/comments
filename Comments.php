<?php
/**
 * Created by PhpStorm.
 * User: lutov
 * Date: 30.01.2020
 * Time: 11:19
 */

class Comments {
	/**
	 * @var PDO
	 */
	protected $pdo;

	/**
	 * Comments constructor.
	 * @param PDO $pdo
	 */
	public function __construct(PDO $pdo) {

		$this->pdo = $pdo;

	}

	/**
	 * @param int $user_id
	 * @param int $game_id
	 * @param string $text
	 * @return int
	 */
	public function add(int $user_id, int $game_id, string $text) {

		$stmt = $this->pdo->prepare('INSERT INTO comments SET comment = :comment, user_id = :user_id, game_id = :game_id');
		$stmt->execute(array('user_id' => $user_id, 'comment' => $text, 'game_id' => $game_id));
		$comment_id = (int) $this->pdo->lastInsertId();

		return $comment_id;

	}

	/**
	 * @param int $comment_id
	 * @param string $text
	 */
	public function update(int $comment_id, string $text) {

		$stmt = $this->pdo->prepare('UPDATE comments SET comment = :comment WHERE id = :id');
		$stmt->execute(array('id' => $comment_id, 'comment' => $text));

	}

	/**
	 * @param int $user_id
	 * @param int $comment_id
	 * @return bool
	 */
	public function delete(int $user_id, int $comment_id) {

		$stmt = $this->pdo->prepare('DELETE FROM comments WHERE user_id = :user_id AND id = :id LIMIT 1');
		$stmt->execute(array('user_id' => $user_id, 'id' => $comment_id));

		return true;

	}

	/**
	 * @param int $user_id
	 * @param int $game_id
	 * @return mixed
	 */
	public function getUserCommentByGame(int $user_id, int $game_id) {

		$stmt = $this->pdo->prepare('SELECT comments.id, user_id, game_id, comment, name FROM comments LEFT JOIN users ON user_id = users.id WHERE user_id = :user_id AND game_id = :game_id LIMIT 1');
		$stmt->execute(array('user_id' => $user_id, 'game_id' => $game_id));
		$comment = $stmt->fetch();

		return $comment;

	}

	/**
	 * @param int $game_id
	 * @return array
	 */
	public function getRandomByGame(int $game_id) {

		$comments = array();

		$stmt = $this->pdo->prepare('SELECT comments.id, user_id, game_id, comment, name FROM comments LEFT JOIN users ON user_id = users.id WHERE game_id = :game_id ORDER BY rand() LIMIT 10');
		$stmt->execute(array('game_id' => $game_id));
		while($comment = $stmt->fetch()) {
			$comments[] = $comment;
		}

		return $comments;

	}

}