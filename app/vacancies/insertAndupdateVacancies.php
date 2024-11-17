<?php 
$branch_details = [];
include '../../includes/db-config.php';
session_start();

$vacancies_details = [];
if (isset($_REQUEST['id'])) {
    $vacancies = $conn->query("SELECT * FROM `Vacancies` WHERE ID = '".$_REQUEST['id']."'");
    $vacancies_details = mysqli_fetch_assoc($vacancies);
    if($vacancies_details['Raised_by'] == '0') {
        $vacancies_details['Raised_by'] = 'head';
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
            <h5 class="mb-0">
            <?php if(!empty($vacancies_details)) { ?>
                Update Vacancies
            <?php } else { ?>
                Add Vacancies
            <?php } ?>
            </h5>
            <a type="button" class="close" data-dismiss="modal" id = "hide-modal" aria-hidden="true"><i class="bi bi-x-circle-fill"></i></a>
        </div>
        <hr/>
        <form role="form" id="form-vacancies" action="/app/vacancies/storeAndupdateVacancies" method="POST">
            <div class="row mb-1">
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Department</label>
                    <div class="col-sm-12">
                        <select type="text" class="form-control form-control-sm single-select select2" name="department" id="department" onchange="getBranchAndDepartment(this.value)" <?php if(!empty($vacancies_details)) { ?> disabled <?php } ?>>
                        </select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Branch</label>
                    <select type="text" class="form-control form-control-sm single-select select2" name="branch" id="branch" onchange="getReportingPersonList()" <?php if(!empty($vacancies_details)) { ?> disabled <?php } ?>>
                    </select>
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">No. Of Vacancies</label>
                    <div class="col-sm-12">
                        <input type="text" class="form-control form-control-sm" name="numofvacancies" value="<?php echo !empty($vacancies_details) ? $vacancies_details['NumOfVacanciesRaised'] : '' ?>">
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Designation</label>
                    <select type="text" class="form-control form-control-sm single-select select2" name="designation" id="designation" onchange="getReportingPersonList()" <?php if(!empty($vacancies_details)) { ?> disabled <?php } ?>>
                    </select>
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Vacancy Raised By</label>
                    <select type="text" class="form-control form-control-sm single-select select2" name="raisedby" id="raisedby">
                    </select>
                </div>
            </div>
            <hr/>
            <div class="row mb-2">
                <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary">
                    <?php if (!empty($vacancies_details)) { ?>
                        Update
                    <?php } else { ?>
                        Register
                    <?php }  ?>    
                    </button>
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
    <?php if(!empty($vacancies_details)) { ?>
        department_id = '<?=$vacancies_details['Department_id']?>';
    <?php } ?>
    $.ajax({
        url : "/app/common/departmentList",
        type : "post",
        data : {
            department_id
        },
        success : function(data) {
            $("#department").html(data);
            <?php if(!empty($vacancies_details)) { ?>
                $("#department").trigger('change');
            <?php } ?>
        }
    });
});

function getBranchAndDepartment(department_id) {
    getDepartmentDesignation(department_id);
    getBranches(department_id);
}

function getDepartmentDesignation(department_id) {
    var designation_id = '';
    <?php if(!empty($vacancies_details)) { ?>
        designation_id = '<?=$vacancies_details['Designation_id']?>';
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
            <?php if(!empty($vacancies_details)) { ?>
                $("#designation").trigger('change');
            <?php } ?>
        }
    });
}

function getBranches(department_id) {
    var branch_id = '';
    <?php if(!empty($vacancies_details)) { ?>
        branch_id = <?=$vacancies_details['Branch_id']?>;
    <?php } ?>
    var organization_id = '';
    <?php if(!empty($vacancies_details)) { ?>
        organization_id = <?=$vacancies_details['Organization_id']?>;
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
            <?php if(!empty($vacancies_details['Branch_id'])) { ?>
                $("#branch").trigger('change');
            <?php } ?>
        }
    });
}

function getReportingPersonList(branch_id) {
    var branch_id = ''; var department_id = ''; var hierarchy_value = '';
    if ($("#designation").val().length > 0 && $("#branch").val().length > 0 ) {
        branch_id = $("#branch").val();
        department_id = $("#department").val();
        hierarchy_value = $("#designation").val().split("_")[1];
    } else {
        return ;
    }
    var raised_by = '';
    <?php if(!empty($vacancies_details['Raised_by'])) { ?>
        raised_by = '<?=$vacancies_details['Raised_by']?>';
    <?php } ?>
    $.ajax({
        url : "/app/common/reportingPersonList",
        type : 'post',
        data : {
            raised_by ,
            branch_id,
            department_id , 
            hierarchy_value
        },
        success : function(data) {
            $("#raisedby").html(data);
            <?php if(!empty($vacancies_details['Raised_by'])) {  ?>
                $("#raisedby").trigger('change');
            <?php } ?>
        }
    });
}


$(function(){
    $('#form-vacancies').validate({
    rules: {
        branch: {required:true},
        department : {required:true},
        numofvacancies : {required:true},
        designation : {required:true},
        raisedby : {required:true},
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

function getRaisedPersonList(vertical,typeofdata) {
    var branch = $("#branch").val();
    var designation = $("#designation").val();
    var id = '';
    <?php if (!empty($vacancies_details)) {?>
        id = '<?=$vacancies_details['ID']?>';
    <?php } ?>
    if (designation.length > 0 ) {
        var hierarchy = (designation.split("_"))[1]; 
    } else {
        Swal.fire({
            text: "Please select designation first",
            icon: 'warning',
        });
        return;
    }
    $.ajax({
        url : "/app/vacancies/getVerticalAndRaisedPersonList" , 
        type : "POST" , 
        data : {
            branch,
            vertical,
            id,
            typeofdata,
            hierarchy
        },
        success : function(data) {
            $("#"+typeofdata).html(data);
        }
    })
}

$('#hide-modal').click(function() {
    $('.modal').modal('hide');
});

$("#form-vacancies").on('submit',function(e){
    e.preventDefault();
    if ($("#form-vacancies").valid()) {
        var formData = new FormData(this);
        <?php if (!empty($vacancies_details)) {?>
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
                    if(data.message == 'Duplicate Entry Found') {
                        Swal.fire({
                            title: data.message,
                            text: data.text, 
                            icon: 'error',
                        });
                        $('.modal').modal('hide');
                    } else {
                        toastr.error(data.message);
                    }
                }
            }
        });
    }
});

</script>