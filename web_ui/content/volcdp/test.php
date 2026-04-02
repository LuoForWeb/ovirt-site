<?php 
    session_start();
    $user_name = "111";
    $user_level = 1;
    $_SESSION['user_name'] = $user_name;
    $_SESSION['user_level'] = $user_level;
    $userlist = array(
        "user_level"=> $_SESSION['user_level'],
        "user_name"=> $_SESSION['user_name'],
    );
    echo json_encode($userlist);
?>