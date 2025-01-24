<!-- Leave Form Modal -->
<div class="card-body">
    <div class="border p-4 rounded shadow">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0 text-primary">Leave Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <hr>
        <div>
            <h5> Subject : <?=$_REQUEST['mail_subject']?> </h5>
        </div>
        <br>
        <div style="font-size: medium;">
            <?=$_REQUEST['mail_body']?>
        </div>
        <br>
        <?php if(!empty($_REQUEST['file_path'])) { ?>
        <a href="<?=$_REQUEST['file_path']?>" style = "color: white;" type="button" target="_blank" class="btn btn-sm btn-info">
            View Attachment
        </a>
        <?php } ?>
        <?php if($_REQUEST['status'] == '3') { ?>
            <!-- Form Section -->
            <form id="form-applyLeave" action="/app/leaveRecord/storeAndupdateLeave" method="POST">
                <!-- Submit Button -->
                <hr>
                <?php if($_REQUEST['type'] == 'requestedLeave') { ?>
                    <div class="text-end">
                        <button type="submit" name="status" value="approved" class="btn btn-sm btn-success">Approved</button>
                        <button type="submit" name="status" value="dis_approved" class="btn btn-sm btn-danger">Dis-Approved</button>
                    </div>
                <?php } else { ?>
                    <div class="text-end">
                        <button type="submit" name="status" value="widthdraw" class="btn btn-sm btn-danger"><i class="bi bi-arrow-counterclockwise"></i> Widthdraw</button>
                    </div>
                <?php } ?>
            </form>
        <?php } ?>
    </div>
</div>

<script>
$("#form-applyLeave").on('submit',function(e){
    e.preventDefault();
    var formData = new FormData(this);
    const clickButton = document.activeElement;
    const status = (clickButton.value == 'approved') ? 'approved' : "dis_approved";
    formData.append('leave_id','<?=$_REQUEST['leave_id']?>');
    formData.append('formType','updateLeaveStatus');
    formData.append('status',clickButton.value);
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
        } else {
            $('.table').DataTable().ajax.reload(null, false);
        }
    });
});
</script>