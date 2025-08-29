<?php
    declare(strict_types=1);

    require_once '../php/h.php';
    require_once '../php/db/basic_db_func.php';

    /*
    * ログイン状態を保持するためのトークンを保存するテーブルを作成します
    */

    // DB接続
    $pdo = connect_db(); 
    
    //テーブル作成 
    $sql = "CREATE TABLE IF NOT EXISTS logintokens"
    ." ("
    . "id INT,"
    . "token TEXT,"
    . "expires TIMESTAMP"
    .");"; 

    $stmt = $pdo->query($sql);
    
    //テーブル内容の表示
    $result = show_db($pdo, 'logintokens');

?>


<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>create_users_table</title>
</head>

<body>
    <?php foreach ($result as $row): ?>
        <?=h($row[1])?>
        <hr>
    <?php endforeach;?>
</body>
</html>