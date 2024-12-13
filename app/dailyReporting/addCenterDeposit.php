<?php 
## Database configuration
include '../../includes/db-config.php';
session_start();

$dealCloseCenter_option = dealCloseCenter(); 

function dealCloseCenter() {

    global $conn;
    $dealCloseCenter_details = [];
    $dealCloseCenters = $conn->query("SELECT id , center_name FROM `Closure_details` WHERE doc_prepare IS NOT NULL AND doc_received IS NOT NULL AND doc_closed IS NOT NULL AND Deleted_At IS NULL");
    if ($dealCloseCenters->num_rows > 0 ) {
        while($row = mysqli_fetch_assoc($dealCloseCenters)) {
            $dealCloseCenter_details[] = $row;
        }
    }
    $option = '<option value = "">Select Center</option>';
    foreach ($dealCloseCenter_details as $value) {
        $option .= '<option value = "'.$value['id'].'">'.$value['center_name'].'</option>';
    }
    return $option;
}

?>

<div class="card-body mb-2" name = "add_center_deposit_card_<?=$_REQUEST['center_deposit']?>" id = "add_center_deposit_card_<?=$_REQUEST['center_deposit']?>"> 
    <div class="border p-2 rounded">
        <div class="row mb-1">
            <div class="col-sm-6">
                <label for="name" class="col-sm-12 col-form-label">Center Name</label>
                <div class="col-sm-12">
                    <select type="text" class="form-control form-control-sm single-select select2" required name="deposit_center_<?=$_REQUEST['center_deposit']?>" id = "center_name_<?=$_REQUEST['center_deposit']?>">
                        <?=$dealCloseCenter_option?>
                    </select>
                </div>
            </div>
            <div class="col-sm-6">
                <label class="col-sm-12 col-form-label">Deposit Amount</label>
                <div class="col-sm-12">
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">₹</span>
                        </div>
                        <input type="text" class="form-control form-control-sm" name="deposit_amount_<?=$_REQUEST['center_deposit']?>" id = "deposit_amount_<?=$_REQUEST['center_deposit']?>" required placeholder="eg:- Rs. 50,000" onkeypress="return /[0-9]/i.test(event.key)">
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-sm-12 d-flex justify-content-end gap-1">
                <button type="button" class="btn-sm btn-success" id = "add_center_deposit_card_<?=$_REQUEST['center_deposit']?>" onclick="addCenterDepositCard(this.id)">Add More</button>
                <button type="button" class="btn-sm btn-danger" id = "remove_center_deposit_card_<?=$_REQUEST['center_deposit']?>" onclick="removeCenterDepositCard(this.id)">Remove</button>
            </div>
        </div>
    </div>
</div>
<script src="/assets/plugins/select2/js/select2.min.js"></script>
<script src="/assets/js/form-select2.js"></script>
