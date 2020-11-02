<?php

function calcScore($bid, $wins){
    if ($wins >= $bid){
        return $bid*10 + $wins - $bid;
    }
    else{
        return 0;
    }
}