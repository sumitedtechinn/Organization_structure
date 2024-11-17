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
    $option .= '<option value = "self">Self</option>';
    foreach ($dealCloseCenter_details as $value) {
        $option .= '<option value = "'.$value['id'].'">'.$value['center_name'].'</option>';
    }
    return $option;
}

?>

<div class="card-body mb-2" name = "add_admission_card_<?=$_REQUEST['admission_count']?>" id = "add_admission_card_<?=$_REQUEST['admission_count']?>"> 
    <div class="border p-2 rounded">
        <div class="row mb-1">
            <div class="col-sm-6">
                <label for="name" class="col-sm-12 col-form-label">Admission Center</label>
                <div class="col-sm-12">
                    <select type="text" class="form-control form-control-sm single-select select2" required name="admission_center_<?=$_REQUEST['admission_count']?>" id = "admission_center_<?=$_REQUEST['admission_count']?>">
                        <?=$dealCloseCenter_option?>
                    </select>
                </div>
            </div>
            <div class="col-sm-6">
                <label class="col-sm-12 col-form-label">No. Of Admission</label>
                <div class="col-sm-12">
                    <input type="text" class="form-control form-control-sm" name="numOfAdmission_<?=$_REQUEST['admission_count']?>" id = "numOfAdmission_<?=$_REQUEST['admission_count']?>" required placeholder="eg:- 4">
                </div>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-sm-6">
                <label class="col-sm-12 col-form-label">Admission Amount</label>
                <div class="col-sm-12">
                    <input type="text" class="form-control form-control-sm" name="admission_amount_<?=$_REQUEST['admission_count']?>" id = "admission_amount_<?=$_REQUEST['admission_count']?>" required placeholder="eg:- Rs. 1000" onkeypress="return /[0-9]/i.test(event.key)">
                </div>
            </div>
            <div class="col-sm-6">
                <label class="col-sm-12 col-form-label">Projection Type</label>
                <div class="col-sm-12">
                    <select type="text" class="form-control form-control-sm single-select select2" required name="admission_projection_type_<?=$_REQUEST['admission_count']?>" id = "admission_projection_type_<?=$_REQUEST['admission_count']?>">
                    </select>
                </div>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-sm-12 d-flex justify-content-end gap-1">
                <button type="button" class="btn-sm btn-success" id = "add_admission_card_<?=$_REQUEST['admission_count']?>" onclick="addAdmissionCard(this.id)">Add More</button>
                <button type="button" class="btn-sm btn-danger" id = "remove_admission_card_<?=$_REQUEST['admission_count']?>" onclick="removeAdmissionCard(this.id)">Remove</button>
            </div>
        </div>
    </div>
</div>
<script src="/assets/plugins/select2/js/select2.min.js"></script>
<script src="/assets/js/form-select2.js"></script>
<script>

$(document).ready(function() {
    var projection_type = '';
    var projectionType_form = 'admission';
    $.ajax({
        url : "/app/common/projectionTypeList" ,
        type : "post",
        data : {
            projection_type,
            projectionType_form
        },
        success : function(data) {
            $("#admission_projection_type_<?=$_REQUEST['admission_count']?>").html(data);
        }
    })
});


</script>