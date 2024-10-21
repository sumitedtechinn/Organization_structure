<?php

$organization_details = [];

if (isset($_REQUEST['organization_id']) && !empty($_REQUEST['organization_id'])) {
    ## Database configuration
    include '../../includes/db-config.php';
    session_start();
    $organization = $conn->query('SELECT * FROM `organization` WHERE id = '.$_REQUEST['organization_id']);
    $organization_details = mysqli_fetch_assoc($organization);
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
            <?php if(!empty($organization_details)) { ?>
                Update Organization
            <?php } else { ?>
                Add Organization
            <?php }?>
            </h5>
            <a type="button" class="close" data-dismiss="modal" id = "hide-modal" aria-hidden="true"><i class="bi bi-x-circle-fill"></i></a>
        </div>
        <hr/>
        <form role="form" id="form-organization" action="/app/organization/storeAndupdateOrganization" method="POST">
            <div class="row">
                <label for="name" class="col-sm-4 col-form-label">Organization Name</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control form-control-sm" name="name" value="<?php echo (count($organization_details) > 0) ? $organization_details['organization_name'] : '' ?>" placeholder="eg: Edtech">
                </div>
            </div>
            <div class="row mb-1">
                <label class="col-sm-4 col-form-label">Start Date</label>
                <div class="col-sm-8">
                    <input type="date" class="form-control form-control-sm" value="<?php echo (count($organization_details) > 0) ? $organization_details['start_date'] : '' ?>" name="start_date">
                </div>
            </div>
            <div class="row">
                <label class="col-sm-4 col-form-label">Uploade Logo</label>
                <div class="col-sm-8">
                    <?php if(!empty($organization_details['logo'])) { ?>
                        <img src="<?=$organization_details['logo']?>" width="50px" class="mb-2" alt="uploaded logo">
                    <?php } ?>
                    <input class="form-control form-control-sm " type="file" accept="image/*" name="image"> 
                </div>
            </div>
            <hr/>
            <div class="row mb-2">
                <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary">
                    <?php if (!empty($organization_details)) { ?>
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
<script type="text/javascript">

$(function(){
    $('#form-organization').validate({
    rules: {
        name: {required:true},
        start_date : {required:true},
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


$("#form-organization").on('submit',function(e){
    e.preventDefault();
    if ($("#form-organization").valid()) {
        var formData = new FormData(this);
        <?php if(isset($_REQUEST['organization_id'])) { ?>
            formData.append('ID',<?=$_REQUEST['organization_id']?>);
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
                    toastr.error(data.message);
                }
            }
        });
    }
});

</script>
