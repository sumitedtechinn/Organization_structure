<style>
.label_text{
    font-weight: 500;
    font-size: 16px;
}
</style>
<!-- Modal -->
<div class="card-body" >
    <div class="border p-4 rounded">
        <div class="card-title d-flex align-items-center justify-content-start">
            <h5 class="mb-0">Hold Ticket</h5>
        </div>
        <hr/>
        <form role="form" id="form-holdTicket" action="/app/tickets/storeAndupdateTicket" method="POST">
            <div class="row mb-1">
                <label class="col-sm-12 col-form-label label_text">Hold By</label>
                <div class="col-sm-12">
                    <input class="form-control" name="hold_by" id="hold_by"></input>
                </div>
            </div>
            <div class="row mb-1">
                <label class="col-sm-12 col-form-label label_text">Comment</label>
                <div class="col-sm-12">
                    <textarea class="form-control" name="hold_ticket_comment" id="hold_ticket_comment" rows="3"></textarea>
                </div>
            </div>
            <hr/>
            <div class="row mb-2">
                <div class="col-sm-12 text-end">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal" id="cancelsetDeadline">Close</button>
                    <button type="submit" class="btn btn-primary">Post</button>
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
    $('#form-holdTicket').validate({
    rules: {
        hold_ticket_comment: {required:true} , 
        hold_by : {required:true}
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