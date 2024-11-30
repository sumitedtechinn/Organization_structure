<?php

## Database configuration
include '../../includes/db-config.php';
session_start();

$user_details = [];
$user_designation_added_inside = '';
if (isset($_REQUEST['id'])) {
    $user_details = $conn->query("SELECT * FROM users WHERE users.ID = '".$_REQUEST['id']."'");
    $user_details = mysqli_fetch_assoc($user_details);
    if($user_details['Assinged_Person_id'] == '0') {
        $user_details['Assinged_Person_id'] = 'head';
    }
    $checkAddedInside = $conn->query("SELECT Designation.added_inside as `added_inside` FROM users LEFT JOIN Designation ON Designation.ID = users.Designation_id WHERE users.ID = '".$_REQUEST['id']."'");
    $checkAddedInside = mysqli_fetch_column($checkAddedInside);
    $added_insideList = ['1' => 'organization' , '2' => 'branch' , '3' => 'vertical' , '4' => 'department'];
    if(array_key_exists($checkAddedInside,$added_insideList)) {
        $user_designation_added_inside = $added_insideList[$checkAddedInside];
    }
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
            <h5 class="mb-0">Assign Reporting Person</h5>
            <a type="button" class="close" data-dismiss="modal" id = "hide-modal-md" aria-hidden="true"><i class="bi bi-x-circle-fill"></i></a>
        </div>
        <hr/>
        <form role="form" id="form-assing" action="/app/user/updateUserAssingData" method="POST">
            <div class="row mb-2">
                <label class="col-sm-4 col-form-label">Reporting Person</label>
                <div class="col-sm-8">
                    <select type="text" class="form-control form-control-sm single-select select2" name="reporting_person" id="reporting_person" <?php if($user_designation_added_inside == 'department') { ?> onchange="checkVacancy(this.value)" <?php } else { ?> onchange="checkAssign(this.value)" <?php } ?>>
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
    var assing_person_id = '';
    <?php if(!empty($user_details['Assinged_Person_id'])) { ?>
        assing_person_id = '<?=$user_details['Assinged_Person_id']?>';
    <?php } ?>
    <?php if($user_designation_added_inside == 'department') { ?>
        var branch_id = '<?=$user_details['Branch_id']?>';
        var department_id = '<?=$user_details['Department_id']?>';
        var organization_id = '<?=$user_details['Organization_id']?>';
        var hierarchy_value = '<?=$user_details['Hierarchy_value']?>';
        $.ajax({
            url : "/app/common/reportingPersonList",
            type : 'post',
            data : {
                assing_person_id,
                branch_id,
                department_id,
                organization_id,
                hierarchy_value,
                "designation_type" : "insideDepartment"
            },
            success : function(data) {
                $("#reporting_person").html(data);
                <?php if(!empty($user_details['Assinged_Person_id'])) {  ?>
                    $("#reporting_person").trigger('change');
                <?php } ?>
            }
        });
    <?php } elseif($user_designation_added_inside == 'organization') { ?>
        var organization_id = '<?=$user_details['Organization_id']?>';
        var hierarchy_value = '<?=$user_details['Hierarchy_value']?>';
        $.ajax({
            url : "/app/common/reportingPersonList",
            type : 'post',
            data : {
                assing_person_id,
                organization_id,
                hierarchy_value,
                "designation_type" : "insideOrganization"
            },
            success : function(data) {
                $("#reporting_person").html(data);
                <?php if(!empty($user_details['Assinged_Person_id'])) {  ?>
                    $("#reporting_person").trigger('change');
                <?php } ?>
            }
        });
    <?php } elseif($user_designation_added_inside == 'branch') { ?>
        var branch_id = '<?=$user_details['Branch_id']?>';
        var organization_id = '<?=$user_details['Organization_id']?>';
        var hierarchy_value = '<?=$user_details['Hierarchy_value']?>';
        $.ajax({
            url : "/app/common/reportingPersonList",
            type : 'post',
            data : {
                assing_person_id,
                branch_id,
                organization_id,
                hierarchy_value,
                "designation_type" : "insideBranch"
            },
            success : function(data) {
                $("#reporting_person").html(data);
                <?php if(!empty($user_details['Assinged_Person_id'])) {  ?>
                    $("#reporting_person").trigger('change');
                <?php } ?>
            }
        });
    <?php } elseif ($user_designation_added_inside == 'vertical') { ?>
        var vertical_id = '<?=$user_details['Vertical_id']?>';
        var branch_id = '<?=$user_details['Branch_id']?>';
        var organization_id = '<?=$user_details['Organization_id']?>';
        var hierarchy_value = '<?=$user_details['Hierarchy_value']?>';
        $.ajax({
            url : "/app/common/reportingPersonList",
            type : 'post',
            data : {
                assing_person_id,
                vertical_id,
                branch_id,
                organization_id,
                hierarchy_value,
                "designation_type" : "insideVertical"
            },
            success : function(data) {
                $("#reporting_person").html(data);
                <?php if(!empty($user_details['Assinged_Person_id'])) {  ?>
                    $("#reporting_person").trigger('change');
                <?php } ?>
            }
        });
    <?php } ?>
});

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
                parent_id,
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
        var id = '<?=$_REQUEST['id']?>';
        var search = 'users';
        $.ajax({
            url : "/app/common/checkAssignDetails",
            type : "post", 
            data : {
            id,
            search,
            parent_id,
            "type" : "update_reporting"
            },
            dataType : "json",
            success : function(data) {
                if(data.status == 400) {
                    Swal.fire({
                        title : data.title,
                        text : data.text,
                        icon: 'error',
                    });
                    $("#reporting_person").val(data.previous);
                    $("#reporting_person").trigger('change');
                }   
            }
        });
    }
}

function checkAssign(parent_id) {
    var id = '<?=$_REQUEST['id']?>';
    var search = 'users';
    $.ajax({
        url : "/app/common/checkAssignDetails",
        type : "post", 
        data : {
        id,
        search,
        parent_id,
        "type" : "update_reporting"
        },
        dataType : "json",
        success : function(data) {
            if(data.status == 400) {
                Swal.fire({
                    title : data.title,
                    text : data.text,
                    icon: 'error',
                });
                $("#reporting_person").val(data.previous);
                $("#reporting_person").trigger('change');
            }   
        }
    });
}

$("#form-assing").on('submit',function(e){
    e.preventDefault();
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
});

</script>