<?php 

include '../../includes/db-config.php';
session_start();

$desigantion_details = [];

if (isset($_REQUEST['id'])) {
    $designation = $conn->query("SELECT * FROM `Designation` WHERE ID = '".$_REQUEST['id']."'");
    $desigantion_details = mysqli_fetch_assoc($designation);
}

$designation_inside = []; $desigantion_added_inside_name = '';
if (isset($_REQUEST['designation_inside'])) {
    $designation_inside['id'] = mysqli_real_escape_string($conn,$_REQUEST['designation_inside']);
    list($designation_inside['name'],$designation_inside['url']) = createDesignationInsideInputField($designation_inside['id']);
} elseif (!empty($desigantion_details)) {
    $designation_inside['id'] = mysqli_real_escape_string($conn,$desigantion_details['added_inside']);
    list($designation_inside['name'],$designation_inside['url']) = createDesignationInsideInputField($designation_inside['id']);
}

function createDesignationInsideInputField($designation_inside_id) {

    global $conn;
    global $desigantion_added_inside_name;
    $url = '';
    $designation_inside_name = $conn->query("SELECT designation_inside_type FROM `designation_inside` WHERE ID = '$designation_inside_id'");
    $designation_inside_name = mysqli_fetch_column($designation_inside_name);
    $desigantion_added_inside = (explode(' ',$designation_inside_name))[1];
    $desigantion_added_inside_name = "Inside ". ucfirst($desigantion_added_inside);
    $url = "/app/designation/get".ucfirst($desigantion_added_inside)."Designation";
    return [$desigantion_added_inside,$url];
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
            <h6 class="mb-0"><?php echo !empty($desigantion_details) ? "Update Designation $desigantion_added_inside_name" : "Add Designation $desigantion_added_inside_name"; ?></h6>
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
                    <label class="col-sm-12 col-form-label"><?php echo ucfirst($designation_inside['name']) ?></label>
                    <div class="col-sm-12">
                        <select type="text" class="form-control form-control-sm single-select select2" name="<?=$designation_inside['name']?>" id="<?=$designation_inside['name']?>" onchange="getSelectedTypeDesignation(this.value)" <?php if(!empty($desigantion_details)) { ?> disabled <?php } ?>>
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
                    <?php echo !empty($desigantion_details) ? "Update" : "Register"; ?>
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

$(document).ready(function() {
    var typeData = '<?=$designation_inside['name']?>';
    var funcname = 'get'+(typeData.charAt(0).toUpperCase() + typeData.slice(1))+'Data'; 
    window[funcname]();
});

function getOrganizationData() {
    var organization_id = '';
    <?php if(!empty($desigantion_details)) { ?>
        organization_id = '<?=$desigantion_details['organization_id']?>';
    <?php } ?>
    $.ajax({
        url : "/app/common/organizationList",
        type : "post",
        data : {
            organization_id
        },
        success : function(data) {
            $("#organization").html(data);
            <?php if(!empty($desigantion_details)) { ?>
                $("#organization").trigger('change');
            <?php } ?>
        }
    });   
}

function getBranchData() {
    var organization_id = ''; var branch_id = '';
    <?php if(!empty($desigantion_details)) { ?>
        branch_id = '<?=$desigantion_details['branch_id']?>';
        organization_id = '<?=$desigantion_details['organization_id']?>';
    <?php } else {  ?>
        organization_id = '<?php echo isset($_REQUEST['organization_id']) ? $_REQUEST['organization_id'] : ''; ?>';
    <?php } ?>
    $.ajax({
        url : "/app/common/branchList",
        type : "post",
        data : {
            organization_id,
            branch_id
        },
        success : function(data) {
            $("#branch").html(data);
            <?php if(!empty($desigantion_details)) { ?>
                $("#branch").trigger('change');
            <?php } ?>
        }
    });
}

function getVerticalData() {
    var organization_id = ''; var branch = ''; var vertical_id = '';
    <?php if(!empty($desigantion_details)) { ?>
        branch = '<?=$desigantion_details['branch_id']?>';
        organization_id = '<?=$desigantion_details['organization_id']?>';
        vertical_id = '<?=$desigantion_details['vertical_id']?>';
    <?php } else {  ?>
        organization_id = '<?php echo isset($_REQUEST['organization_id']) ? $_REQUEST['organization_id'] : ''; ?>';
        branch = '<?php echo isset($_REQUEST['branch_id']) ? $_REQUEST['branch_id'] : ''; ?>';
    <?php } ?>
    $.ajax({
        url : "/app/common/verticalList",
        type : "post",
        data : {
            organization_id,
            branch,
            vertical_id
        },
        success : function(data) {
            $("#vertical").html(data);
            <?php if(!empty($desigantion_details)) { ?>
                $("#vertical").trigger('change');
            <?php } ?>
        }
    });
}

function getDepartmentData() {
    var organization_id = ''; var branch_id = ''; var vertical_id = ''; var department_id = '';
    <?php if(!empty($desigantion_details)) { ?>
        branch_id = '<?=$desigantion_details['branch_id']?>';
        organization_id = '<?=$desigantion_details['organization_id']?>';
        vertical_id = '<?=$desigantion_details['vertical_id']?>';
        department_id = '<?=$desigantion_details['department_id']?>';
    <?php } else {  ?>
        organization_id = '<?php echo isset($_REQUEST['organization_id']) ? $_REQUEST['organization_id'] : ''; ?>';
        branch_id = '<?php echo isset($_REQUEST['branch_id']) ? $_REQUEST['branch_id'] : ''; ?>';
        vertical_id = '<?php echo isset($_REQUEST['vertical_id']) ? $_REQUEST['vertical_id'] : ''; ?>';
    <?php } ?>
    $.ajax({
        url : "/app/common/departmentList",
        type : "post",
        data : {
            organization_id,
            branch_id,
            vertical_id,
            department_id
        },
        success : function(data) {
            $("#department").html(data);
            <?php if(!empty($desigantion_details)) { ?>
                $("#department").trigger('change');
            <?php } ?>
        }
    });
}

function getSelectedTypeDesignation(id) {
    var data = createData(id);
    $.ajax({
        url : data.url,
        type : "post",
        data : data,
        success : function(data) {
            $("#parent_desigantion").html(data);
            <?php if(!empty($desigantion_details)) { ?>
                $("#parent_desigantion").trigger('change');
            <?php } ?>
        }
    });
}

function createData(id) {
    var data = {};
    data.url = '<?=$designation_inside['url']?>';
    data.hierarchy_value = '<?php echo !empty($desigantion_details) ? $desigantion_details['hierarchy_value'] : '' ?>';
    data.added_inside_id = '<?=$designation_inside['id']?>';
    <?php if($designation_inside['id'] == '1') { ?>
        data.organization_id = id;
    <?php } elseif($designation_inside['id'] == '2') { ?>
        data.organization_id = '<?php echo isset($_REQUEST['organization_id']) ? $_REQUEST['organization_id'] : ''; ?>';
        data.branch_id = id;
    <?php } elseif($designation_inside['id'] == '3') { ?>
        data.organization_id = '<?php echo isset($_REQUEST['organization_id']) ? $_REQUEST['organization_id'] : ''; ?>';
        data.branch_id = '<?php echo isset($_REQUEST['branch_id']) ? $_REQUEST['branch_id'] : ''; ?>';
        data.vertical_id = id;
    <?php } elseif($designation_inside['id'] == '4') { ?>
        data.organization_id = '<?php echo isset($_REQUEST['organization_id']) ? $_REQUEST['organization_id'] : ''; ?>';
        data.branch_id = '<?php echo isset($_REQUEST['branch_id']) ? $_REQUEST['branch_id'] : ''; ?>';
        data.vertical_id = '<?php echo isset($_REQUEST['vertical_id']) ? $_REQUEST['vertical_id'] : ''; ?>';
        data.department_id = id;
    <?php } ?>
    return data;
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
    var rules = {
        designation: {required:true},
        designation_code : {required:true},
        parent_desigantion : {required:true},
        node_colour : {required:true}
    };
    var inputfiledName = '<?=$designation_inside['name']?>';
    rules[inputfiledName] = {required:true};
    $('#form-addDesignation').validate({
    rules: rules,
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
        var checkParllelHierarchy_data = createDataForParllelHierarchy(formData);
        for (const key in checkParllelHierarchy_data) {
            if(key == 'organization_id') {
                formData.append('organization_id',checkParllelHierarchy_data[key]);
            } else if (key == 'branch_id') {
                formData.append('branch_id',checkParllelHierarchy_data[key]);
            } else if (key == 'vertical_id') {
                formData.append('vertical_id',checkParllelHierarchy_data[key]);
            } else if (key == 'department_id') {
                formData.append('department_id',checkParllelHierarchy_data[key]);
            }
        }
        formData.append('added_inside','<?=$designation_inside['id']?>');
        $.ajax({
            url : "/app/designation/checkParallelHierarchy",
            type : "post",
            dataType: 'json',
            data : checkParllelHierarchy_data,
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

function createDataForParllelHierarchy(formDetails) {
    var data = {};
    data.selected_insideType = '<?=$designation_inside['name']?>';
    data.selected_insideTypeId = '<?=$designation_inside['id']?>';
    data.parent_desigantion = formDetails.get("parent_desigantion");
    <?php if($designation_inside['id'] == '1') { ?>
        data.organization_id = formDetails.get(data.selected_insideType);
    <?php } elseif($designation_inside['id'] == '2') { ?>
        data.organization_id = '<?php echo isset($_REQUEST['organization_id']) ? $_REQUEST['organization_id'] : ''; ?>';
        data.branch_id = formDetails.get(data.selected_insideType);
    <?php } elseif($designation_inside['id'] == '3') { ?>
        data.organization_id = '<?php echo isset($_REQUEST['organization_id']) ? $_REQUEST['organization_id'] : ''; ?>';
        data.branch_id = '<?php echo isset($_REQUEST['branch_id']) ? $_REQUEST['branch_id'] : ''; ?>';
        data.vertical_id = formDetails.get(data.selected_insideType);
    <?php } elseif($designation_inside['id'] == '4') { ?>
        data.organization_id = '<?php echo isset($_REQUEST['organization_id']) ? $_REQUEST['organization_id'] : ''; ?>';
        data.branch_id = '<?php echo isset($_REQUEST['branch_id']) ? $_REQUEST['branch_id'] : ''; ?>';
        data.vertical_id = '<?php echo isset($_REQUEST['vertical_id']) ? $_REQUEST['vertical_id'] : ''; ?>';
        data.department_id = formDetails.get(data.selected_insideType);
    <?php } ?>
    return data;
}

</script>