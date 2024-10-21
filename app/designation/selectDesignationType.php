<div class="card-body">
    <div class="border p-4 rounded">
        <div class="card-title d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Select Designation Type</h5>
            <a type="button" class="close" data-dismiss="modal" id = "hide-modal" aria-hidden="true"><i class="bi bi-x-circle-fill"></i></a>
        </div>
        <hr/>
        <div class="row row-cols-1 row-cols-lg-3 justify-content-center g-lg-5">
            <div class="col">
                <div class="card">
                    <img src="/../../assets/images/sample_user.jpeg" class="card-img-top" alt="...">
                    <div class="card-body">
                    <h5 class="card-title">Organization</h5>
                    <p class="card-text">Add designation inside Organization</p>
                    <a href="#" class="btn btn-primary" id="InsideOrganization" onclick="gotoFormPage(this.id)">Add Designation</a>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card">
                    <img src="/../../assets/images/sample_user.jpeg" class="card-img-top" alt="...">
                    <div class="card-body">
                    <h5 class="card-title">Branch</h5>
                    <p class="card-text">Add designation inside Branch</p>
                    <a href="#" class="btn btn-primary" id="InsideBranch" onclick="gotoFormPage(this.id)">Add Designation</a>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card">
                    <img src="/../../assets/images/sample_user.jpeg" class="card-img-top" alt="...">
                    <div class="card-body">
                    <h5 class="card-title">Department</h5>
                    <p class="card-text">Add designation inside Department</p>
                    <a href="#" class="btn btn-primary" id="InsideDepartment" onclick="gotoFormPage(this.id)">Add Designation</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">

function gotoFormPage(page_type) {
    var url = "/app/designation/insertAndupdateDesignation"+page_type;    
    $.ajax({
        url : url, 
        type : "get", 
        success : function(data) {
            $('#lg-modal-content').html(data);
            $('#lgmodal').modal('show');
        }
    })
}

$('#hide-modal').click(function() {
    $('.modal').modal('hide');
});
</script>