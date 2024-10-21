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
                <label class="col-sm-4 col-form-label">Department</label>
                <div class="col-sm-8">
                    <select type="text" class="form-control form-control-sm single-select select2" name="department" id="department" onchange="checkDepartmetnforUpdate(this.value)" >
                    </select>
                </div>
            </div>
            <div class="row mb-2">
                <label class="col-sm-4 col-form-label">Desigantion</label>
                <div class="col-sm-8">
                    <select type="text" class="form-control form-control-sm single-select select2" name="designation" id="designation" onchange="checkUserForUpdateDesignation(this.value)">
                    </select>
                </div>
            </div>
            <div class="row mb-2">
            <label class="col-sm-4 col-form-label">Branch</label>
                <div class="col-sm-8">
                    <select type="text" class="form-control form-control-sm single-select select2" name="branch" id="branch" onchange="checkUserForUpdateBranch(this.value)">
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
    var department_id = '';
    <?php if(!empty($user_details)) { ?>
        department_id = '<?=$user_details['Department_id']?>';
    <?php } ?>
    $.ajax({
        url : "/app/common/departmentList",
        type : "post",
        data : {
            department_id
        },
        success : function(data) {
            $("#department").html(data);
            <?php if(!empty($user_details)) { ?>
                $("#department").trigger('change');
            <?php } ?>
        }
    });
});

function checkDepartmetnforUpdate(department_id) {
    <?php if(!empty($user_details)) { ?>
        var id = '<?=$_REQUEST['id']?>';
        var search = 'users';
        $.ajax({
            url : "/app/common/checkAssignDetails" ,
            type : "post", 
            data : {
            id,
            search,
            department_id,
            "type" : "update_department"
            },
            dataType : "json",
            success : function(data) {
                if(data.status == 400) {
                    Swal.fire({
                        title : data.title,
                        text : data.text,
                        icon: 'error',
                    });
                    $("#department").val(data.previous);
                    $("#department").trigger('change');
                } else if(data.status == 200) {
                    getDepartmentDesignation(department_id);
                    getDepartmentBranch(department_id);            
                }   
            }
        })
    <?php } else { ?>
        getDepartmentDesignation(department_id);
        getDepartmentBranch(department_id);
    <?php } ?>
}

function getDepartmentDesignation(department_id) {
    var designation_id = '';
    <?php if(!empty($user_details)) { ?>
        designation_id = '<?=$user_details['Designation_id']?>';
    <?php } ?>
    $.ajax({
        url : "/app/designation/getDepartmentDesignation",
        type : "post",
        data : {
            department_id,
            designation_id,
            'type' : 'userDesignation'
        },
        success : function(data) {
            $("#designation").html(data);
            <?php if(!empty($user_details)) { ?>
                $("#designation").trigger('change');
            <?php } ?>
        }
    });
}

function getDepartmentBranch(department_id) {
    var branch_id = ''; organization_id = '';
    <?php if(!empty($user_details['Branch_id'])) { ?>
        branch_id = <?=$user_details['Branch_id']?>;
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
            <?php if(!empty($user_details['Branch_id'])) { ?>
                $("#branch").trigger('change');
            <?php } ?>
        }
    });
}

$('#hide-modal-md').click(function() {
    $('.modal').modal('hide');
});


function checkVacancy(parent_id) {
    var user_id = '<?=$_REQUEST['id']?>';
    if (parent_id.length > 0 ) {
        $.ajax({
            url: "/app/user/checkVacancy",
            type: 'post',
            data: {
                user_id,
            },
            dataType: 'json',
            success: function(data) {
                if( data.status == 400) {
                    Swal.fire({
                        text: data.message,
                        title : "Sorry Can't Assign",
                        icon: 'warning',
                    });
                    $("#reporting_person").val("");
                    $("#reporting_person").trigger('change');    
                }
            }
        });
    } else {
        $.ajax({
            url : "/app/user/checkUserAssign",  
            type: 'post',
            data: {
                user_id,
                parent_id ,
                'type' : 'un-assign'
            },
            dataType: 'json',
            success : function(data) {
                if( data.status == 400) {
                    Swal.fire({
                        text: data.message,
                        title : "Sorry can't un-assign",
                        icon: 'warning',
                    });
                    $("#reporting_person").val(data.previous);
                    $("#reporting_person").trigger('change');    
                }
            }
        });
    }
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
                }   
            }
        });    
    <?php } ?>
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
            department : {required:true},
            designation : {required:true},
            branch: {required:true},
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


</script>