<?php

include '../../includes/db-config.php';
require '../../api/ticket/ValidateJWTToken.php';

$decoded_data = [];

if(isset($_REQUEST['token'])) {
  $token = $_REQUEST['token'];
  $jwtValidator = new ValidateJWTToken($token,$conn);
  $decoded_data = getTokenData();
}

$decoded_data['logo'] = '';
if ($decoded_data['status'] == '400') {
  if($decoded_data['title'] == "Ticket Successfully Re-open") {
    $decoded_data['logo'] = "../../uploads/response_gif/success.gif";
    $decoded_data['messageColor'] = "text-success";
  } else {
    $decoded_data['logo'] = "../../uploads/response_gif/error.gif";
    $decoded_data['messageColor'] = "text-danger";
  }
}

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
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reopen Ticket</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
  <style>
    body {
      background-color: #f4f4f4;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 2rem;
    }

    section {
      background-color: #fff;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      max-width: 600px;
      width: 100%;
    }

    #response_gif {
      height: 100px;
      width: 100px;
      margin-bottom: 1rem;
    }

    textarea {
      resize: vertical;
    }
  </style>
</head>
<body>
  <section>
    <?php if ($decoded_data['status'] == '400') { ?>
      <div class="text-center">
        <img src="<?=$decoded_data['logo']?>" alt="Loading" id="response_gif">
        <h3 class="<?=$decoded_data['messageColor']?>"><?= $decoded_data['title'] ?></h3>
        <p class="text-muted"><?= $decoded_data['message'] ?></p>
      </div>
    <?php } else { ?>
      <h2 class="mb-4">Ticket Review Query Form</h2>
      <form action="" id="reopen-form" method="post" enctype="multipart/form-data">
        <div class="mb-3">
          <label for="message" class="form-label">Please mention the Re-open Reason</label>
          <textarea class="form-control" name="message" id="message" rows="5" required></textarea>
        </div>
        <div class="mb-3">
          <label for="attachment" class="form-label">Attachment (optional)</label>
          <input type="file" class="form-control" name="attachment" id="attachment" accept="image/*,application/pdf">
        </div>
        <div class="d-grid">
          <button type="submit" class="btn btn-primary" id="saveForm">Submit</button>
        </div>
      </form>
    <?php } ?>
  </section>
  
  <script type="text/javascript">
  
    document.getElementById("reopen-form").addEventListener("submit" ,async function(e) {
      e.preventDefault();
      let formData = new FormData(this);
      formData.append("method",'<?=$decoded_data['method']?>');
      formData.append("ticket_id",'<?=$decoded_data['ticket_id']?>');
      let response = await fetch("/app/tickets/storeAndupdateTicket" , {
        method : "POST" , 
        body : formData
      });
      if(!response.ok) {
        console.error(`HTTP error! Status: ${response.status}`);
      }
      const data = await response.json();
      if (data != null) {
        window.location.reload(); // Reload the page on success
      }
    });

  </script>
</body>
</html>
