<?php 

## Database configuration
include '../../includes/db-config.php';
session_start();

$role_details = [];

if(isset($_POST['id'])) {
    $role = $conn->query("SELECT name , guard_name FROM `roles` WHERE ID = '".$_POST['id']."'");
    $role_details = mysqli_fetch_assoc($role);
}
?>

<div class="card-body">
    <div class="border p-4 rounded">
        <div class="card-title d-flex align-items-center justify-content-between">
            <h5 class="mb-0">
                <?php if(empty($role_details)) {?>
                    Add Role
                <?php } else { ?>
                    Update Role
                <?php } ?>
            </h5>
            <a type="button" class="close" data-dismiss="modal" id = "hide-modal" aria-hidden="true"><i class="bi bi-x-circle-fill"></i></a>
        </div>
        <hr/>
        <form role="form" id="form-addrole" action="/app/roles/storeRoles" method="POST">
            <div class="row mb-2">
                <label for="name" class="col-sm-4 col-form-label" style="font-size: small;">Name</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control form-control-sm" name="name" value="<?php echo !empty($role_details) ? $role_details['name']  : '' ?>" placeholder="eg: Website User">
                </div>
            </div>
            <div class="row">
                <label for="name" class="col-sm-4 col-form-label" style="font-size: small;">Guard Name</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control form-control-sm" name="guard_name" value="<?php echo !empty($role_details) ? $role_details['guard_name'] : '' ?>" placeholder="eg: user">
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
<script type="text/javascript">

$(function(){
    $('#form-addrole').validate({
    rules: {
        name: {required:true},
        guard_name : {required:true},
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

$("#form-addrole").on('submit',function(e){
    e.preventDefault();
    if ($("#form-addrole").valid()) {
        var formData = new FormData(this);
        <?php if(isset($_POST['id'])) { ?>
            formData.append('id',<?=$_POST['id']?>);
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
                    location.reload();
                } else {
                    toastr.error(data.message);
                }
            }
        });
    }
});
</script>