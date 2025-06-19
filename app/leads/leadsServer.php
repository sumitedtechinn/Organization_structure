<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include '../../includes/db-config.php';

if (isset($_REQUEST['leadsId']) && isset($_REQUEST['leadUrl'])) {
    $url = mysqli_real_escape_string($conn, $_REQUEST['leadUrl']);
    unset($_REQUEST['leadUrl']);

    try {
        $requestData = $_REQUEST;
        $jsonData = json_encode($requestData);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);

        // Pass data in header instead of POST body
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-Custom-Data: ' . base64_encode($jsonData) // Or just use $jsonData if the server can parse it
        ]);

        // Don't send body
        curl_setopt($ch, CURLOPT_POSTFIELDS, ''); // Empty body

        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400) {
            throw new Exception("Request failed with HTTP code $httpCode");
        }
        echo $response;
    } catch (Exception $e) {
        echo json_encode(['status' => 400, 'message' => "Error: " . $e->getMessage()]);
    }
}
?>
