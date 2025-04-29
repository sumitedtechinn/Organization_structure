<div class="card-body">
    <div class="border p-4 rounded">
        <div class="card-title d-flex align-items-center justify-content-between">
            <h5 class="mb-0" id="model_heading"></h5>
            <a type="button" class="close" data-dismiss="modal" id = "hide-modal" aria-hidden="true"><i class="bi bi-x-circle-fill"></i></a>
        </div>
        <hr/> 
        <div class="col-sm-12" id="content_box"></div>        
    </div>
</div>

<script>

$('#hide-modal').click(function() {
    $('.modal').modal('hide');
});

</script>