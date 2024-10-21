<?php 

include '../../includes/db-config.php';
session_start();

$projection_type_details = []; 
if (isset($_REQUEST['id'])) {
    $projection_type = $conn->query("SELECT * FROM `Projection_type` WHERE ID = '".$_REQUEST['id']."'");
    $projection_type_details = mysqli_fetch_assoc($projection_type);
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

<!-- Modal -->
<div class="card-body">
    <div class="border p-4 rounded">
        <div class="card-title d-flex align-items-center justify-content-between">
            <h5 class="mb-0">
                <?php if (!empty($projection_type_details)) { ?>
                    Update
                <?php } else { ?>
                    Add
                <?php } ?>  
                Projection Type</h5>
            <a type="button" class="close" data-dismiss="modal" id = "hide-modal" aria-hidden="true"><i class="bi bi-x-circle-fill"></i></a>
        </div>
        <hr/>
        <form role="form" id="form-projectionType" action="/app/projectionType/storeAndupdateProjectionType" method="POST">
            <div class="row mb-2">
                <label for="name" class="col-sm-4 col-form-label">Name</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control form-control-sm" name="name" value="<?php echo !empty($projection_type_details) ?  $projection_type_details['Name'] : '' ?>" placeholder="eg: B2B Center">
                </div>
            </div>
            <div class="row mb-2">
                <label class="col-sm-4 col-form-label">Department</label>
                <div class="col-sm-8">
                    <select type="text" class="form-control form-control-sm single-select select2" name="department" id="department" onchange="getBranchList(this.value)">
                    </select>
                </div>
            </div>
            <div class="row mb-2">
                <label class="col-sm-4 col-form-label">Branch</label>
                <div class="col-sm-8">
                    <select type="text" class="form-control form-control-sm single-select select2" name="branch" id="branch">
                    </select>
                </div>
            </div>
            <hr/>
            <div class="row mb-2">
                <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary">
                    <?php if (!empty($projection_type_name)) { ?>
                        Update
                    <?php } else { ?>
                        Register
                    <?php } ?>    
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>


<script src="/assets/plugins/jquery-validation/js/jquery.validate.js"></script>
<script src="/assets/plugins/select2/js/select2.min.js"></script>
<script src="/assets/js/form-select2.js"></script>
<script>

$(function(){
    $('#form-projectionType').validate({
    rules: {
        name: {required:true},
        department : {required:true} , 
        branch : {required:true}
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
});

$(document).ready(function(){
    var department_id = '';
    <?php if(!empty($projection_type_details)) { ?>
        department_id = '<?=$projection_type_details['department_id']?>';
    <?php } ?>
    $.ajax({
        url : "/app/common/departmentList",
        type : "post",
        data : {
            department_id
        },
        success : function(data) {
            $("#department").html(data);
            <?php if(!empty($projection_type_details)) { ?>
                $("#department").trigger('change');
            <?php } ?>
        }
    });
});

function getBranchList(department_id) {
    var branch_id = ''; var organization_id = '';
    <?php if(!empty($projection_type_details)) { ?>
        branch_id = <?=$projection_type_details['branch_id']?>;
    <?php } ?>
    $.ajax({
        url : "/app/common/branchList" ,
        type : 'post',
        data : {
            branch_id,
            department_id,
            organization_id
        },
        success : function(data) {
            $("#branch").html(data);
            <?php if(!empty($projection_type_details['Branch_id'])) { ?>
                $("#branch").trigger('change');
            <?php } ?>
        }
    });    
}


$('#hide-modal').click(function() {
    $('.modal').modal('hide');
});

$("#form-projectionType").on('submit',function(e){
    e.preventDefault();
    if ($("#form-projectionType").valid()) {
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
