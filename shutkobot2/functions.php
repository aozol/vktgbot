<?php

function get_avg_med($finishId)
{
    
    GLOBAL $dblink;
    
    $sql=mysqli_query($dblink,"SELECT vote FROM `".DBP."votes` WHERE finishId={$finishId} ORDER BY vote ASC");
    
    if(list($v)=mysqli_fetch_array($sql))
    {
        $vote[0]=$v;
        $sum=$v;
        $n=1;
    }
    
    else
    {
        $avg=0;
        $med=0;
    }
    
    while (list($v)=mysqli_fetch_array($sql))
    {
        $vote[$n]=$v;
        $sum+=$v;
        $n++;
        
    }


    
    if ($data['n'] % 2 ==0)
        $med=($vote[$n/2-1]+$vote[$n/2])/2;
    
    else
        $med=$vote[$n/2-1/2];
    
    $avg=$sum/$n;
    
    mysqli_query($dblink,"UPDATE `".DBP."finishes` SET avgVote={$avg},medVote={$med} WHERE finishId={$finishId}");
    
    return array($avg,$med);
    
}

?>
