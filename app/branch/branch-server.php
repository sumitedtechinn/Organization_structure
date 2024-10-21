<?php 

## Database configuration
include '../../includes/db-config.php';
session_start();

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
    $orderby = "ORDER BY $columnName $columnSortOrder";
} else {
    $orderby = "ORDER BY Start_date ASC";
}

$searchValue = mysqli_real_escape_string($conn, $_POST['search']['value']); // Search value

$searchQuery = "";
if (!empty($searchValue)) {
    $searchQuery = "AND (Branch_name LIKE '%$searchValue%' OR Contact LIKE '%$searchValue%' OR Start_date LIKE '%$searchValue%' OR Pin_code LIKE '%$searchValue%' OR Country LIKE '%$searchValue%' OR State LIKE '%$searchValue%' OR City LIKE '%$searchValue%' OR Address LIKE '%$searchValue%')"; 
}

if (isset($_POST['organization_filter']) && !empty($_POST['organization_filter'])) {
    $searchQuery .= "AND Branch.organization_id = '".$_POST['organization_filter']."'";
}

if($_SESSION['role'] == '2' || $_SESSION['role'] == '3') {
    $searchQuery .= "AND Branch.organization_id = '".$_SESSION['Organization_id']."'";
}

$delete_query = '';

if (isset($_POST['branchtype'])) {
    $delete_query .= "Deleted_At IS NOT NULL";
} else {
    $delete_query .= "Deleted_At IS NULL";
}

## Total number of records without filtering
$all_count = $conn->query("SELECT COUNT(ID) as `allcount` FROM Branch WHERE $delete_query $searchQuery");
$records = mysqli_fetch_assoc($all_count);
$totalRecords = $records['allcount'];

## Total number of record with filtering    
$filter_count = $conn->query("SELECT COUNT(ID) as `filtered` FROM Branch WHERE $delete_query $searchQuery");
$records = mysqli_fetch_assoc($filter_count);
$totalRecordwithFilter = $records['filtered'];

## Fetch Record
$branches = $conn->query("SELECT * FROM Branch WHERE $delete_query $searchQuery $orderby LIMIT $row , $rowperpage");

$data = [];
if ($branches->num_rows > 0) {
    while($row = mysqli_fetch_assoc($branches)) {
        $start_date = date_format(date_create($row["Start_date"]),'d-M-Y');
        $organization = $conn->query("SELECT organization_name FROM `organization` WHERE id = '".$row['organization_id']."'");
        $organization_name = mysqli_fetch_column($organization);
        checkBranchUser($row['organization_id'],$row["ID"]);
        $brancheHead_info = checkBranchUser($row['organization_id'],$row["ID"]);
        if(!$brancheHead_info) {
            $brancheHead_info = "Not Assigned Head";
        }
        $data[] = array(
            "ID" => $row["ID"], 
            "Branch_name" => $row["Branch_name"],
            "Branch_head" => $brancheHead_info,
            "organization_name" => $organization_name, 
            "Contact" => $row["Contact"], 
            "Start_date" => $start_date,
            "Pin_code" => $row["Pin_code"],
            "Country" => $row["Country"],
            "State" => $row["State"] ,
            "City" => $row["City"] ,
            "Address" => $row["Address"],
            "image" => $row['image'],
            "Country_code" => $row['Country_code']
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

echo json_encode($response);

function checkBranchUser($organization_id,$branch_id) {

    global $conn;
    $user_info = [];
    $checkDesignation = $conn->query("SELECT id , CONCAT(designation_name,'(',code,')') as `designation`,color FROM `Designation` WHERE organization_id = '$organization_id' AND branch_id = '$branch_id' AND department_id IS NULL");
    if($checkDesignation->num_rows > 0 ) {
        $i = 0;
        while($row = mysqli_fetch_assoc($checkDesignation)) {
            $checkUser = $conn->query("SELECT Name FROM `users` WHERE Department_id IS NULL AND Branch_id = '$branch_id' AND Vertical_id IS NULL AND Designation_id = '".$row['id']."' AND Organization_id = '$organization_id' AND Deleted_At IS NULL");
            $user_name = [];
            if($checkUser->num_rows > 0) {
                $j = 0;
                while($user = mysqli_fetch_assoc($checkUser)) {
                    $user_name[$j] = $user['Name'];
                    $j++;
                }
            }
            $user_info[$i]['user_name'] = $user_name;
            $user_info[$i]['designation'] = $row['designation'];
            $user_info[$i]['color'] = $row['color'];
            $i++; 
        }
        return $user_info;
    } else {
        return false;
    }
}

?>