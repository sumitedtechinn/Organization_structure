<?php
include '../../includes/db-config.php';
session_start();

$leave_record = [];
if(isset($_REQUEST['leave_id'])) {
    $id = mysqli_real_escape_string($conn,$_REQUEST['leave_id']);
    $leaveDetails = $conn->query("SELECT * FROM `leave_record` WHERE id = '$id'");
    if ($leaveDetails->num_rows > 0 ) {
        $leave_record = mysqli_fetch_assoc($leaveDetails);
    }
}

?>

<style>    
    .select2-container {
        z-index: 999999 !important;
    }
    .iti__country-list{
        z-index: 9999999 !important;
    }
    .error {
        color: red;
        font-size: small;
    }
    label{
        font-weight: 500;
    }
</style>

<!-- Leave Form Modal -->
<div class="card-body">
    <div class="border p-4 rounded shadow">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0 text-primary">Leave Form</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <hr>

        <!-- Form Section -->
        <form id="form-applyLeave" action="/app/leaveRecord/storeAndupdateLeave" method="POST">
            <!-- Leave Type and Start Date -->
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="leave_type" class="form-label">Leave Type</label>
                    <select class="form-select form-select-sm single-select select2" name="leave_type" id="leave_type">
                        <!-- Options will be dynamically loaded -->
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control form-control-sm" name="start_date" id="start_date" value="<?php echo !empty($leave_record) ? $leave_record['start_date'] : '' ?>" min="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>

            <!-- End Date and Leave Days -->
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control form-control-sm" name="end_date" id="end_date" value = "<?php echo !empty($leave_record) ? $leave_record['end_date'] : '' ?>" min="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="col-md-6">
                    <label for="mail_to" class="form-label">Mail To</label>
                    <select class="form-select form-select-sm single-select select2" name="mail_to" id="mail_to" onchange="makeMAilCCUserList(this.value)" <?php if(!empty($leave_record)) { ?> disabled <?php } ?>>
                        <!-- Options will be dynamically loaded -->
                    </select>
                </div>
            </div>

            <!-- Mail To and Mail CC -->
            <div class="mb-3">
                <label for="mail_cc" class="form-label">Mail CC</label>
                <select class="form-select form-select-sm multiple-select select2" multiple="multiple" name="mail_cc[]" id="mail_cc" <?php if(!empty($leave_record)) { ?> disabled <?php } ?> >
                    <!-- Options will be dynamically loaded -->
                </select>
            </div>

            <!-- Subject -->
            <div class="mb-3">
                <label for="mail_subject" class="form-label">Subject</label>
                <input type="text" class="form-control form-control-sm" name="mail_subject" id="mail_subject" placeholder="Enter subject" value="<?php echo !empty($leave_record) ? $leave_record['mail_subject'] : '' ?>" >
            </div>

            <!-- Leave Reason -->
            <div class="mb-3">
                <label for="leave_reason" class="form-label">Leave Reason</label>
                <textarea class="form-control" name="leave_reason" id="leave_reason" rows="4" placeholder="Enter leave reason"><?php echo !empty($leave_record) ? $leave_record['mail_body'] : '' ?></textarea>
            </div>

            <!-- File Upload -->
            <div class="mb-3">
                <label for = "supporting_document" class="form-label">Supporting Doc's <span class="text-primary" style="font-size: x-small;">(If avilable)</span></label>
                <input type="file" class="form-control" name="supporting_document" id="supporting_document" accept="image/*,application/pdf">
            </div>

            <!-- Submit Button -->
            <hr>
            <div class="d-flex justify-content-end align-items-center gap-2">
                <div class="spinner-border text-primary" id="spinner" style="display: none;" role="status"></div>
                <button type="submit" class="btn btn-primary">
                    <?php echo !empty($leave_record) ? "Update Leave" : "Apply Leave"; ?>
                </button>
            </div>
        </form>
    </div>
</div>
<script src="/assets/plugins/jquery-validation/js/jquery.validate.js"></script>
<script src="/assets/plugins/select2/js/select2.min.js"></script>
<script src="/assets/js/form-select2.js"></script>

<script>

$(function(){
    $('#form-applyLeave').validate({
    rules: {
        leave_type: {required:true},
        start_date : {required:true},
        end_date : {required:true},
        mail_to : {required:true},
        //mail_cc : {required:true},
        mail_subject : {
            required:true,
            maxlength: 100,
        },
        leave_reason : {required:true},
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

$(document).ready(function(){
    getLeaveType();
    getUserList();
});

function getLeaveType() {
    let leavetype_id = '';
    <?php if(!empty($leave_record) && isset($leave_record['leave_type'])) { ?>
        leavetype_id = '<?=$leave_record['leave_type']?>';
    <?php } ?>
    $.ajax({
        url : "/app/common/getLeaveType" ,  
        type : 'post',
        data : {
            leavetype_id
        },
        success : function(data) {
            $("#leave_type").html(data);
            if(leavetype_id != '') {
                $("#leave_type").trigger('change');
            }
        }
    });
}

function getUserList() {
    let user_id = '<?=$_SESSION['ID']?>';
    let mail_to = ''; 
    <?php if(!empty($leave_record) && isset($leave_record['mail_to'])) { ?>
        mail_to = '<?=$leave_record['mail_to']?>';
    <?php } ?>
    $.ajax({
        url : "/app/leaveRecord/getListOfUpperHierarchyUser" ,  
        type : "post" , 
        data : {
            user_id,
            mail_to,
            'type' : "mail_to"
        },
        success : function(data) {
            $("#mail_to").html(data);
            if (mail_to != '') {
                $("#mail_to").trigger('change');
            }
        }
    })
}

function makeMAilCCUserList(mail_toUser) {
    let user_id = '<?=$_SESSION['ID']?>';
    let mail_cc = ''; 
    <?php if(!empty($leave_record) && isset($leave_record['mail_cc'])) { ?>
        mail_cc = '<?=$leave_record['mail_cc']?>';
    <?php } ?>
    $.ajax({
        url : "/app/leaveRecord/getListOfUpperHierarchyUser" ,  
        type : "post" , 
        data : {
            user_id,
            mail_toUser,
            mail_cc,
            'type' : "mail_cc"
        },
        success : function(data) {
            $("#mail_cc").html(data);
        }
    })
}

$("#form-applyLeave").on('submit',function(e){
    e.preventDefault();
    if ($("#form-applyLeave").valid()) {
        var formData = new FormData(this);
        <?php if(!empty($leave_record)) { ?>
            formData.append("leave_id",'<?=$_REQUEST['leave_id']?>');
        <?php } ?>
        $("#spinner").css('display','block');
        $.ajax({
            url: this.action,
            type: 'post',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(data) {
                $("#spinner").css('display','none');
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