<?php

require '../../includes/db-config.php';
session_start();

$layout = [];

if (isset($_REQUEST['search_id']) && !empty($_REQUEST['search_id']) && isset($_REQUEST['id_type']) && !empty($_REQUEST['id_type'])) {
    $search_id = mysqli_real_escape_string($conn,$_REQUEST['search_id']);
    $id_type = mysqli_real_escape_string($conn,$_REQUEST['id_type']);

    $where_clause = '';
    if($id_type == 'department') {
        $where_clause = "department_id = '$search_id'";
    } elseif($id_type == 'organization') {
        $where_clause = "branch_id IS NULL AND organization_id = '$search_id'";
    } else {
        $where_clause = "branch_id = '$search_id'";
    }
    getSalesHierarchyData($where_clause);
} else {
    $department = $conn->query("SELECT ID FROM `Designation` LIMIT 1");
    $department = mysqli_fetch_column($department);
    $where_clause = "department_id = '$department'";
    getSalesHierarchyData($where_clause);
}

echo json_encode($layout);

function getSalesHierarchyData($where_clause) {
    global $layout;
    global $conn;
    $designation = $conn->query("SELECT * FROM `Designation` WHERE $where_clause AND Deleted_At IS NULL");
    if($designation->num_rows > 0) {
        while($row = mysqli_fetch_assoc($designation)) {
            $parent_hierarchy = intval($row['hierarchy_value']) - 1; 
            $parent_id = $row['parent_id'];
            $tag = $row['code'];
            $layout[] = array(
                'id' => 'hirarchy_value_'. $row['hierarchy_value'] .'_'. $row['ID'] , 
                'pid' => "hirarchy_value_".$parent_hierarchy."_".$parent_id ,
                "Designation" => $row['designation_name'],
                "Code" => $row['code'],
                "color" => $row['color'],
                "tags" => ["$tag"]  
            );
        }
    }
}


?>