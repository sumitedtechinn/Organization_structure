<?php
include '../../includes/db-config.php';
session_start();

$data_field = file_get_contents('php://input'); // by this we get raw data
$data_field = json_decode($data_field,true);
$function_name = $data_field['requestType'];

$response = match($function_name) {
    "userLeaveDetails" => call_user_func($function_name),    
};

echo $response;

function userLeaveDetails() : string {

    global $conn;
    try{
        $fullDayLeave_details = $conn->query(fullDayLeaveQuery());
        $fullDayLeave_details = mysqli_fetch_assoc($fullDayLeave_details);
        $currentMonthLeaveUsed = mysqli_real_escape_string($conn,$fullDayLeave_details['full_day_current']);
        $lastMonthLeaveUsed = mysqli_real_escape_string($conn,$fullDayLeave_details['full_day_last_month']);
        $SecondLastMonthUsed = mysqli_real_escape_string($conn,$fullDayLeave_details['full_day_2nd_last_month']);
    
        $carryForwardLeave = 0;
        if ($SecondLastMonthUsed <= 1) {
            ++$carryForwardLeave;
        }
        if($lastMonthLeaveUsed <= 1) {
            ++$carryForwardLeave;
        }
    
        $totalAavailiableLeave = intval(2 + $carryForwardLeave) - intval($currentMonthLeaveUsed);
        if ($totalAavailiableLeave < 0 ) {
            $totalAavailiableLeave = 0;
        }
        
        $halfDayLeave = $conn->query(halfDayLeaveQuery());
        $halfDayLeaveUsed = mysqli_fetch_column($halfDayLeave);
        $halfDayLeaveAvailable = ($halfDayLeaveUsed == 0) ? '1' : '0';
    
        $restrictedLeave = $conn->query(restrictedLeaveQuery());
        $restrictedLeaveUsed = mysqli_fetch_column($restrictedLeave);
        $restrictedLeaveAvailiable = 2- intval($restrictedLeaveUsed);

        return json_encode(['status' => 200, 'halfDayLeaveUsed' => $halfDayLeaveUsed , 'halfDayLeaveAvailiable' => $halfDayLeaveAvailable , 'fullDayLeaveUsed' => $currentMonthLeaveUsed , 'fullDayLeaveAvailable' => "$totalAavailiableLeave"  , "restrictedLeaveUsed" => $restrictedLeaveUsed , "restrictedLeaveAvailiable" => $restrictedLeaveAvailiable]);
    
    } catch (Error $e) {
        return json_encode(['status' => 400, 'message' => $e->getMessage() ]);
    }
}

function halfDayLeaveQuery() : string {

    $query = "SELECT SUM(CASE WHEN leave_type = '3' AND MONTH(start_date) = MONTH(CURRENT_DATE()) AND YEAR(start_date) = YEAR(CURRENT_DATE()) AND status = '1' THEN DATEDIFF(end_date,start_date) + 1 ELSE 0 END) AS `half_day_used` FROM `leave_record` WHERE user_id = '".$_SESSION['ID']."'";
    return $query;
}

function restrictedLeaveQuery() : string {
    $query = "SELECT SUM(CASE WHEN leave_type = '4' AND YEAR(start_date) = YEAR(CURRENT_DATE()) AND status = '1' THEN DATEDIFF(end_date,start_date)+1 ELSE 0 END) as `restricted_day_used` FROM leave_record WHERE user_id = '".$_SESSION['ID']."'";
    return $query;
}

function fullDayLeaveQuery() : string {

    $id = $_SESSION['ID'];
    $query = "
    SELECT SUM( CASE 
        WHEN (leave_type = 1 OR leave_type = 2) 
            AND status = 1 
            AND MONTH(start_date) = MONTH(CURRENT_DATE()) 
            AND YEAR(start_date) = YEAR(CURRENT_DATE()) 
        THEN 
            CASE 
                WHEN MONTH(end_date) = MONTH(CURRENT_DATE()) 
                THEN DATEDIFF(end_date, start_date) + 1 
                ELSE DATEDIFF(LAST_DAY(start_date), start_date) + 1
            END 
        WHEN (leave_type = 1 OR leave_type = 2) 
            AND status = 1 
            AND MONTH(end_date) = MONTH(CURRENT_DATE()) 
            AND YEAR(end_date) = YEAR(CURRENT_DATE()) 
        THEN DATEDIFF(end_date,DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01')) + 1
        ELSE 0 
    END
    ) AS full_day_current,
    SUM(
    CASE 
        WHEN (leave_type = 1 OR leave_type = 2) 
            AND status = 1 
            AND MONTH(start_date) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) 
            AND YEAR(start_date) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH) 
        THEN 
            CASE 
                WHEN MONTH(end_date) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) 
                THEN DATEDIFF(end_date, start_date) + 1 
                ELSE DATEDIFF(LAST_DAY(start_date), start_date) + 1
            END 
        WHEN (leave_type = 1 OR leave_type = 2) 
            AND status = 1 
            AND MONTH(end_date) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) 
            AND YEAR(end_date) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH) 
        THEN DATEDIFF(end_date,DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01')) + 1
        ELSE 0 
    END
    ) AS full_day_last_month,
    SUM(
    CASE 
        WHEN (leave_type = 1 OR leave_type = 2) 
            AND status = 1 
            AND MONTH(start_date) = MONTH(CURRENT_DATE() - INTERVAL 2 MONTH) 
            AND YEAR(start_date) = YEAR(CURRENT_DATE() - INTERVAL 2 MONTH) 
        THEN 
            CASE 
                WHEN MONTH(end_date) = MONTH(CURRENT_DATE() - INTERVAL 2 MONTH) 
                THEN DATEDIFF(end_date, start_date) + 1 
                ELSE DATEDIFF(LAST_DAY(start_date), start_date) + 1
            END 
        WHEN (leave_type = 1 OR leave_type = 2) 
            AND status = 1 
            AND MONTH(end_date) = MONTH(CURRENT_DATE() - INTERVAL 2 MONTH) 
            AND YEAR(end_date) = YEAR(CURRENT_DATE() - INTERVAL 2 MONTH) 
        THEN DATEDIFF(end_date,DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01')) + 1
        ELSE 0 
    END
    ) AS full_day_2nd_last_month
    FROM 
    leave_record
    WHERE 
    user_id = '$id'";
    return $query;  
}

?>