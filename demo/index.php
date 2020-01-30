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

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/bxslider/4.2.12/jquery.bxslider.css">

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
<script src="https://cdn.jsdelivr.net/bxslider/4.2.12/jquery.bxslider.min.js"></script>

<script>

    let user_id = $('#user_id').val();
    let game_id = $('#game_id').val();

    let comment_input = $('#comment_input');
    let comments = $('#comments');
    let comments_default = $('#comments_default');

    let comment_button = $('#comment_button');

    function getUserComment() {

        let url = $('#comment_form').prop('action');

        let params = {
            action: 'get_user_comment',
            user_id: user_id,
            game_id: game_id
        };

        $.post(url, params, function(data) {
            //console.log(data);
            let comment = data.comment;
            if(comment.id) {

                comments_default.hide();
                comment_input.hide();

                let user_comment = $('#comment_'+comment.id);
                if(user_comment) {return false;}

                renderComment(comment.id, comment.user_id, comment.name, comment.comment);

            }
        });

    }

    function getComments() {

        let url = $('#comment_form').prop('action');

        let params = {
            action: 'get_comments',
            game_id: game_id
        };

        $.post(url, params, function(data) {

            let comments_list = data.comments;
            comments_default.hide();
            $.each(comments_list, function(key, comment){
                if(comment.id) {renderComment(comment.id, comment.user_id, comment.name, comment.comment);}
            });

            getUserComment();

            comments.bxSlider({
                controls: false,
                auto: true,
                autoControls: true,
                pause: 30000,
                stopAutoOnClick: true,
                //pager: true,
            });

        });

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

            //console.log(data);
            let comment = data.comment;
            if(comment.id) {

                comments_default.hide();
                comment_input.hide();

                renderComment(comment.id, comment.user_id, comment.name, comment.comment);

            }

        });

    }

    function editComment(id) {

        let update_button = $('#update_button');
        let edit_button = $('#edit_button');
        let delete_button = $('#delete_button');
        let cancel_button = $('#cancel_button');

        let edit_name = $('#name_'+id);
        let edit_text = $('#text_'+id);
        let edit_comment = $('#edit_comment_'+id);

        edit_button.hide();
        delete_button.hide();
        update_button.show();
        cancel_button.show();

        edit_name.hide();
        edit_text.hide();
        edit_comment.show();

    }

    function updateComment(id) {

        let url = $('#comment_form').prop('action');

        let edit_comment = $('#edit_comment_'+id).val();

        let params = {
            user_id: user_id,
            game_id: game_id,
            comment_id: id,
            comment: edit_comment
        };

        $.post(url, params, function(data) {
            //console.log(data);
            let comment = data.comment;
            if(comment.id) {
                $('#text_'+id).html(comment.comment);
                cancelComment(id);
            }
        });

    }

    function deleteComment(id) {

        if(confirm('Confirm delete?')) {

            let url = $('#comment_form').prop('action');

            let params = {
                action: 'delete_comment',
                user_id: user_id,
                comment_id: id
            };

            //console.log(params);

            $.post(url, params, function(data) {
                //console.log(data);
                let comment = data.comment;
                if(comment.deleted) {
                    $('#comment_'+id).remove();
                    comment_input.show();
                }

            });

        }

    }

    function cancelComment(id) {

        let update_button = $('#update_button');
        let edit_button = $('#edit_button');
        let delete_button = $('#delete_button');
        let cancel_button = $('#cancel_button');

        let edit_name = $('#name_'+id);
        let edit_text = $('#text_'+id);
        let edit_comment = $('#edit_comment_'+id);

        edit_button.show();
        delete_button.show();
        update_button.hide();
        cancel_button.hide();

        edit_name.show();
        edit_text.show();
        edit_comment.hide();

    }

    function renderComment(id, comment_user_id, name, comment) {

        let block = '';

        block += '<div class="" id="comment_'+id+'">';
        block += '<div class="card-body">';
        block += '<h5 class="card-title" id="name_'+id+'">'+name+'</h5>';
        block += '<p class="card-text" id="text_'+id+'">'+comment+'</p>';
        if(user_id === comment_user_id) {

            block += '<textarea name="comment_'+id+'" id="edit_comment_'+id+'" class="form-control mb-3 hide" maxlength="250">'+comment+'</textarea>';
            block += '<div class="btn-group" role="group">';
            block += '<a href="#" class="btn btn-outline-success btn-sm hide" id="update_button">Update</a>';
            block += '<a href="#" class="btn btn-outline-primary btn-sm" id="edit_button">Edit</a>';
            block += '<a href="#" class="btn btn-outline-danger btn-sm" id="delete_button">Delete</a>';
            block += '<a href="#" class="btn btn-outline-secondary btn-sm hide" id="cancel_button">Cancel</a>';
            block += '</div>';

        }
        block += '</div>';
        block += '</div>';

        comments.append(block);

        if(user_id === comment_user_id) {

            let update_button = $('#update_button');
            let edit_button = $('#edit_button');
            let delete_button = $('#delete_button');
            let cancel_button = $('#cancel_button');

            update_button.click(function () {
                updateComment(id);
            });
            edit_button.click(function () {
                editComment(id);
            });
            delete_button.click(function () {
                deleteComment(id);
            });
            cancel_button.click(function () {
                cancelComment(id);
            });

            $('.hide').hide();

        }

    }

    $(function() {

        $('.hide').hide();

        getComments();

        comment_button.click(function() {addComment()});

    });

</script>

</body>
</html>
