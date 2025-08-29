<?php
declare(strict_types=1);

require_once '../php/db/basic_db_func.php';
require_once '../php/session.php';
require_once '../php/h.php';

/*
* 新規登録完了画面
* 新規登録画面以外からのアクセスは内容を変える
* セッションを参考に、ユーザーIDを表示しつつ、セッションを破棄
* ログイン画面へ誘導する
*/

session_start();

if (!isset($_SESSION['data']['register_id']) || !isset($_SESSION['data']['token']) || !isset($_GET['t']) || $_SESSION['data']['token']!==$_GET['t']) {
    //不正なアクセス
    $valid_access = false;
} else {
    //正しいアクセス
    $valid_access = true;
    $register_id = $_SESSION['data']['register_id'];
}

//セッション情報の削除
$_SESSION = [];
deleteSession();
session_destroy();

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>sns_register_end</title>
</head>

<body>
    <?php if ($valid_access): ?>
        <p>ユーザー登録が完了しました。<br>
        あなたのユーザーIDは、<span style="color:red;"><?=h(strval($register_id))?></span>です。<br>
        ユーザーIDと登録したパスワードで、ログインしてください。
        </p>
        <div>
            <a href="./login.php">ログイン画面へ</a>
        </div>
    <?php else: ?>
        <p>不正なアクセスです。</p>
    <?php endif; ?>
    
</body>
</html>