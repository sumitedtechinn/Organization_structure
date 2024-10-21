<?php

## Database configuration
include '../../includes/db-config.php';
session_start();

$user_details = [];
if (isset($_REQUEST['id'])) {
    $user_details = $conn->query("SELECT * FROM users WHERE ID = '".$_REQUEST['id']."'");
    $user_details = mysqli_fetch_assoc($user_details);
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
            <h5 class="mb-0">Assign</h5>
            <a type="button" class="close" data-dismiss="modal" id = "hide-modal-md" aria-hidden="true"><i class="bi bi-x-circle-fill"></i></a>
        </div>
        <hr/>
        <form role="form" id="form-assing" action="/app/user/updateUserAssingData" method="POST">
            <div class="row mb-2">
                <label class="col-sm-4 col-form-label">Organization</label>
                <div class="col-sm-8">
                    <select type="text" class="form-control form-control-sm single-select select2" name="organization" id="organization" onchange="checkUserForUpdateOrganization(this.value)">
                    </select>
                </div>
            </div>
            <?php if($_REQUEST['page_type'] == 'InsideBranch') { ?>
            <div class="row mb-2">
            <label class="col-sm-4 col-form-label">Branch</label>
                <div class="col-sm-8">
                    <select type="text" class="form-control form-control-sm single-select select2" name="branch" id="branch" onchange="checkUserForUpdateBranch(this.value)">
                    </select>
                </div>
            </div>
            <?php } ?>
            <div class="row mb-2">
                <label class="col-sm-4 col-form-label">Desigantion</label>
                <div class="col-sm-8">
                    <select type="text" class="form-control form-control-sm single-select select2" name="designation" id="designation" onchange="checkUserForUpdateDesignation(this.value)">
                    </select>
                </div>
            </div>
            <hr/>
            <div class="row mb-2">
                <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary">Assign</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="/assets/plugins/jquery-validation/js/jquery.validate.js"></script>
<script src="/assets/plugins/select2/js/select2.min.js"></script>
<script src="/assets/js/form-select2.js"></script>
<script type="text/javascript">

$(document).ready(function(){
    var organization_id = '';
    <?php if(!empty($user_details)) { ?>
        organization_id = '<?=$user_details['Organization_id']?>';
    <?php } ?>
    $.ajax({
        url : "/app/common/organizationList",
        type : "post",
        data : {
            organization_id
        },
        success : function(data) {
            $("#organization").html(data);
            <?php if(!empty($user_details)) { ?>
                $("#organization").trigger('change');
            <?php } ?>
        }
    });
});

function checkUserForUpdateOrganization(organization_id) {
    <?php if(!empty($user_details)) { ?>
        var id = '<?=$_REQUEST['id']?>';
        var search = 'users';
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
                    getOrganizationDesignation(organization_id);            
                }   
            }
        })
    <?php } else { ?>
        getOrganizationDesignation(organization_id);
    <?php } ?>
}

function getOrganizationDesignation(organization_id) {
    var designation_id = '';
    <?php if(!empty($user_details)) { ?>
        designation_id = '<?=$user_details['Designation_id']?>';
    <?php } ?>
    <?php if($_REQUEST['page_type'] == 'InsideOrganization') { ?>
    $.ajax({
        url : "/app/designation/getOrganizationDesignation",
        type : "post",
        data : {
            organization_id,
            designation_id,
            "type" : "adminDesignation"
        },
        success : function(data) {
            $("#designation").html(data);
            <?php if(!empty($user_details)) { ?>
                $("#designation").trigger('change');
            <?php } ?>
        }
    });
    <?php } else { ?>
        getOrganizationBranch(organization_id);
    <?php } ?>
}

function getOrganizationBranch(organization_id) {
    var branch_id = '';
    <?php if(!empty($user_details['Branch_id'])) { ?>
        branch_id = '<?=$user_details['Branch_id']?>';
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
            <?php if(!empty($user_details['Branch_id'])) { ?>
                $("#branch").trigger('change');
            <?php } ?>
        }
    });
}

function checkUserForUpdateBranch(branch_id) {
    <?php if(!empty($user_details)) { ?>
        var id = '<?=$_REQUEST['id']?>';
        var search = 'users';
        $.ajax({
            url : "/app/common/checkAssignDetails",
            type : "post", 
            data : {
            id,
            search,
            branch_id,
            "type" : "update_branch"
            },
            dataType : "json",
            success : function(data) {
                if(data.status == 400) {
                    Swal.fire({
                        title : data.title,
                        text : data.text,
                        icon: 'error',
                    });
                    $("#branch").val(data.previous);
                    $("#branch").trigger('change');
                } else {
                    getBranchDesignation(branch_id);
                }   
            }
        });    
    <?php } else { ?>
        getBranchDesignation(branch_id);
    <?php } ?>
}

function getBranchDesignation(branch_id){
    var organization_id = $("#organization").val();
    var designation_id = '';
    <?php if(!empty($user_details)) { ?>
        designation_id = '<?=$user_details['Designation_id']?>';
    <?php } ?>
    $.ajax({
        url : "/app/designation/getBranchDesignation",
        type : "post",
        data : {
            branch_id,
            organization_id,
            designation_id,
        },
        success : function(data) {
            $("#designation").html(data);
            <?php if(!empty($user_details)) { ?>
                $("#designation").trigger('change');
            <?php } ?>
        }
    });
}

function checkUserForUpdateDesignation(designation_id) {
    <?php if(!empty($user_details)) { ?>
        var id = '<?=$_REQUEST['id']?>';
        var search = 'users';
        $.ajax({
            url : "/app/common/checkAssignDetails",
            type : "post", 
            data : {
            id,
            search,
            designation_id,
            "type" : "update_designation"
            },
            dataType : "json",
            success : function(data) {
                if(data.status == 400) {
                    Swal.fire({
                        title : data.title,
                        text : data.text,
                        icon: 'error',
                    });
                    $("#designation").val(data.previous);
                    $("#designation").trigger('change');
                }   
            }
        });    
    <?php } ?>
}

$(function(){
    $('#form-assing').validate({
        rules: {
            organization : {required:true},
            designation : {required:true},
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

$("#form-assing").on('submit',function(e){
    e.preventDefault();
    if ($("#form-assing").valid()) {
        var formData = new FormData(this);
        formData.append('ID',<?=$_REQUEST['id']?>);
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

$('#hide-modal-md').click(function() {
    $('.modal').modal('hide');
});

</script>