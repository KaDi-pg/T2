<?php
    declare(strict_types=1);

    require_once dirname(__FILE__).'/php/h.php';
    require_once dirname(__FILE__).'/php/db/basic_db_func.php';

    /*
    *ユーザーデータを格納するデータベースを作成します。
    */

    // DB接続
    $pdo = connect_db(); 
    
    //テーブル作成 
    $sql = "CREATE TABLE IF NOT EXISTS users"
    ." ("
    . "id INT AUTO_INCREMENT PRIMARY KEY,"
    . "pass TEXT,"
    . "name TEXT,"
    . "profile TEXT,"
    . "icon_id INT"
    .");"; 

    $stmt = $pdo->query($sql);
    
    //テーブル内容の表示
    $result = show_db($pdo, 'users');

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