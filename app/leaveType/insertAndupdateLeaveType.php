<?php
include '../../includes/db-config.php';
session_start();

$leaveType_details = [];
if (isset($_REQUEST['leaveType_id'])) {
    $leaveTypeId = mysqli_real_escape_string($conn,$_REQUEST['leaveType_id']);
    $leaveType = $conn->query("SELECT * FROM `leaveType` WHERE id = '$leaveTypeId'");
    if($leaveType->num_rows > 0) {
        $leaveType_details = mysqli_fetch_assoc($leaveType); 
    }
}

?>

<!-- Modal -->
<style>

label{
    font-weight: 400;
}
.error {
    color: red;
    font-size: small;
}
</style>
<div class="card-body">
    <div class="border p-4 rounded">
        <div class="card-title d-flex align-items-center justify-content-between">
            <h5 class="mb-0"><?php echo !empty($leaveType_details) ? "Update" : "Add" ?> Leave Type</h5>
            <a type="button" class="close" data-dismiss="modal" id = "hide-modal" aria-hidden="true"><i class="bi bi-x-circle-fill"></i></a>
        </div>
        <hr/> 
        <form role="form" id="form-leaveType" action="/app/leaveType/storeAndupdateLeaveType" method="POST">
            <div class="row">
                <label for="name" class="col-sm-4 col-form-label">Leave Name</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control form-control-sm" name="leave_name" value="<?php echo (!empty($leaveType_details)) ? $leaveType_details['leaveName'] : '' ?>" placeholder="eg: Casual Leave">
                </div>
            </div>
            <div class = "row mb-1">
                <label class="col-sm-4 col-form-label">Number Of Leave</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control form-control-sm" name="numofleave" value="<?php echo (!empty($leaveType_details)) ? $leaveType_details['numOfLeave'] : '' ?>" placeholder="eg: 10">
                </div>
            </div>
            <div class = "row mb-1">
                <label class="col-sm-4 col-form-label">Leave Carry Forward</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control form-control-sm" name="leave_carryforward" value="<?php echo (!empty($leaveType_details)) ? $leaveType_details['leaveCarryForward'] : '' ?>" placeholder="eg: 10">
                </div>
            </div>
            <div class="row mb-1">
                <label class="col-sm-4 col-form-label">Leave Weightage</label>
                <div class="col-sm-8 d-flex" style="margin-top: 0.4rem;gap: 0.7rem;">
                    <div>
                        <input class="form-check-input" type="radio" name="leave_weightage" id="leave_full_day" value="full_day">
                        <label class="form-check-label" value = "leave_full_day">Full Day</label>    
                    </div>
                    <div>
                        <input class="form-check-input" type="radio" name="leave_weightage" id="leave_half_day" value="half_day">
                        <label class="form-check-label" for="leave_half_day">Half Day</label>
                    </div>
                </div>
            </div>
            <hr/>
            <div class="row mb-2">
                <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary"><?php echo !empty($leaveType_details) ? "Update" : "Add" ?></button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="/assets/plugins/jquery-validation/js/jquery.validate.js"></script>

<script>

$(function(){
    $('#form-leaveType').validate({
    rules: {
        leave_name: {required:true},
        numofleave : {required:true},
        leave_carryforward : {required:true},
        leave_weightage : {required:true}
    },
    highlight: function (element) {
        $(element).addClass('error');
        $(element).closest('.form-control').addClass('has-error');
    },
    unhighlight: function (element) {
        $(element).removeClass('error');
        $(element).closest('.form-control').removeClass('has-error');
    },
    errorPlacement: function (error, element) {
        if (element.parent('.input-group').length) {
            error.insertAfter(element.parent());
        } else if (element.is('select')) {
            error.insertAfter(element.next('.select2'));
        } else {
            error.insertAfter(element);
        }
    }
    });
})


$('#hide-modal').click(function() {
    $('.modal').modal('hide');
});

$("#form-leaveType").on('submit',function(e){
    e.preventDefault();
    if ($("#form-leaveType").valid()) {
        var formData = new FormData(this);
        <?php if(!empty($leaveType_details)) { ?>
            formData.append('id','<?=$_REQUEST['leaveType_id']?>');
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
                if (data.status == 200) {
                    $('.modal').modal('hide');
                    toastr.success(data.message);
                    $('.table').DataTable().ajax.reload(null, false);
                } else {
                    toastr.error(data.message);
                }
            }
        });
    }
});

</script>