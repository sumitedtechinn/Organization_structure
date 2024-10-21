<?php 

include '../../includes/db-config.php';
session_start();

$desigantion_details = [];

if (isset($_REQUEST['id'])) {
    $designation = $conn->query("SELECT * FROM `Designation` WHERE ID = '".$_REQUEST['id']."'");
    $desigantion_details = mysqli_fetch_assoc($designation);
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
            <h5 class="mb-0">
                <?php if(!empty($desigantion_details)) { ?>
                    Update 
                <?php } else { ?>
                    Add
                <?php } ?>
                Designation</h5>
            <a type="button" class="close" data-dismiss="modal" id = "hide-modal" aria-hidden="true"><i class="bi bi-x-circle-fill"></i></a>
        </div>
        <hr/>
        <form role="form" id="form-addDesignation" action="/app/designation/storeDesigantion" method="POST">
            <div class="row mb-1">
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Designation</label>
                    <div class="col-sm-12">
                        <input type="text" class="form-control" name="designation" id = "designation" placeholder="eg:- Team Lead" onblur="createDesignationCode(this.value)" value ="<?php echo !empty($desigantion_details) ? $desigantion_details['designation_name'] : '' ?>" />
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Designation Code</label>
                    <div class="col-sm-12">
                        <input type="text" class="form-control" name="designation_code" id = "designation_code" disabled placeholder="eg:- TL" value ="<?php echo !empty($desigantion_details) ? $desigantion_details['code'] : '' ?>"/>
                    </div>
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Department</label>
                    <div class="col-sm-12">
                        <select type="text" class="form-control form-control-sm single-select select2" name="department" id="department" onchange="getDepartmentDesignation(this.value)" <?php if(!empty($desigantion_details)) { ?> disabled <?php } ?> >
                        </select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Parent Desigantion</label>
                    <div class="col-sm-12">
                        <select type="text" class="form-control form-control-sm single-select select2" name="parent_desigantion" id="parent_desigantion" <?php if(!empty($desigantion_details)) { ?> disabled <?php } ?> >
                        </select>
                    </div>
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Select Node Colour</label>
                    <div class="col-sm-12">
                        <input type="color" class="form-control form-control-color" name="node_colour" title="Choose your color" value = "<?php echo !empty($desigantion_details) ? $desigantion_details['color'] : '' ?>">
                    </div>
                </div>
            </div>
            <hr/>
            <div class="row mb-2">
                <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary">
                    <?php if(!empty($desigantion_details)) { ?>
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

<script type="text/javascript">

$(document).ready(function(){
    var department_id = '';
    <?php if(!empty($desigantion_details)) { ?>
        department_id = '<?=$desigantion_details['department_id']?>';
    <?php } ?>
    $.ajax({
        url : "/app/common/departmentList",
        type : "post",
        data : {
            department_id
        },
        success : function(data) {
            $("#department").html(data);
            <?php if(!empty($desigantion_details)) { ?>
                $("#department").trigger('change');
            <?php } ?>
        }
    });
});

function getDepartmentDesignation(department_id) {
    var hierarchy_value = '';
    <?php if(!empty($desigantion_details)) { ?>
        hierarchy_value = '<?=$desigantion_details['hierarchy_value']?>';
    <?php } ?>
    $.ajax({
        url : "/app/designation/getDepartmentDesignation",
        type : "post",
        data : {
            department_id,
            hierarchy_value
        },
        success : function(data) {
            $("#parent_desigantion").html(data);
            <?php if(!empty($desigantion_details)) { ?>
                $("#parent_desigantion").trigger('change');
            <?php } ?>
        }
    });
}

function createDesignationCode(designation) {
    var designation_code = '';
    if( designation.indexOf(" ") != -1) {
        let designation_arr = designation.split(" ");
        for(let key in designation_arr) {
            designation_code += designation_arr[key].substring(0,1);
        }
    } else {
        designation_code = designation.substring(0,2);
    }
    $("#designation_code").val(designation_code.toUpperCase());
}

$(function(){
    $('#form-addDesignation').validate({
    rules: {
        designation: {required:true},
        designation_code : {required:true},
        department : {required:true},
        parent_desigantion : {required:true},
        node_colour : {required:true}
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

function storeDesignation(formAllData,url) {
    console.log(formAllData);
    console.log(url);
    $.ajax({
        url: url,
        type: 'post',
        data: formAllData,
        cache: false,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function(data) {
            if (data.status == 200) {
                $('.modal').modal('hide');
                toastr.success(data.message);
                initializeChart();
                $('.table').DataTable().ajax.reload(null, false);
            } else {
                toastr.error(data.message);
            }
        }
    });
}

$("#form-addDesignation").on('submit',function(e){
    e.preventDefault();
    if ($("#form-addDesignation").valid()) {
        var url = this.action;
        var formData = new FormData(this);
        <?php if(isset($_REQUEST['id'])) { ?>
            formData.append("ID",<?=$_REQUEST['id']?>);
        <?php } ?>
        formData.append('designation_code',$("#designation_code").val());
        <?php if(isset($_REQUEST['id'])) { ?>
            storeDesignation(formData,url);
            return;
        <?php } ?>
        var department = formData.get("department");
        var parent_desigantion = formData.get("parent_desigantion");
        $.ajax({
            url : "/app/designation/checkParallelHierarchy",
            type : "post",
            dataType: 'json',
            data : {
                department,
                parent_desigantion
            },
            success : function(data) {
                if(data.status == 400) {
                    Swal.fire({
                    title: data.title,
                    text: data.text,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Process.',
                    cancelButtonText : 'No',
                    }).then((isConfirm) => {
                        var addIn = (isConfirm.value === true) ? "Add in above" : "Add in parallel";
                        formData.append("designation_addIn",addIn);
                        storeDesignation(formData,url);
                    });
                } else {
                    formData.append("designation_addIn","Add in above");
                    storeDesignation(formData,url);
                }
            }
        });
    }
});



</script>