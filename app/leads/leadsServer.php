<?php
## Database configuration
include '../../includes/db-config.php';

if (isset($_REQUEST['leadsId']) && isset($_REQUEST['leadUrl'])) {
    $url = mysqli_real_escape_string($conn,$_REQUEST['leadUrl']);
    unset($_REQUEST['leadUrl']);
    try {
        $request = [];
        $request = $_REQUEST;
        //$request = json_encode($request);
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);           // Get response as string
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);           // Follow redirects (important for 301)
        curl_setopt($ch, CURLOPT_POST, true);                     // Use POST method
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($request)); // Encode POST fields

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo "cURL Error: " . curl_error($ch);
        } else {
            echo "Response: " . $response;
        }

        curl_close($ch);
        echo $response;
    } catch (Error $e) {
        return json_encode(['status'=>400,'message'=>"Error : ".$e->getMessage()]);
    }
}

?>