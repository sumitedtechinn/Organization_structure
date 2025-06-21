<?php
require '../../includes/db-config.php';
session_start();

$finalRes = [];

if (isset($_REQUEST['method']) && $_REQUEST['method'] == 'setNodeColor') {
    $color = mysqli_real_escape_string($conn,$_REQUEST['color']);
    $table = mysqli_real_escape_string($conn,$_REQUEST['table']);
    $finalRes = setNodeColor($color,$table);
} elseif (isset($_REQUEST['method']) && $_REQUEST['method'] == 'fetchNodeColor') {
    $table = mysqli_real_escape_string($conn,$_REQUEST['table']);
    $finalRes = fetchNodeColor($table);
}

echo json_encode($finalRes);

function setNodeColor($color,$table) {
    global $conn;
    try {
        $update_color = $conn->query("UPDATE $table SET color = '$color'");
        return sendResponse($update_color,ucfirst($table) ." updated successfully!");
    } catch (Exception $e) {
        return sendResponse(false,"Exception : " . $e->getMessage());
    }
}

function fetchNodeColor($tableName) {
    global $conn;
    try {
        $fetchNodeColor = $conn->query("SELECT `color` FROM $tableName WHERE `color` IS NOT NULL and `Deleted_At` IS NULL LIMIT 1");
        $nodeColor = ($fetchNodeColor->num_rows > 0) ? mysqli_fetch_column($fetchNodeColor) : "#fffff";
        
        return sendResponse(true,$nodeColor);
    } catch (Exception $e) {
        return sendResponse(false,"Exception : " . $e->getMessage());
    }
}

function sendResponse($response,$message) : array {
    return ($response) ? ['status' => 200 , 'message' => $message] : ['status' => 400 , 'message' => $message];
}

?>