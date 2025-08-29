<?php
    declare(strict_types=1);

    require_once '../php/h.php';
    require_once '../php/db/basic_db_func.php';

    /*
    *ユーザーデータを格納するデータベースを作成します。
    */

    // DB接続
    $pdo = connect_db(); 
    
    //テーブル作成 
    $sql = "CREATE TABLE IF NOT EXISTS posts"
    ." ("
    . "post_id INT AUTO_INCREMENT PRIMARY KEY,"
    . "user_id INT,"
    . "user_name TEXT,"
    . "post_text TEXT,"
    . "post_time TIMESTAMP,"
    . "post_image_id INT,"
    . "edited BOOLEAN"
    .");"; 

    $stmt = $pdo->query($sql);
    
    //テーブル内容の表示
    $result = show_db($pdo, 'posts');

?>


<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>create_posts_table</title>
</head>

<body>
    <?php foreach ($result as $row): ?>
        <?=h($row[1])?>
        <hr>
    <?php endforeach;?>
</body>
</html>