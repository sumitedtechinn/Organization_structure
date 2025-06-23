<?php 
$branch_details = [];
include '../../includes/db-config.php';
session_start();

$vertical_details = [];
if (isset($_REQUEST['vertical_id']) && !empty($_REQUEST['vertical_id'])) {
    $vertical = $conn->query("SELECT Vertical_name as `name`, organization_id , Branch_id as `branch` , Vertical.image as `image` FROM Vertical  WHERE Vertical.ID = ".$_REQUEST['vertical_id']);
    $vertical_details = mysqli_fetch_assoc($vertical);
    $map_branch = json_decode($vertical_details['branch'],true);
}

?>
<style>
    .error {
        color: red;
        font-size: small;
    }
</style>

<!-- Modal -->
<div class="card-body">
    <div class="border p-4 rounded">
        <div class="card-title d-flex align-items-center justify-content-between">
            <h6 class="mb-0">Add Vertical</h6>
            <a type="button" class="close" data-dismiss="modal" id = "hide-modal" aria-hidden="true"><i class="bi bi-x-circle-fill"></i></a>
        </div>
        <hr/>
        <form role="form" id="form-vertical" action="/app/vertical/storeAndupdateVertical" method="POST">
            <div class="row">
                <label for="name" class="col-sm-4 col-form-label">Name</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control form-control-sm" name="name" value="<?php echo (count($vertical_details)> 0) ? $vertical_details['name'] : '' ?>" placeholder="eg: Jamia">
                </div>
            </div>
            <div class="row mb-1">
                <label class="col-sm-4 col-form-label">Organization</label>
                <div class="col-sm-8">
                <select type="text" class="form-control form-control-sm single-select select2" name="organization" id="organization" onchange="checkOrganizationforUpdate(this.value)"></select>
                </div>
            </div>
            <div class="row mb-1">
                <label class="col-sm-4 col-form-label">Branch</label>
                <div class="col-sm-8">
                    <select type="text" class="form-control form-control-sm multiple-select select2" multiple="multiple" name="branch[]" id="branch"> 
                    </select>
                </div>
            </div>
            <div class="row">
                <label class="col-sm-4 col-form-label">Uploade Image</label>
                <div class="col-sm-8">
                    <?php if(!empty($vertical_details['image'])) { ?>
                        <img src="<?=$vertical_details['image']?>" width="50px" class="mb-2" alt="uploaded image">
                    <?php } ?>
                    <input class="form-control form-control-sm mb-3" type="file" accept="image/*" name="image"> 
                </div>
            </div>
            <hr/>
            <div class="row mb-2">
                <div class="col-sm-12 text-end">
                    <button type="submit" class="btn btn-primary btn-sm"><?= (!empty($branch_details)) ? "Update" : "Register"?>   
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>


<script src="/assets/plugins/jquery-validation/js/jquery.validate.js"></script>
<script>

$(document).ready(function(){
    let filter_data_field = Array.from(document.getElementById("form-vertical").querySelectorAll("select")).map((param) => param.id).forEach(param => {
        $("#"+param).select2({
            placeholder: 'Choose ' + param.charAt(0).toUpperCase() + param.slice(1,param.length), 
            allowClear: true,
            width: '100%' , 
            dropdownParent: $('#mdmodal')
        });
    });
    var organization_id = '';
    <?php if(isset($vertical_details['organization_id'])) { ?>
        organization_id = <?=$vertical_details['organization_id']?>;
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

function checkOrganizationforUpdate(organization_id) {
    <?php if(isset($_REQUEST['vertical_id']) && !empty($_REQUEST['vertical_id'])) { ?>
        var id = '<?=$_REQUEST['vertical_id']?>';
        var search = 'Vertical';
        $.ajax({
            url : "/app/common/checkAssignDetails" ,
            type : "post" , 
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
                    if( data.message == "vertical not mapped") {
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
    <?php if(isset($vertical_details['branch'])) { ?>
        branch_id = <?=$vertical_details['branch']?>;
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
        }
    });
}

$(function(){
    $('#form-vertical').validate({
    rules: {
        name: {required:true},
        organization : {required:true},
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

$("#form-vertical").on('submit',function(e){
    e.preventDefault();
    if ($("#form-vertical").valid()) {
        var formData = new FormData(this);
        <?php if(count($vertical_details) > 0) { ?>
            formData.append("ID",<?=$_REQUEST['vertical_id']?>);
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
                    if(data.message == 'Department assign on this vertical') {
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
