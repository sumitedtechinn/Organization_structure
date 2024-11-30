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
    $orderby = "ORDER BY users.$columnName $columnSortOrder";
} else {
    $orderby = "ORDER BY users.ID ASC";
}

$searchValue = mysqli_real_escape_string($conn, $_POST['search']['value']); // Search value

$searchQuery = "";
if (!empty($searchValue)) {
    $searchQuery = "AND (users.Name LIKE '%$searchValue%' OR users.Email LIKE '%$searchValue%' OR users.DOJ LIKE '%$searchValue%' OR users.Mobile LIKE '%$searchValue%' OR users.Pincode LIKE '%$searchValue%' OR users.State LIKE '%$searchValue%' OR users.Country LIKE '%$searchValue%' OR users.City LIKE '%$searchValue%' OR users.Address LIKE '%$searchValue%')"; 
}

if( isset($_SESSION['role']) &&  $_SESSION['role'] != 1 ) {
    if($_SESSION['role'] == 2) {
        $searchQuery .= " AND users.Organization_id = '".$_SESSION['Organization_id']."'";
        $searchQuery .= " AND users.Branch_id = '".$_SESSION['Branch_id']."'";
        $searchQuery .= " AND users.Vertical_id = '".$_SESSION['Vertical_id']."'";
        $searchQuery .= " AND users.Department_id ='".$_SESSION['Department_id']."'";
    } elseif ($_SESSION['role'] == 3) {
        $searchQuery .= "AND users.Organization_id = '".$_SESSION['Organization_id']."'";
    }
}

if(isset($_SESSION['role'])) {
    $searchQuery .= "AND users.role != '1'";
}

if (!empty($_POST['organizationFilter'])) {
    $searchQuery .= " AND users.Organization_id = '".$_POST['organizationFilter']."'";
} 

if (!empty($_POST['branchFilter'])) {
    $searchQuery .= " AND users.Branch_id = '".$_POST['branchFilter']."'";
}

if (!empty($_POST['verticalFilter'])) {
    $searchQuery .= " AND users.Vertical_id = '".$_POST['verticalFilter']."'";
}

if (!empty($_POST['departmentFilter'])) {
    $searchQuery .= " AND users.Department_id = '".$_POST['departmentFilter']."'";
}

if (!empty($_POST['designationFilter'])) {
    $searchQuery .= " AND users.Designation_id = '".$_POST['designationFilter']."'";
}

$delete_query = '';
if(isset($_POST['usertype'])) {
    $delete_query = "users.Deleted_At IS NOT NULL";
} else {
    $delete_query = "users.Deleted_At IS NULL";
}

## Total number of records without filtering
$all_count = $conn->query("SELECT COUNT(users.ID) as `allcount` FROM users WHERE $delete_query $searchQuery");
$records = mysqli_fetch_assoc($all_count);
$totalRecords = $records['allcount'];

## Total number of record with filtering
$filter_count = $conn->query("SELECT COUNT(users.ID) as `filtered` FROM users WHERE $delete_query $searchQuery");
$records = mysqli_fetch_assoc($filter_count);
$totalRecordwithFilter = $records['filtered'];

## Fetch Record
$users = $conn->query("SELECT users.* ,Branch.Branch_name as `branch`, organization.organization_name as `organization`, Vertical.Vertical_name as `vertical`, Department.department_name as `department` , roles.guard_name as `role_name` FROM users LEFT JOIN Department ON Department.id = users.Department_id LEFT JOIN organization ON organization.ID = users.Organization_id LEFT JOIN Branch ON Branch.ID = users.Branch_id LEFT JOIN Vertical ON Vertical.ID = users.Vertical_id LEFT JOIN roles ON roles.ID = users.role WHERE $delete_query $searchQuery $orderby LIMIT $row , $rowperpage");

$data = [];
if ($users->num_rows > 0) {
    while($row = mysqli_fetch_assoc($users)) {
        $doj = date_format(date_create($row["DOJ"]),'d-M-Y');
        $password = base64_decode($row['Password']);
        $designation_inside = '';$designation_name = '';
        $organization_info_assign = 'No';
        if ( $row['Designation_id'] != null ) {
            $organization_info_assign = 'Yes';
            $checkAddedInside = $conn->query("SELECT Designation.added_inside as `added_inside` , CONCAT(designation_name,'(',code,')') as `designation_name` FROM users LEFT JOIN Designation ON Designation.ID = users.Designation_id WHERE users.ID = '".$row['ID']."'");
            $checkAddedInside = mysqli_fetch_assoc($checkAddedInside);
            $added_insideList = ['1' => 'InsideOrganization' , '2' => 'InsideDepartment' , '3' => 'InsideVertical' , '4' => 'InsideDepartment'];
            if(array_key_exists($checkAddedInside['added_inside'],$added_insideList)) {
                $designation_inside = $added_insideList[$checkAddedInside['added_inside']];
            }
            $designation_name = $checkAddedInside['designation_name'];    
        }
        $data[] = array(
            "ID" => $row["ID"], 
            "Name" => $row["Name"],
            "Email" => $row["Email"], 
            "Contact" => $row["Mobile"],
            "Country_code" => $row['Country_code'], 
            "doj" => $doj,
            "department" => $row['department'],
            "department_id" => $row['Department_id'],
            "designation_id" =>$row['Designation_id'],
            "designation" => $designation_name,
            "hierarchy_value" => $row['Hierarchy_value'],
            "password" => $password,
            "pincode" => $row['Pincode'],
            "Country" => $row["Country"],
            "State" => $row["State"] ,
            "City" => $row["City"] ,
            "Address" => $row["Address"],
            "image" => $row['Photo'],
            "branch_id" => $row['Branch_id'],
            "branch_name" => $row['branch'],
            "vertical_id" => $row['Vertical_id'],
            "vertical_name" => $row['vertical'],
            "organization_id" => $row['Organization_id'],
            "organization_name" => $row['organization'],
            "assinged_person" => $row['Assinged_Person_id'],
            "role_name" => $row['role_name'],
            "role" => $row['role'],
            "organization_info_assign" => $organization_info_assign , 
            'designation_inside' => $designation_inside
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


?>