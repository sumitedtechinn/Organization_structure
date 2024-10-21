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
    $orderby = "ORDER BY Vacancies.$columnName $columnSortOrder";
} else {
    $orderby = "ORDER BY Vacancies.ID ASC";
}

$searchQuery = "";

if (isset($_POST['selectBranch']) && !empty($_POST['selectBranch'])) {
    $searchQuery .= " AND Vacancies.Branch_id = '".$_POST['selectBranch']."'";
}

if (isset($_POST['selectVertical']) && !empty($_POST['selectVertical'])) {
    $searchQuery .= "AND Vacancies.Vertical_id = '".$_POST['selectVertical']."'";
}

if (isset($_POST['selectOrganization']) && !empty($_POST['selectOrganization'])) {
    $searchQuery .= "AND Vacancies.Organization_id ='".$_POST['selectOrganization']."'";
}

if (isset($_POST['selectDesignation']) && !empty($_POST['selectDesignation'])) {
    $searchQuery .= "AND Vacancies.Designation_id ='".$_POST['selectDesignation']."'";
}

if (isset($_POST['selectDepartment']) && !empty($_POST['selectDepartment'])) {
    $searchQuery .= "AND Vacancies.Department_id ='".$_POST['selectDepartment']."'";
}

if($_SESSION['role'] == '2' || $_SESSION[''] == '3') {
    $searchQuery .= "AND Vacancies.Organization_id = '".$_SESSION['Organization_id']."'";
} 

## Total number of records without filtering
$all_count = $conn->query("SELECT COUNT(ID) as `allcount` FROM Vacancies WHERE Deleted_At IS NULL $searchQuery");
$records = mysqli_fetch_assoc($all_count);
$totalRecords = $records['allcount'];

## Total number of record with filtering
$filter_count = $conn->query("SELECT COUNT(ID) as `filtered` FROM Vacancies WHERE Deleted_At IS NULL $searchQuery");
$records = mysqli_fetch_assoc($filter_count);
$totalRecordwithFilter = $records['filtered'];

## Fetch Record
$vacancies = $conn->query("SELECT Vacancies.* , organization.organization_name as `organization` , Designation.designation_name as `designation` , Department.department_name as `department` , Branch.Branch_name as `branch` , Vertical.Vertical_name as `vertical` , users.Name as `raised_person_name` FROM `Vacancies` LEFT JOIN organization ON organization.id = Vacancies.Organization_id LEFT JOIN Designation ON Designation.ID = Vacancies.Designation_id LEFT JOIN Department ON Department.id = Vacancies.Department_id LEFT JOIN Branch ON Branch.ID = Vacancies.Branch_id LEFT JOIN Vertical ON Vertical.ID = Vacancies.Vertical_id LEFT JOIN users ON users.ID = Vacancies.Raised_by where Vacancies.Deleted_At IS NULL $searchQuery $orderby LIMIT $row , $rowperpage");

$data = [];
if ($vacancies->num_rows > 0) {
    while($row = mysqli_fetch_assoc($vacancies)) {
        $vacancies_fill = $conn->query("SELECT COUNT(users.ID) as `allcount` FROM users where Branch_id = '".$row['Branch_id']."' AND Vertical_id = '".$row['Vertical_id']."' AND Organization_id = '".$row['Organization_id']."' AND Department_id = '".$row['Department_id']."' AND  Designation_id = '".$row['Designation_id']."' AND Assinged_Person_id IS NOT NULL AND Deleted_At IS NULL");
        $numofvacanciesfill = mysqli_fetch_column($vacancies_fill);
        if(($row["NumOfVacanciesRaised"]-$numofvacanciesfill) == 0) {
            $status = "Completed";
        } else {
            $status = "In Progress";
        }
        if($row['raised_person_name'] == null) {
            $row['raised_person_name'] = "Head";
        }
        $data[] = array(
            "id" => $row["ID"], 
            "numofvacancies" => $row["NumOfVacanciesRaised"],
            "numofvacanciesfill" => $numofvacanciesfill,
            "organization" =>  $row['organization'],
            "organization_id" => $row['Organization_id'], 
            "department" => $row['department'], 
            "department_id" => $row['Department_id'],
            "designation" => $row["designation"],
            "designation_id" => $row['Designation_id'] ,  
            "branch" => $row["branch"],
            "branch_id" => $row['Branch_id'],
            "vertical" => $row["vertical"],
            "vertical_id" => $row['Vertical_id'] , 
            "raised_by" => $row["raised_person_name"],
            "raised_by_person_id" => $row['Raised_by'], 
            "status" => $status
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