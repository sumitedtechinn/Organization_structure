<?php
## Database configuration   
include '../../../includes/db-config.php';

require_once('../../../vendor/autoload.php');
require_once("../../../vendor/nuovo/spreadsheet-reader/SpreadsheetReader.php");

use Shuchkin\SimpleXLSXGen;

session_start();

$data_field = file_get_contents('php://input'); // by this we get raw data
if (!empty($data_field)) {
    $data_field = json_decode($data_field,true);
    $_REQUEST = array_merge($_REQUEST,$data_field);
}

$stepsLog = '';
$finalRes = [];
//$stepsLog .= date(DATE_ATOM) . " :: Request received for : " . json_encode($_REQUEST) . "\n\n";

try {
    $finalRes = insertAttendanceData();    
} catch (Exception $e) {
    $stepsLog .= date(DATE_ATOM) . " :: CSV file is required \n\n";
} finally {
    saveLog($finalRes);
}


function insertAttendanceData() {
    global $conn , $stepsLog , $formatDate;

    $stepsLog .= date(DATE_ATOM) . " :: mehod inside insertAttendanceData \n\n";
    if(!isset($_FILES['attendance_sheet']) || empty($_FILES['attendance_sheet']['name'])) {
        $stepsLog .= date(DATE_ATOM) . " :: CSV File is required \n\n";
        return showResponse(false,"CSV file is required");
    }
    if(!checkFileType($_FILES['attendance_sheet']['type'])) {
        $stepsLog .= date(DATE_ATOM) . " ::  File type is not matched \n\n";
        return showResponse(false,"File type is not matched");
    }
    try {
        $inputFile = $_FILES['attendance_sheet']['tmp_name'];
        $reader = new SpreadsheetReader($inputFile);
        $outputRow = []; $headers = [];
        $firstRow = true;
        foreach ($reader as $data) {
            if($firstRow) {
                $headers = array_map(fn($value) => implode('_', explode(' ', strtolower(trim($value)))), $data);
                $headers[] = 'status';
                $outputRow[] = $headers; 
                $firstRow = false;
                continue;
            }
            $row = array_combine(array_slice($headers,0,count($headers)-1),$data);
            $attendance = [];
            $attendance_status = '';
            // check Emplyee id by bio-metric id 
            $empDetails = getEmplyeeDetails($row['employee_code']);
            $stepsLog .= date(DATE_ATOM) . " Response received from getEmplyeeDetails => " . json_encode($empDetails) . "\n\n";
            if($empDetails['status'] == 400) {
                $outputRow[] = array_merge($row,[$empDetails['message']]);
                continue;
            }
            
            $row['employee_name'] = $empDetails['message']['emp_name'];
            unset($empDetails['message']['emp_name']);
            $attendance = array_merge($row,$empDetails['message']);
            $attendance['date'] = convertToDateYMD($row['date']);
            $row['date'] = $attendance['date'];
            // check Employee attendance already present or not 
            $attendanceStatus = checkEmployeeAttendancePresentOrNot($attendance['user_id'],$attendance['user_biometric_id'],$attendance['date']); 
            $stepsLog .= date(DATE_ATOM) . " Response received from checkEmployeeAttendancePresentOrNot => " . json_encode($attendanceStatus) . "\n\n";
            if($attendanceStatus['status'] == 400) {
                $outputRow[] = array_merge($row,[$attendanceStatus['message']]);
                continue;
            }

            //if this case true that mark as absent for this case check user apply for leave or not 
            if(empty($attendance['in_time']) && empty($attendance['out_time'])) {
                $weekOff = false;
                // check week off for organization 
                $weekOffStatus = checkWeekOffStatus($attendance['date'],$attendance['organization']);
                if ($weekOffStatus['status'] == 400) {
                    $outputRow[] = array_merge($row,[$weekOffStatus['message']]);
                    continue;
                } else {
                    $attendance_status = 5;
                    $attendance_discription = "Weekly Off";
                    $weekOff = $weekOffStatus['weekOffStatus'];
                }

                if(!$weekOff) {
                    $holidayResponse = checkParticularDayHoliday($attendance['date'],$attendance['organization']);
                    $stepsLog .= date(DATE_ATOM) . " Response received from checkParticularDayHoliday => " . json_encode($holidayResponse) . "\n\n";
                    if($holidayResponse['status'] == 200) {
                        $attendance_status = 4;
                        $attendance_discription = "Holiday for {$holidayResponse['message']}";
                    } else {
                        $leaveWeightage = "leaveType.leaveWeightage = '1'"; 
                        $userFullDayLeaveStatus = checkUserAppliedLeave($attendance['user_id'],$attendance['date'],$leaveWeightage);
                        $stepsLog .= date(DATE_ATOM) . " Response received from userFullDayLeaveStatus => " . json_encode($userFullDayLeaveStatus) . "\n\n";
                        if ($userFullDayLeaveStatus['status'] == '400') {
                            $outputRow[] = array_merge($row,[$userFullDayLeaveStatus['message']]);
                            continue;
                        }

                        $attendance_status = (!is_null($userFullDayLeaveStatus['data'])) ? 3 : 1;
                        $attendance_discription = $userFullDayLeaveStatus['message'];
                    }
                }   
                $attendance['status'] = $attendance_status;
                $attendance['description'] = $attendance_discription;
                $insertResponse =  insertAttendanceRecord($attendance);
                $outputRow[] = array_merge($row,[$insertResponse['message']]);
                continue; 
            }

            // Miss out time punch
            if(!empty($attendance['in_time']) && empty($attendance['out_time'])) {
                $attendance['description'] = $attendance['status'];
                $attendance['status'] = 1;
                $insertResponse =  insertAttendanceRecord($attendance);
                $outputRow[] = array_merge($row,[$insertResponse['message']]);
                continue;
            }

            /**
             * Both In_time and out_time present 
             * 
             * 1) check in_time according to there organization relaxation time if employee beyond that time then late
             * by is difference between in_time - relaxtion time 
             * But here 2 case half day and short leave
             * - if user came in the late by case then check short leave and half day if user applied short leave and came 
             * before 11:30 then not mention any thing in late by and replace with 0:00
             * - if user applied short leave and came after the 11:30 then put the time diff btw in_time - 11:30
             * - Now for the half day if user applied then check user must came before 14:00 if after that then late by 
             */
            if (!empty($attendance['in_time']) && !empty($attendance['out_time'])) {
                list($attendance['late_by'],$attendance['description']) = checkInTimeCase($attendance['user_id'],$attendance['in_time'],$attendance['organization'],$attendance['date']);

                list($attendance['early_by'],$attendance['description']) = checkOutTimeCase($attendance['user_id'],$attendance['out_time'],$attendance['organization'],$attendance['date']);

                $attendance['status'] = 2;
                $attendance['duration'] = calculateLateByTime($attendance['out_time'],$attendance['in_time']);
                $insertResponse =  insertAttendanceRecord($attendance);
                $outputRow[] = array_merge($row,[$insertResponse['message']]);
                continue;
            }
        
        }
        unlink($inputFile);
        $stepsLog .= date(DATE_ATOM) . " :: Attendance Inserted \n\n";
        return ['status' => 200 , 'message' => 'All Attandance inserted' , 'outputRow' => $outputRow];
    } catch (Exception $e) {
        return showResponse(false,"Exception : " . $e->getMessage());
    }
}

function checkOutTimeCase($user_id,$out_time,$organization_id,$date) {
    global $conn,$stepsLog;

    $stepsLog .= date(DATE_ATOM) . " :: mehod inside checkOutTimeCase \n\n";
    $stepsLog .= date(DATE_ATOM) . " :: requested param :: user_id => $user_id , out_time => $out_time , organization_id => $organization_id , date => $date  \n\n";
    $early_by = "00:00";$description = "";
    try {
        $fetchOrganizationSetting_query = "SELECT id, in_time , out_time , relaxation_time FROM `attendance_setting` WHERE organization_id = '$organization_id'";
        $stepsLog .= date(DATE_ATOM) . " :: fetchOrganizationSetting_query => $fetchOrganizationSetting_query \n\n";
        $fetchOrganizationSetting = $conn->query($fetchOrganizationSetting_query);
        $fetchOrganizationSetting = mysqli_fetch_assoc($fetchOrganizationSetting);
        $organizationSetOutTime = $fetchOrganizationSetting['out_time'];
        if( strtotime($out_time) < strtotime($organizationSetOutTime)) {
            $leaveWeightage = "(leaveType.leaveWeightage = '0.25' OR leaveType.leaveWeightage = '0.5')";
            $userAppliedLeave = checkUserAppliedLeave($user_id,$date,$leaveWeightage); 
            if($userAppliedLeave['status'] == 200) {
                // If this case true means any leave is taken either short or casual 
                if(!empty($userAppliedLeave['data'])) {
                    if(strtolower(explode(" ",$userAppliedLeave['meaasge'])[0]) == 'short') {
                        if(strtotime($out_time) > strtotime("16:30")) {
                            $early_by = calCulatEarlyTime($out_time,"16:30");
                            $description = $userAppliedLeave['meaasge'];
                        }
                    } else {
                        if(strtotime($out_time) > strtotime("13:30")) {
                            $early_by = calCulatEarlyTime($out_time,"13:30");
                            $description = $userAppliedLeave['meaasge'];
                        }
                    }
                } else {
                    $early_by = calCulatEarlyTime($out_time,$organizationSetOutTime);
                    $description = "No Short or half dat leave are applied";
                }
            }
        }
        return [$early_by,$description];
    } catch (Exception $e) {
        $stepsLog .= date(DATE_ATOM) . " :: Exception : " . $e->getMessage();
    }
}

/**
 * check in_time according to there organization relaxation time if employee beyond that time then late
 * by is difference between in_time - relaxtion time 
 * But here 2 case half day and short leave
 * - if user came in the late by case then check short leave and half day if user applied short leave and came 
 * before 11:30 then not mention any thing in late by and replace with 0:00
 * - if user applied short leave and came after the 11:30 then put the time diff btw in_time - 11:30
 * - Now for the half day if user applied then check user must came before 14:00 if after that then late by
 */
function checkInTimeCase($user_id,$in_time,$organization_id,$date) {
    global $conn,$stepsLog;

    $stepsLog .= date(DATE_ATOM) . " :: mehod inside checkInTimeCase \n\n";
    $stepsLog .= date(DATE_ATOM) . " :: requested param :: user_id => $user_id , in_time => $in_time , organization_id => $organization_id , date => $date  \n\n";
    $late_by = "00:00"; $description = "";
    try {
        $fetchOrganizationSetting_query = "SELECT id, in_time , out_time , relaxation_time FROM `attendance_setting` WHERE organization_id = '$organization_id'";
        $stepsLog .= date(DATE_ATOM) . " :: fetchOrganizationSetting_query => $fetchOrganizationSetting_query \n\n";
        $fetchOrganizationSetting = $conn->query($fetchOrganizationSetting_query);
        $fetchOrganizationSetting = mysqli_fetch_assoc($fetchOrganizationSetting);
        $relaxation_time = $fetchOrganizationSetting['relaxation_time'];
        
        // condition for check user late by in_time 
        if (strtotime($relaxation_time) < strtotime($in_time)) {
            // if true then check leave on present day 
            $leaveWeightage = "(leaveType.leaveWeightage = '0.25' OR leaveType.leaveWeightage = '0.5')";
            $userAppliedLeave = checkUserAppliedLeave($user_id,$date,$leaveWeightage);
            if($userAppliedLeave['status'] == 200) {
                // If this case true means any leave is taken either short or casual 
                if(!empty($userAppliedLeave['data'])) {
                    if(strtolower(explode(" ",$userAppliedLeave['meaasge'])[0]) == 'short') {
                        if(strtotime($in_time) > strtotime("11:30")) {
                            $late_by = calculateLateByTime($in_time,"11:30");
                            $description = $userAppliedLeave['meaasge'];
                        }
                    } else {
                        if(strtotime($in_time) > strtotime("11:30")) {
                            $late_by = calculateLateByTime($in_time,"14:00");
                            $description = $userAppliedLeave['meaasge'];
                        }
                    }
                } else {
                    $late_by = calculateLateByTime($in_time,$relaxation_time);
                    $description = "No Short or half dat leave are applied";
                }
            }
        }
        return [$late_by,$description];
    } catch (Exception $e) {
        $stepsLog .= date(DATE_ATOM) . " :: Exception : " . $e->getMessage();
    }
}

function calCulatEarlyTime($out_time,$organizationSetOutTime) : string {
    $timeDiff = strtotime($organizationSetOutTime) - strtotime($out_time); 
    $hour = floor($timeDiff/3600);
    $min = floor(($timeDiff % 3600) / 60);
    $early_by = "$hour:$min";
    return $early_by;
}

function calculateLateByTime($in_time,$max_time) : string {
    $timeDiff = strtotime($in_time) - strtotime($max_time);
    $hour = floor($timeDiff/3600);
    $min = floor(($timeDiff % 3600) / 60);
    $late_by = "$hour:$min";
    return $late_by;
}

function insertAttendanceRecord($attendance) {
    global $conn,$stepsLog;

    $stepsLog .= date(DATE_ATOM) . " :: mehod inside insertAttendanceRecord \n\n";
    $stepsLog .= date(DATE_ATOM) . " :: requested param :: attendance_data => " . json_encode($attendance) . " \n\n";
    try {
        $user_id = (int)$attendance['user_id'];
        $biometric_id = (int)$attendance['user_biometric_id'];
        $date = mysqli_real_escape_string($conn, $attendance['date']);
        $in_time = mysqli_real_escape_string($conn, $attendance['in_time'] ?? '');
        $out_time = mysqli_real_escape_string($conn, $attendance['out_time'] ?? '');
        $duration = mysqli_real_escape_string($conn, $attendance['duration'] ?? '00:00');
        $late_by = mysqli_real_escape_string($conn, $attendance['late_by'] ?? '00:00');
        $early_by = mysqli_real_escape_string($conn, $attendance['early_by'] ?? '00:00');
        $punch_records = mysqli_real_escape_string($conn, $attendance['punch_records'] ?? '');
        $status = (int)($attendance['status'] ?? 0);
        $description = mysqli_real_escape_string($conn, $attendance['description'] ?? '');

        // Final query string
        $insertQuery = "INSERT INTO `attendance`(`user_id`, `user_biometric_id`, `attendance_date`, `in_time`, `out_time`, `work_duration`, `late_by`, `early_by`, `punch_in_record`, `status`, `description`) VALUES ($user_id,$biometric_id,'$date','$in_time','$out_time','$duration','$late_by','$early_by','$punch_records',$status,'$description')";

        $stepsLog .= date(DATE_ATOM) . " :: insertattendance_query => $insertQuery \n\n";

        $result = mysqli_query($conn, $insertQuery);

         if ($result) {
            $stepsLog .= date(DATE_ATOM) . " :: Insert into attendance successful \n\n";
            return ['status' => 200, 'message' => 'Inserted attendance successfully'];
        } else {
            throw new Exception("Insert attendance failed: " . mysqli_error($conn));
        }
    } catch (Exception $e) {
        $stepsLog .= date(DATE_ATOM) . " :: Exception : " . $e->getMessage() . " on line : " . $e->getLine();
        return showResponse(false,"Exception : " . $e->getMessage());
    }
}

function getEmplyeeDetails($emp_bio_metric_code) {
    global $conn,$stepsLog;

    $stepsLog .= date(DATE_ATOM) . " :: mehod inside getEmplyeeDetails \n\n";

    try {
        $employeeDetails_query = "SELECT users.ID as `user_id` , users.biometric_id as `user_biometric_id` ,  users.Name as `emp_name` , users.Organization_id as `organization` FROM `users` WHERE users.biometric_id = '$emp_bio_metric_code'";
        $stepsLog .= date(DATE_ATOM) . " :: employeeDetails_query => $employeeDetails_query \n\n";
        $employeeDetails = $conn->query($employeeDetails_query);
        if ($employeeDetails->num_rows > 0) {
            $employeeDetails = mysqli_fetch_assoc($employeeDetails);
            return ['status' => 200 , 'message' => $employeeDetails];
        } else {
            return showResponse(false,"Employee code not found");
        }
    } catch (Exception $e) {
        return showResponse(false,"Exception : " . $e->getMessage());
    }
}


function checkEmployeeAttendancePresentOrNot($user_id,$user_biometric_id,$fromated_date) {
    global $conn,$stepsLog;

    $stepsLog .= date(DATE_ATOM) . " :: mehod inside checkEmployeeAttendancePresentOrNot \n\n";
    $stepsLog .= date(DATE_ATOM) . " :: requested param :: user_id => $user_id , biometric_id => $user_biometric_id , date => $fromated_date \n\n";
    try {    
        $checkEmployeeAttendance_query = "SELECT * FROM `attendance` WHERE `user_id` = '$user_id' AND `user_biometric_id` = '$user_biometric_id' AND attendance_date = '$fromated_date'";
        $stepsLog .= date(DATE_ATOM) . " :: checkEmployeeAttendance_query => $checkEmployeeAttendance_query \n\n";
        $checkEmployeeAttendance = $conn->query($checkEmployeeAttendance_query);
        if($checkEmployeeAttendance->num_rows > 0) {
            return showResponse(false,"Attendance already uploaded");
        } else {
            return ['status' => 200];
        }
    } catch (Exception $e) {
        return showResponse(false,"Exception : " . $e->getMessage());
    }
}

function checkParticularDayHoliday($date,$organization_id) {
    global $conn,$stepsLog;

    $stepsLog .= date(DATE_ATOM) . " :: mehod inside checkParticularDayHoliday \n\n";
    $stepsLog .= date(DATE_ATOM) . " :: requested param :: date => $date , organization => $organization_id \n\n";
    try {
        $checkHoliday_query = "SELECT JSON_UNQUOTE(JSON_SEARCH(holiday, 'one', '$date')) AS `holiday_name` FROM attendance_setting WHERE organization_id = '$organization_id' AND JSON_SEARCH(holiday, 'one', '$date') IS NOT NULL";
        $stepsLog .= date(DATE_ATOM) . " :: checkHoliday_query => $checkHoliday_query \n\n";
        $checkHoliday = $conn->query($checkHoliday_query);
        if($checkHoliday->num_rows > 0) {
            $checkHoliday = mysqli_fetch_assoc($checkHoliday);
            $holiday_name = explode('.',$checkHoliday['holiday_name'])[1];
            return ['status' => 200 , 'message' => $holiday_name];
        } else {
            return showResponse(false,"Not Assign any holiday");    
        }
    } catch (Exception $e) {
        return showResponse(false,"Exception : " . $e->getMessage());
    }
}

/**
 * function for check user applied for full day leave (Earned Leave and casual leave) 
 */
function checkUserAppliedLeave($user_id,$fromated_date,$leaveWeightage) {
    global $conn,$stepsLog,$formatDate;

    $stepsLog .= date(DATE_ATOM) . " :: mehod inside checkUserAppliedLeave \n\n";
    $stepsLog .= date(DATE_ATOM) . " :: requested param :: user_id => $user_id \n\n";
    try {
        $checkUserAppliedLeave_query = "SELECT leave_record.id , leaveType.leaveName as `leaveType_name` , leave_record.leave_type as `leaveType_id` FROM `leave_record` LEFT JOIN leaveType ON leaveType.id = leave_record.leave_type WHERE leave_record.user_id = '$user_id' AND '$fromated_date' BETWEEN leave_record.start_date AND leave_record.end_date AND $leaveWeightage AND leave_record.status = '1' limit 1";
        $stepsLog .= date(DATE_ATOM) . " :: checkUserAppliedLeave_query => $checkUserAppliedLeave_query \n\n";
        $checkUserAppliedLeave = $conn->query($checkUserAppliedLeave_query);
        if($checkUserAppliedLeave->num_rows > 0) {
            $checkUserAppliedLeave = mysqli_fetch_all($checkUserAppliedLeave);
            return ['status' => 200 , 'message' => "{$checkUserAppliedLeave['leaveType_name']} is applied" , "data" => $checkUserAppliedLeave['leaveType_id']];
        } else {
            return ['status' => 200 , 'message' => "Leave not applied mark as absent" , 'data' => null];
        } 
    } catch (Exception $e) {
        return showResponse(false,"Exception : " . $e->getMessage());
    }
}

function checkWeekOffStatus($date,$organization_id) {
    global $conn,$stepsLog;

    $stepsLog .= date(DATE_ATOM) . " :: mehod inside checkWeekOffStatus \n\n";
    $stepsLog .= date(DATE_ATOM) . " :: requested param :: date => $date , organization_id => $organization_id \n\n";
    try {
        $getWeekOffDay_query = "SELECT `week_off` FROM `attendance_setting` WHERE organization_id = '$organization_id'";
        $stepsLog .= date(DATE_ATOM) . " :: getWeekOffDay_query => $getWeekOffDay_query \n\n";
        $getWeekOffDay = $conn->query($getWeekOffDay_query);
        $getWeekOffDay = mysqli_fetch_column($getWeekOffDay);
        $weekOffList = json_decode($getWeekOffDay,true); 
        $day = strtolower(date("l",strtotime($date)));
        return (in_array($day,$weekOffList)) ? ['status' => 200 , 'weekOffStatus' => true] : ['status' => 200 , 'weekOffStatus' => false];
    } catch (Exception $e) {
        $stepsLog .= date(DATE_ATOM) . " :: Exception => " . $e->getMessage();
        return showResponse(false,"Exception : " . $e->getMessage());
    }
}

function checkFileType($uploadFileType) {

    $uploadFileType = strtolower(trim($uploadFileType));
    $requiredType = [
        'application/vnd.ms-excel',
        'text/csv',
        'text/xls',
        'text/xlsx',
        'application/csv',
        'application/vnd.oasis.opendocument.spreadsheet',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ];

    return (in_array($uploadFileType,$requiredType)) ? true : false;
}

function convertToDateYMD($dateString) {
    if (str_contains($dateString, '/')) {
        $dd = array_map(fn($val) => str_pad($val, 2, '0', STR_PAD_LEFT), explode('/', $dateString));
        return "{$dd[2]}-{$dd[0]}-{$dd[1]}"; // m/d/Y → Y-m-d
    } else {
        $dd = array_map(fn($val) => str_pad($val, 2, '0', STR_PAD_LEFT), explode('-', $dateString));
        return "{$dd[2]}-{$dd[1]}-{$dd[0]}"; // d-m-Y → Y-m-d
    }
}

function showResponse($response, $message = "Something went wrong!") {
    global $stepsLog;
    $result = ($response) ? ['status' => 200, 'message' => "$message successfully!"] : ['status' => 400, 'message' => $message]; 
    $stepsLog .= date(DATE_ATOM) . " :: respose => " . json_encode($result) . "\n\n";
    return $result;   
}

function saveLog($response) {
    global $stepsLog;
    $stepsLog .= " ============ End Of Script ================== \n\n";
    $pdf_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/attendance/';
    deleteOldTicketLogs($pdf_dir,5);
    $fh = fopen($pdf_dir . 'insertAttendace_' . date('y-m-d') . '.log' , 'a');
    fwrite($fh,$stepsLog);
    fclose($fh);
    if(isset($response['outputRow'])) {
        $xlsx = SimpleXLSXGen::fromArray($response['outputRow'])->downloadAs('Attendance Status ' . date('h m s') . '.xlsx');
    }
    //echo json_encode($response);
    exit;
}

function deleteOldTicketLogs($logDir, $daysOld = 5) {
    $maxFileAge = $daysOld * 86400; // 5 days in seconds
    if (!is_dir($logDir)) return;
    foreach (glob($logDir . '/createTicket_*.log') as $file) {
        if (is_file($file) && (time() - filemtime($file)) > $maxFileAge) {
            unlink($file);
        }
    }
}
?>
