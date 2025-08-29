<?php
declare(strict_types=1);

/*
* ランダム生成に関する関数をまとめたファイル
*/


function generateToken($num)
{
    $bytes = random_bytes($num);
    return bin2hex($bytes);
}