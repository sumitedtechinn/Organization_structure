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

        /**
         * Get the updated projection ids
         * And after that update projection and doc_received time stamp
         */
        $doc_received_ids = null;
        $projectionNotPresetnforDocReceivedId = [];
        if(isset($_REQUEST['doc_received'])) {
            $doc_received = $_REQUEST['doc_received'];
            unset($_REQUEST['doc_received']);
            $docReceivedAndProjection = getProjectionId($doc_received,$date);
            foreach($docReceivedAndProjection as $key=>$value) {
                if ($value != 'projection not generated') {
                    $updateDocPrepareStatus = $conn->query("UPDATE Closure_details SET doc_received = CURRENT_TIMESTAMP , projection_id = '$value' WHERE id = '$key'");        
                } else {
                    $unset_key = array_search($key,$doc_received);
                    unset($doc_received[$unset_key]);
                    $projectionNotPresetnforDocReceivedId[] = $key;
                }    
            }
            if(!empty($doc_received)) {
                $doc_received_ids = json_encode($doc_received);
            }
        }
        
        $doc_closed_ids = null;
        $projectionNotPresetnforDocCloseId = [];
        if(isset($_REQUEST['doc_close'])) {
            $doc_closed = $_REQUEST['doc_close'];
            unset($_REQUEST['doc_close']);
            $docCloseAndProjection = getProjectionId($doc_closed,$date);
            foreach ($docCloseAndProjection as $key => $value) {
                if($value != 'projection not generated') {
                    $updateDocreceivedStatus = $conn->query("UPDATE Closure_details SET doc_closed = CURRENT_TIMESTAMP , projection_id = '$value' WHERE id = '$key'");
                } else {
                    $unset_key = array_search($key,$doc_closed);
                    unset($doc_closed[$unset_key]);
                    $projectionNotPresetnforDocCloseId[] = $key;
                }
            }  
            if(!empty($doc_closed)) {
                $doc_closed_ids = json_encode($doc_closed);
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
            $year = date_format($date,'Y');

            $insert_query = "INSERT INTO `Closure_details`(`center_name`, `center_email` , `contact`, `country_code`, `projectionType`, `projection_id`, `user_id`, `doc_prepare`) VALUES ";
            $insert_query_arr = [];
            $projectionTypeName = [];
            foreach ($doc_prepare as $key=>$value) {
                $checkProjectionOnParticularProjectionType = $conn->query("SELECT ID FROM `Projection` WHERE user_id = '".$_SESSION['ID']."' AND month = '$month' AND year = '$year' AND projectionType = '".$value['projection_type']."' AND Deleted_At IS NULL");
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
        if(empty($projectionTypeName) && empty($projectionNotPresetnforDocReceivedId) && empty($projectionNotPresetnforDocCloseId)) {
           showResponse($insertDailyStatus,"inserted",'success');
        } else {
            $message = '<ul class="text-sm-left">';
            if(!empty($projectionNotPresetnforDocReceivedId)) {
                $message .= createMessage($projectionNotPresetnforDocReceivedId);
            }
            if(!empty($projectionNotPresetnforDocCloseId)) {
                $message .= createMessage($projectionNotPresetnforDocCloseId);
            }
            if(!empty($projectionTypeName)) {
                $text = implode(',',$projectionTypeName);
                $message .= "<li class='text-start fs-6'>($text) Center details not inserted as projection not assign to user for thise projection type on current month</li>";    
            }
            $message .= '</ul>';
            showResponse(false,$message,'warning');
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
    $date = date_create($report_date);
    $date = date_format($date,'Y-m-d');
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
    $projectionNotPresetnforDocReceivedId = [];
    if(isset($_REQUEST['doc_received'])) {
        $doc_received = $_REQUEST['doc_received'];
        $doc_received_id_duplicate = $doc_received;
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
            $newId_addedInDocReceived = $doc_received;
            $newId_addedInDocReceived = array_values($newId_addedInDocReceived);
            $doc_received = [];
            $previousAdded_docReceivedIds = array_diff($doc_received_id_duplicate,$newId_addedInDocReceived);
            if(!empty($newId_addedInDocReceived)) {
                $docReceivedAndProjection = getProjectionId($newId_addedInDocReceived,$date);
                foreach($docReceivedAndProjection as $key=>$value) {
                    if ($value != 'projection not generated') {
                        $updateDocPrepareStatus = $conn->query("UPDATE Closure_details SET doc_received = CURRENT_TIMESTAMP , projection_id = '$value' WHERE id = '$key'");        
                    } else {
                        $unset_key = array_search($key,$newId_addedInDocReceived);
                        unset($newId_addedInDocReceived[$unset_key]);
                        $projectionNotPresetnforDocReceivedId[] = $key;
                    }    
                }
            }
            $doc_received = array_merge($previousAdded_docReceivedIds,$newId_addedInDocReceived);
        } else {
            $docReceivedAndProjection = getProjectionId($doc_received,$date);
            foreach($docReceivedAndProjection as $key=>$value) {
                if ($value != 'projection not generated') {
                    $updateDocPrepareStatus = $conn->query("UPDATE Closure_details SET doc_received = CURRENT_TIMESTAMP , projection_id = '$value' WHERE id = '$key'");        
                } else {
                    $unset_key = array_search($key,$doc_received);
                    unset($doc_received[$unset_key]);
                    $projectionNotPresetnforDocReceivedId[] = $key;
                }    
            }
        }
        if(!empty($doc_received)) {
            $doc_received_ids = json_encode($doc_received);
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
    $projectionNotPresetnforDocCloseId = [];
    if(isset($_REQUEST['doc_close'])) {
        $doc_closed = $_REQUEST['doc_close'];
        $doc_closed_ids_duplicate = $doc_closed;
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
            $newId_addedInDocClosed = $doc_closed;
            $newId_addedInDocClosed = array_values($newId_addedInDocClosed);
            $doc_closed = [];
            $previousAdded_docClosedIds = array_diff($doc_closed_ids_duplicate,$newId_addedInDocClosed);
            if(!empty($newId_addedInDocClosed)) {
                $docCloseAndProjection = getProjectionId($newId_addedInDocClosed,$date);
                foreach ($docCloseAndProjection as $key => $value) {
                    if($value != 'projection not generated') {
                        $updateDocreceivedStatus = $conn->query("UPDATE Closure_details SET doc_closed = CURRENT_TIMESTAMP , projection_id = '$value' WHERE id = '$key'");
                    } else {
                        $unset_key = array_search($key,$newId_addedInDocClosed);
                        unset($newId_addedInDocClosed[$unset_key]);
                        $projectionNotPresetnforDocCloseId[] = $key;
                    }
                }
            }
            $doc_closed = array_merge($previousAdded_docClosedIds,$newId_addedInDocClosed);
        } else {
            $docCloseAndProjection = getProjectionId($doc_closed,$date);
            foreach ($docCloseAndProjection as $key => $value) {
                if($value != 'projection not generated') {
                    $updateDocreceivedStatus = $conn->query("UPDATE Closure_details SET doc_closed = CURRENT_TIMESTAMP , projection_id = '$value' WHERE id = '$key'");
                } else {
                    $unset_key = array_search($key,$doc_closed);
                    unset($doc_closed[$unset_key]);
                    $projectionNotPresetnforDocCloseId[] = $key;
                }
            }
        }
        if(!empty($doc_closed)) {
            $doc_closed_ids = json_encode($doc_closed);   
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

        $month = date("n",strtotime($date));
        $year = date("Y",strtotime($date));
        $insert_query = "INSERT INTO `Closure_details`(`center_name`, `center_email` , `contact`, `country_code`, `projectionType`, `projection_id`, `user_id`, `doc_prepare`) VALUES ";
        $insert_query_arr = [];
        $projectionTypeName = [];
        $timestamp = date('Y-m-d h:i:s');
        foreach ($doc_prepare as $key=>$value) {
            $checkProjectionOnParticularProjectionType = $conn->query("SELECT ID FROM `Projection` WHERE user_id = '".$_SESSION['ID']."' AND month = '$month' AND year = '$year' AND projectionType = '".$value['projection_type']."' AND Deleted_At IS NULL");
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
        }
    }
    $doc_updated_prepare_ids = array_merge($update_doc_prepare,$doc_prepare_ids);
    if(count($doc_updated_prepare_ids) > 0) {
        $doc_updated_prepare_ids = json_encode($doc_updated_prepare_ids);
    } else {
        $doc_updated_prepare_ids = '';
    }
    $updateDailyReport = $conn->query("UPDATE `daily_reporting` SET `user_id`='".$report_details['user_id']."',`total_call`='$total_call',`new_call`='$new_call',`doc_prepare`='$doc_updated_prepare_ids',`doc_received`='$doc_received_ids',`doc_close`='$doc_closed_ids',`numofmeeting` = '$numofmeeting',`date`='$report_date' WHERE id = '$report_id'");
    if(empty($projectionTypeName) && empty($projectionNotPresetnforDocReceivedId) && empty($projectionNotPresetnforDocCloseId)) {
        showResponse($updateDailyReport,'updated','success');
    } else {
        $message = '<ul class="text-sm-left">';
        if(!empty($projectionNotPresetnforDocReceivedId)) {
            $message .= createMessage($projectionNotPresetnforDocReceivedId);
        }
        if(!empty($projectionNotPresetnforDocCloseId)) {
            $message .= createMessage($projectionNotPresetnforDocCloseId);
        }
        if(!empty($projectionTypeName)) {
            $text = implode(',',$projectionTypeName);
            $message .= "<li class='text-start fs-6'>($text) Center details not inserted as projection not assign to user for thise projection type on current month</li>";    
        }
        $message .= '</ul>';
        showResponse(false,$message,'warning');
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

/**
 * Step-1 : Get the projection type id of each closure/center 
 * Step-2 : After getting the projection type search that if on this month on that projection type projection generated for user or not
 * step-2 : if genrated then pass all projection id from this closure
 */
function getProjectionId($doc_ids,$report_date) {

    global $conn; 
    $report_date = strtotime($report_date);
    $month = date("n",$report_date);
    $year = date("Y",$report_date);
    $user_id = mysqli_real_escape_string($conn,$_SESSION['ID']);
    $docIdsAndprojection = []; 
    $projection_type = $conn->query("SELECT projectionType FROM `Closure_details` WHERE id IN (".implode(',',$doc_ids).")");
    $i = 0;
    while ($id = mysqli_fetch_assoc($projection_type)) {
        $projection = $conn->query("SELECT ID FROM `Projection` WHERE projectionType = '".$id['projectionType']."' AND user_id = '$user_id' AND month = '$month' AND year = '$year' AND Deleted_At IS NULL");
        if ($projection->num_rows > 0) {
            $docIdsAndprojection[$doc_ids[$i]] = mysqli_fetch_column($projection);
        } else {
            $docIdsAndprojection[$doc_ids[$i]] = "projection not generated";
        }
        $i++;
    }
    return $docIdsAndprojection;
}

/**
 * Create messeage for those doc ids for which projection not create on this month
 */
function createMessage($doc_ids) {
    global $conn;
    $message = '';
    foreach ($doc_ids as $value) {
        $getProjectionTypeName =  $conn->query("SELECT Closure_details.center_name as `center` , Projection_type.Name as `projection_type` FROM Closure_details LEFT JOIN Projection_type ON Projection_type.ID = Closure_details.projectionType WHERE Closure_details.id = '$value'");
        $getProjectionTypeName = mysqli_fetch_assoc($getProjectionTypeName);
        $message .= "<li class='text-start fs-6'>Status not updated for <b>".$getProjectionTypeName['center']."</b>,projection not assigned on current month for the <b>".$getProjectionTypeName['projection_type']."</b> type.</li>";
    }
    return $message;
}
?>