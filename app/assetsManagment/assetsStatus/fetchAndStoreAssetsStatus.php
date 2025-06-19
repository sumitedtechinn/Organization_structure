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

if(isset($_REQUEST['method']) && $_REQUEST['method'] == 'checkStatus') {
    $id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? mysqli_real_escape_string($conn,$_REQUEST['id']) : "";
    if (!empty($id)) {
        $statusData = checkAssetsStatusPresentOrNot($id);
        $finalRes = setFormData("Update Assets Status","Update",$statusData);
    } else {
        $finalRes = setFormData("Add Assets Status","Add",[]);
    }   
} elseif (isset($_REQUEST['method']) && $_REQUEST['method'] == 'insertOrUpdate') {
    $finalRes = insertOrUpdateData();
}

echo json_encode($finalRes);

function checkAssetsStatusPresentOrNot($id) {
    global $conn;

    $fetchStatus_query = "SELECT `status_name` FROM `assets_status` WHERE Deleted_at IS NULL AND id = '$id'";
    $fetchStatus = $conn->query($fetchStatus_query);
    $fetchStatus = mysqli_fetch_assoc($fetchStatus);
    return $fetchStatus;
}

function setFormData($modelHeading,$buttonText,$data) {
    $formData = [
        "model_heading" => $modelHeading ,
        "buttonText" => $buttonText , 
        "form_data" => !empty($data) ? $data : [] ,
    ];
    return $formData;
}

function insertOrUpdateData() {
    global $conn;

    $id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? mysqli_real_escape_string($conn,$_REQUEST['id']) : "";
    $status_name = mysqli_real_escape_string($conn,$_REQUEST['status_name']);
    if (!empty($id)) {
        $query = "UPDATE assets_status SET status_name = '$status_name' WHERE id = '$id'";
        $message = "Update";
    } else {
        $query = "INSERT INTO `assets_status` (`status_name`) VALUES('$status_name')";
        $message = "Insert";
    }
    $response = $conn->query($query);
    return sendResponse($response,$message);
}

function sendResponse($response,$message = "Something Went Wrong") {
    return ($response) ? ['status' => 200 , 'message' => "Status $message successfully"] : ['status' => 400 , 'message' => $message];
}
?>