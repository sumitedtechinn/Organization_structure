<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-top.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-bottom.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/topbar.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/menu.php');?>

<style>
    .error {
        color: red;
        font-size: small;
    }
</style>


<!--start content-->
<main class="page-content" >
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Projection Table</h5>
                <div class="d-flex justify-content-end gap-2 col-sm-2" style="z-index: 0 !important;">
                    <div class="col-sm-10">
                    <select type="text" class="form-control month_container form-control-sm single-select select2" name="select_month" id="select_month" onchange="getSelectedMonth()">
                        <option value=""> Select Month</option>
                        <option value="1" > January</option>
                        <option value="2" > February</option>
                        <option value="3" > March</option>
                        <option value="4" > April</option>
                        <option value="5" > May</option>
                        <option value="6" > June</option>
                        <option value="7" > July</option>
                        <option value="8" > August</option>
                        <option value="9" > September</option>
                        <option value="10"> October</option>
                        <option value="11"> November</option>
                        <option value="12"> December</option> 
                        <option value="13">All Month</option>   
                    </select>
                    </div>
                    <button class="btn btn-primary" style="font-size: smaller;" id ="return_button">Go To Projection</button>
                </div>
            </div>
            <div class="table-responsive mt-3">
                <table class="table table-striped align-middle" id="projectionTable">
                    <thead class="table-secondary">
                        <tr>
                            <th>Image</th>
                            <th>User</th>
                            <th>Projection Type</th>                            
                            <th>Organization Info</th>
                            <th>Month</th>
                            <th>Closure Pending</th>
                            <th>Closure Complete</th>
                            <th>View Closure</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</main>
<!--end page main-->

<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer-top.php');?>
<script src="/assets/plugins/select2/js/select2.min.js"></script>
<script src="/assets/js/form-select2.js"></script>
<script type="text/javascript">

$('#projectionTable').DataTable({
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    "searching": false ,
    'ajax': {
        'url': '/app/addProjection/addProjection_server',
        'type': 'POST',
        'data' : function (d) {
            d.selected_month = $('#select_month').val(); 
            d.projectionType = 'delete-projection';
        }
    },
    'columns': [{
            data: "user_image",
            render : function(data,type,row) {
                var img = '<div class="d-flex align-items-center gap-3 cursor-pointer"><img src="'+data+'" class="rounded-circle" width="44" height="44" alt=""></div>';
                return img;
            }
        },{
            data: "user" ,
            render : function(data,type,row) {
                var name = (row.user_delete == 'Yes') ? '<span class="text-danger">'+data+'</span>' : '<span>'+data+'</span>';
                var designation = (row.designation_delete == 'Yes') ? '<span class="text-danger">'+row.designation+'</span>' : '<span>'+row.designation+'</span>';
                return  '<div style="font-size:small;"><p class = "mb-1"><b>Name : </b>'+name+'</p><p class = "mb-1 text-wrap" style = "width:250px;" ><b>Designation : </b>'+designation+'</p></div>';
            }
        },{
            data: "projection_type" ,
            render : function(data,type,row) {
                var projectionType = (row.projection_type_delete == 'Yes') ? '<div class="text-danger">'+data+'</div>' : '<div>'+data+'</div>';
                return projectionType;
            } 
        },{
            data: "Organization Info" ,
            render : function(data,type,row) {
                var organization = (row.organization_delete == 'Yes') ? '<span class = "text-danger">'+row.organization+'</span>' : '<span>'+row.organization+'</span>'; 
                var branch = (row.branch_delete == 'Yes') ? '<span class="text-danger">'+row.branch+'</span>' : '<span>'+row.branch+'</span>';
                var vertical = (row.vertical_delete == 'Yes') ? '<span class="text-danger">'+row.vertical+'</span>' : '<span>'+row.vertical+'</span>';
                var department = (row.department == 'Yes') ? '<span class="text-danger">'+row.department+'</span>' : '<span>'+row.department+'</span>';
                return '<div style="font-size:small;"><p class = "mb-1"><b>Organization : </b>'+organization+'</p><p class = "mb-1"><b>Branch : </b>'+branch+'</p><p class = "mb-1"><b>Vertical : </b>'+vertical+'</p><p class = "mb-1"><b>Department : </b>'+department+'</p></div>';
            }
        },{
            data : "month",
            render : function(data,type,row) {
                return '<div class="badge rounded-pill bg-info m-1" style="background-color:#1a2232 !important;font-size:14px;">'+data+'</div>';
            }
        },{
            data : "numOfClosurePending", 
        },{
            data : "numOfClosure" ,
            render : function(data,type,row) {
                return '<div>'+row.numOfClosureComplete+'/<b>'+data+'</b></div>';
            }
        },{
            data : "View Clouser" , 
            render : function(data,type,row) {
                var view = '<button type="button" class="btn btn-outline-info px-4" style="font-size:small" onclick = "viewClosureDetails('+row.ID+')">View </button>';
                return view;
            }
        },{         
            data : "Action" ,
            render : function(data, type, row) {
                var table = "Projection";
                var restore = '<button type="button" class="btn btn-info text-white px-4" onclick = "restoreDetails('+row.ID+',&#39;'+table+'&#39;)">Restore</button>';
                var del = '<button type="button" class="btn btn-danger px-4" onclick = "parmanentDeleteDetails('+row.ID+',&#39;'+table+'&#39;)">Delete</button>';
                return '<div class = "table-actions d-flex align-items-center gap-2 fs-6">' + restore+del + '</div>';
            }
        }
    ],
    "dom": '<"row"<"col-sm-12 col-md-6 d-flex justify-content-start"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>><"table-responsive"t><"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
    "destroy": true,
    "scrollCollapse": true,
    drawCallback: function(settings, json) {
        $('[data-toggle="tooltip"]').tooltip({
            template: '<div class="tooltip custom-tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'

        });
    },
    "aaSorting": []
});

function getSelectedMonth() {
    $('#projectionTable').DataTable().ajax.reload(null, false);
}

$("#return_button").on('click',function(){
    window.location.href = "/projection/addprojection";
});

</script>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer-bottom.php');?>