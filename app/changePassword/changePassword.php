<div class="card-body" style="font-size: smaller;">
    <div class="border p-4 rounded">
        <div class="card-title d-flex align-items-center justify-content-between">
            <h6 class="mb-0">Change Password</h6>
            <a type="button" class="close" data-dismiss="modal" id = "hide-modal" aria-hidden="true"><i class="bi bi-x-circle-fill"></i></a>
        </div>
        <hr/>
        <form role="form" id="form-changepassword" action="/app/changePassword/storeNewPassword" method="POST">
            <div class="row">
                <label for="name" class="col-sm-4 col-form-label">Enter Password</label>
                <div class="col-sm-8">
                    <input type="password" class="form-control form-control-sm" name="change_password" id="change_password"/>
                </div>
            </div>
            <hr/>
            <div class="row">
                <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary btn-small" >Submit</button>
                </div>
            </div>
        </form>
    </div>
</div>


<script src="/assets/plugins/jquery-validation/js/jquery.validate.js"></script>
<script type="text/javascript">

$(function(){
    $('#form-changepassword').validate({
    rules: {
        change_password : {required:true},
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
        var password = $("#change_password").val();
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Process.'
        }).then((isConfirm) => {
            if (isConfirm.value === true) {
                $.ajax({
                    url: "/app/changePassword/storeNewPassword", 
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        password
                    },
                    //processData: false,
                    success: function(data) {
                        if (data.status == 200) {
                            $('.modal').modal('hide');
                            alert(data.message);
                        } else {
                            alert(data.message);
                        }
                    }
                });
            }
        }); 
    }
}); 

</script>
