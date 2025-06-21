<?php 

## Database configuration
include '../../includes/db-config.php';
session_start();

$data_field = file_get_contents('php://input'); // by this we get raw data
if (!empty($data_field)) {
    $data_field = json_decode($data_field,true);
    $_REQUEST = array_merge($_REQUEST,$data_field);
}

$finalRes = [];
$stepsLog = date(DATE_ATOM) . ": request received => " . json_encode($_REQUEST) . " \n\n";

if(isset($_REQUEST['method']) && $_REQUEST['method'] == 'fetchUserAssets') {
    $user_id = isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id']) ? mysqli_real_escape_string($conn,$_REQUEST['user_id']) : "";
    $finalRes = fetchUserAssets($user_id);
} elseif (isset($_REQUEST['method']) && $_REQUEST['method'] == 'fetchAssets') {
    $category_id = mysqli_real_escape_string($conn,$_REQUEST['category_id']);
    $user_id = mysqli_real_escape_string($conn,$_REQUEST['user_id']);
    $finalRes = fetchAssets($category_id,$user_id);
} elseif (isset($_REQUEST['method']) && $_REQUEST['method'] == 'insertOrUpdate') {
    unset($_REQUEST['method']);
    $finalRes = insertOrUpdateAssetsAssignation();
} elseif (isset($_REQUEST['method']) && $_REQUEST['method'] == 'fetchUserAssignedAssetsDetails') {
    $user_id = isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id']) ? mysqli_real_escape_string($conn,$_REQUEST['user_id']) : "";
    $finalRes = fetchUserAssignedAssetsDetails($user_id);
}

$stepsLog .= date(DATE_ATOM) . " final response => " . json_encode($finalRes) . "\n\n";
saveLog();

function fetchUserAssets($user_id) {
    global $conn,$stepsLog;

    $stepsLog .= date(DATE_ATOM). " :: method inside the fetchUserAssets \n\n";
    $userAssets_query = "SELECT assets_assignation FROM users WHERE ID = '$user_id'";
    $stepsLog .= date(DATE_ATOM) . " :: userAssets_query => $userAssets_query \n\n";
    $userAssets = $conn->query($userAssets_query);
    $userAssets = mysqli_fetch_column($userAssets);
    $stepsLog .= date(DATE_ATOM) . " :: userAssets => " . $userAssets . " \n\n";
    $assets_category = assetsCategory();
    $dropDownData = json_encode(["assets_category" => $assets_category]);
    if(!is_null($userAssets)) {
        $userAssets = json_decode($userAssets,true);
        return setFormData("Update Assets","Save",$userAssets,$dropDownData);
    } else {
        return setFormData("Add Assets","Save",[],$dropDownData);
    }   
}

function assetsCategory() {
    global $conn , $stepsLog;
    
    $stepsLog .= date(DATE_ATOM). " :: method inside the assetsCategory \n\n";
    $fetchCategory_query = "SELECT id, category_name FROM `assets_category` WHERE Deleted_at IS NULL";
    $stepsLog .= date(DATE_ATOM) . " :: fetchCategory_query => $fetchCategory_query \n\n"; 
    $fetchCategory = $conn->query($fetchCategory_query);
    $assets_category = [];
    while ($row = mysqli_fetch_assoc($fetchCategory)) {
        $assets_category[$row['id']] = $row['category_name'];
    }
    return $assets_category;
}

function fetchAssets($category_id,$user_id) {
    global $conn , $stepsLog;

    $stepsLog .= date(DATE_ATOM). " :: method inside the fetchAssets \n\n";
    try {
        $assets = [];
        $fetchAssets_query = "SELECT assets.id , CONCAT(UPPER(assets.brand_name), ' ' ,assets.model_number , '(', assets.assets_code, ')') AS `assets_name` FROM `assets` LEFT JOIN assets_status ON assets_status.id = assets.assets_status WHERE assets.assets_category = '$category_id' AND ( assets.assets_assign_to IS NULL OR assets.assets_assign_to = '$user_id') AND (assets_status.status_name LIKE '%backup%' OR assets.assets_assign_to = '$user_id') AND assets.Deleted_at IS NULL";
        $stepsLog .= date(DATE_ATOM) . " :: fetchAssets_query => $fetchAssets_query \n\n";
        $fetchAssets = $conn->query($fetchAssets_query);
        while ($row = mysqli_fetch_assoc($fetchAssets)) {
            $assets[$row['id']] = $row['assets_name'];
        }
        return ['status' => 200 , 'message' => json_encode($assets)];    
    } catch (Exception $e) {
        return sendResponse(false,"Exception : " . $e->getMessage());
    }
}

function fetchUserAssignedAssetsDetails($user_id) {
    global $conn , $stepsLog;

    $stepsLog .= date(DATE_ATOM). " :: method inside the fetchUserAssignedAssetsDetails \n\n";
    try {
        // check User Assets assign or not 
        $checkUserAssets_query = "SELECT assets_assignation FROM `users` WHERE ID = '$user_id'";
        $stepsLog .= date(DATE_ATOM) . " :: checkUserAssets_query => $checkUserAssets_query \n\n";
        $checkUserAssets = $conn->query($checkUserAssets_query);
        $checkUserAssets = mysqli_fetch_column($checkUserAssets);
        $stepsLog .= date(DATE_ATOM) . " :: checkUserAssets response => $checkUserAssets \n\n";

        if(is_null($checkUserAssets)) {
            return ['status' => 200 , 'assets_assignation' => false , 'message' => "No Assets Assign Yet"];    
        }

        $userAssets = json_decode($checkUserAssets,true);
        $assetsInfo = [];
        array_walk($userAssets, function($asset_id,$category_id) use(&$stepsLog,&$conn,&$assetsInfo) {
            $fetchAssetsDetails_query = "SELECT assets_category.category_name as `assets_category` , assets.assets_code , assets.brand_name , assets.model_number FROM assets LEFT JOIN assets_category ON assets.assets_category = assets_category.id WHERE assets.id = '$asset_id'";      
            $stepsLog .= date(DATE_ATOM) . " :: fetchAssetsDetails_query => $fetchAssetsDetails_query \n\n"; 
            $fetchAssetsDetails = $conn->query($fetchAssetsDetails_query);
            $fetchAssetsDetails = mysqli_fetch_assoc($fetchAssetsDetails);
            $stepsLog .= date(DATE_ATOM) . " :: fetchAssetsDetails response =>  " . json_encode($fetchAssetsDetails)  . "\n\n"; 
            $assetsInfo[$category_id] = $fetchAssetsDetails;      
        });

        return ['status' => 200  , 'assets_assignation' => true , 'message' => json_encode($assetsInfo)];    
    } catch (Exception $e) {
        return sendResponse(false,"Exception : " . $e->getMessage());
    }
}

function setFormData($modelHeading,$buttonText,$data,$dropDownData = "") {
    $formData = [
        "model_heading" => $modelHeading ,
        "buttonText" => $buttonText , 
        "form_data" => !empty($data) ? $data : [] ,
        "dropDownFiled" => $dropDownData
    ];
    return $formData;
}

function insertOrUpdateAssetsAssignation() {
    global $conn , $stepsLog;

    $stepsLog .= date(DATE_ATOM). " :: method inside the insertOrUpdateAssetsAssignation \n\n";
    try {
        $user_id = mysqli_real_escape_string($conn,$_REQUEST['user_id']);
        unset($_REQUEST['user_id']);
        $group = [];
        foreach($_REQUEST as $key => $value) {
            $arr = explode("_",$key);
            $slnum = array_pop($arr);
            $fieldName = implode("_",$arr);
            $group[$slnum][$fieldName] = $value;
        }
        $final_group = array_column($group,"assets","assets_category");
        $stepsLog .= date(DATE_ATOM) . " :: final_group => " . json_encode($final_group) . " \n\n";

        // insert or update data on assets_assignation
        list($updateAssetsTableGroupArr,$removeAssetsTableGroupArr) =  insertOrUpdateDataOnAssetsAssignationTable($final_group,$user_id);

        // Update data in Assets Table 
        if(!empty($updateAssetsTableGroupArr)) {
            updateAsetsTableForAssignation($user_id,$updateAssetsTableGroupArr);
        }

        if (!empty($removeAssetsTableGroupArr)) {
            updateAsetsTableForRemoveAssignation($user_id,$removeAssetsTableGroupArr);
        }

        // Update User Table 
        updateUserTable($user_id,$final_group); 
        
        return sendResponse(true,"Updated");
    } catch (Exception $e) {
        $stepsLog .= date(DATE_ATOM) . " :: Exception => " . $e->getMessage() . " on line => " . $e->getLine();
        return sendResponse(false," :: Exception => " . $e->getMessage());
    }
}

function insertOrUpdateDataOnAssetsAssignationTable($group,$user_id) {
    global $conn , $stepsLog;

    $stepsLog .= date(DATE_ATOM). " :: method inside the insertOrUpdateDataOnAssetsAssignationTable \n\n";
    try {
        $updateAssetsTableGroupArr = [];
        $removeAssetsTableGroupArr = [];
        $insertDataInAssetsAssignation = $group;
        $fetchUserAssets_query = "SELECT id , asset_id , category_id FROM assets_assignment WHERE user_id = '$user_id'";
        $stepsLog .= date(DATE_ATOM) . " :: fetchUserAssets_query => $fetchUserAssets_query \n\n";
        $fetchUserAssets = $conn->query($fetchUserAssets_query);
        
        // Assets alredy assign to user
        $userAssets = [];

        if ($fetchUserAssets->num_rows > 0) {
            while ($row = $fetchUserAssets->fetch_assoc()) {
                $userAssets[$row['category_id']] = [
                    'asset_id' => $row['asset_id'],
                    'id' => $row['id']
                ];
            }
        }
        
        $stepsLog .= date(DATE_ATOM) . " :: userAssets response => " . json_encode($userAssets) . " \n\n";
        
        foreach ($userAssets as $category => $value) {
            // if array key exist that mean came in update case or nothing change 
            if(array_key_exists($category,$group)) {
                if ($value['asset_id'] != $group[$category]) {
                    $removeAssetsTableGroupArr[$category] = $value['asset_id'];
                    $updateAssetsTableGroupArr[$category] = $group[$category];
                    unset($insertDataInAssetsAssignation[$category]);
                    updateAssetsAssignation($value['id'],$group[$category]);
                    uppdateAssetHistoryForReturn($user_id,$category,$value['asset_id']);
                } else {
                    unset($insertDataInAssetsAssignation[$category]);
                    unset($group[$category]);
                }
            } else {
                // Now else case indicate delete data from assignation
                $removeAssetsTableGroupArr[$category] = $value['asset_id'];
                deleteAssetsAssignation($value['id'],$user_id,$value['asset_id'],$category);
            }
        }

        // insert new assignation 
        $insertDataInAssignationTableArr = [];
        foreach($insertDataInAssetsAssignation as $category => $assets) {
            $updateAssetsTableGroupArr[$category] = $assets;
            $insertDataInAssignationTableArr[] = "('$assets','$category','$user_id',CURRENT_DATE)";
        }

        if(!empty($insertDataInAssignationTableArr)) {
            $insertDataInAssignationTable_query = "INSERT INTO `assets_assignment`(`asset_id`, `category_id`, `user_id`, `assigned_on`) VALUES " . implode(",",$insertDataInAssignationTableArr);
            $stepsLog .= date(DATE_ATOM) . " :: insertDataInAssignationTable_query => $insertDataInAssignationTable_query \n\n";
            $insertDataInAssignationTable = $conn->query($insertDataInAssignationTable_query);
            $stepsLog .= date(DATE_ATOM) . " :: insertDataInAssignationTable response => $insertDataInAssignationTable \n\n";
        }
        
        // insert data in assets_history
        $insertDataInHistoryTableArr = [];
        foreach ($group as $category => $assets) {
            $insertDataInHistoryTableArr[] = "('$assets','$category','$user_id',CURRENT_DATE)";
        }

        if (!empty($insertDataInHistoryTableArr)) {
            $insertDataInHistoryTable_query = "INSERT INTO `assets_history`(`asset_id`, `category_id`, `user_id`, `assigned_on`) VALUES " . implode(",",$insertDataInHistoryTableArr);
            $stepsLog .= date(DATE_ATOM) . " :: insertDataInHistoryTable_query => $insertDataInHistoryTable_query \n\n";
            $insertDataInHistoryTable = $conn->query($insertDataInHistoryTable_query);
            $stepsLog .= date(DATE_ATOM) . " :: insertDataInHistoryTable response => $insertDataInHistoryTable \n\n";
        }   

        return [$updateAssetsTableGroupArr,$removeAssetsTableGroupArr];
    } catch (Exception $e) {
        $stepsLog .= date(DATE_ATOM) . " :: Exception => " . $e->getMessage() . " on line => " . $e->getLine() . "\n\n";
    }
}

function updateAssetsAssignation($row_id,$asset_id) {
    global $conn , $stepsLog;

    $stepsLog .= date(DATE_ATOM). " :: method inside the updateAssetsAssignation \n\n";
    $updateAssetsAssignation_query = "UPDATE assets_assignment SET asset_id = '$asset_id' , assigned_on = CURRENT_DATE , Deleted_At = NULL WHERE id = '$row_id'";
    $stepsLog .= date(DATE_ATOM) . " :: updateAssetsAssignation_query => $updateAssetsAssignation_query \n\n";
    $updateAssetsAssignation = $conn->query($updateAssetsAssignation_query);
    $stepsLog .= date(DATE_ATOM) . " :: updateAssetsAssignation query response => $updateAssetsAssignation \n\n";
}

function deleteAssetsAssignation($assignationTable_id,$user_id,$asset_id,$category_id) {
    global $conn , $stepsLog;

    $stepsLog .= date(DATE_ATOM). " :: method inside the deleteAssetsAssignation \n\n";
    try {
        // Delete record from assignation table
        
        $deleteassignation_query = "DELETE FROM assets_assignment WHERE id = '$assignationTable_id'";
        $stepsLog .= date(DATE_ATOM) . " :: deleteassignation_query => $deleteassignation_query \n\n";
        $deleteassignation = $conn->query($deleteassignation_query);
        $stepsLog .= date(DATE_ATOM) . " :: deleteassignation response => $deleteassignation \n\n"; 
        
        // Insert return record data in the history table 
        $insertDataInHistoryTable_query = "INSERT INTO `assets_history`(`asset_id`, `category_id`, `user_id`, `return_on`) VALUES ('$asset_id','$category_id','$user_id',CURRENT_DATE)";
        $stepsLog .= date(DATE_ATOM) . " :: insertDataInHistoryTabele_query => $insertDataInHistoryTable_query \n\n";
        $insertDataInHistoryTable = $conn->query($insertDataInHistoryTable_query);
        $stepsLog .= date(DATE_ATOM) . " :: insertDataInHistoryTable response => $insertDataInHistoryTable \n\n"; 

    } catch (Exception $e) {
        $stepsLog .= date(DATE_ATOM) . " :: Exception => " . $e->getMessage() . " on line => " . $e->getLine();
    }
}

function uppdateAssetHistoryForReturn($user_id,$category_id,$asset_id) {
    global $conn , $stepsLog;

    $stepsLog .= date(DATE_ATOM). " :: method inside the deleteAssetsAssignation \n\n";
    try {
        // Delete record from assignation table
        
        $returnDateOfAssets_query = "UPDATE assets_history SET return_on = CURRENT_DATE WHERE asset_id = '$asset_id' AND category_id = '$category_id' AND user_id = '$user_id'";
        $stepsLog .= date(DATE_ATOM) . " :: returnDateOfAssets_query => $returnDateOfAssets_query \n\n";
        $returnDateOfAssets = $conn->query($returnDateOfAssets_query);
        $stepsLog .= date(DATE_ATOM) . " :: returnDateOfAssets response => $returnDateOfAssets \n\n"; 
        
    } catch (Exception $e) {
        $stepsLog .= date(DATE_ATOM) . " :: Exception => " . $e->getMessage() . " on line => " . $e->getLine();
    }
}

function updateAsetsTableForAssignation($user_id,$final_group) {
    global $conn , $stepsLog;

    $stepsLog .= date(DATE_ATOM). " :: method inside the updateAsetsTableForAssignation \n\n";
    try {
        foreach ($final_group as $category_id => $asset_id) {
            $updateForAssignAssetsTable_query = "UPDATE assets SET assets_assign_to = '$user_id' , assets_status = (SELECT id FROM `assets_status` WHERE status_name LIKE '%use%') WHERE id = '$asset_id' AND assets_category = '$category_id'";
            $stepsLog .= date(DATE_ATOM) . " :: updateForAssignAssetsTable_query => $updateForAssignAssetsTable_query \n\n";
            $updateForAssignAssetsTable = $conn->query($updateForAssignAssetsTable_query);
            $stepsLog .= date(DATE_ATOM) . " :: updateForAssignAssetsTable response => $updateForAssignAssetsTable \n\n";
        }
    } catch (Exception $e) {
        $stepsLog .= date(DATE_ATOM) . " :: Exception => " . $e->getMessage() . " on line => " . $e->getLine() . "\n\n";
    }
}

function updateAsetsTableForRemoveAssignation($user_id,$removeAssetsTableGroupArr) {
    global $conn , $stepsLog;

    $stepsLog .= date(DATE_ATOM). " :: method inside the updateAsetsTableForRemoveAssignation \n\n";
    try {
        foreach ($removeAssetsTableGroupArr as $category_id => $asset_id) {
            $updateAssetsForRemoveTable_query = "UPDATE assets SET assets_assign_to = NULL , assets_status = (SELECT id FROM `assets_status` WHERE status_name LIKE '%backup%') WHERE id = '$asset_id' AND assets_category = '$category_id'";
            $stepsLog .= date(DATE_ATOM) . " :: updateAssetsForRemoveTable_query => $updateAssetsForRemoveTable_query \n\n";
            $updateAssetsForRemoveTable = $conn->query($updateAssetsForRemoveTable_query);
            $stepsLog .= date(DATE_ATOM) . " :: updateAssetsForRemoveTable response => $updateAssetsForRemoveTable \n\n";
        }
    } catch (Exception $e) {
        $stepsLog .= date(DATE_ATOM) . " :: Exception => " . $e->getMessage() . " on line => " . $e->getLine();
    }
}

function updateUserTable($user_id,$final_group) {
    global $conn , $stepsLog;

    $stepsLog .= date(DATE_ATOM). " :: method inside the updateUserTable \n\n";
    try {
        $final_group = json_encode($final_group);
        $updateUserTable_query = "UPDATE users SET assets_assignation = '$final_group' WHERE ID = '$user_id'";
        $stepsLog .= date(DATE_ATOM) . " :: updateUserTable_query => $updateUserTable_query \n\n";
        $updateUserTable = $conn->query($updateUserTable_query);
        $stepsLog .= date(DATE_ATOM) . " :: updateUserTable response => $updateUserTable \n\n";
    } catch (Exception $e) {
        $stepsLog .= date(DATE_ATOM) . " :: Exception => " . $e->getMessage() . " on line => " . $e->getLine();
    }
}

function sendResponse($response,$message = "Something Went Wrong") {
    return ($response) ? ['status' => 200 , 'message' => "Assets $message successfully"] : ['status' => 400 , 'message' => $message];
}

function saveLog() {
    global $stepsLog , $finalRes;

    $stepsLog .= " ============ End Of Script ================== \n\n";
    $pdf_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/assets_log/';
    deleteOldTicketLogs($pdf_dir,5);
    $fh = fopen($pdf_dir . 'assets_' . date('y-m-d') . '.log' , 'a');
    fwrite($fh,$stepsLog);
    fclose($fh);
    echo json_encode($finalRes);
    exit;
}

function deleteOldTicketLogs($logDir, $daysOld = 5) {
    $maxFileAge = $daysOld * 86400; // 5 days in seconds
    if (!is_dir($logDir)) return;
    foreach (glob($logDir . '/assets_*.log') as $file) {
        if (is_file($file) && (time() - filemtime($file)) > $maxFileAge) {
            unlink($file);
        }
    }
}
?>