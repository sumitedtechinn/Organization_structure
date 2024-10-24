<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-top.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-bottom.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/topbar.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/menu.php');?>

<!--start content-->
<main class="page-content">
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Permissions List</h5>
                <div class="d-flex justify-content-end gap-2 col-sm-2" style="z-index: 0 !important;">
                    <div class="col-sm-12">
                        <select type="text" class="form-control form-control-sm single-select select2" id = "apply_page_filter" onchange="applyFilter()">
                        <option value="">Select Page</option>
                        <?php $pages = $conn->query("SELECT * FROM `pages`");
                        while($row = mysqli_fetch_assoc($pages)) {
                            echo '<option value = "'.$row['ID'].'">'.$row['Name'].'</option>';
                        }
                        ?>
                        </select>
                    </div>
                    <div class="col-sm-12">
                        <select type="text" class="form-control form-control-sm single-select select2" id = "permission_type_filter" onchange="applyFilter()">
                        <option value="">Select Permission</option>
                        <?php $permission_type = $conn->query("SELECT * FROM `Permission_type`");
                        while($row = mysqli_fetch_assoc($permission_type)) {
                            echo '<option value = "'.$row['ID'].'">'.$row['Name'].'</option>';
                        }
                        ?>
                        </select>
                    </div>
                    <?php if(in_array('Permission Delete',$_SESSION['permission'])) { ?>
                    <div id="page-button">
                        <div class="theme-icons shadow-sm p-2 cursor-pointer rounded" title="Go to Trash" data-bs-toggle="tooltip" id = "trash_button">
                            <i class="bi bi-trash-fill"></i>
                        </div>
                        <button class="btn btn-primary" style="font-size: small;" id ="return_button">Go To Permission</button>
                    </div>
                    <?php } ?>
                    <button class="btn btn-primary" style="font-size: smaller;display: none;" id ="return_button">Go To Permission</button>
                    <?php if( in_array('Permission Create',$_SESSION['permission'])) { ?>
                    <div class="d-flex align-items-center theme-icons shadow-sm p-2 cursor-pointer rounded" title="Add" onclick="addpermission()" data-bs-toggle="tooltip">
                    <i class="bi bi-plus-circle-fill" id="add_permission"></i>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <div class="table-responsive mt-3">
                <table class="table align-middle" id="permissionTable">
                    <thead class="table-secondary">
                        <tr>
                            <th>Permission</th>
                            <th>Page</th>
                            <th>Assigned To</th>
                            <th>Created Date</th>
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

var permissionSettings = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    "searching": false ,
    'ajax': {
        'url': '/app/permission/permission-server',
        'type': 'POST',
        'data' : function(d) {
            d.apply_page_filter = $("#apply_page_filter").val();
            d.permission_type_filter = $("#permission_type_filter").val();
        }
    },
    'columns': [{
            data: "type" ,
        },{
            data: "page" ,
        },{
            data : "role_assign" ,
            render : function(data,type,row) {
                var roles = '';
                if(data.length > 0) {
                    var roles_name = data.split(',');
                    for(let key in roles_name ){
                        roles += '<span class="badge rounded-pill bg-info m-1" style="background-color:#1a2232 !important;">'+roles_name[key]+'</span>';
                    }
                }
                return '<div class ="d-flex align-items-center gap-3 fs-6">'+roles+'</div>';
            }
        },{
            data : "created"
        },{         
            data : "Action" ,
            render : function(data, type, row) {
                var table = "permission";
                var del = '';
                <?php if(in_array('Permission Delete',$_SESSION['permission'])) { ?>    
                del = '<div class="text-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete" onclick = "deleteDetails('+row.ID+',&#39;'+table+'&#39;)"><i class="bi bi-trash-fill"></i></div>';
                <?php } ?>
                return '<div class = "table-actions d-flex align-items-center gap-3 fs-6">' + del + '</div>';
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
};


var permissionTrashSettings = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    "searching": false ,
    'ajax': {
        'url': '/app/permission/permission-server',
        'type': 'POST',
        'data' : function(d) {
            d.deleteType = 'permission_delete';
            d.apply_page_filter = $("#apply_page_filter").val();
            d.permission_type_filter = $("#permission_type_filter").val();
        }
    },
    'columns': [{
            data: "type" ,
        },{
            data: "page" ,
        },{
            data : "role_assign" ,
            render : function(data,type,row) {
                var roles = '';
                if(data.length > 0) {
                    var roles_name = data.split(',');
                    for(let key in roles_name ){
                        roles += '<span class="badge rounded-pill bg-info m-1" style="background-color:#1a2232 !important;">'+roles_name[key]+'</span>';
                    }
                }
                return '<div class ="d-flex align-items-center gap-3 fs-6">'+roles+'</div>';
            }
        },{
            data : "created"
        },{         
            data : "Action" ,
            render : function(data, type, row) {
                var table = "permission";
                var restore = '<button type="button" class="btn btn-info text-white px-4" onclick = "restoreRoleDetails('+row.ID+',&#39;'+table+'&#39;)">Restore</button>';
                var del = '<button type="button" class="btn btn-danger px-4" onclick = "parmanentDeleteDetails('+row.ID+',&#39;'+table+'&#39;)">Delete</button>';
                return '<div class = "table-actions d-flex align-items-center gap-3 fs-6">' + restore+del + '</div>';
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
};


$(document).ready(function() {
    $("#return_button").css('display','none');
    $('#permissionTable').DataTable(permissionSettings);
});

$("#trash_button").on('click',function(){
    $('#permissionTable').DataTable(permissionTrashSettings);
    $("#return_button").css('display','block');
    $("#trash_button").css('display','none');
});

$("#return_button").on('click',function(){
    $('#permissionTable').DataTable(permissionSettings);
    $("#return_button").css('display','none');
    $("#trash_button").css('display','block');
});

function addpermission() {
    $.ajax({
        url : "/app/permission/insertAndupdatePermission", 
        type : 'get',
        success : function(data){
            $('#md-modal-content').html(data);
            $('#mdmodal').modal('show');
        }
    });
}

function updateDetails(id) {
    $.ajax({
        url : "/app/permission/insertAndupdatePermission", 
        type : 'post',
        data : {
            id
        },
        success : function(data){
            $('#md-modal-content').html(data);
            $('#mdmodal').modal('show');
        }
    });
}

function applyFilter() {
    $('#permissionTable').DataTable().ajax.reload(null, false);
}

</script>

<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer-bottom.php');?>