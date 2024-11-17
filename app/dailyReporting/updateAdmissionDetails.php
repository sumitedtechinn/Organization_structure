<?php 
## Database configuration
include '../../includes/db-config.php';
session_start();

$admission_details = [];
if(isset($_REQUEST['id'])) {
    $id = mysqli_real_escape_string($conn,$_REQUEST['id']);
    $admission = $conn->query("SELECT * FROM `admission_details` WHERE id = '$id'");
    $admission_details = mysqli_fetch_assoc($admission);
}

$dealCloseCenter_option = dealCloseCenter(); 

function dealCloseCenter() {

    global $conn;
    global $admission_details;
    $dealCloseCenter_details = [];
    $dealCloseCenters = $conn->query("SELECT id , center_name FROM `Closure_details` WHERE doc_prepare IS NOT NULL AND doc_received IS NOT NULL AND doc_closed IS NOT NULL AND Deleted_At IS NULL");
    if ($dealCloseCenters->num_rows > 0 ) {
        while($row = mysqli_fetch_assoc($dealCloseCenters)) {
            $dealCloseCenter_details[] = $row;
        }
    }
    $option = '<option value = "">Select Center</option>';
    $option .= (!empty($admission_details) && $admission_details['admission_by'] == 'self') ? '<option value = "self" selected >Self</option>' : '<option value = "self">Self</option>';
    foreach ($dealCloseCenter_details as $value) {
        $option .= (!empty($admission_details) && $admission_details['admission_by'] == $value['id']) ? '<option value = "'.$value['id'].'" selected >'.$value['center_name'].'</option>' : '<option value = "'.$value['id'].'">'.$value['center_name'].'</option>';
    }
    return $option;
}

?>

<style>
    .select2-container {
        z-index: 999999 !important;
    }
    .error {
        color: red;
        font-size: small;
    }
</style>

<div class="card-body">
    <div class="border p-4 rounded">
        <div class="card-title d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Update Admission</h5>
            <a type="button" class="close" data-dismiss="modal" id = "hide-modal" aria-hidden="true"><i class="bi bi-x-circle-fill"></i></a>
        </div>
        <hr/>
        <form role="form" id="form-admission" action="/app/dailyReporting/storeAdmissionDetails" method="POST">
            <div class="row mb-1">
                <div class="col-sm-6">
                    <label for="name" class="col-sm-12 col-form-label">Admission Center</label>
                    <div class="col-sm-12">
                        <select type="text" class="form-control form-control-sm single-select select2" required name="admission_center" id = "admission_center">
                            <?=$dealCloseCenter_option?>
                        </select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">No. Of Admission</label>
                    <div class="col-sm-12">
                        <input type="text" class="form-control form-control-sm" name="numOfAdmission" id = "numOfAdmission" required value="<?=$admission_details['numofadmission']?>" placeholder="eg:- 4">
                    </div>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Admission Amount</label>
                    <div class="col-sm-12">
                        <input type="text" class="form-control form-control-sm" name="admission_amount" id = "admission_amount" required value="<?=$admission_details['amount']?>" placeholder="eg:- Rs. 1000" onkeypress="return /[0-9]/i.test(event.key)">
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Projection Type</label>
                    <div class="col-sm-12">
                        <select type="text" class="form-control form-control-sm single-select select2" required name="admission_projection_type" id = "admission_projection_type" disabled>
                        </select>
                    </div>
                </div>
            </div>
            <hr/>
            <div class="row mb-2">
                <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary" onclick="updateAdmissionDetails()">Update</button>
                </div>
            </div>
        </form>        
    </div>
</div>

<script src="/assets/plugins/jquery-validation/js/jquery.validate.js"></script>
<script src="/assets/plugins/select2/js/select2.min.js"></script>
<script src="/assets/js/form-select2.js"></script>
<script>

$(document).ready(function() {
    var projection_type = '';
    <?php if(!empty($admission_details)) { ?>
        projection_type = '<?=$admission_details['projectionType']?>';
    <?php } ?>
    var projectionType_form = 'admission';
    $.ajax({
        url : "/app/common/projectionTypeList" ,
        type : "post",
        data : {
            projection_type,
            projectionType_form
        },
        success : function(data) {
            $("#admission_projection_type").html(data);
        }
    })
});

$(function(){
    $('#form-admission').validate({
    rules: {
        admission_center: {required:true}, 
        numOfAdmission : {required:true} ,
        admission_amount : {required:true},
        admission_projection_type : {required:true}
    },
    highlight: function (element) {
        $(element).addClass('error');
        $(element).closest('.form-control').addClass('has-error');
    },
    unhighlight: function (element) {
        $(element).removeClass('error');
        $(element).closest('.form-control').removeClass('has-error');
    }
    });
})

$("#form-admission").on('submit',function(e){
    e.preventDefault();
    if ($("#form-admission").valid()) {
        var formData = new FormData(this);
        <?php if(isset($_REQUEST['id'])) { ?>
            formData.append('id',<?=$_REQUEST['id']?>);
        <?php } ?>
        $.ajax({
            url: this.action,
            type: 'post',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(data) {
                if(data.status == 200) {
                    toastr.success(data.message);
                    $('.modal').modal('hide');
                } else {
                    toastr.error(data.message);
                }   
            }
        });
    }
});

</script>