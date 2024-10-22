<?php 

include '../../includes/db-config.php';
session_start();

$projection_details = []; 
if (isset($_REQUEST['projection_id'])) {
    $projection = $conn->query("SELECT * FROM `Projection` WHERE ID = '".$_REQUEST['projection_id']."'");
    $projection_details = mysqli_fetch_assoc($projection);
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
                <?php if (!empty($projection_details)) { ?>
                    Update
                <?php } else { ?>
                    Add
                <?php } ?>  
                Projection</h5>
            <a type="button" class="close" data-dismiss="modal" id = "hide-modal" aria-hidden="true"><i class="bi bi-x-circle-fill"></i></a>
        </div>
        <hr/>
        <form role="form" id="form-projection" action="/app/addProjection/storeAndupdateProjection" method="POST">
            <div class="row mb-1">
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Projection Type</label>
                    <div class="col-sm-12">
                        <select type="text" class="form-control form-control-sm single-select select2" name="projection_type" id = "projection_type" onchange="getUserList(this.value)" <?php if(!empty($projection_details)) { ?> disabled <?php } ?>>
                        </select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">User</label>
                    <div class="col-sm-12">
                        <select type="text" class="form-control form-control-sm single-select select2" name="user" id="user" <?php if(!empty($projection_details)) { ?> disabled <?php } ?>>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Num Of Closure</label>
                    <div class="col-sm-12">
                        <input type="text" class="form-control" name="numOfClosure" id = "numOfClosure" value="<?php echo !empty($projection_details) ? $projection_details['numOfClosure'] : '' ?>">
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Month</label>
                    <div class="col-sm-12">
                        <select type="text" class="form-control form-control-sm single-select select2" name="month" id="month" <?php if(!empty($projection_details)) { ?> disabled <?php } ?>>
                            <option value="">Select</option>
                            <option value="1" <?php if(!empty($projection_details) && $projection_details['month'] == "1") { ?> selected <?php } ?> >Jan</option>
                            <option value="3" <?php if(!empty($projection_details) && $projection_details['month'] == "2") { ?> selected <?php } ?>>Mar</option>
                            <option value="2" <?php if(!empty($projection_details) && $projection_details['month'] == "3") { ?> selected <?php } ?>>Feb</option>
                            <option value="4" <?php if(!empty($projection_details) && $projection_details['month'] == "4") { ?> selected <?php } ?>>Apr</option>
                            <option value="5" <?php if(!empty($projection_details) && $projection_details['month'] == "5") { ?> selected <?php } ?>>May</option>
                            <option value="6" <?php if(!empty($projection_details) && $projection_details['month'] == "6") { ?> selected <?php } ?>>Jun</option>
                            <option value="7" <?php if(!empty($projection_details) && $projection_details['month'] == "7") { ?> selected <?php } ?>>Jul</option>
                            <option value="8" <?php if(!empty($projection_details) && $projection_details['month'] == "8") { ?> selected <?php } ?>>Aug</option>
                            <option value="9" <?php if(!empty($projection_details) && $projection_details['month'] == "9") { ?> selected <?php } ?>>Sep</option>
                            <option value="10" <?php if(!empty($projection_details) && $projection_details['month'] == "10") { ?> selected <?php } ?>>Oct</option>
                            <option value="11" <?php if(!empty($projection_details) && $projection_details['month'] == "11") { ?> selected <?php } ?>>Nov</option>
                            <option value="12" <?php if(!empty($projection_details) && $projection_details['month'] == "12") { ?> selected <?php } ?>>Dec</option>
                        </select>
                    </div>
                </div>
            </div>
            <hr/>
            <div class="row mb-2">
                <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary">
                    <?php if (!empty($projection_details)) { ?>
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
    $('#form-projection').validate({
    rules: {
        projection_type: {required:true}, 
        user : {required:true} ,
        numOfClosure : {required:true},
        month : {required:true} , 
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

$(document).ready(function(){
    var projection_type = '';
    <?php if(!empty($projection_details)) { ?>
        projection_type = '<?=$projection_details['projectionType']?>';
    <?php } ?>
    $.ajax({
        url : "/app/common/projectionTypeList" ,
        type : "post" ,
        data : {
            projection_type
        },
        success : function(data) {
            $("#projection_type").html(data);
            <?php if(!empty($projection_details)) { ?>
                $("#projection_type").trigger('change');
            <?php } ?>
        }
    })
});

$('#hide-modal').click(function() {
    $('.modal').modal('hide');
});

function getUserList(projection_type){
    var user_id = '';
    <?php if(!empty($projection_details)) { ?>
        user_id = '<?=$projection_details['user_id']?>';
    <?php } ?>
    $.ajax({
        url : "/app/addProjection/userList",
        type : 'post', 
        data : {
            projection_type , 
            user_id
        }, 
        success : function(data) {
            $("#user").html(data);
            <?php if(!empty($projection_details)) { ?>
                $("#user").trigger('change');
            <?php } ?>
        }
    });
}

$("#form-projection").on('submit',function(e){
    e.preventDefault();
    if ($("#form-projection").valid()) {
        var formData = new FormData(this);
        <?php if(isset($_REQUEST['projection_id'])) { ?>
            formData.append('id',<?=$_REQUEST['projection_id']?>);
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
                    getFilterData();
                    $('#projectionTable').DataTable().ajax.reload(null, false);
                } else {
                    if(data.message == 'Duplicate') {
                        Swal.fire({
                            title : "Duplicate Entry Found" , 
                            text: "This Projection already present",
                            icon: 'error',
                        });
                    } else if (data.message == 'Number of closure is too less') {
                        Swal.fire({
                            title : "Closure is less" , 
                            text: "More then this center is inserted",
                            icon: 'error',
                        });
                    }
                }
            }
        });
    }
});

</script>
