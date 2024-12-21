<?php 

## Database configuration
include '../../includes/db-config.php';
session_start();

$closure_data = ['center_total_projection_number' => 0 , 'center_completed_projection_number' => 0 , 'center_pending_projection_number' => 0 , 'admission_total_projection_number' => 0 , 'admission_completed_projection_number' => 0 , 'admission_pending_projection_number' => 0];

$projection_type = $conn->query("SELECT 
GROUP_CONCAT(CASE WHEN Name LIKE '%Center%' OR Name LIKE '%center%' THEN id END) AS center_id,
GROUP_CONCAT(CASE WHEN Name LIKE '%Admission%' OR Name LIKE '%admission%' THEN id END) AS admission_id
FROM `Projection_type` WHERE Name LIKE '%Center%' OR Name LIKE '%center%' OR Name LIKE '%Admission%' OR Name LIKE '%admission%'");

$projection_type = mysqli_fetch_assoc($projection_type);

$center_projectionTypeId = !is_null($projection_type['center_id']) ? $projection_type['center_id'] : ''; 
$admission_projectionTypeId = !is_null($projection_type['admission_id']) ? $projection_type['admission_id'] : '';;

$center_projectionSearchQuery = '';$admission_projectionSearchQuery = '';
if(!empty($center_projectionTypeId)) {
    $center_projectionSearchQuery = " AND projectionType IN ($center_projectionTypeId)"; 
}

if(!empty($admission_projectionTypeId)) {
    $admission_projectionSearchQuery = " AND projectionType IN ($admission_projectionTypeId)";
}

$projectionSearchQuery = '';

if(isset($_SESSION['role']) && $_SESSION['role'] == '3') {
    $projectionSearchQuery .= " AND organization_id = '".$_SESSION['Organization_id']."'";
}

if(isset($_POST['organization']) && !empty($_POST['organization']) && $_POST['organization'] != 'None') {
    $projectionSearchQuery .= " AND organization_id = '".$_POST['organization']."'";
}

if(isset($_POST['branch']) && !empty($_POST['branch']) && $_POST['branch'] != 'None') {
    $projectionSearchQuery .= " AND branch_id = '".$_POST['branch']."'";
}

if(isset($_POST['vertical']) && !empty($_POST['vertical']) && $_POST['vertical'] != 'None') {
    $projectionSearchQuery .= " AND vertical_id = '".$_POST['vertical']."'";
}

if(isset($_POST['department']) && !empty($_POST['department']) && $_POST['department'] != 'None') {
    $projectionSearchQuery .= " AND department_id = '".$_POST['department']."'";
}

if(isset($_SESSION['role']) && $_SESSION['role'] == '2') {
    $projectionSearchQuery .= " AND user_id IN (".implode(',',$_SESSION['allChildId']).")";
}

if(isset($_POST['projectionType']) && !empty($_POST['projectionType']) && $_POST['projectionType'] != 'None') {
    $projectionSearchQuery .= " AND projectionType = '".$_POST['projectionType']."'";
}

if(isset($_POST['user']) && !empty($_POST['user']) && $_POST['user'] != 'None') {
    $projectionSearchQuery .= " AND user_id = '".$_POST['user']."'";
}

if(isset($_POST['month']) && !empty($_POST['month']) && $_POST['month'] != 'None') {
    if ($_POST['month'] != 13) {
        $projectionSearchQuery .= " AND month = '".$_POST['month']."'";
    }
}

if(isset($_POST['year']) && !empty($_POST['year']) && $_POST['year'] != 'None') {
    $projectionSearchQuery .= " AND year = '".$_POST['year']."'";
}

countProjectionData($center_projectionSearchQuery,'center');
countProjectionData($admission_projectionSearchQuery,'admission');


function countProjectionData($projectionTypeSearchIds,$type) {

    global $conn;
    global $closure_data;
    global $projectionSearchQuery;

    $projectionDeleteQuery = "Deleted_At IS NULL";

    $projections = $conn->query("SELECT ID,numOfClosure FROM `Projection` WHERE $projectionDeleteQuery $projectionSearchQuery $projectionTypeSearchIds");

    $projection_ids = [];
    if ($projections->num_rows > 0) {
        while($projection = mysqli_fetch_assoc($projections)) {
            $projection_ids[] = $projection['ID'];
            $closure_data[$type.'_total_projection_number'] += $projection['numOfClosure']; 
        }

        if($type == 'center') {
            ## Fetch Record
            $closure_details = $conn->query("SELECT Closure_details.* FROM Closure_details WHERE Closure_details.Projection_id IN (".implode(',',$projection_ids).") AND Closure_details.Deleted_At IS NULL");
            if ($closure_details->num_rows > 0 ) {
                while($row = mysqli_fetch_assoc($closure_details)) {
                    $status = "";
                    $doc_received_status = is_null($row['doc_received']) ?  false : true;
                    if($doc_received_status) {
                        $doc_colse_status = is_null($row['doc_closed']) ? false : true;
                        if($doc_colse_status) {
                            $closure_data[$type.'_completed_projection_number']++;
                        } else {
                            $closure_data[$type.'_pending_projection_number']++;
                        }
                    } else {
                        $closure_data[$type.'_pending_projection_number']++;
                    }
                }
            }
        } else {
            ## Fetch Record
            $admission_details = $conn->query("SELECT admission_details.* FROM `admission_details` WHERE admission_details.projection_id IN (".implode(',',$projection_ids).") AND admission_details.Deleted_At IS NULL");
            if($admission_details->num_rows > 0 ) {
                while($row = mysqli_fetch_assoc($admission_details)) {
                    $closure_data[$type.'_completed_projection_number'] += $row['numofadmission']; 
                }
            }
            $closure_data[$type.'_pending_projection_number'] = $closure_data[$type.'_total_projection_number'] - $closure_data[$type.'_completed_projection_number'];
        }
    }
}

echo json_encode($closure_data);

?>