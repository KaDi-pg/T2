<?php
declare(strict_types=1);


/*
* 基本的なDBの処理を行う関数を定義します。
*/

function connect_db()
{
    //データベースへの接続をします
    // githubように伏せ字済み
    $dsn = 'mysql:dbname=***;host=localhost';
    $user = '***';
    $password = '***';
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

    return $pdo;
}

function show_db($pdo, $table_name)
{
    //データベース内のテーブル内容を表示します
    $sql ='SHOW CREATE TABLE '.$table_name;
    $result = $pdo -> query($sql);

    return $result;
}

function get_max($pdo, $table_name, $column):int
{
    //データベース内の特定のテーブル、カラムの最大値を取得する
    //テーブル名、カラム名は、必ずこちらで指定すること
    $sql = 'SELECT max('.$column.') as max_num FROM '.$table_name;
    $stmt = $pdo -> query($sql);
    $res = $stmt->fetchAll();

    if (is_numeric($res[0]["max_num"])){
        return (int)$res[0]["max_num"];
    } else {
        return -1;
    }
}

function is_in_data_int($pdo, $table_name, $column, $num)
{
    //データベース内の特定のテーブル、カラムに、該当する値が1つ以上存在するかどうかを確認する
    //$numは、ユーザーが入れるデータが入りうるため、SQL対策したものを必ず使用する
    if(!is_numeric($num)){
        return false;
    }else{
        $sql = 'SELECT COUNT(*) FROM '.$table_name.' WHERE '.$column.'=:num';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':num', $num, PDO::PARAM_INT);
        $stmt->execute();
        
        $num_of_data = $stmt->fetchAll();
        if((int)$num_of_data[0][0] != 0){
            //該当するコメントが1件でもあれば、存在を報告
            return true;
        }else{
            return false;
        }
    }
}

function is_in_data_str($pdo, $table_name, $column, $str)
{
    //データベース内の特定のテーブル、カラムに、該当する文字列が1つ以上存在するかどうかを確認する
    //$strは、ユーザーが入れるデータが入りうるため、SQL対策したものを必ず使用する

    $sql = 'SELECT COUNT(*) FROM '.$table_name.' WHERE '.$column.'=:str';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':str', $str, PDO::PARAM_STR);
    $stmt->execute();
    
    $num_of_data = $stmt->fetchAll();
    if((int)$num_of_data[0][0] != 0){
        //該当するコメントが1件でもあれば、存在を報告
        return true;
    }else{
        return false;
    }
}

function match_pass($pdo, $table_name, $id, $pass)
{
    //データベース内の特定のテーブル、に保管されたハッシュ化されたパスワードが、入力されたパスワードと一致するか確認
    //カラムは、IDはid,　パスワードはpass
    //$id, $passは、SQL対策必須
    $sql = 'SELECT pass FROM '.$table_name.' WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $pass_arr = $stmt->fetchAll();
    $hash_pass = $pass_arr[0]['pass'];

    return password_verify($pass, $hash_pass);
}
