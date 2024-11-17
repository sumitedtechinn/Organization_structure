<?php 

## Database configuration
include '../../includes/db-config.php';
session_start();

if (isset($_REQUEST['admission_ids'])) {

    $admission_ids = mysqli_real_escape_string($conn,$_REQUEST['admission_ids']);
    ## Read value
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length']; // Rows display per page
    $orderby = '';

    if (isset($_POST['order'])) {
        $columnIndex = $_POST['order'][0]['column']; // Column index
        $columnName = $_POST['columns'][$columnIndex]['data']; // Column name 
        $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
    }

    if (isset($columnSortOrder)) {
        $orderby = "ORDER BY admission_details.$columnName $columnSortOrder";
    } else {
        $orderby = "ORDER BY admission_details.id ASC";
    }

    $searchQuery = "";

    ## Total number of records without filtering
    $all_count = $conn->query("SELECT COUNT(id) as `allcount` FROM admission_details WHERE admission_details.id IN ($admission_ids)");
    $records = mysqli_fetch_assoc($all_count);
    $totalRecords = $records['allcount'];

    ## Total number of record with filtering
    $filter_count = $conn->query("SELECT COUNT(id) as `filtered` FROM admission_details WHERE admission_details.id IN ($admission_ids) $searchQuery");
    $records = mysqli_fetch_assoc($filter_count);
    $totalRecordwithFilter = $records['filtered'];

    ## Fetch Record
    $admission_details = $conn->query("SELECT admission_details.* , Projection_type.Name as `projection_type` , IF( admission_details.admission_by != 'self', Closure_details.center_name,'Self') as `adm_byname` FROM `admission_details` LEFT JOIN Projection_type ON Projection_type.ID = admission_details.projectionType LEFT JOIN Closure_details ON Closure_details.id = admission_details.admission_by WHERE admission_details.Deleted_At IS NULL AND admission_details.id IN ($admission_ids) $searchQuery $orderby LIMIT $row , $rowperpage");

    $data = [];
    if ($admission_details->num_rows > 0 ) {
        while( $row = mysqli_fetch_assoc($admission_details)) {
            $data[] = array(
                'ID' => $row['id'],
                'adm_by' => $row['adm_byname'],
                'projection_type' => $row['projection_type'], 
                'projection_id' => $row['projection_id'] , 
                'user_id' => $row['user_id'],
                'adm_number' => $row['numofadmission'],
                'adm_amount' => number_format($row['amount'],2,",",".")
            );
        }
    }

    ## Response
    $response = array(
        "draw" => intval($draw),
        "iTotalRecords" => $totalRecords,
        "iTotalDisplayRecords" => $totalRecordwithFilter,
        "aaData" => $data
    );
}

echo json_encode($response);
?>