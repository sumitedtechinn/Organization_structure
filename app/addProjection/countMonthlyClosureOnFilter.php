<?php 

## Database configuration
include '../../includes/db-config.php';
session_start();

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
    $projectionSearchQuery .= " AND organization_id = '".$_SESSION['Organization_id']."'";
    $projectionSearchQuery .= " AND branch_id = '".$_SESSION['Branch_id']."'";
    $projectionSearchQuery .= " AND vertical_id = '".$_SESSION['Vertical_id']."'";
    $projectionSearchQuery .= " AND department_id = '".$_SESSION['Department_id']."'";
}

if(isset($_POST['projectionType']) && !empty($_POST['projectionType']) && $_POST['projectionType'] != 'None') {
    $projectionSearchQuery .= "AND projectionType = '".$_POST['projectionType']."'";
}

if(isset($_POST['user']) && !empty($_POST['user']) && $_POST['user'] != 'None') {
    $projectionSearchQuery .= " AND user_id = '".$_POST['user']."'";
}

if(isset($_POST['month']) && !empty($_POST['month']) && $_POST['month'] != 'None') {
    $projectionSearchQuery .= " AND month = '".$_POST['month']."'";
}

if(isset($_POST['year']) && !empty($_POST['year']) && $_POST['year'] != 'None') {
    $projectionSearchQuery .= " AND year = '".$_POST['year']."'";
}

$projectionDeleteQuery = "Deleted_At IS NULL";

$projections = $conn->query("SELECT ID , numOfClosure FROM `Projection` WHERE $projectionDeleteQuery $projectionSearchQuery");

$total_projection = 0 ; $projection_complete = 0; $projection_pending = 0;
if ($projections->num_rows > 0) {
    while($projection = mysqli_fetch_assoc($projections)) {
        $projection_ids[] = $projection['ID'];
        $total_projection += $projection['numOfClosure']; 
    }

    ## Fetch Record
    $closure_details = $conn->query("SELECT Closure_details.* FROM Closure_details WHERE Closure_details.Projection_id IN (".implode(',',$projection_ids).") AND Closure_details.Deleted_At IS NULL");
    if ($closure_details->num_rows > 0 ) {
        $total_closure = $closure_details->num_rows;
        while($row = mysqli_fetch_assoc($closure_details)) {
            $status = "";
            $doc_received_status = is_null($row['doc_received']) ?  false : true;
            if($doc_received_status) {
                $doc_colse_status = is_null($row['doc_closed']) ? false : true;
                if($doc_colse_status) {
                    $projection_complete++;
                } else {
                    $projection_pending++;         
                }
            } else {
                $projection_pending++;
            }
        }
    }
}

echo json_encode(['total_projection' => $total_projection , 'projection_complete' => $projection_complete , 'projection_pending' => $projection_pending]);

?>