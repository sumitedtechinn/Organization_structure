<?php

require '../../includes/db-config.php';
session_start();

$created_permission_list = $conn->query("SELECT pages.Name as `page`, Permission_type.Name as `permission_type_name` , permission.ID as `permission_id`  FROM `permission` LEFT JOIN pages ON pages.ID = permission.page LEFT JOIN Permission_type ON Permission_type.ID = permission.permission_type WHERE permission.Deleted_at IS NULL");

$permissionOnPageBasis = [];

if($created_permission_list->num_rows > 0 ) {
    while ($row = mysqli_fetch_assoc($created_permission_list)) {
        $permissionOnPageBasis[$row['page']][$row['permission_id']] = $row['permission_type_name'];
    }
}

$allotedPermissions = [];
if ( isset($_REQUEST['id'])) {
    $db_permission = $conn->query("SELECT * FROM `role_has_permissions` WHERE role_id = '".$_REQUEST['id']."'");
    while($row = mysqli_fetch_assoc($db_permission)) {
        $allotedPermissions[] = $row['permission_id'];
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
    input[type=checkbox]:checked {
        background-color: #32bfff !important;
        color: #ffffff !important;
    }
    input[type='checkbox']:after {
        box-shadow: none !important;
    }
</style>

<div class="card-body">
    <div class="border p-4 rounded">
        <div class="card-title text-center">
            <h4 class="mb-0">Edit Role <?=$_REQUEST['fullname']?></h4>
            <p class="text-muted">Set role permissions</p>
        </div>
        <form id="editRoleForm" action="/app/roles/storeUpdatedRoleAndPermission" method="POST" class="row g-3">
            <div class="col-12 mb-4">
                <label class="form-label" for="name">Role Name</label>
                <input type="text" id="name" name="name" value="<?=$_REQUEST['fullname']?>" class="form-control" placeholder="Enter a role name"/>
            </div>
            <div class="col-12">
                <h5 class="text-muted">Role Permissions</h5>
                <!-- Permission table -->
                <div class="table-responsive">
                    <table class="table table-flush-spacing">
                        <tbody class="text-muted">
                            <?php foreach ($permissionOnPageBasis as $page => $pagePermission) { ?>
                            <tr>
                                <td class="text-nowrap fw-medium">
                                <?=ucwords($page)?>
                                </td>
                                <td>
                                <div class="d-flex">
                                    <?php foreach ($pagePermission as $id=>$name) { ?>
                                    <div class="form-check me-3 me-lg-5">
                                        <input class="form-check-input permission-checkboxes" <?php echo in_array($id, $allotedPermissions) ? 'checked' : '' ?> name="permissions[]"
                                        value="<?=$id?>" type="checkbox" id="permission-checkbox-<?=$id?>" />
                                        <label class="form-check-label" for="permission-checkbox-<?=$id?>">
                                        <?=$name?>
                                        </label>
                                    </div>
                                    <?php } ?>
                                </div>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <!-- Permission table -->
            </div>
            <div class="col-12 text-center mt-4">
                <button type="submit" class="btn btn-info me-sm-3 me-1 text-white">Submit</button>
                <button type="reset" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
            </div>    
        </form>
    </div>
</div>


<script src="/assets/plugins/jquery-validation/js/jquery.validate.js"></script>
<script src="/assets/plugins/select2/js/select2.min.js"></script>
<script src="/assets/js/form-select2.js"></script>
<script type="text/javascript">

$(function() {
    $('#selectAll').click(function() {
        if ($(this).prop("checked")) {
            console.log("came to check section");
            $(".permission-checkboxes").prop("checked", true);
        } else {
            console.log("came to un-check section");
            $(".permission-checkboxes").prop("checked", false);
        }
    });

    $('.permission-checkboxes').click(function() {
        if ($(".permission-checkboxes").length == $(".permission-checkboxes:checked").length) {
            $("#selectAll").prop("checked", true);
        } else {
            $("#selectAll").prop("checked", false);
        }
    });
})

$(function(){
    $('#editRoleForm').validate({
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

$("#editRoleForm").submit(function(e) {
    e.preventDefault();
    if ($("#editRoleForm").valid()) {
        $(':input[type="submit"]').prop('disabled', true);
        var formData = new FormData(this);
        formData.append("id","<?=$_REQUEST['id']?>");
        $.ajax({
            url: this.action,
            type: 'post',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                $(':input[type="submit"]').prop('disabled', false);
                if (response.status == 200) {
                    toastr.success(response.message);
                    $(".modal").modal('hide');
                    setTimeout(function() {
                    window.location.reload();
                    }, 2000)
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(response) {
                $(':input[type="submit"]').prop('disabled', false);
                toastr.error(response.message);
            }
        });
    }
})

</script>