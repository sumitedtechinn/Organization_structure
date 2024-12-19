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
        
        /**
         * make numofmeeting data in json formate
         */
        $numofmeeting = null;
        if (isset($_REQUEST['numofmeeting'])) {
            if (!empty($_REQUEST['numofmeeting'])) {
                $numofmeeting = mysqli_real_escape_string($conn,$_REQUEST['numofmeeting']);
                $numofmeeting_arr = [];
                $count = 1;
                while($count <= $numofmeeting) {
                    $numofmeeting_arr[] = $_REQUEST["client_$count"];
                    unset($_REQUEST["client_$count"]);
                    $count++;
                }
                unset($_REQUEST['numofmeeting']);
                $numofmeeting = json_encode($numofmeeting_arr);
            } else {
                unset($_REQUEST['numofmeeting']);
            }
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
        
        /**
         * Get the update proectionId
         * And after that update projection and deal_close time stamp
         * And insert the center one time submitted amount
         */
        $doc_closed_ids = null;
        $projectionNotPresetnforDocCloseId = [];
        if(isset($_REQUEST['doc_close'])) {
            $doc_closed = $_REQUEST['doc_close'];
            unset($_REQUEST['doc_close']);
            $docCloseAndProjection = getProjectionId($doc_closed,$date);
            foreach ($docCloseAndProjection as $key => $value) {
                if($value != 'projection not generated') {
                    $amount = mysqli_real_escape_string($conn,$_REQUEST["center_create_amount_$key"]);
                    unset($_REQUEST["center_create_amount_$key"]);
                    $updateDocreceivedStatus = $conn->query("UPDATE Closure_details SET doc_closed = CURRENT_TIMESTAMP , amount = '$amount' , projection_id = '$value' WHERE id = '$key'");
                } else {
                    $unset_key = array_search($key,$doc_closed);
                    unset($doc_closed[$unset_key]);
                    unset($_REQUEST["center_create_amount_$key"]);
                    $projectionNotPresetnforDocCloseId[] = $key;
                }
            }  
            if(!empty($doc_closed)) {
                $doc_closed_ids = json_encode($doc_closed);
            } 
        }

        /**
        * 1) Check for center deposit data
        * 2) IF data present then store in the center_deposit table 
        * 3) Store the id 
        * 4) Concat the deposite amount in the closure_details table deposite amount column
        */

        $str = 'deposit_center';
        $center_deposit_ids = null;
        $deposite_center_details = [];
        $b = 0;
        foreach ($_REQUEST as $key => $value) {
            if (strpos($key, $str) !== false) {
                $admkeycount = (explode('_',$key))[2];
                $deposite_center_details[$b]['deposit_center'] = $_REQUEST['deposit_center_'.$admkeycount];
                $deposite_center_details[$b]['deposit_amount'] = $_REQUEST['deposit_amount_'.$admkeycount];
                unset($_REQUEST["deposit_center_".$admkeycount],$_REQUEST["deposit_amount_".$admkeycount]);
                $b++;
            }
        }

        if(!empty($deposite_center_details)) {
            $insert_deposit = "INSERT INTO `center_deposite`(`user_id`, `center_id`, `deposit_amount`) VALUES";
            $insert_deposit_arr = [];
            foreach ($deposite_center_details as $value) {
                $insert_deposit_arr[] = "('".$_SESSION['ID']."','".$value['deposit_center']."','".$value['deposit_amount']."')";
                $getAlreadyDepositAmount = $conn->query("SELECT deposit_amount FROM `Closure_details` WHERE id = '".$value['deposit_center']."'");
                $getAlreadyDepositAmount = mysqli_fetch_column($getAlreadyDepositAmount);
                $concatDepositAmount = intval($value['deposit_amount']);
                if(!is_null($getAlreadyDepositAmount)) {
                    $concatDepositAmount += $getAlreadyDepositAmount; 
                }
                $updatedepositAmount = $conn->query("UPDATE Closure_details SET deposit_amount = '$concatDepositAmount' WHERE id = '".$value['deposit_center']."'");
            }
            if(!empty($insert_deposit_arr)) {
                $insert_deposit_query = $conn->query($insert_deposit . implode(',',$insert_deposit_arr));
                if(count($insert_deposit_arr) > 1) {
                    $first_inserted_id = $conn->insert_id;
                    $last_inserted_id = $first_inserted_id + count($insert_deposit_arr) - 1;
                    $center_deposit_ids = json_encode(createAllInsertedIdsList($first_inserted_id,$last_inserted_id));
                } else {
                    $first_inserted_id = $conn->insert_id;
                    $center_deposit_ids = json_encode([$first_inserted_id]);
                }
            }
        }

        /**
         * 1) make an array for center admission
         * 2)  Once the center admission array make then insert the details in admission table 
        */
 
        $word = 'admission_center';
        $admission_insert_ids = null;
        $projectionNotPresentForAdmission = [];
        $admission_details = [];
        $a = 0;
        foreach ($_REQUEST as $key => $value) {
            if (strpos($key, $word) !== false) {
                $admkeycount = (explode('_',$key))[2];
                $admission_details[$a]['adm_centerId'] = $_REQUEST["admission_center_".$admkeycount];
                $admission_details[$a]['adm_projectionType'] = $_REQUEST["admission_projection_type_".$admkeycount];
                $admission_details[$a]['numofadmission'] = $_REQUEST["numOfAdmission_".$admkeycount];
                $admission_details[$a]['adm_amount'] = $_REQUEST["admission_amount_".$admkeycount];
                unset($_REQUEST["admission_center_".$admkeycount],$_REQUEST["admission_projection_type_".$admkeycount],$_REQUEST["numOfAdmission_".$admkeycount],$_REQUEST["admission_amount_".$admkeycount]);
                $a++;
            }
        }

        // If admission_details array is not empty then any action will perform
        if(!empty($admission_details)) {
            $insert_admission = "INSERT INTO `admission_details`(`user_id`,`projectionType`,`projection_id`, `admission_by`,`numofadmission`,`amount`,`deposit_amount`) VALUES";
            $insert_admission_arr = [];
            foreach ($admission_details as $key => $value) {
                $projection_id = checkProjectionAssing($value['adm_projectionType'],$date);
                if($projection_id) {
                    list($received_amount,$deposit_amount) = checkAndUpdateCenterDeposit($value['adm_centerId'],$value['adm_amount']);
                    $insert_admission_arr[] = "('".$_SESSION['ID']."','".$value['adm_projectionType']."','$projection_id','".$value['adm_centerId']."','".$value['numofadmission']."','$received_amount','$deposit_amount')"; 
                } else {
                    if(!in_array($value['adm_projectionType'],$projectionNotPresentForAdmission)) {
                        $projectionNotPresentForAdmission[] = $value['adm_projectionType'];
                    }
                    unset($admission_details[$key]); 
                }
            }
            if(!empty($insert_admission_arr)) {
                $insert_admission_query = $conn->query($insert_admission . implode(',',$insert_admission_arr));
                if(count($insert_admission_arr) > 1) {
                    $first_inserted_id = $conn->insert_id;
                    $last_inserted_id = $first_inserted_id + count($insert_admission_arr) - 1;
                    $admission_insert_ids = json_encode(createAllInsertedIdsList($first_inserted_id,$last_inserted_id));
                } else {
                    $first_inserted_id = $conn->insert_id;
                    $admission_insert_ids = json_encode([$first_inserted_id]);
                }
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

        $insertDailyStatus = $conn->query("INSERT INTO `daily_reporting`(`user_id`, `total_call`, `new_call`,`admission_ids`,`doc_prepare`, `doc_received`, `doc_close`,`center_deposit_id`,`numofmeeting`,`date`) VALUES ('".$_SESSION['ID']."','$total_call','$new_call','$admission_insert_ids','$doc_prepare_ids','$doc_received_ids','$doc_closed_ids','$center_deposit_ids','$numofmeeting','$report_date')");
        if(empty($projectionTypeName) && empty($projectionNotPresetnforDocReceivedId) && empty($projectionNotPresetnforDocCloseId) && empty($projectionNotPresentForAdmission)) {
           showResponse($insertDailyStatus,"inserted",'success');
        } else {
            $message = '<ul class="text-sm-left">';
            if(!empty($projectionNotPresentForAdmission)) {
                $message .= createMessageForAdmission($projectionNotPresentForAdmission);
            }
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

    /**
     * make numofmeeting data in json formate
     */
    $numofmeeting = null;
    if (isset($_REQUEST['numofmeeting'])) {
        $numofmeeting = mysqli_real_escape_string($conn,$_REQUEST['numofmeeting']);
        $numofmeeting_arr = [];
        $count = 1;
        while($count <= $numofmeeting) {
            $numofmeeting_arr[] = $_REQUEST["client_$count"];
            unset($_REQUEST["client_$count"]);
            $count++;
        }
        unset($_REQUEST['numofmeeting']);
        $numofmeeting = json_encode($numofmeeting_arr);
    }

    /**
    * 1) Check for center deposit data
    * 2) IF data present then store in the center_deposit table 
    * 3) Store the id 
    * 4) Concat the deposite amount in the closure_details table deposite amount column
    */

    $str = 'deposit_center';
    $center_deposit_ids = null;
    $deposite_center_details = [];
    $b = 0;
    foreach ($_REQUEST as $key => $value) {
        if (strpos($key, $str) !== false) {
            $admkeycount = (explode('_',$key))[2];
            $deposite_center_details[$b]['deposit_center'] = $_REQUEST['deposit_center_'.$admkeycount];
            $deposite_center_details[$b]['deposit_amount'] = $_REQUEST['deposit_amount_'.$admkeycount];
            unset($_REQUEST["deposit_center_".$admkeycount],$_REQUEST["deposit_amount_".$admkeycount]);
            $b++;
        }
    }

    if(!empty($deposite_center_details)) {
        $insert_deposit = "INSERT INTO `center_deposite`(`user_id`, `center_id`, `deposit_amount`) VALUES";
        $insert_deposit_arr = [];
        foreach ($deposite_center_details as $value) {
            $user_id = $_SESSION['role'] == '2' ? $_SESSION['ID'] : $report_details['user_id'];
            $insert_deposit_arr[] = "('$user_id','".$value['deposit_center']."','".$value['deposit_amount']."')";
            $getAlreadyDepositAmount = $conn->query("SELECT deposit_amount FROM `Closure_details` WHERE id = '".$value['deposit_center']."'");
            $getAlreadyDepositAmount = mysqli_fetch_column($getAlreadyDepositAmount);
            $concatDepositAmount = intval($value['deposit_amount']);
            if(!is_null($getAlreadyDepositAmount)) {
                $concatDepositAmount += $getAlreadyDepositAmount; 
            }
            $updatedepositAmount = $conn->query("UPDATE Closure_details SET deposit_amount = '$concatDepositAmount' WHERE id = '".$value['deposit_center']."'");
        }
        if(!empty($insert_deposit_arr)) {
            $insert_deposit_query = $conn->query($insert_deposit . implode(',',$insert_deposit_arr));
            if(count($insert_deposit_arr) > 1) {
                $first_inserted_id = $conn->insert_id;
                $last_inserted_id = $first_inserted_id + count($insert_deposit_arr) - 1;
                $deposit_insert_ids = createAllInsertedIdsList($first_inserted_id,$last_inserted_id);
                if(!empty($report_details['center_deposit_id'])) {
                    $center_deposit_ids = array_merge(json_decode($report_details['center_deposit_id'],true),$deposit_insert_ids);
                    $center_deposit_ids = json_encode($center_deposit_ids);
                } else {
                    $center_deposit_ids = json_encode($deposit_insert_ids);
                }
            } else {
                $first_inserted_id = $conn->insert_id;
                $deposit_insert_ids = [$first_inserted_id];
                if(!empty($report_details['center_deposit_id'])) {
                    $center_deposit_ids = array_merge(json_decode($report_details['center_deposit_id'],true),$deposit_insert_ids);
                    $center_deposit_ids = json_encode($center_deposit_ids);
                } else {
                    $center_deposit_ids = json_encode($deposit_insert_ids);
                }
            }
        } else {
            $center_deposit_ids = $report_details['center_deposit_id'];
        }
    } else {
        $center_deposit_ids = $report_details['center_deposit_id'];
    }   

    /**
     * 1) make an array for center admission
     * 2)  Once the center admission array make then insert the details in admission table 
    */

    $word = 'admission_center';
    $admission_insert_ids = null;
    $projectionNotPresentForAdmission = [];
    $admission_details = [];
    $a = 0;
    foreach ($_REQUEST as $key => $value) {
        if (strpos($key, $word) !== false) {
            $admkeycount = (explode('_',$key))[2];
            $admission_details[$a]['adm_centerId'] = $_REQUEST["admission_center_".$admkeycount];
            $admission_details[$a]['adm_projectionType'] = $_REQUEST["admission_projection_type_".$admkeycount];
            $admission_details[$a]['numofadmission'] = $_REQUEST["numOfAdmission_".$admkeycount];
            $admission_details[$a]['adm_amount'] = $_REQUEST["admission_amount_".$admkeycount];
            unset($_REQUEST["admission_center_".$admkeycount],$_REQUEST["admission_projection_type_".$admkeycount],$_REQUEST["numOfAdmission_".$admkeycount],$_REQUEST["admission_amount_".$admkeycount]);
            $a++;
        }
    }

    // If admission_details array is not empty then any action will perform
    if(!empty($admission_details)) {
        $insert_admission = "INSERT INTO `admission_details`(`user_id`,`projectionType`,`projection_id`, `admission_by`,`numofadmission`,`amount`,`deposit_amount`) VALUES";
        $insert_admission_arr = [];
        foreach ($admission_details as $key => $value) {
            $projection_id = checkProjectionAssing($value['adm_projectionType'],$date,$report_details['user_id']);
            if($projection_id) { 
                list($received_amount,$deposit_amount) = checkAndUpdateCenterDeposit($value['adm_centerId'],$value['adm_amount']);
                $user_id = $_SESSION['role'] == '2' ? $_SESSION['ID'] : $report_details['user_id'];  
                $insert_admission_arr[] = "('$user_id','".$value['adm_projectionType']."','$projection_id','".$value['adm_centerId']."','".$value['numofadmission']."','$received_amount','$deposit_amount')"; 
            } else {
                if(!in_array($value['adm_projectionType'],$projectionNotPresentForAdmission)) {
                    $projectionNotPresentForAdmission[] = $value['adm_projectionType'];
                }
                unset($admission_details[$key]); 
            }
        }
        if(!empty($insert_admission_arr)) {
            $insert_admission_query = $conn->query($insert_admission . implode(',',$insert_admission_arr));
            if(count($insert_admission_arr) > 1) {
                $first_inserted_id = $conn->insert_id;
                $last_inserted_id = $first_inserted_id + count($insert_admission_arr) - 1;
                $insert_ids = createAllInsertedIdsList($first_inserted_id,$last_inserted_id);
                if(!empty($report_details['admission_ids'])) {
                    $admission_insert_ids = array_merge(json_decode($report_details['admission_ids'],true),$insert_ids);
                    $admission_insert_ids = json_encode($admission_insert_ids);
                } else {
                    $admission_insert_ids = json_encode($insert_ids);
                }
            } else {
                $first_inserted_id = $conn->insert_id;
                $insert_ids = [$first_inserted_id];
                if(!empty($report_details['admission_ids'])) {
                    $admission_insert_ids = array_merge(json_decode($report_details['admission_ids'],true),$insert_ids);
                    $admission_insert_ids = json_encode($admission_insert_ids);
                } else {
                    $admission_insert_ids = json_encode($insert_ids);
                }
            }
        } else {
            $admission_insert_ids = $report_details['admission_ids'];    
        }
    } else {
        $admission_insert_ids = $report_details['admission_ids'];
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
        // 1st update amount deal close amount
        $word = 'center_create_amount';
        foreach ($_REQUEST as $key => $value) {
            if (strpos($key, $word) !== false) {
                $id = (explode('_',$key))[3];
                $update = $conn->query("UPDATE Closure_details SET  amount = '$value' WHERE id = '$id'");
                unset($_REQUEST['center_create_amount_'.$id]);
            }
        }

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
                    $amount = mysqli_real_escape_string($conn,$_REQUEST["center_create_amount_$key"]);
                    unset($_REQUEST["center_create_amount_$key"]);
                    $updateDocreceivedStatus = $conn->query("UPDATE Closure_details SET doc_closed = CURRENT_TIMESTAMP , amount = '$amount' , projection_id = '$value' WHERE id = '$key'");
                } else {
                    $unset_key = array_search($key,$doc_closed);
                    unset($doc_closed[$unset_key]);
                    unset($_REQUEST["center_create_amount_$key"]);
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
        $updateClosedCenterStatus = $conn->query("UPDATE Closure_details SET doc_closed = null , amount = null WHERE id IN (".implode(',',$db_doc_closed).")");
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
    $updateDailyReport = $conn->query("UPDATE `daily_reporting` SET `user_id`='".$report_details['user_id']."',`total_call`='$total_call',`new_call`='$new_call',`admission_ids` = '$admission_insert_ids',`doc_prepare`='$doc_updated_prepare_ids',`center_deposit_id` = '$center_deposit_ids' ,`doc_received`='$doc_received_ids',`doc_close`='$doc_closed_ids',`numofmeeting` = '$numofmeeting',`date`='$report_date' WHERE id = '$report_id'");
    if(empty($projectionTypeName) && empty($projectionNotPresetnforDocReceivedId) && empty($projectionNotPresetnforDocCloseId) && empty($projectionNotPresentForAdmission)) {
        showResponse($updateDailyReport,'updated','success');
    } else {
        $message = '<ul class="text-sm-left">';
        if(!empty($projectionNotPresentForAdmission)) {
            $message .= createMessageForAdmission($projectionNotPresentForAdmission);
        }
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

// check for given projection type projection assing to user for this month or not 
function checkProjectionAssing($projection_type_id,$report_date,$user_id=null) {

    global $conn;

    $report_date = strtotime($report_date);
    $month = date("n",$report_date);
    $year = date("Y",$report_date);
    if($_SESSION['role'] == '2') {
        $user_id = mysqli_real_escape_string($conn,$_SESSION['ID']);
    }
    $projection = $conn->query("SELECT ID FROM `Projection` WHERE month = '$month' AND year = '$year' AND projectionType = '$projection_type_id' AND user_id = '$user_id'");
    if($projection->num_rows > 0) {
        $projection_id = mysqli_fetch_column($projection);
        return $projection_id;
    } else {
        return false;
    }
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

function createMessageForAdmission($projectionType_arr) : string {
    global $conn;
    $message = '';
    $projectionType_name = $conn->query("SELECT GROUP_CONCAT(Name) FROM `Projection_type` WHERE ID IN (".implode(',',$projectionType_arr).")");
    $projectionType_name = mysqli_fetch_column($projectionType_name);
    $message .= "<li class='text-start fs-6'>Status not updated for admission,projection not assigned on current month for the <b>".$projectionType_name."</b> type.</li>";
    return $message;
}

function checkAndUpdateCenterDeposit($center_id,$admAmount) {

    global $conn;
    $received_amount = ''; $deposit_amount = '';$updatedWidthrawAmount = '';
    if ($center_id != 'self') {
        $amountDepositClosureDetails = $conn->query("SELECT deposit_amount , withdraw_amount FROM Closure_details WHERE id = '$center_id'");   
        $amountDepositClosureDetails = mysqli_fetch_assoc($amountDepositClosureDetails);
        if(!is_null($amountDepositClosureDetails['deposit_amount'])) {
            $diffAmount = !is_null($amountDepositClosureDetails['withdraw_amount']) ? intval($amountDepositClosureDetails['deposit_amount'] - $amountDepositClosureDetails['withdraw_amount']) : intval($amountDepositClosureDetails['deposit_amount']);
            // Then concat all the amount to widthraw amount 
            if ($diffAmount >= $admAmount) {
                $updatedWidthrawAmount = !is_null($amountDepositClosureDetails['withdraw_amount']) ? ($amountDepositClosureDetails['withdraw_amount']+$admAmount) : ($admAmount); 
                $received_amount = null;
                $deposit_amount = $admAmount;    
            } else {
                $restAmount = intval($admAmount) - intval($diffAmount);
                $updatedWidthrawAmount = !is_null($amountDepositClosureDetails['withdraw_amount']) ? ($amountDepositClosureDetails['withdraw_amount']+$diffAmount) : ($diffAmount);
                $received_amount = $restAmount;
                $deposit_amount = $diffAmount;
            }
        } else {
            $received_amount = $admAmount;
            $deposit_amount = null;    
        }
    } else {
        $received_amount = $admAmount;
        $deposit_amount = null;
    }
    $setClosurequery = '';
    if(empty($updatedWidthrawAmount)) {
        $setClosurequery = "withdraw_amount = NULL";
    } else {
        $setClosurequery = "withdraw_amount = '$updatedWidthrawAmount'";
    }
    $updateClosureDeposit = $conn->query("UPDATE Closure_details SET $setClosurequery WHERE id = '$center_id'");
    return [$received_amount,$deposit_amount];
}
?>