<?php

require '../../includes/db-config.php';
session_start();

$optionTag = '';
if($_SESSION['role'] != '2') {

    if (isset($_REQUEST['organization_id']) && isset($_REQUEST['branch_id']) && isset($_REQUEST['vertical_id']) && isset($_REQUEST['department_id'])) {
        $organization_id = mysqli_real_escape_string($conn,$_REQUEST['organization_id']);
        $branch_id = mysqli_real_escape_string($conn,$_REQUEST['branch_id']);
        $vertical_id = mysqli_real_escape_string($conn,$_REQUEST['vertical_id']);
        $department_id = mysqli_real_escape_string($conn,$_REQUEST['department_id']);
        
        $searchQuery = "AND users.Organization_id = '$organization_id' AND users.Branch_id = '$branch_id' AND users.Vertical_id = '$vertical_id' AND users.Department_id = '$department_id' AND users.Assinged_Person_id IS NOT NULL";

        $optionTag = getUser($searchQuery);
    }

} elseif ($_SESSION['role'] == '2') {
    $searchQuery .= " AND users.ID IN (".implode(',',$_SESSION['allChildId']).") AND users.Assinged_Person_id IS NOT NULL";
    $optionTag = getUser($searchQuery);
}

echo $optionTag;

/**
 * On Super Admin Login and Admin login 
 * -- Organization info came in the request body
 * On User login
 * -- Allthe organization info came from session
 * On the basis of organization info we get the user list
 */

function getUser($searchQuery) {
    global $conn;
    $option = '<option value="">Select User</option>';
    $delete_query = "users.Deleted_At IS NULL";
    $users = $conn->query("SELECT users.ID , CONCAT(users.Name,'(',Designation.code,')') as `name` FROM `users` LEFT JOIN Designation ON Designation.ID = users.Designation_id WHERE $delete_query $searchQuery");
    while ($row = mysqli_fetch_assoc($users)) {
        $option .= '<option value = "'.$row['ID'].'">'.$row['name'].'</option>';
    }
    return $option;
}
?>