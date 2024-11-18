<?php 

## Database configuration
include '../../includes/db-config.php';
session_start();

if (isset($_REQUEST['projection_id'])) {

    $projection_id = mysqli_real_escape_string($conn,$_REQUEST['projection_id']);

    ## Read value
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length']; // Rows display per page
    $orderby = '';
    $data = [];

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

    $searchValue = mysqli_real_escape_string($conn, $_POST['search']['value']); // Search value

    $searchQuery = "";
    if (!empty($searchValue)) {
        $searchQuery = "AND (Closure_details.center_name LIKE '%$searchValue%' OR users.Name LIKE '%$searchValue%')"; 
    }

    ## Total number of records without filtering
    $all_count = $conn->query("SELECT COUNT(admission_details.id) as `allcount` FROM admission_details WHERE admission_details.projection_id = '$projection_id' AND Deleted_At IS NULL");
    $records = mysqli_fetch_assoc($all_count);
    $totalRecords = $records['allcount'];

    ## Total number of record with filtering
    $filter_count = $conn->query("SELECT COUNT(admission_details.id) as `filtered` FROM admission_details LEFT JOIN Closure_details ON Closure_details.id = admission_details.admission_by LEFT JOIN users ON users.ID = admission_details.user_id WHERE admission_details.projection_id = '$projection_id' AND admission_details.Deleted_At IS NULL $searchQuery");
    $records = mysqli_fetch_assoc($filter_count);
    $totalRecordwithFilter = $records['filtered'];

    ## Fetch Record
    $admission_details = $conn->query("SELECT admission_details.* ,Projection_type.Name as `projection_type`, IF(admission_details.admission_by != 'self', Closure_details.center_name , CONCAT('Self','(',users.Name,')')) as `adm_by` FROM `admission_details` LEFT JOIN Closure_details ON Closure_details.id = admission_details.admission_by LEFT JOIN users ON users.ID = admission_details.user_id LEFT JOIN Projection_type ON Projection_type.ID = admission_details.projectionType WHERE admission_details.Deleted_At IS NULL AND admission_details.projection_id = '$projection_id' $searchQuery $orderby LIMIT $row , $rowperpage");

    if ($admission_details->num_rows > 0 ) {
        while( $row = mysqli_fetch_assoc($admission_details)) {
            $createDate = date_format(date_create($row['created_at']),'d-M-Y');
            $data[] = array(
                'ID' => $row['id'],
                'adm_by' => $row['adm_by'],
                'projection_type' => $row['projection_type'], 
                'projection_id' => $row['projection_id'] , 
                'user_id' => $row['user_id'],
                'adm_number' => $row['numofadmission'],
                'adm_amount' => number_format($row['amount'],2,",","."),
                'adm_date' => $createDate
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