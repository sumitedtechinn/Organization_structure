<?php

require '../../includes/db-config.php';
session_start();

if (isset($_REQUEST['id'])) {
    updateDailyReport();
} elseif (isset($_REQUEST['total_call']) && isset($_REQUEST['new_call']) && isset($_REQUEST['report_date'])) {
    insertDailyReport();
}
 
/**
 * get doc_received_id , doc_closed_id and then check doc_prepare_id 
 * For doc_prepare_id check projkection is generated for particular projection type
 */
function insertDailyReport() {

    global $conn;
    $total_call = mysqli_real_escape_string($conn,$_REQUEST['total_call']);
    $new_call = mysqli_real_escape_string($conn,$_REQUEST['new_call']);
    $report_date = mysqli_real_escape_string($conn,$_REQUEST['report_date']);
    $country_code = mysqli_real_escape_string($conn,$_REQUEST['country_code']);
    $result = explode('+', $country_code);
    $country_code_arr = array_filter($result);

    $date = date_create($report_date);
    $date = date_format($date,"Y-m-d");
    $checkDailyReport = $conn->query("SELECT * FROM `daily_reporting` WHERE user_id = '".$_SESSION['ID']."' AND date = '$date'");

    if ($checkDailyReport->num_rows == 0 ) {
        $doc_received = []; $doc_closed = []; $doc_prepare = [];
        unset($_REQUEST['total_call'],$_REQUEST['new_call'],$_REQUEST['report_date'],$_REQUEST['country_code']);
        
        $numofmeeting = null;
        if (isset($_REQUEST['numofmeeting'])) {
            $numofmeeting = mysqli_real_escape_string($conn,$_REQUEST['numofmeeting']);
            unset($_REQUEST['numofmeeting']);
        }

        $doc_received_ids = null;
        if(isset($_REQUEST['doc_received'])) {
            $doc_received = $_REQUEST['doc_received'];
            unset($_REQUEST['doc_received']);
            $updateDocPrepareStatus = $conn->query("UPDATE Closure_details SET doc_received = CURRENT_TIMESTAMP WHERE id IN (".implode(',',$doc_received).")");
            if($updateDocPrepareStatus) {
                $doc_received_ids = json_encode($doc_received);
            } else {
                showResponse(false,"Doc received status not updated",'error');
                die;
            }
        }
        
        $doc_closed_ids = null;
        if(isset($_REQUEST['doc_close'])) {
            $doc_closed = $_REQUEST['doc_close'];
            unset($_REQUEST['doc_close']);
            $updateDocreceivedStatus = $conn->query("UPDATE Closure_details SET doc_closed = CURRENT_TIMESTAMP WHERE id IN (".implode(',',$doc_closed).")");
            if($updateDocreceivedStatus) {
                $doc_closed_ids = json_encode($doc_closed);
            } else {
                showResponse(false,"Doc closed status not updated",'error');
                die;
            }
        }
        
        $doc_prepare_ids = null;
        if (!empty($_REQUEST)) {
            foreach ($_REQUEST as $centerkey => $centervalue) {
                $key_arr = explode('_',$centerkey);
                $key = $key_arr[count($key_arr)-1];
                unset($key_arr[count($key_arr)-1]);
                $bind = implode("_",$key_arr);
                $doc_prepare[$key][$bind] = $centervalue; 
            }

            $i = 1;
            foreach ($doc_prepare as $key=>$value) {
                $doc_prepare[$key]['country_code'] = '+'.$country_code_arr[$i];
                $i++;
            }

            $date = date_create($report_date);
            $month = date_format($date,"n");

            $insert_query = "INSERT INTO `Closure_details`(`center_name`, `center_email` , `contact`, `country_code`, `projectionType`, `projection_id`, `user_id`, `doc_prepare`) VALUES ";
            $insert_query_arr = [];
            $projectionTypeName = [];
            $timestamp = date('Y-m-d h:i:s');
            foreach ($doc_prepare as $key=>$value) {
                $checkProjectionOnParticularProjectionType = $conn->query("SELECT ID FROM `Projection` WHERE user_id = '".$_SESSION['ID']."' AND month = '$month' AND projectionType = '".$value['projection_type']."' AND Deleted_At IS NULL");
                if ($checkProjectionOnParticularProjectionType->num_rows > 0 ) {
                    $projection_id = mysqli_fetch_column($checkProjectionOnParticularProjectionType);
                    $insert_query_arr[] = "('".$value['center_name']."','".$value['center_email']."','".$value['contact_number']."','".$value['country_code']."','".$value['projection_type']."','$projection_id','".$_SESSION['ID']."',CURRENT_TIMESTAMP)";
                } else {
                    $projectionType = $conn->query("SELECT Name FROM `Projection_type` WHERE ID = '".$value['projection_type']."'");
                    $projectionTypeName[] = mysqli_fetch_column($projectionType);
                }
            }
            if (!empty($insert_query_arr)) {
                $insert_closure = $conn->query($insert_query . implode(',',$insert_query_arr));
                if(count($insert_query_arr) > 1) {
                    $first_inserted_id = $conn->insert_id;
                    $last_inserted_id = $first_inserted_id + count($insert_query_arr) - 1;
                    $doc_prepare_ids = json_encode(createAllInsertedIdsList($first_inserted_id,$last_inserted_id));
                } else {
                    $first_inserted_id = $conn->insert_id;
                    $doc_prepare_ids = json_encode([$first_inserted_id]);
                }
            } else {
                showResponse(false,"Projection not generated",'error');
                die;
            }
        }

        $insertDailyStatus = $conn->query("INSERT INTO `daily_reporting`(`user_id`, `total_call`, `new_call`, `doc_prepare`, `doc_received`, `doc_close`,`numofmeeting`,`date`) VALUES ('".$_SESSION['ID']."','$total_call','$new_call','$doc_prepare_ids','$doc_received_ids','$doc_closed_ids','$numofmeeting','$report_date')");
        if(empty($projectionTypeName)) {
            showResponse($insertDailyStatus,"inserted",'success');
        } else {
            $message = implode(',',$projectionTypeName);
            showResponse(false,"Daily report inserted ,($message) projection type closure doc not inserted as projection is not generated",'warning');
        }
    } else {
        showResponse(false,"Duplicate entry found",'error');
        die;
    }
}

function updateDailyReport() {

    global $conn;
    $report_id = mysqli_real_escape_string($conn,$_REQUEST['id']);
    unset($_REQUEST['id']);
    $report_details = $conn->query("SELECT * FROM `daily_reporting` WHERE id = '$report_id'");
    $report_details = mysqli_fetch_assoc($report_details);

    $total_call = mysqli_real_escape_string($conn,$_REQUEST['total_call']);
    $new_call = mysqli_real_escape_string($conn,$_REQUEST['new_call']);
    $report_date = $report_details['date'];
    $country_code_arr = [];
    if(isset($_REQUEST['country_code']) && !empty($_REQUEST['country_code'])) {
        $country_code = mysqli_real_escape_string($conn,$_REQUEST['country_code']);
        $result = explode('+', $country_code);
        $country_code_arr = array_filter($result);
        unset($_REQUEST['country_code']);
    } else {
        unset($_REQUEST['country_code']);
    }
    unset($_REQUEST['total_call'],$_REQUEST['new_call']);

    $numofmeeting = null;
    if (isset($_REQUEST['numofmeeting'])) {
        $numofmeeting = mysqli_real_escape_string($conn,$_REQUEST['numofmeeting']);
        unset($_REQUEST['numofmeeting']);
    } else {
        $numofmeeting = $report_details['numofmeeting'];
    }

    /**
     * check for doc prepare ids
     */
    $update_doc_prepare = [];
    if(isset($_REQUEST['doc_prepare'])) {
        $update_doc_prepare = $_REQUEST['doc_prepare'];
        unset($_REQUEST['doc_prepare']);
        $db_doc_prepare = json_decode($report_details['doc_prepare'],true);
        foreach($update_doc_prepare as $value) {
            if(in_array($value,$db_doc_prepare)){
                $key = array_search($value,$db_doc_prepare);
                unset($db_doc_prepare[$key]);
            }
        }
        if(!empty($db_doc_prepare)) {
            foreach($db_doc_prepare as $value) {
                $deleteCenter = $conn->query("UPDATE Closure_details SET Deleted_At = CURRENT_TIMESTAMP , doc_prepare = null , doc_received = null , doc_closed = null WHERE id = '$value'");
            }
        }
    } elseif (!isset($_REQUEST['doc_prepare']) && !empty($report_details['doc_prepare'])) {
        unset($_REQUEST['doc_prepare']);
        $db_doc_prepare = json_decode($report_details['doc_prepare'],true);
        $deleteCenter = $conn->query("UPDATE Closure_details SET Deleted_At = CURRENT_TIMESTAMP , doc_prepare = null , doc_received = null , doc_closed = null WHERE id IN (".implode(',',$db_doc_prepare).")");
    } else {
        unset($_REQUEST['doc_prepare']);
    }

    /**
     * check for doc received ids
     */
    $doc_received_ids = '';
    if(isset($_REQUEST['doc_received'])) {
        $doc_received = $_REQUEST['doc_received'];
        $doc_received_ids = json_encode($_REQUEST['doc_received']);
        unset($_REQUEST['doc_received']);
        $db_doc_received = json_decode($report_details['doc_received'],true);
        if(!empty($db_doc_received)) {
            foreach ($doc_received as $key => $value) {
                if(in_array($value,$db_doc_received)) {
                    $db_key = array_search($value,$db_doc_received);
                    unset($db_doc_received[$db_key]);
                    unset($doc_received[$key]);
                }
            }
            if(!empty($db_doc_received)) {
                foreach ($db_doc_received as $value) {
                    $updateReceivedCenterStatus = $conn->query("UPDATE Closure_details SET doc_received = null , doc_closed = null WHERE id = '$value'");
                }
            }
            if(!empty($doc_received)) {
                foreach ($doc_received as $value) {
                    $updateDocPrepareStatus = $conn->query("UPDATE Closure_details SET doc_received = CURRENT_TIMESTAMP WHERE id = '$value'");        
                }
            }
        } else {
            $updateDocPrepareStatus = $conn->query("UPDATE Closure_details SET doc_received = CURRENT_TIMESTAMP WHERE id IN (".implode(',',$doc_received).")");    
        }
    } elseif (!isset($_REQUEST['doc_received']) && !empty($report_details['doc_received'])) {
        unset($_REQUEST['doc_received']);
        $db_doc_received = json_decode($report_details['doc_received'],true);
        $updateReceivedCenterStatus = $conn->query("UPDATE Closure_details SET doc_received = null , doc_closed = null WHERE id IN (".implode(',',$db_doc_received).")");
    } else {
        unset($_REQUEST['doc_received']);
    }

    /**
     * check for doc closed ids
     */
    $doc_closed_ids = '';
    if(isset($_REQUEST['doc_close'])) {
        $doc_closed = $_REQUEST['doc_close'];
        $doc_closed_ids = json_encode($_REQUEST['doc_close']);
        unset($_REQUEST['doc_close']);
        $db_doc_closed = json_decode($report_details['doc_close'],true);
        if(!empty($db_doc_closed)) {
            foreach ($doc_closed as $key => $value) {
                if(in_array($value,$db_doc_closed)) {
                    $db_key = array_search($value,$db_doc_closed);
                    unset($db_doc_closed[$db_key]);
                    unset($doc_closed[$key]);
                }
            }
            if(!empty($db_doc_closed)) {
                foreach ($db_doc_closed as $value) {
                    $updateClosedCenterStatus = $conn->query("UPDATE Closure_details SET doc_closed = null WHERE id = '$value'");
                }
            }
            if(!empty($doc_closed)) {
                foreach ($doc_closed as $value) {
                    $updateDocReceivedStatus = $conn->query("UPDATE Closure_details SET doc_closed = CURRENT_TIMESTAMP WHERE id = '$value'");        
                }
            }
        } else {
            $updateDocReceivedStatus = $conn->query("UPDATE Closure_details SET doc_closed = CURRENT_TIMESTAMP WHERE id IN (".implode(',',$doc_closed).")");    
        }
    } elseif(!isset($_REQUEST['doc_close']) && !empty($report_details['doc_close'])) {
        unset($_REQUEST['doc_close']);
        $db_doc_closed = json_decode($report_details['doc_close'],true);
        $updateClosedCenterStatus = $conn->query("UPDATE Closure_details SET doc_closed = null WHERE id IN (".implode(',',$db_doc_closed).")");
    } else {
        unset($_REQUEST['doc_close']);
    }

    /**
     * Check for new doc_prepare
     */
    $doc_prepare_ids = [];
    if (!empty($_REQUEST)) {
        foreach ($_REQUEST as $centerkey => $centervalue) {
            $key_arr = explode('_',$centerkey);
            $key = $key_arr[count($key_arr)-1];
            unset($key_arr[count($key_arr)-1]);
            $bind = implode("_",$key_arr);
            $doc_prepare[$key][$bind] = $centervalue; 
        }

        $i = 1;
        foreach ($doc_prepare as $key=>$value) {
            $doc_prepare[$key]['country_code'] = '+'.$country_code_arr[$i];
            $i++;
        }

        $date = date_create($report_date);
        $month = date_format($date,"n");

        $insert_query = "INSERT INTO `Closure_details`(`center_name`, `center_email` , `contact`, `country_code`, `projectionType`, `projection_id`, `user_id`, `doc_prepare`) VALUES ";
        $insert_query_arr = [];
        $projectionTypeName = [];
        $timestamp = date('Y-m-d h:i:s');
        foreach ($doc_prepare as $key=>$value) {
            $checkProjectionOnParticularProjectionType = $conn->query("SELECT ID FROM `Projection` WHERE user_id = '".$_SESSION['ID']."' AND month = '$month' AND projectionType = '".$value['projection_type']."' AND Deleted_At IS NULL");
            if ($checkProjectionOnParticularProjectionType->num_rows > 0 ) {
                $projection_id = mysqli_fetch_column($checkProjectionOnParticularProjectionType);
                $insert_query_arr[] = "('".$value['center_name']."','".$value['center_email']."','".$value['contact_number']."','".$value['country_code']."','".$value['projection_type']."','$projection_id','".$_SESSION['ID']."',CURRENT_TIMESTAMP)";
            } else {
                $projectionType = $conn->query("SELECT Name FROM `Projection_type` WHERE ID = '".$value['projection_type']."'");
                $projectionTypeName[] = mysqli_fetch_column($projectionType);
            }
        }
        if (!empty($insert_query_arr)) {
            $insert_closure = $conn->query($insert_query . implode(',',$insert_query_arr));
            if(count($insert_query_arr) > 1) {
                $first_inserted_id = $conn->insert_id;
                $last_inserted_id = $first_inserted_id + count($insert_query_arr) - 1;
                $doc_prepare_ids = createAllInsertedIdsList($first_inserted_id,$last_inserted_id);
            } else {
                $first_inserted_id = $conn->insert_id;
                $doc_prepare_ids = [$first_inserted_id];
            }
        } else {
            showResponse(false,"Projection not generated for Projection type (".implode(',',$projectionTypeName).")",'error');
            die;
        }
    }
    $doc_updated_prepare_ids = json_encode(array_merge($update_doc_prepare,$doc_prepare_ids));

    $updateDailyReport = $conn->query("UPDATE `daily_reporting` SET `user_id`='".$report_details['user_id']."',`total_call`='$total_call',`new_call`='$new_call',`doc_prepare`='$doc_updated_prepare_ids',`doc_received`='$doc_received_ids',`doc_close`='$doc_closed_ids',`numofmeeting` = '$numofmeeting',`date`='$report_date' WHERE id = '$report_id'");
    if(empty($projectionTypeName)) {
        showResponse($updateDailyReport,'updated','success');
    } else {
        $message = implode(',',$projectionTypeName);
        showResponse(false,"Daily report updated , But center of projection type ($message) not inserted as projections not assign",'warning');
    }
}

function createAllInsertedIdsList($first,$last) {

    $inserted_ids = [];
    $inserted_ids[] = $first;
    $first++;
    while($first < $last) {
        $inserted_ids[] = $first;
        $first++;
    }
    $inserted_ids[] = $last;
    return $inserted_ids;
}

function showResponse($response, $message = "Something went wrong!",$type) {
    if ($response) {
        echo json_encode(['status' => 200, 'message' => "Daily Report $message successfully!", 'type' => $type]);
    } else {
        echo json_encode(['status' => 400, 'message' => $message , 'type' => $type]);
    }
}

?>