<?php
function test_foreach(array $arr){
    $sum = 0;
    foreach($arr as $x){
        $sum += $x;
    }
    return $sum;
}

function test_array_reduce(array $arr){
    return array_reduce($arr, "add", 0);
}
