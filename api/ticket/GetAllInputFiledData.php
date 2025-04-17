<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

## Database configuration
include '../../includes/db-config.php';
//session_start();

$obj = new GetAllInputFiledData();
$obj->conn = $conn;
$obj->request = file_get_contents('php://input');
try {
    if(!empty($obj->request)) {
        $obj->stepsLog = date(DATE_ATOM) . ": request received => $obj->request \n\n";
        $obj->request = json_decode($obj->request, true);
        foreach ($obj->request as $value) {
            $function_name = "get". ucfirst($value['name']);
            $role = $value['role'];
            //$select_value = $value['value'];
            $response = $obj->$function_name($role);
            $obj->stepsLog .= date(DATE_ATOM) ." Response received from $function_name => $response \n";
            $obj->finalRes[$value['name']] = $response;
        }
    } else {
        $obj->finalRes['error'] = "Empty@@##@@Empty reqeust found";
    }
} catch (Exception $e) {
    $obj->finalRes['exception'] = "Exception@@##@@" . $e->getMessage();
    $obj->stepsLog = date(DATE_ATOM) . ": got exception => " . $e->getMessage() . " in file => " . $e->getFile() . " on line => " . $e->getLine();
} catch (Error $e) {
    $obj->finalRes['error'] = "Error@@##@@" . $e->getMessage();
    $obj->stepsLog = date(DATE_ATOM) . ": got error => " . $e->getMessage() . " in file => " . $e->getFile() . " on line => " . $e->getLine();
} finally {
    $obj->saveLog();
}

class GetAllInputFiledData {
    
    public $conn; 
    public $stepsLog; 
    public $request;
    public $finalRes;

    public function getCategory($role) : string {
        
        $this->stepsLog .= date(DATE_ATOM). " :: method inside the getCategory \n\n";
        try{
            $dropDown = "";
            $role_json = json_encode([$role]);
            $allCategory_query = "SELECT GROUP_CONCAT(ticket_category.id,'@@',ticket_category.name) as `category` , Department.id as `department_id` , Department.department_name as `department_name` FROM `ticket_category` LEFT JOIN Department ON Department.id = ticket_category.department WHERE JSON_CONTAINS(ticket_category.erpRole, '$role_json') GROUP BY ticket_category.department";
            $this->stepsLog .= date(DATE_ATOM) . " allCategory query => $allCategory_query \n";
            $allCategory = $this->conn->query($allCategory_query);
            if ($allCategory->num_rows > 0) {
                while ($department_category = mysqli_fetch_assoc($allCategory)) {
                    $depart_name = $department_category['department_name'];
                    $depart_id = $department_category['department_id'];
                    $optiongroup = '<optgroup label = "'.$depart_name.'">';
                    $category = explode(',',$department_category['category']);
                    foreach ($category as $key => $value) {
                        list($category_id,$category_name) = explode('@@',$value);
                        $optiongroup .= '<option value="'.$category_id.'##'.$depart_id.'">'.$category_name.'</option>';                        
                    }
                    $optiongroup .= '</optgroup>';
                    $dropDown .= $optiongroup;
                }
            }
            return $dropDown;
        } catch (Error $e) {
            return "Error@@##@@" . $e->getMessage() . " on line : " . $e->getLine();
        }    
    }

    public function saveLog() {

        $this->stepsLog .= " ============ End Of Script ================== \n\n";
        $pdf_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/ticket_log/';
        $this->deleteOldTicketLogs($pdf_dir,5);
        $fh = fopen($pdf_dir . 'apiTicketFilter_' . date('y-m-d') . '.log' , 'a');
        fwrite($fh,$this->stepsLog);
        fclose($fh);
        echo json_encode($this->finalRes);
        exit;
    }

    public function deleteOldTicketLogs($logDir, $daysOld = 5) {
        $maxFileAge = $daysOld * 86400; // 5 days in seconds
        if (!is_dir($logDir)) return;
        foreach (glob($logDir . '/apiTicketFilter_*.log') as $file) {
            if (is_file($file) && (time() - filemtime($file)) > $maxFileAge) {
                unlink($file);
            }
        }
    }
}

?>