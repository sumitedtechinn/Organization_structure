<div class="card-body">
    <div class="border p-4 rounded">
        <div class="card-title d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Assign Organization Info</h5>
            <a type="button" class="close" data-dismiss="modal" id = "hide-modal" aria-hidden="true"><i class="bi bi-x-circle-fill"></i></a>
        </div>
        <hr/>
        <div class="row justify-content-center g-lg-3">
            <div class="col-sm-4">
                <div class="card">
                    <img src="/../../assets/images/sample_user.jpeg" class="card-img-top" alt="...">
                    <div class="card-body">
                    <h6 class="card-title">Organization</h6>
                    <p class="card-text">Assign inside Organization</p>
                    <a href="#" class="btn btn-primary" style="font-size: 0.8rem;" id="InsideOrganization" onclick="gotoFormPage(this.id)">Add Designation</a>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card">
                    <img src="/../../assets/images/sample_user.jpeg" class="card-img-top" alt="...">
                    <div class="card-body">
                    <h6 class="card-title">Branch</h6>
                    <p class="card-text">Assign inside Branch</p>
                    <a href="#" class="btn btn-primary" style="font-size: 0.8rem;" id="InsideBranch" onclick="gotoFormPage(this.id)">Add Designation</a>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card">
                    <img src="/../../assets/images/sample_user.jpeg" class="card-img-top" alt="...">
                    <div class="card-body">
                    <h6 class="card-title">Vertical</h6>
                    <p class="card-text">Assign inside Vertical</p>
                    <a href="#" class="btn btn-primary" style="font-size: 0.8rem;" id="InsideVertical" onclick="gotoFormPage(this.id)">Add Designation</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">

function gotoFormPage(page_type) {
    var id = '<?=$_REQUEST['id']?>';
    var url = "/app/user/adminOrganizationInfo";    
    $.ajax({
        url : url, 
        type : "post", 
        data : {
            page_type,
            id
        },
        success : function(data) {
            $('#md-modal-content').html(data);
            $('#mdmodal').modal('show');
        }
    })
}

$('#hide-modal').click(function() {
    $('.modal').modal('hide');
});
</script>