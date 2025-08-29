<?php
declare(strict_types=1);

require_once 'php/h.php';
require_once 'php/db/basic_db_func.php';
require_once 'php/token.php';

/*
* メインのSNS画面です。みんなの投稿の表示や、新規投稿ができます。
*/

$pdo = connect_db();

//ログアウトボタンが押されたときの処理。これだけ最初に行う
if (!empty($_POST['logout'])) {
    //ログアウトボタン
    //データベースとCookieのトークンの削除
    $sql = 'DELETE from logintokens WHERE token=:token';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':token', $_COOKIE['logintoken'], PDO::PARAM_STR);
    $stmt->execute();
    setcookie('logintoken', '', time()-60*60*24, '/', '', false, true);
    
    $is_logined = false;
    $login_id = -1;
    $login_name = "";
} elseif (isset($_COOKIE['logintoken'])) {
    //ログインを判定
    $sql = 'SELECT id,expires FROM logintokens WHERE token =:token';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':token', $_COOKIE['logintoken'], PDO::PARAM_STR);
    $stmt->execute();
    $user_data = $stmt->fetchAll();

    //クッキーの保存期間内か確認
    $cur_time = date('Y-m-d H:i:s');
    $duration = strtotime($cur_time) - strtotime($user_data[0]['expires']);
    if ($duration >= 0 && $duration <= 60*60*24) {
        $is_logined = true;
        $login_id = intval($user_data[0]['id']);

        //ユーザー情報の取得
        $sql = 'SELECT * FROM users WHERE id=:id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', intval($user_data[0]['id']), PDO::PARAM_INT);
        $stmt->execute();
        $user_info = $stmt->fetchAll();
        $login_name = $user_info[0]['name'];

        //同じものがログイントークンデータベース上に存在しないトークンを再作成し、データベースとクッキーへ保存
        $is_bad_token = true;
        while ($is_bad_token)
        {
            $new_token = generateToken(16);
            $is_bad_token = is_in_data_str($pdo, 'logintokens', 'token', $new_token);
        }
        
        $sql = 'UPDATE logintokens SET token=:newtoken, expires=NOW() WHERE token=:oldtoken';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':oldtoken', $_COOKIE['logintoken'], PDO::PARAM_STR);
        $stmt->bindParam(':newtoken', $new_token, PDO::PARAM_STR);
        $stmt->execute();

        setcookie('logintoken', $new_token, time()+60*60*24, '/', '', false, true);
    } else {
        $is_logined = false;
        $login_id = -1;
        $login_name = "";
    }
} else {
    $is_logined = false;
    $login_id = -1;
    $login_name = "";
}


$edit_state_id = 0;
$default_text = "";


//フォーム送信時の処理
if (!empty($_POST['login'])) {
    //ログインボタンが押されたとき
    //ログイン画面へ
    header('Location: register/login.php');
} elseif (!empty($_POST['register'])) {
    //新規登録ボタンが押されたとき
    //新規登録画面へ
    header('Location: register/register.php');
} elseif (!empty($_POST['newpost'])) {
    //新規投稿、編集済投稿がされたとき
    if ($_POST["edit_id"]==0) {
        //新規投稿
        $sql = "INSERT INTO posts (user_id, user_name, post_text, post_time, edited) VALUES (:userid, :username, :posttext, NOW(), false)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':userid', $login_id, PDO::PARAM_INT);
        $stmt->bindParam(':username', $login_name, PDO::PARAM_STR);
        $stmt->bindParam(':posttext', $_POST["comment"], PDO::PARAM_STR); 
        $stmt->execute();
    } else {
        //編集投稿
        $sql = $sql = 'UPDATE posts SET post_text=:posttext, edited=true WHERE post_id=:id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', intval($_POST["edit_id"]), PDO::PARAM_INT);
        $stmt->bindParam(':posttext', $_POST["comment"], PDO::PARAM_STR); 
        $stmt->execute();
    }
} elseif (!empty($_POST['delete'])) {
    //削除ボタンが押されたとき
    $sql = "DELETE from posts WHERE post_id= :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":id", intval($_POST["delete_post_id"]), PDO::PARAM_INT);
    $stmt->execute();
} elseif (!empty($_POST['edit'])) {
    //編集ボタンが押されたとき
    $sql = "SELECT * FROM posts WHERE post_id= :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":id", intval($_POST["edit_post_id"]), PDO::PARAM_INT);
    $stmt->execute();
    $update_content = $stmt->fetchAll();
    
    $edit_state_id = $update_content[0]['post_id'];
    $default_text = $update_content[0]['post_text'];
} else {
    //何もしない
}


//posts tableに登録されている投稿の総件数を確認
$sql = 'SELECT COUNT(*) FROM posts as num_of_comments';
$stmt = $pdo->query($sql);
$num_of_comments = $stmt->fetchAll();

//posts tableに登録されているすべての投稿を読み出す
$sql = 'SELECT * FROM posts ORDER BY post_id';
$stmt = $pdo->query($sql);
$posts = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>最終成果SNS</title>
    <link rel="stylesheet" href="css/mainstyle.css" type="text/css">
    <script type="text/javascript" language="javascript" src="js/func.js" charset="UTF-8"></script>
</head>

<body>
<h2>簡易会員制掲示板</h2>


<!--ログイン状態に合わせたボタンの表示-->
<?php if($is_logined):?>
    <p>ようこそ、<?=h($login_name)?>さん</p>
    <form method="POST" action="" id="logout_form">    
        <input type="submit" name="logout" value="ログアウト">
    </form>
<?php else:?>
    <form method="POST" action="" id="login_form">    
        <input type="submit" name="login" value="ログイン">
    </form>
    <form method="POST" action="" id="logout_form">    
        <input type="submit" name="register" value="新規登録">
    </form>
<?php endif;?>

<hr>

<!--データベースの内容を記述-->
<?php if((int)$num_of_comments[0][0]!=0): ?>
    <div id='comments'>
        <?php foreach($posts as $row):?>
            <div class='each_comment'>
                <?=h($row["post_id"])?>. <?=h($row["user_name"])?>  <span style='color: #808080'><?=h($row["post_time"])?></span><br>
                <div style='white-space:pre-wrap; padding:10px'><?=h($row["post_text"])?></div>
                <?php if($row["edited"]):?>
                    <div class='edited_sign'>（編集済）</div>
                <?php endif;?>
                <?php if($row["user_id"]==$login_id):?>
                    <form method="POST" action="" class="delete_form">    
                        <input type="hidden" name="delete_post_id" value=<?=$row["post_id"]?>>
                        <input type="submit" name="delete" value="削除">
                    </form>
                    <form method="POST" action="#newpost_open_button" class="edit_form">    
                        <input type="hidden" name="edit_post_id" value=<?=$row["post_id"]?>>
                        <input type="submit" name="edit" value="編集">
                    </form>
                <?php endif;?>
            </div>
            <hr>
        <?php endforeach?>
    </div>
<?php else: ?>
    <div id='no_comments'>
        まだ投稿はありません。
    </div>
<?php endif;?>


<hr>
<!--投稿機能-->
    <div class="form">
        <input type="button" id="newpost_open_button" onclick="pushNewpostButton(<?=$is_logined?>);" value="投稿する▼"><br>
        <form method="POST" action="" id="post_form" style="display: none;">
            <?php if($edit_state_id == 0): ?>
                新規投稿を行います。投稿内容を入力後、投稿ボタンを押してください。<br><br>
            <?php else:?>
                投稿の編集を行います。投稿内容を編集後、投稿ボタンを押してください。<br><br>
            <?php endif;?>      
            <input type="hidden" name="edit_id" value=<?=$edit_state_id?> style="width: 420px;">
            <input type="hidden" name="post_user_id" value=<?=$login_id?> style="width: 420px;">
            <textarea id="comment" name="comment" rows="5" style="width: 420px;" placeholder="いまどうしてる？"><?=h($default_text)?></textarea><br>
            <input type="submit" name="newpost" value="投稿">
            <input type="button" id="newpost_close_button" onclick="closeNewpostArea();" value="閉じる▲">
        </form>
    
        <!--フォームの送信確認処理-->
        <?php if(!empty($_POST["comment"])):?>
            <!--正常な送信-->
            投稿を行いました。<br>
        <?php elseif(!empty($_POST["comment"])):?>
            <!--送信処理中の不具合-->
            <div style='color: red;'>投稿の過程で問題が発生しました。投稿をやり直してください。<br></div>
        <?php elseif(isset($_POST["comment"])): ?>
            <!--名前かコメントが空文字列-->
            <div style='color: red;'>1文字以上の内容を投稿してください。<br></div>
        <?php else: ?>
            <!--未送信。何もしない-->
        <?php endif;?>

        <!--編集時に、あらかじめフォームを開く-->
        <?php if(!empty($_POST['edit'])): ?>
            <script>
                pushNewpostButton(true);
            </script>
        <?php else: ?>
            <!--何もしない-->
        <?php endif; ?>
    </div>
</body>
</html>