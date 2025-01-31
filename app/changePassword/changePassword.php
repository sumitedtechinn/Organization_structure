<style>
.formLabel {
    margin-top: 0.4rem;
    color: #474242;
    font-size: 0.9rem;
}
.error {
    color: red;
    font-size: small;
}
</style>
<div class="card-body" style="font-size: smaller;">
    <div class="border p-4 rounded shadow-sm">
        <div class="card-title d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Change Password</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <hr/>
        <form id="form-changepassword" action="/app/changePassword/storeNewPassword" method="POST">
            <div class="mb-3 row">
                <label for="old_password" class="col-sm-4 formLabel">Old Password</label>
                <div class="col-sm-8">
                    <input type="password" class="form-control form-control-sm" name="old_password" id="old_password" placeholder="Enter old password" required />
                </div>
            </div>
            <div class="mb-3 row">
                <label for="new_password" class="col-sm-4 formLabel">New Password</label>
                <div class="col-sm-8">
                <input type="password" class="form-control form-control-sm" name="new_password" id="new_password" placeholder="Enter new password" required />
                </div>
            </div>
            <div class="mb-3 row">
                <label for="confirm_password" class="col-sm-4 formLabel">Confirm Password</label>
                <div class="col-sm-8">
                    <input type="password" class="form-control form-control-sm" name="confirm_password" id="confirm_password" placeholder="Re-enter new password" required />
                </div>
            </div>
            <hr/>
            <div class="d-flex justify-content-end align-items-center gap-2">
                <button type="submit" class="btn btn-sm btn-primary">Update Password</button>
            </div>
        </form>
    </div>
</div>

<script src="/assets/plugins/jquery-validation/js/jquery.validate.js"></script>
<script type="text/javascript">

jQuery.validator.addMethod("notEqual",function(value,element,param) {
    let paramValue = $(param).val(); 
    return (value == paramValue) ? false : true; 
},"New password must be different from the old password.");

$(function(){
    $('#form-changepassword').validate({
    rules: {
        old_password : {required: true},
        new_password : {required: true,minlength : 8, notEqual : "#old_password"},
        confirm_password : {required : true,equalTo: "#new_password"}
    },
    message : {
        old_password : {
            required: "Please enter your old password", 
            minlength : "Password must be at least 8 characters long"
        },
        new_password : {
            required: "Please enter your new password", 
            minlength : "Password must be at least 8 characters long",
        },
        confirm_password : {
            required: "Please confirm your password.",
            equalTo: "Passwords do not match.",
        }
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

$("#form-changepassword").on('submit',function(e){
    e.preventDefault();
    if ($("#form-changepassword").valid()) {
        var formData = new FormData(this);
        console.log(this.action);
        $.ajax({
            url: this.action, 
            type: 'POST',
            dataType: 'json',
            processData: false,
            contentType: false,
            data: formData,
            success: function(data) {
                if (data.status == 200) {
                    $('.modal').modal('hide');
                    toastr.success(data.message);
                } else {
                    toastr.error(data.message);
                }
            }
        }); 
    }
}); 

</script>
