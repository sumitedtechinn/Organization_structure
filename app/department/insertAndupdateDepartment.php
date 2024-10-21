<?php

## Database configuration
include '../../includes/db-config.php';
session_start();

$department_details = [];
if (isset($_REQUEST['id'])) {
    $department = $conn->query("SELECT * FROM `Department` WHERE id = '".$_REQUEST['id']."'");
    $department_details = mysqli_fetch_assoc($department);
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
            <h5 class="mb-0">Add Department</h5>
            <a type="button" class="close" data-dismiss="modal" id = "hide-modal" aria-hidden="true"><i class="bi bi-x-circle-fill"></i></a>
        </div>
        <hr/>
        <form role="form" id="form-department" action="/app/department/storeAndupdateDepartment" method="POST">
            <div class="row mb-1">
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Department</label>
                    <div class="col-sm-12">
                    <input type="text" class="form-control form-control-sm" name="department" id="department" value="<?php echo !empty($department_details) ? $department_details['department_name'] : '' ?>" placeholder="eg:-IT">
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Organization</label>
                    <div class="col-sm-12">
                    <select type="text" class="form-control form-control-sm single-select select2" name="organization" id="organization" onchange="checkOrganizationforUpdate(this.value)"></select>
                    </div>
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Branch</label>
                    <div class="col-sm-12">
                        <select type="text" class="form-control form-control-sm multiple-select select2" multiple="multiple" name="branch[]" id="branch" <?php if(empty($department_details)) { ?> onchange="getVerticalList()" <?php } else { ?> onchange="getVerticalList('<?=$department_details['vertical_id']?>')" <?php } ?>>
                        </select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Vertical</label>
                    <div class="col-sm-12">
                        <select type="text" class="form-control form-control-sm single-select select2" name="vertical" id="vertical" onchange="checkVerticalForUpdate(this.value)">
                        </select>
                    </div>
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Uploade Logo</label>
                    <div class="col-sm-12">
                        <?php if(!empty($department_details['logo'])) { ?>
                            <img src="<?=$department_details['logo']?>" width="50px" class="mb-2" alt="uploaded image">
                        <?php } ?>
                        <input class="form-control form-control-sm mb-3" type="file" accept="image/*" name="image"> 
                    </div>
                </div>
            </div>
            <hr/>
            <div class="row mb-2">
                <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary">Register</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="/assets/plugins/jquery-validation/js/jquery.validate.js"></script>
<script src="/assets/plugins/select2/js/select2.min.js"></script>
<script src="/assets/js/form-select2.js"></script>
<script>

$(document).ready(function(){
    var organization_id = '';
    <?php if(isset($department_details['organization_id'])) { ?>
        organization_id = <?=$department_details['organization_id']?>;
    <?php } ?>
    $.ajax({
        url : "/app/common/organizationList" , 
        type : 'post',
        data : {
            organization_id
        },
        success : function(data) {
            $("#organization").html(data);
            if(organization_id != '') {
                $("#organization").trigger('change');
            }
        }
    });
});

$(function(){
    $('#form-department').validate({
    rules: {
        department: {required:true},
        organization : {required:true},
        vertical : {required:true},
        branch : {required:true},
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

$('#hide-modal').click(function() {
    $('.modal').modal('hide');
});

function checkOrganizationforUpdate(organization_id) {
    <?php if(!empty($department_details)) { ?>
        var id = '<?=$_REQUEST['id']?>';
        var search = 'Department';
        $.ajax({
            url : "/app/common/checkAssignDetails" ,
            type : "post", 
            data : {
            id,
            search,
            organization_id,
            "type" : "update_organization"
            },
            dataType : "json",
            success : function(data) {
                if(data.status == 400) {
                    Swal.fire({
                        title : data.title,
                        text : data.text,
                        icon: 'error',
                    });
                    $("#organization").val(data.previous);
                    $("#organization").trigger('change');
                } else if(data.status == 200) {
                    if( data.message == "Department not mapped") {
                        $(".select2-selection__rendered li").remove();
                    }
                    getBranchList(organization_id);            
                }   
            }
        })
    <?php } else { ?>
        getBranchList(organization_id);
    <?php } ?>
}

function getBranchList(organization_id) {
    var branch_id = '';
    <?php if(isset($department_details['branch_id'])) { ?>
        branch_id = <?=$department_details['branch_id']?>;
    <?php } ?>
    $.ajax({
        url : "/app/common/branchList" ,
        type : 'post',
        data : {
            branch_id,
            organization_id
        },
        success : function(data) {
            $("#branch").html(data);
            if($("#branch").val().length > 0 ) {
                $("#branch").trigger('change');
            } 
        }
    });
}

function checkVerticalForUpdate(vertical_id) {
    <?php if(!empty($department_details)) { ?>
        var id = '<?=$_REQUEST['id']?>';
        var search = 'Department';
        $.ajax({
            url : "/app/common/checkAssignDetails" ,
            type : "post", 
            data : {
            id,
            search,
            vertical_id,
            "type" : "update_vertical"
            },
            dataType : "json",
            success : function(data) {
                if(data.status == 400) {
                    Swal.fire({
                        title : data.title,
                        text : data.text,
                        icon: 'error',
                    });
                    $("#vertical").val(data.previous);
                    $("#vertical").trigger('change');
                }   
            }
        })
    <?php } ?>
}

$("#form-department").on('submit',function(e){
    e.preventDefault();
    if ($("#form-department").valid()) {
        var formData = new FormData(this);
        <?php if(isset($_REQUEST['id'])) { ?>
            formData.append("ID",<?=$_REQUEST['id']?>);
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
                    if(data.message == 'Users assign on this Department!' || data.message == 'Vacancies assign on this Department!' ) {
                        Swal.fire({
                            title : data.title,
                            text : data.message,
                            icon: 'error',
                        });
                    } else {
                        toastr.error(data.message);    
                    }
                }
            }
        });
    }
});

</script>