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

}