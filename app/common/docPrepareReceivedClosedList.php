<?php

require '../../includes/db-config.php';
session_start();

if(isset($_REQUEST['typeOfList'])) {
    
    $typeOfList = mysqli_real_escape_string($conn,$_REQUEST['typeOfList']);
    if ($typeOfList == 'doc-prepare') {
        $centerList = getListOfAllDocPrepareCenter();
    } elseif ($typeOfList == 'doc-received') {
        $centerList = getListOfAllDocReceived();
    } elseif ($typeOfList == 'today-doc-prepare') {
        $centerList = getTodayListOfAllDocPrepare();
    }
    echo $centerList;
}

function getListOfAllDocPrepareCenter() : string {
    global $conn;
    $option = '<option value="">select center</option>';
    $db_doc_received_list = '';
    if(isset($_REQUEST['doc_received_list']) && !empty($_REQUEST['doc_received_list'])) {
        $db_doc_received_list = $_REQUEST['doc_received_list'];
        $db_doc_received_list = json_decode($db_doc_received_list,true);
        $docPrepareList = $conn->query("SELECT id , center_name FROM `Closure_details` WHERE id IN (".implode(',',$db_doc_received_list).")");
        while($row = mysqli_fetch_assoc($docPrepareList)) {
            $option .= '<option value = "'.$row['id'].'" selected >'.$row['center_name'].'</option>';
        }   
    }
    
    $user_id = !empty($_REQUEST['user_id']) ?  mysqli_real_escape_string($conn,$_REQUEST['user_id']) : mysqli_real_escape_string($conn,$_SESSION['ID']);
    $docPrepareList = $conn->query("SELECT id , center_name FROM `Closure_details` WHERE user_id = '$user_id' and doc_received IS NULL AND doc_closed IS NULL AND Deleted_At IS NULL");
    if($docPrepareList->num_rows > 0) {
        while($row = mysqli_fetch_assoc($docPrepareList)) {
            $option .= '<option value = "'.$row['id'].'">'.$row['center_name'].'</option>';
        }
    }
    return $option;
}

function getListOfAllDocReceived() : string {
    global $conn;
    $db_doc_closed_list = '';
    $option = '<option value="">select center</option>';
    if(isset($_REQUEST['doc_closed_list']) && !empty($_REQUEST['doc_closed_list'])) {
        $db_doc_closed_list = $_REQUEST['doc_closed_list'];
        $db_doc_closed_list = json_decode($db_doc_closed_list,true);
        $docPrepareList = $conn->query("SELECT id , center_name FROM `Closure_details` WHERE id IN (".implode(',',$db_doc_closed_list).")");
        while($row = mysqli_fetch_assoc($docPrepareList)) {
            $option .= '<option value = "'.$row['id'].'" selected >'.$row['center_name'].'</option>';
        }   
    }
    $user_id = !empty($_REQUEST['user_id']) ?  mysqli_real_escape_string($conn,$_REQUEST['user_id']) : mysqli_real_escape_string($conn,$_SESSION['ID']);
    $docClosed = $conn->query("SELECT id , center_name FROM `Closure_details` WHERE user_id = '$user_id' and doc_received IS NOT NULL AND doc_closed IS NULL AND Deleted_At IS NULL");
    if($docClosed->num_rows > 0) {
        while($row = mysqli_fetch_assoc($docClosed)) {
            $option .= '<option value="'.$row['id'].'">'.$row['center_name'].'</option>';
        }
    }
    return $option;
}

function getTodayListOfAllDocPrepare() {
    global $conn;
    $db_doc_prepare_list = '';
    $option = '<option value="">select center</option>';
    if(isset($_REQUEST['doc_prepare_list']) && !empty($_REQUEST['doc_prepare_list'])) {
        $db_doc_prepare_list = $_REQUEST['doc_prepare_list'];
        $db_doc_prepare_list = json_decode($db_doc_prepare_list,true);
        $docPrepareList = $conn->query("SELECT id , center_name FROM `Closure_details` WHERE id IN (".implode(',',$db_doc_prepare_list).")");
        while($row = mysqli_fetch_assoc($docPrepareList)) {
            $option .= '<option value = "'.$row['id'].'" selected >'.$row['center_name'].'</option>';
        }   
    }
    return $option;
}

?>