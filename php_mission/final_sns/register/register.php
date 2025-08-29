<?php
declare(strict_types=1);

require_once '../php/db/basic_db_func.php';
require_once '../php/session.php';
require_once '../php/token.php';

/*
* 新規登録フォームのある画面
* 名前とパスワードを入力してもらう
* 送信ボタンが押されたら、記入すべき事項が全て記入されているか確認、確認ができたら、登録完了画面へ
*/

session_start();

$input_err = null;

if (!empty($_POST["user_name"]) && !empty($_POST["pass"])) {
    //パスワードのハッシュ化
    $hashed_pass =  password_hash($_POST["pass"], PASSWORD_DEFAULT);
    
    //ニックネームとパスワードのデータベースへの登録
    $pdo = connect_db();
    $max_id = get_max($pdo, 'users', 'id');
    $user_id = $max_id + 1;
    
    $sql = 'INSERT INTO users (id, pass, name) VALUES (:id, :pass, :name)';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':pass', $hashed_pass, PDO::PARAM_STR);
    $stmt->bindParam(':name', $_POST["user_name"], PDO::PARAM_STR);
    $stmt->execute();

    //ID情報をセッションに保持し、登録完了画面へ移動。
    $token = generateToken(8);
    $_SESSION['data']=[
        'register_id' => $user_id,
        'token' => $token
    ];
    header('Location: register_end.php?t='.$token);

} elseif (isset($_POST["user_name"])) {
    //名前かパスワードかのどちらかが記入されていない場合
    //記入をうながす。
    $input_err = "has_blank";
} else {
    //未送信
    //何もしない
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>sns_register</title>
</head>

<body>
    <form method="POST" action="">
        新規登録フォーム<br>
        ニックネームとパスワードを入力後、新規登録ボタンを押してください。<br>
        ニックネーム：<input type="text" name="user_name" style="width: 400px;"><br>
        パスワード（8文字以上の英数）：<input type="password" name="pass" minlength=8 pattern="[a-zA-Z0-9]+" autocomplete="current-password" title="パスワードは8文字以上の半角英数字です。" style="width: 420px;" required><br>
        <button type="submit" name="operation" value="login">新規登録</button>
        <?php if ($input_err == "has_blank"): ?>
            <span style="color: red;">ニックネームとパスワードの両方を入力してください。</span>
        <?php endif;?>
    </form>
    
</body>
</html>