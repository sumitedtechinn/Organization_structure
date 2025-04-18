<?php

include '../../includes/db-config.php';
require '../../api/ticket/ValidateJWTToken.php';

$token = $_REQUEST['token'];

$jwtValidator = new ValidateJWTToken($token,$conn);
$data_field = getTokenData();

function getTokenData() {
  global $jwtValidator;
  try {
    $decoded_data = $jwtValidator->decodeJWTToken();
    if(isset($decoded_data['status'])) {
      return $decoded_data;
    }
    $decoded_data = $jwtValidator->checkExpireDateAndTime();
    if(isset($decoded_data['status']) && $decoded_data['status'] == '400') {
        return $decoded_data;
    } 
    return $jwtValidator->checkTicketReopenStatus($decoded_data['ticket_id']);
  } catch (Exception $e) {
    return showResponse(false,"Error","Error : "+$e->getMessage());
  }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Close</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <style>
        #response_gif{
            height: 100px;
            width: 100px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
<section>
    <div class="card" id="card_body" style="background-color: #c4bebe57;height:100vh;">
        <div class="card-body" style="margin: auto;">
            <div class="d-flex justify-content-center align-items-center" style="margin-top: 2rem;margin-bottom: 1rem; height: 100%; text-align: center;flex-direction: column;">
                <img src="../../uploads/response_gif/loader.gif" alt="image" id="response_gif">
                <h3 id="title"><?=$data_field['title']?></h3>
                <div class="message" id = "message" style="font-size: medium;"><?=$data_field['message']?></div>
            </div>
        </div>
    </div>
</section>

<script type="text/javascript">

window.onload = updateTicketStatus();

async function fetchData(url,option) {
    try {
        const response = await fetch(url,{
            method: "POST",
            body : JSON.stringify(option)
        });
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('There has been a problem with your fetch operation:', error);
        return null;
    }
}

async function updateTicketStatus() {
    let layout = {
        "loader" : {
            "image" : "../../uploads/response_gif/loader.gif" , 
            "color" : "#c4bebe57"  
        },
        "error" : {
            "image" : "../../uploads/response_gif/error.gif" , 
            "color" : "#e6646438"
        },
        "success" : {
            "image" : "../../uploads/response_gif/success.gif" , 
            "color" : "#E1F4E5"
        }
    };
    let status = '<?=$data_field['status']?>';
    console.log(status);
    if( status == 400) {
        document.getElementById("card_body").style.backgroundColor = layout.error.color;
        document.getElementById("response_gif").src = layout.error.image;
        document.getElementById("title").innerHTML = "<?=$data_field['title']?>";
        return;
    } else {
        document.getElementById("card_body").style.backgroundColor = layout.loader.color;
        document.getElementById("response_gif").src = layout.loader.image;
        document.getElementById("title").innerHTML = "<?=$data_field['title']?>";
    }
    const url = "/app/tickets/storeAndupdateTicket"; 
    const option = {"ticket_id" : '<?=$data_field['ticket_id']?>' , "method" : '<?=$data_field['method']?>'};
    const data = await fetchData(url,option);
    if(data.status == 200) {
        document.getElementById("title").innerHTML = data.message;
        document.getElementById("card_body").style.backgroundColor = layout.success.color;
        document.getElementById("response_gif").src = layout.success.image;
    } else {
        document.getElementById("title").innerHTML = data.message;
        document.getElementById("card_body").style.backgroundColor = layout.error.color;
        document.getElementById("response_gif").src = layout.error.image;
    }
}

</script>

</body>
</html>