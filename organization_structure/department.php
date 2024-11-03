<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-top.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-bottom.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/topbar.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/menu.php');?>
<?php 
$node_color = $conn->query("SELECT color FROM Department LIMIT 1");
$node_color = mysqli_fetch_column($node_color);
?>

<!--start content-->
<main class="page-content">
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h5 class="mb-0">Department Details</h5>
                <div class="d-flex justify-content-end gap-2 col-sm-2">
                    <?php if($_SESSION['role'] != '2') { ?>
                    <div class="col-sm-2 ">
                        <input type="color" class="form-control form-control-color" name="node_color" id="node_color" title="Select Node Color" value="<?php echo !is_null($node_color) ? $node_color : '' ?>"  onchange="setNodeColor(this.value,'Department')">
                    </div>
                    <?php } ?>
                    <?php if(in_array('Department Delete',$_SESSION['permission'])) { ?>
                    <div class="theme-icons shadow-sm p-2 cursor-pointer rounded" title="Go to Trash" data-bs-toggle="tooltip" id = "trash_button">
                        <i class="bi bi-trash-fill"></i>
                    </div>
                    <button class="btn btn-primary" style="font-size: small;" id ="return_button">Go To Department</button>
                    <?php } ?>
                    <?php if( in_array('Department Create',$_SESSION['permission'])) { ?>
                    <div class="d-flex align-items-center theme-icons shadow-sm p-2 cursor-pointer rounded" title="Add" onclick="addDepartment()" data-bs-toggle="tooltip">
                        <i class="bi bi-plus-circle-fill" id="add_department"></i>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-sm-4" style="z-index: 0 !important;">
                    <select type="text" class="form-control form-control-sm single-select select2" name="organization_filter" id="organization_filter" onchange="reloadTable(this.id)">
                    </select>
                </div>
                <div class="col-sm-4" style="z-index: 0 !important;">
                    <select type="text" class="form-control form-control-sm single-select select2" name="branch_filter" id="branch_filter" onchange="reloadTable(this.id)">
                    </select>
                </div>
                <div class="col-sm-4" style="z-index: 0 !important;">
                    <select type="text" class="form-control form-control-sm single-select select2" name="vertical_filter" id="vertical_filter" onchange="reloadTable(this.id)">
                    </select>
                </div>
            </div>
            <div class="table-responsive mt-3">
                <table class="table align-middle" id="departmentTable">
                    <thead class="table-secondary">
                        <tr>
                            <th>Department</th>
                            <th>Organization</th>
                            <th>Branch</th>
                            <th>Vertical</th>
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

$(document).ready(function(){
    var filter_data_field = ['organization','branch','vertical'];
    $.ajax({
        url : "/app/common/getAllFilterData" , 
        type : "post",
        contentType: 'json',  // Set the content type to JSON 
        data: JSON.stringify(filter_data_field), 
        dataType: 'json', 
        success : function(data) {
            for (const key in data) {
                $("#"+key+"_filter").html(data[key]);
            }
        }   
    })
});


var departmentSettings = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    'ajax': {
        'url': '/app/department/department-server',
        'type': 'POST',
        'data' : function(d) {
            d.organizationFilter = $("#organization_filter").val();
            d.branchFilter = $("#branch_filter").val();
            d.verticalFilter = $("#vertical_filter").val();
        }
    },
    'columns': [{
            data: "department",
            render : function(data,type,row) {
                if(row.logo != null ) {
                    var image = '<div class="d-flex align-items-center gap-3 cursor-pointer"><img src="'+row.logo+'" class="rounded-circle" width="44" height="44" alt=""></div>';
                } else { 
                    var image = '<div class="d-flex align-items-center gap-3 cursor-pointer"><img src="../../assets/images/sample_vertical.jpg" class="rounded-circle" width="44" height="44" alt=""></div>';
                }
                var name = '<div class = "d-flex align-items-center gap-3 fw-bold">'+data+'</div>';
                return '<div class = "d-flex align-items-center gap-3 fs-6">'+image+name+'</div>';
            }
        },{
            data: "organization",
        },{
            data: "branch" ,
        },{
            data: "vertical" ,
        },{         
            data : "Action" ,
            render : function(data, type, row) {
                var edit = ''; var del = ''; var table = 'Department';
                <?php if(in_array('Department Update',$_SESSION['permission'])) { ?>
                var edit = '<div class="text-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit" onclick = "updateDetails('+row.ID+')"><i class="bi bi-pencil-fill"></i></div>';
                <?php } ?>
                <?php if(in_array('Department Delete',$_SESSION['permission'])) { ?>
                var del = '<div class="text-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete" onclick = "checkAssignDetails('+row.ID+',&#039;'+table+'&#039;)"><i class="bi bi-trash-fill"></i></div>';
                <?php } ?>
                return '<div class = "table-actions d-flex align-items-center gap-3 fs-6">' + edit+del + '</div>';
            }
        }
    ],
    "sDom": "lf<t><'row'<p i>>",
    "destroy": true,
    "scrollCollapse": true,
    "oLanguage": {
        "sInfo": "Showing <b>_START_ to _END_</b> of _TOTAL_ entries"
    },
    drawCallback: function(settings, json) {
        $('[data-toggle="tooltip"]').tooltip();
    },
    "aaSorting": []
};

var departmentTrashSettings = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    'ajax': {
        'url': '/app/department/department-server',
        'type': 'POST',
        'data' : function(d) {
            d.departmentType = "deleteDepartment";
            d.organizationFilter = $("#organization_filter").val();
            d.branchFilter = $("#branch_filter").val();
            d.verticalFilter = $("#vertical_filter").val();
        }
    },
    'columns': [{
            data: "department",
            render : function(data,type,row) {
                if(row.logo != null ) {
                    var image = '<div class="d-flex align-items-center gap-3 cursor-pointer"><img src="'+row.logo+'" class="rounded-circle" width="44" height="44" alt=""></div>';
                } else { 
                    var image = '<div class="d-flex align-items-center gap-3 cursor-pointer"><img src="../../assets/images/sample_vertical.jpg" class="rounded-circle" width="44" height="44" alt=""></div>';
                }
                var name = '<div class = "d-flex align-items-center gap-3 fw-bold">'+data+'</div>';
                return '<div class = "d-flex align-items-center gap-3 fs-6">'+image+name+'</div>';
            }
        },{
            data: "organization",
        },{
            data: "branch" ,
        },{
            data: "vertical" ,
        },{         
            data : "Action" ,
            render : function(data, type, row) {
                var table = "Department";
                var restore = '<button type="button" class="btn btn-info text-white px-4" onclick = "restoreDetails('+row.ID+',&#39;'+table+'&#39;)">Restore</button>';
                var del = '<button type="button" class="btn btn-danger px-4" onclick = "parmanentDeleteDetails('+row.ID+',&#39;'+table+'&#39;)">Delete</button>';
                return '<div class = "table-actions d-flex align-items-center gap-3 fs-6">' + restore+del + '</div>';
            }
        }
    ],
    "sDom": "lf<t><'row'<p i>>",
    "destroy": true,
    "scrollCollapse": true,
    "oLanguage": {
        "sInfo": "Showing <b>_START_ to _END_</b> of _TOTAL_ entries"
    },
    drawCallback: function(settings, json) {
        $('[data-toggle="tooltip"]').tooltip();
    },
    "aaSorting": []
};

function reloadTable() {
    $('.table').DataTable().ajax.reload(null, false);
}


$(document).ready(function() {
    $("#return_button").css('display','none');
    $('#departmentTable').dataTable(departmentSettings);
});

$("#trash_button").on('click',function(){
    $('#departmentTable').dataTable(departmentTrashSettings);
    $("#return_button").css('display','block');
    $("#trash_button").css('display','none');
});


$("#return_button").on('click',function(){
    $('#departmentTable').dataTable(departmentSettings);
    $("#return_button").css('display','none');
    $("#trash_button").css('display','block');
});


function addDepartment() {
    $.ajax({
        url : "/app/department/insertAndupdateDepartment", 
        type : 'get',
        success : function(data){
            $('#lg-modal-content').html(data);
            $('#lgmodal').modal('show');
        }
    });
}

function updateDetails(id) {
    $.ajax({
        url : "/app/department/insertAndupdateDepartment", 
        type : 'post',
        data : {
            id
        },
        success : function(data){
            $('#lg-modal-content').html(data);
            $('#lgmodal').modal('show');
        }
    });
}

</script>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer-bottom.php');?>