<?php 
$website_details = [];
include '../../includes/db-config.php';
//session_start();

$website_details = [];
if (isset($_REQUEST['website_id'])) {}

?>
<!-- Modal -->
<div class="card-body">
    <div class="border p-4 rounded">
        <div class="card-title d-flex align-items-center justify-content-between">
            <h5 class="mb-0"><?php echo !empty($website_details) ? "Update" : "Add" ?> Website</h5>
            <a type="button" class="close" data-dismiss="modal" id = "hide-modal" aria-hidden="true"><i class="bi bi-x-circle-fill"></i></a>
        </div>
        <hr/> 
        <form role="form" id="form-website" action="/app/leads/storeAndUpdateWebsite" method="POST">
            <div class="row">
                <label for="name" class="col-sm-4 col-form-label">Website Name</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control form-control-sm" name="websiteName" placeholder="eg: Jamia">
                </div>
            </div>
            <div class="row mb-1">
                <label class="col-sm-4 col-form-label">Website Url</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control form-control-sm" name="websiteUrl" placeholder="eg: https://www.google.com">
                </div>
            </div>
            <hr/>
            <div class="row mb-2">
                <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary">
                        <?php echo !empty($website_details) ? "Update" : "Register" ?>    
                    </button>
                </div>
            </div>
        </form>        
    </div>
</div>
<script src="/assets/plugins/jquery-validation/js/jquery.validate.js"></script>
<script src="/assets/plugins/select2/js/select2.min.js"></script>
<script src="/assets/js/form-select2.js"></script>
<script>

$(function(){
    $('#form-website').validate({
    rules: {
        websiteName: {required:true},
        websiteUrl : {required:true},
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

$("#form-website").on('submit',function(e){
    e.preventDefault();
    if ($("#form-website").valid()) {
        var formData = new FormData(this);
        <?php if(!empty($website_details) > 0) { ?>
            formData.append("ID",<?=$_REQUEST['website_id']?>);
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
                    $("#websiteTable").DataTable(websiteSetting);
                } else {
                    toastr.error(data.message); 
                }
            }
        });
    }

});
</script>