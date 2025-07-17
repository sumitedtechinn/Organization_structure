<?php 

## Database configuration
include '../../../includes/db-config.php';
session_start();

$data_field = file_get_contents('php://input'); // by this we get raw data
if (!empty($data_field)) {
    $data_field = json_decode($data_field,true);
    $_REQUEST = array_merge($_REQUEST,$data_field);
}

// echo "<pre>";
// print_r($_REQUEST);
// exit();

$finalRes = [];

if(isset($_REQUEST['method']) && $_REQUEST['method'] == 'checkAttendanceSetting') {
    $id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? mysqli_real_escape_string($conn,$_REQUEST['id']) : "";
    $attendanceSettingData = [];
    $organization = fetchOrganization();
    $dropDownData = json_encode(["organization_id" => $organization]);
    if (!empty($id)) {
        $attendanceSettingData = checkAttendanceSetting($id);
        $finalRes = setFormData("Update Setting","Update",$attendanceSettingData,$dropDownData);
    } else {
        $finalRes = setFormData("Add Setting","Add",[],$dropDownData);
    }   
} elseif (isset($_REQUEST['method']) && $_REQUEST['method'] == 'insertOrUpdate') {
    $finalRes = insertOrUpdateData();
} elseif (isset($_REQUEST['method']) && $_REQUEST['method'] == 'fetchHolidayList') {
    $id = mysqli_real_escape_string($conn,$_REQUEST['id']);
    $finalRes = fetchHolidayList($id);
}

echo json_encode($finalRes);

function checkAttendanceSetting($id) {
    global $conn;
    
    $fetchAttendanceSetting_query = "SELECT organization_id , in_time , out_time , relaxation_time , week_off , holiday FROM `attendance_setting` WHERE id = '$id'";
    $fetchAttendanceSetting = $conn->query($fetchAttendanceSetting_query);
    $fetchAttendanceSetting = mysqli_fetch_assoc($fetchAttendanceSetting);
    $weekOff = json_decode($fetchAttendanceSetting['week_off'],true);
    foreach ($weekOff as $value) {
        $newKey = "week_off_$value";
        $fetchAttendanceSetting[$newKey] = "checked";
    }
    unset($fetchAttendanceSetting['week_off']);
    return $fetchAttendanceSetting;
}

/**
 * fetch the organization list
 */
function fetchOrganization() : array{
    global $conn;

    try {
        $fetchOrganization_query = "SELECT id, organization_name FROM `organization` WHERE Deleted_At IS NULL";
        $fetchOrganization = $conn->query($fetchOrganization_query);
        $organization = [];
        if($fetchOrganization->num_rows > 0) {
            $fetchOrganization = mysqli_fetch_all($fetchOrganization,MYSQLI_ASSOC);
            $organization = array_column($fetchOrganization,'organization_name','id');
        } 
        return $organization;
    } catch(Exception $e) {
        return ['error' => $e->getMessage() . "on line : " . $e->getLine()];
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

function insertOrUpdateData() {
    global $conn;

    try {
        $id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? mysqli_real_escape_string($conn,$_REQUEST['id']) : "";
        $organization_id = mysqli_real_escape_string($conn,$_REQUEST['organization_id']);
        $in_time = mysqli_real_escape_string($conn,$_REQUEST['in_time']);
        $out_time = mysqli_real_escape_string($conn,$_REQUEST['out_time']);
        $relaxation_time = mysqli_real_escape_string($conn,$_REQUEST['relaxation_time']);
        $week_off = json_encode($_REQUEST['week_off']);
        $holiday = [];
        foreach ($_REQUEST as $key => $value) {
            if(str_contains($key,'holiday')) {
                $field_type = explode("_",$key);
                $holiday[$field_type[2]][$field_type[1]] = $value;   
            }
        }   
        $holiday_list = json_encode(array_column($holiday,'date','name'));
        if (!empty($id)) {
            $query = "UPDATE `attendance_setting` SET `organization_id`='$organization_id',`in_time`='$in_time',`out_time`='$out_time',`relaxation_time`='$relaxation_time',`week_off`='$week_off',`holiday`='$holiday_list' WHERE `id` = '$id'";
            $message = "Update";
        } else {
            $query = "INSERT INTO `attendance_setting`(`organization_id`, `in_time`, `out_time`, `relaxation_time`, `week_off`, `holiday`) VALUES ('$organization_id','$in_time','$out_time','$relaxation_time','$week_off','$holiday_list')";
            $message = "Insert";
        }
        $response = $conn->query($query);
        return sendResponse($response,$message);    
    } catch (Exception $e) {
        return sendResponse(false,"Exception : " . $e->getMessage());
    }
}

function fetchHolidayList($id) {
    global $conn;

    try {
        $fetchHoliday_query = "SELECT holiday FROM `attendance_setting` WHERE id = '$id'";
        $fetchHoliday = $conn->query($fetchHoliday_query);
        $fetchHoliday = mysqli_fetch_column($fetchHoliday);
        $fetchHoliday = json_decode($fetchHoliday,true);
        $holidayList = array_map(function($holiday_date) {
            $day = date('l',strtotime($holiday_date));
            $date = date('d-M-Y',strtotime($holiday_date));
            return $day.'@@@@'.$date;
        },$fetchHoliday);
        return ['status' => 200, 'message' => json_encode($holidayList)];
    } catch (Exception $e) {
        return sendResponse(false, 'Exception : ' . $e->getMessage() . " on  line : ". $e->getLine());        
    }
} 

function sendResponse($response,$message = "Something Went Wrong") {
    return ($response) ? ['status' => 200 , 'message' => "Setting $message successfully"] : ['status' => 400 , 'message' => $message];
}
?>