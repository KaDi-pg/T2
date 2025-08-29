<?php
declare(strict_types=1);

//XSS対策の関数
function h($str){
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}