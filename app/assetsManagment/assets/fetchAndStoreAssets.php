<?php 

## Database configuration
include '../../../includes/db-config.php';
session_start();

$data_field = file_get_contents('php://input'); // by this we get raw data

if (!empty($data_field)) {
    $data_field = json_decode($data_field,true);
    $_REQUEST = array_merge($_REQUEST,$data_field);
}

$finalRes = [];

if(isset($_REQUEST['method']) && $_REQUEST['method'] == 'checkAssets') {
    $id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? mysqli_real_escape_string($conn,$_REQUEST['id']) : "";
    $assetsData = [];
    $assets_category = fetchAssetsCategory();
    $dropDownData = json_encode(["assets_category" => $assets_category]);
    if (!empty($id)) {
        $assetsData = checkAssetsPresentOrNot($id);
        $finalRes = setFormData("Update Assets","Update",$assetsData,$dropDownData);
    } else {
        $finalRes = setFormData("Add Assets","Add",[],$dropDownData);
    }   
} elseif (isset($_REQUEST['method']) && $_REQUEST['method'] == 'insertOrUpdate') {
    $finalRes = insertOrUpdateData();
} elseif (isset($_REQUEST['method']) && $_REQUEST['method'] == 'getAssetsCode') {
    $assets_category = mysqli_real_escape_string($conn,$_REQUEST['assets_category']);
    $finalRes = getAssetsCode($assets_category);
} elseif (isset($_REQUEST['method']) && $_REQUEST['method'] == 'getAllFilterData') {
    $filterInputFiled = explode('@@@@',$_REQUEST['inputField']);
    $finalRes = getAllFilterData($filterInputFiled);
} elseif (isset($_REQUEST['method']) && $_REQUEST['method'] == 'checkAssetsStatus') {
    $assets_id = mysqli_real_escape_string($conn,$_REQUEST['assets_id']);
    $assets_status = fetchAssetsStatus();
    $status = array_filter($assets_status, fn($element) => !str_contains(strtolower($element),'use'));
    $dropDownData = json_encode(["assets_status" => $status]);
    $selected_status = checkAssetsStatus($assets_id);
    $finalRes = setFormData("Update Status","Update",$selected_status,$dropDownData);
} elseif (isset($_REQUEST['method']) && $_REQUEST['method'] == 'updateAssetStatus') {
    $assets_id = mysqli_real_escape_string($conn,$_REQUEST['id']);
    $assets_status = mysqli_real_escape_string($conn,$_REQUEST['assets_status']);
    $finalRes = updateAssetStatus($assets_id,$assets_status);
} elseif (isset($_REQUEST['method']) && $_REQUEST['method'] == 'checkAssetsDeleteCondition') {
    $assets_id = mysqli_real_escape_string($conn,$_REQUEST['id']);
    $finalRes = checkAssetsDeleteCondition($assets_id);    
}

echo json_encode($finalRes);

function checkAssetsPresentOrNot($id) {
    global $conn;
    
    $fetchAssets_query = "SELECT `brand_name` , `model_number` , `assets_category` , `assets_description` , `assets_code` FROM `assets` WHERE id = '$id'";
    $fetchAssets = $conn->query($fetchAssets_query);
    $fetchAssets = mysqli_fetch_assoc($fetchAssets);
    return $fetchAssets;
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

function fetchAssetsCategory() {
    global $conn;

    $fetchAssetsCategory_query = "SELECT `category_name` , `id` FROM `assets_category` WHERE Deleted_at IS NULL";
    $assets_category = $conn->query($fetchAssetsCategory_query);
    $assets_category = mysqli_fetch_all($assets_category,MYSQLI_ASSOC);
    $assets_category = array_column($assets_category,'category_name','id');
    return $assets_category;
}

function fetchAssetsStatus() {
    global $conn;

    $fetchAssetsStatus_query = "SELECT id , status_name FROM `assets_status` WHERE Deleted_at IS NULL";
    $fetchAssetsStatus = $conn->query($fetchAssetsStatus_query);
    $assets_status = mysqli_fetch_all($fetchAssetsStatus,MYSQLI_ASSOC);
    $assets_status = array_column($assets_status,'status_name','id');
    return $assets_status;
}

function fetchAssetsUser() {
    global $conn;

    $fetchUser_query = "SELECT `id` , `Name` FROM `users` WHERE role != '1' AND Deleted_At IS NULL";
    $fetchUser = $conn->query($fetchUser_query);
    $fetchUser = mysqli_fetch_all($fetchUser,MYSQLI_ASSOC);
    $fetchUser = array_column($fetchUser,'Name','id');
    return $fetchUser;
}

function insertOrUpdateData() {
    global $conn;

    try {
        $id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? mysqli_real_escape_string($conn,$_REQUEST['id']) : "";
        $brand_name = mysqli_real_escape_string($conn,$_REQUEST['brand_name']);
        $model_number = mysqli_real_escape_string($conn,$_REQUEST['model_number']);
        $assets_category = mysqli_real_escape_string($conn,$_REQUEST['assets_category']);
        $assets_code = mysqli_real_escape_string($conn,$_REQUEST['assets_code']);
        $assets_description = mysqli_real_escape_string($conn,$_REQUEST['assets_description']);

        // fetch Assets Status 
        $assets_status = $conn->query("SELECT ID FROM `assets_status` WHERE status_name LIKE '%backup%'");
        $assets_status = mysqli_fetch_column($assets_status);
        
        if (!empty($id)) {
            $query = "UPDATE assets SET brand_name = '$brand_name' , model_number = '$model_number' , assets_category = '$assets_category' , assets_code = '$assets_code' ,  assets_description = '$assets_description' WHERE id = '$id'";
            $message = "Update";
        } else {
            // check Assets already exist or not
            $checkModel = $conn->query("SELECT id FROM `assets` WHERE model_number = '$model_number'");
            if($checkModel->num_rows > 0) {
                return sendResponse(false,"Duplicate Assets, Model number alredy present");         
            }
            $query = "INSERT INTO `assets`(`brand_name`, `model_number`, `assets_category`, `assets_code`, `assets_status`, `assets_description`) VALUES ('$brand_name','$model_number','$assets_category','$assets_code','$assets_status','$assets_description')";
            $message = "Insert";
        }
        $response = $conn->query($query);
        return sendResponse($response,$message);    
    } catch (Exception $e) {
        return sendResponse(false,"Exception : " . $e->getMessage() . " on line : " . $e->getLine());
    }
}

function getAssetsCode($assets_category) {

    global $conn;
    
    try {
        $getCodePrefix_query = "SELECT category_prefix FROM assets_category WHERE id = '$assets_category'";
        $getCodePrefix = $conn->query($getCodePrefix_query);
        $getCodePrefix = mysqli_fetch_column($getCodePrefix);

        // check prefix assets exist or not 
        $maxCode_query = "SELECT assets_code FROM assets WHERE assets_code LIKE '%$getCodePrefix%' AND assets_category = '$assets_category' ORDER BY id DESC LIMIT 1";
        $maxCode = $conn->query($maxCode_query);
        if($maxCode->num_rows > 0) {
            $maxCode = mysqli_fetch_column($maxCode);
            $codeVal = intval(substr($maxCode,strlen($getCodePrefix),strlen($maxCode)));
            ++$codeVal;
            $newCode = str_pad($codeVal,4,'0',STR_PAD_LEFT);
            return ['status' => 200 , 'message' => $getCodePrefix . $newCode];
        } else {
            return ['status' => 200 , 'message' => $getCodePrefix . "0001"];
        }    
    } catch (Exception $e) {
        return sendResponse(false,$e->getMessage());
    }
}

function checkAssetsStatus($assets_id) {
    global $conn;

    $assetsStatus_query = "SELECT assets_status FROM `assets` WHERE id = '$assets_id'";
    $assetsStatus = $conn->query($assetsStatus_query);
    $assetsStatus = mysqli_fetch_assoc($assetsStatus);
    return $assetsStatus;
}

function updateAssetStatus($assets_id,$assets_status) {
    global $conn;

    try {
        $updateAssetStatus_query = "UPDATE assets SET assets_status = '$assets_status' WHERE id = '$assets_id'";
        $updateAssetStatus = $conn->query($updateAssetStatus_query);
        return sendResponse($updateAssetStatus,"Status Updated");
    } catch (Exception $e) {
        return sendResponse(false,"Exception : " . $e->getMessage() . " on line => " . $e->getLine());
    }
}

/**
 * Assets is only delete when assets status is in retired state
 */
function checkAssetsDeleteCondition($assets_id) {
    global $conn;

    try {
        $checkAssetStatus_query = "SELECT IF( assets_status.status_name LIKE '%retired%', 'allow' , 'not_allow') as `delete_status` FROM assets LEFT JOIN assets_status ON assets_status.id = assets.assets_status WHERE assets.id = '$assets_id'";
        $checkAssetStatus = $conn->query($checkAssetStatus_query);
        $checkAssetStatus = mysqli_fetch_column($checkAssetStatus);
        return ['status' => 200 , 'message' => $checkAssetStatus];
    } catch (Exception $e) {
        return sendResponse(false,"Exception : " . $e->getMessage() . " on line => " . $e->getLine());
    }
}

function getAllFilterData($filterInputFiled) {
    $filteData = [];
    foreach ($filterInputFiled as $filterField) {
        $functionName = array_reduce(explode("_",$filterField), function($carry , $item) {
            if ($item != "filter") $carry .= ucfirst($item); 
            return $carry;
        } , "fetch");
        $response = call_user_func($functionName);
        $filteData[$filterField] = json_encode($response);
    }
    return $filteData;
}

function sendResponse($response,$message = "Something Went Wrong") {
    return ($response) ? ['status' => 200 , 'message' => "Asset $message successfully"] : ['status' => 400 , 'message' => $message];
}
?>