<?php

## Database configuration
include '../../includes/db-config.php';
session_start();

$permission_details = [];

if(isset($_REQUEST['id'])) {
    $permission_details = $conn->query("SELECT * FROM permission WHERE ID = '".$_REQUEST['id']."'");
    $permission_details = mysqli_fetch_assoc($permission_details);
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
            <h5 class="mb-0">Add Permission</h5>
            <a type="button" class="close" data-dismiss="modal" id = "hide-modal" aria-hidden="true"><i class="bi bi-x-circle-fill"></i></a>
        </div>
        <hr/>
        <form role="form" id="form-addpermission" action="/app/permission/storePermission" method="POST">
            <div class="row mb-2">
                <label for="name" class="col-sm-4 col-form-label" style="font-size: small;">Permission Type</label>
                <div class="col-sm-8">
                    <select type="text" class="form-control form-control-sm multiple-select select2" multiple="multiple" name="permission_type[]">
                    <option value="">Select</option>
                    <?php $permission_type = $conn->query("SELECT * FROM `Permission_type`");
                    while($row = mysqli_fetch_assoc($permission_type)) {
                        if(!empty($permission_details) && $permission_details['permission_type'] == $row['ID']) {
                            echo '<option value = "'.$row['ID'].'" selected >'.$row['Name'].'</option>';
                        } else {
                            echo '<option value = "'.$row['ID'].'">'.$row['Name'].'</option>';
                        }
                    }
                    ?>
                    </select>
                </div>
            </div>
            <div class="row mb-2">
                <label for="name" class="col-sm-4 col-form-label" style="font-size: small;">Apply Page</label>
                <div class="col-sm-8">
                    <select type="text" class="form-control form-control-sm single-select select2" name="apply_page">
                    <option value="">Select</option>
                    <?php $pages = $conn->query("SELECT * FROM `pages`");
                    while($row = mysqli_fetch_assoc($pages)) {
                        if(!empty($permission_details) && $permission_details['page'] == $row['ID']) {
                            echo '<option value = "'.$row['ID'].'" selected >'.$row['Name'].'</option>';
                        } else {
                            echo '<option value = "'.$row['ID'].'">'.$row['Name'].'</option>';
                        }
                    }
                    ?>
                    </select>
                </div>
            </div>
            <hr/>
            <div class="row">
                <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary">Add</button>
                </div>
            </div>
        </form>
    </div>
</div>


<script src="/assets/plugins/jquery-validation/js/jquery.validate.js"></script>
<script src="/assets/plugins/select2/js/select2.min.js"></script>
<script src="/assets/js/form-select2.js"></script>
<script type="text/javascript">

$(function(){
    $('#form-addpermission').validate({
    rules: {
        permission_type : {required:true},
        apply_page : {required:true}
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

$("#form-addpermission").on('submit',function(e){
    e.preventDefault();
    if ($("#form-addpermission").valid()) {
        var formData = new FormData(this);
        <?php  if (isset($_REQUEST['id'])) { ?>
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
                } else if (data.status == 400) {
                    if ( data.message == "Duplicate Permissions") {
                        Swal.fire({
                            title : data.message,
                            text : data.text ,
                            icon: 'error',
                        }); 
                    }  else {
                        toastr.error(data.message);    
                    }
                }
            }
        });
    }
});
</script>