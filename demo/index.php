<?php
/**
 * Created by PhpStorm.
 * User: lutov
 * Date: 30.01.2020
 * Time: 11:24
 */

require_once(__DIR__."/../vendor/autoload.php");

$controller_url = 'controller.php';

$dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset='.DB_CHARSET;
$opt = [
	PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
	PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	PDO::ATTR_EMULATE_PREPARES => false,
];
$pdo = new PDO($dsn, DB_USER, DB_PASSWORD, $opt);

$errors = array();

$request = $_REQUEST;
$request['game_id'] = 1; // test value

$auth = false;
$user_id = 0;
$user = array();
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
		</div>
	</div>

    <h2 class="mt-2">Comments</h2>
    <div id="comments">
        <div class="alert alert-light" id="comments_default">
            No one has shared a review, be the first to write your review!
        </div>
    </div>

	<?php if($auth) { ?>
        <div id="comment_input">

            <h2 class="mt-2">Add comment</h2>

            <div class="alert alert-secondary">

                <form method="POST" action="<?=$controller_url;?>" id="comment_form">

                    <input type="hidden" id="user_id" name="user_id" value="<?=$user_id;?>">
                    <input type="hidden" id="game_id" name="game_id" value="<?=$game_id;?>">

                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" name="name" id="name" class="form-control">
                        <div class="text-danger small hide" id="name_error"></div>
                    </div>

                    <div class="form-group">
                        <label for="comment">Comment</label>
                        <textarea name="comment" id="comment" class="form-control" maxlength="250"></textarea>
                        <div class="text-danger small hide" id="comment_error"></div>
                    </div>

                    <button type="button" class="btn btn-primary" id="comment_button">Submit</button>

                </form>

            </div>

        </div>
	<? } ?>

</div>

<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script
        src="https://code.jquery.com/jquery-3.4.1.min.js"
        integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
        crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>

<script>

    let user_id = $('#user_id').val();
    let game_id = $('#game_id').val();

    function getUserComment(user_id) {

        let user_comment = {};



        if(user_comment.id) {$('#comment_input').hide();}

    }

    function getComments(game_id) {



    }

    function addComment() {

        let name = $('#name').val();
        let comment = $('#comment').val();

        let name_error = $('#name_error');
        let comment_error = $('#comment_error');

        let error = false;
        name_error.hide();
        comment_error.hide();

        if('' === name) {
            error = true;
            name_error.html('Name required');
            name_error.show();
        }

        if('' === comment) {
            error = true;
            comment_error.html('Text required');
            comment_error.show();
        }

        if(error) {return false;}

        let url = $('#comment_form').prop('action');

        let params = {
            user_id: user_id,
            game_id: game_id,
            name: name,
            comment: comment
        };

        //console.log(params);

        $.post(url, params, function(data) {

            console.log(data);

        });

    }

    $(function() {

        $('.hide').hide();

        getUserComment(user_id);
        getComments(game_id);

        $('#comment_button').click(function() {addComment()});

    });

</script>

</body>
</html>
