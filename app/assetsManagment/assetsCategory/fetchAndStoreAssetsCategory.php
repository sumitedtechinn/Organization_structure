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

if(isset($_REQUEST['method']) && $_REQUEST['method'] == 'checkCategory') {
    $id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? mysqli_real_escape_string($conn,$_REQUEST['id']) : "";
    $categoryData = [];
    if (!empty($id)) {
        $categoryData = checkAssetsCategoryPresentOrNot($id);
        $finalRes = setFormData("Update Assets Category","Update",$categoryData);
    } else {
        $finalRes = setFormData("Add Assets Category","Add",[]);
    }   
} elseif (isset($_REQUEST['method']) && $_REQUEST['method'] == 'insertOrUpdate') {
    $finalRes = insertOrUpdateData();
}

echo json_encode($finalRes);

function checkAssetsCategoryPresentOrNot($id) {
    global $conn;
    
    $fetchCategory_query = "SELECT category_name , category_prefix FROM `assets_category` WHERE Deleted_at IS NULL AND id = '$id'";
    $fetchCategory = $conn->query($fetchCategory_query);
    $fetchCategory = mysqli_fetch_assoc($fetchCategory);
    return $fetchCategory;
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

    try {
        $id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? mysqli_real_escape_string($conn,$_REQUEST['id']) : "";
        $category_name = mysqli_real_escape_string($conn,$_REQUEST['category_name']);
        $category_prefix = strtoupper(mysqli_real_escape_string($conn,$_REQUEST['category_prefix']));
        if (!empty($id)) {
            $query = "UPDATE assets_category SET category_name = '$category_name' , category_prefix = '$category_prefix' WHERE id = '$id'";
            $message = "Update";
        } else {
            $query = "INSERT INTO `assets_category` (`category_name`,`category_prefix`) VALUES('$category_name','$category_prefix')";
            $message = "Insert";
        }
        $response = $conn->query($query);
        return sendResponse($response,$message);    
    } catch (Exception $e) {
        return sendResponse(false,"Exception : " . $e->getMessage());
    }
}

function sendResponse($response,$message = "Something Went Wrong") {
    return ($response) ? ['status' => 200 , 'message' => "Category $message successfully"] : ['status' => 400 , 'message' => $message];
}
?>