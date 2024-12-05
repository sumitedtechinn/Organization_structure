<?php

function session_error_function() {
  header("Location: /");
}

set_error_handler('session_error_function');
session_start();
if(!isset($_SESSION['Name'])){
  header("Location: /");
}
restore_error_handler();
date_default_timezone_set('Asia/Kolkata'); 
header('Content-Type: text/html; charset=utf-8');

include ($_SERVER['DOCUMENT_ROOT'].'/includes/db-config.php');

if(!empty($_SESSION['current_url'])) {
  if ($_SERVER['SCRIPT_NAME'] != '/app/common/unauthorized.php') {
    $_SESSION['previous_url'] = $_SESSION['current_url'];
    $_SESSION['current_url'] = $_SERVER['SCRIPT_NAME'];
  }
} else {
  $_SESSION['current_url'] = $_SERVER['SCRIPT_NAME'];
}

$url_arr = array_filter(explode('/',$_SERVER['SCRIPT_NAME']));
$url_page = explode('.',$url_arr[count($url_arr)])[0];
if (stripos($url_page,'_')) {
    $arr = explode("_",$url_page);
    for($i=0 ; $i < count($arr) ; $i++) {
        $word = ucfirst($arr[$i]);
        $arr[$i] = $word;
    }
    $url_page = implode(" ",$arr);
} else {
    $url_page = ucfirst($url_page);
}

?>
<!doctype html>
<html lang="en" class="semi-dark">
<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="/assets/images/favicon-32x32.jpg" type="image/jpg"/>
  <!--plugins-->
  <link href="/assets/plugins/vectormap/jquery-jvectormap-2.0.2.css" rel="stylesheet"/>
  <link href="/assets/plugins/simplebar/css/simplebar.css" rel="stylesheet" />
  <link href="/assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css" rel="stylesheet" />
  <link href="/assets/plugins/metismenu/css/metisMenu.min.css" rel="stylesheet" />
  <link href="/assets/plugins/select2/css/select2.min.css" rel="stylesheet" />
	<link href="/assets/plugins/select2/css/select2-bootstrap4.css" rel="stylesheet" />
  <link href="/assets/plugins/datatable/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet"/>
  <!-- Bootstrap CSS -->
  <link href="/assets/css/bootstrap.min.css" rel="stylesheet" />
  <link href="/assets/css/bootstrap-extended.css" rel="stylesheet" />
  <link href="/assets/css/style.css" rel="stylesheet" />
  <link href="/assets/css/icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/cdn.jsdelivr.net/npm/bootstrap-icons%401.5.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
  <!-- loader-->
	<link href="/assets/css/pace.min.css" rel="stylesheet" />

  <!--Theme Styles-->
  <link href="/assets/css/dark-theme.css" rel="stylesheet" />
  <link href="/assets/css/light-theme.css" rel="stylesheet" />
  <link href="/assets/css/semi-dark.css" rel="stylesheet" />
  <link href="/assets/css/header-colors.css" rel="stylesheet" />

  <title> <?=$url_page?>  | <?=$app_title?></title>