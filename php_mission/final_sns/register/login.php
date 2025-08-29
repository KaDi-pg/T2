<?php
declare(strict_types=1);

require_once '../php/db/basic_db_func.php';
require_once '../php/session.php';
require_once '../php/token.php';

/*
* ログインフォームのある画面
* ログインボタンが押されていたら、正しいかを確認する。
* IDとパスワードが一致したら、SNSのホーム画面へ遷移、何らかの問題が生じたら、それをこの画面で伝える
*/
session_start();

$input_state = null;

if (!empty($_POST['user_id']) && !empty($_POST['pass'])){
    //IDとパスワードを照合
    //IDが存在するか、その場合パスワードが一致するかをチェック
    $pdo = connect_db();
    $input_id = intval($_POST['user_id']);
    $input_pass = $_POST['pass'];

    //IDが存在するか、その場合パスワードが一致するかをチェック
    if (!is_in_data_int($pdo, 'users', 'id', $input_id)) {
        //該当するIDが存在しない
        $input_state = 'not_exist_user';
    } elseif (!match_pass($pdo, 'users', $input_id, $input_pass)) {
        //パスワードが一致しない
        $input_state = 'wrong_password';
    } else {
        //正しいログイン
        $input_state = 'correct';

        //同じものがログイントークンデータベース上に存在しないトークンを作成し、データベースとクッキーへ保存
        $is_bad_token = true;
        while ($is_bad_token)
        {
            $token = generateToken(16);
            $is_bad_token = is_in_data_str($pdo, 'logintokens', 'token', $token);
        }
        
        $sql = 'INSERT INTO logintokens (id, token, expires) VALUES (:id, :token, NOW())';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $input_id, PDO::PARAM_INT);
        $stmt->bindParam(':token', $token, PDO::PARAM_STR);
        $stmt->execute();

        setcookie('logintoken', $token, time()+60*60*24, '/', '', false, true);

        //メイン画面へ
        header('Location: ../main.php');
    }
} elseif (isset($_POST['operation'])) {
    //いずれかが空欄で送信
    $input_state = 'has_blank';
} else {
    //未送信
    //何もしない
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>sns_login</title>
</head>

<body>
    <form method="POST" action="">
        ログインフォーム<br>
        <input type="number" name="user_id" style="width: 400px;" placeholder="ユーザーID(5桁)"><br>
        <input type="password" name="pass" minlength=8 pattern="[a-zA-Z0-9]+" autocomplete="current-password" title="パスワードは8文字以上の半角英数字です。" style="width: 420px;" required><br>
        <button type="submit" name="operation" value="login">ログイン</button>

        <?php if ($input_state == "has_blank"): ?>
            <span style="color: red;">ユーザーIDとパスワードの両方を入力してください。</span>
        <?php elseif($input_state == "not_exist_user" || $input_state == "wrong_password"):?>
            <span style="color: red;">ユーザーIDまたはパスワードが違います。</span>
        <?php endif;?>
    </form>
        <!-- test commentout -->
        <!-- test commentout 2 -->


</body>
</html>