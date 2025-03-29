<?php

session_start();
$session_arr = [];

if(isset($_REQUEST['sessionRequiredKey'])) {
    $sessionRequiredKey = $_REQUEST['sessionRequiredKey'];
    foreach ($_SESSION as $key => $value) {
        if(in_array($key,$sessionRequiredKey)) {
            $session_arr[$key] = $value;
        }
    }
} else {
    $session_arr = $_SESSION;
}

echo json_encode($session_arr);
?>