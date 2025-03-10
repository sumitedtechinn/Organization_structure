<!-- Modal -->
<div class="card-body" >
    <div class="border p-4 rounded">
        <div class="card-title d-flex align-items-center justify-content-start">
            <h5 class="mb-0">Set DeadLine Date</h5>
        </div>
        <hr/>
        <form role="form" id="form-deadline" action="/app/tickets/storeAndupdateTicket" method="POST">
            <div class="mb-3">
                <label class="form-label">Pick a Date</label>
                <input type="date" class="form-control" name="deadline_date" id="deadline_date">
            </div>
            <hr/>
            <div class="row mb-2">
                <div class="col-sm-12 text-end">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal" id="cancelsetDeadline">Close</button>
                    <button type="submit" class="btn btn-primary">Set</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="/assets/plugins/jquery-validation/js/jquery.validate.js"></script>
<script type="text/javascript">  

$('#hide-modal').click(function() {
    $('.modal').modal('hide');
});

$(function(){
    $('#form-deadline').validate({
    rules: {
        deadline_date: {required:true}
    },
    highlight: function (element) {
        $(element).addClass('error');
        $(element).closest('.form-control').addClass('has-error');
    },
    unhighlight: function (element) {
        $(element).removeClass('error');
        $(element).closest('.form-control').removeClass('has-error');
    },
    errorPlacement: function (error, element) {
        if (element.parent('.input-group').length) {
            error.insertAfter(element.parent());
        } else if (element.is('select')) {
            error.insertAfter(element.next('.select2'));
        } else {
            error.insertAfter(element);
        }
    }
    });
})
</script>