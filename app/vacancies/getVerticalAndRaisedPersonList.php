<?php
## Database configuration
include '../../includes/db-config.php';
session_start();

$data = '';
$check_data = [];

if (isset($_REQUEST['branch']) && !empty($_REQUEST['branch']) && isset($_REQUEST['typeofdata'])) {

    if(isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
        $id = $_REQUEST['id'];
        $check_data = $conn->query("SELECT Vertical.Vertical_name as `vertical` , Vacancies.Raised_by as `raisedby` FROM `Vacancies` LEFT JOIN Vertical ON Vertical.ID = Vacancies.Vertical_id WHERE Vacancies.ID = $id AND Vacancies.Deleted_At IS null");

        $check_data = mysqli_fetch_assoc($check_data);
    }

    if ($_REQUEST['typeofdata'] == 'vertical') {
        $data = getverticalList();
    } else {
        $data = getVacancyRaisedPersonList();
    }
}

echo $data;

function getverticalList() {

    global $conn;
    global $check_data;
    $branch = $_REQUEST['branch'];
    $option = '<option value="">Select</option>';
    $vertical = $conn->query("SELECT ID , Vertical_name as `vertical` FROM `Vertical` WHERE Branch_id LIKE '%$branch%' AND Deleted_At IS NULL");
    if ($vertical->num_rows > 0 ) {
        while ( $row = mysqli_fetch_assoc($vertical)) {
            if (!empty($check_data)) {
                if ( $check_data['vertical'] == $row['vertical'] ) {
                    $option .= '<option value="'.$row['ID'].'" selected>'.$row['vertical'].'</option>';
                } else {
                    $option .= '<option value="'.$row['ID'].'">'.$row['vertical'].'</option>';
                } 
            } else {
                $option .= '<option value="'.$row['ID'].'">'.$row['vertical'].'</option>';
            }
        }
    }
    return $option;
}

function getVacancyRaisedPersonList() {

    global $conn;
    global $check_data;
    $branch = $_REQUEST['branch'];
    $vertical = $_REQUEST['vertical'];
    $hierarchy = $_REQUEST['hierarchy'];
    $option = '<option value="">Select</option>';
    while($hierarchy > 0) {
        $hierarchy -= 1;
        $person_list = $conn->query("SELECT ID, concat(Name,'(',Designation_code,')') as `Name` FROM users WHERE Branch_id = $branch AND Vertical_id LIKE '%$vertical%' AND Hierarchy_value = '$hierarchy' AND Deleted_At IS NULL");
        if ($person_list->num_rows > 0 ) {
            while($row = mysqli_fetch_assoc($person_list)) {
                if(!empty($check_data)) {
                    if ($check_data['raisedby'] == $row['ID']) {
                        $option .= '<option value="'.$row['ID'].'" selected>'.$row['Name'].'</option>';
                    } else {
                        $option .= '<option value="'.$row['ID'].'">'.$row['Name'].'</option>';    
                    }
                } else {
                    $option .= '<option value="'.$row['ID'].'">'.$row['Name'].'</option>';
                }
            }
        }
    }
    if(!empty($check_data)) {
        if ($check_data['raisedby'] == 0) {
            $option .= '<option value="0" selected >Head</option>';
        } else {
            $option .= '<option value="0">Head</option>';    
        }        
    } else {
        $option .= '<option value="0">Head</option>';
    }
    return $option;
}

?>