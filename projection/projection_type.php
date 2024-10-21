<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-top.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-bottom.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/topbar.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/menu.php'); ?>

<!--start content-->
<main class="page-content">
    <div class="row justify-content-center">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Projection Type</h5>
                    <div class="d-flex justify-content-end gap-2 col-sm-2">
                        <?php if(in_array('Projection Type Delete',$_SESSION['permission'])) { ?>
                        <div class="theme-icons shadow-sm p-2 cursor-pointer rounded" title="Go to Trash" data-bs-toggle="tooltip" id = "trash_button">
                            <i class="bi bi-trash-fill"></i>
                        </div>
                        <button class="btn btn-primary" style="font-size: small;" id ="return_button">Go To Projection Type </button>
                        <?php } ?>
                        <?php if( in_array('Projection Type Create',$_SESSION['permission'])) { ?>
                        <div class="d-flex align-items-center theme-icons shadow-sm p-2 cursor-pointer rounded" title="Add" onclick="addProjectionType()" data-bs-toggle="tooltip">
                            <i class="bi bi-plus-circle-fill" id="add_projection_type"></i>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between gap-1 mt-3">
                    <div class="col-sm-3" style="z-index:0!important;">
                        <select type="text" class="form-control form-control-sm single-select select2" name="organization_filter" id="organization_filter" onchange="applyfilter()">
                            <option value="">Select Organization</option>
                            <?php $organization = $conn->query("SELECT id , organization_name FROM `organization` WHERE Deleted_At IS NULL");
                            while($row = mysqli_fetch_assoc($organization)) {
                                echo '<option value = "'.$row['id'].'" >'.$row['organization_name'].'</option>';
                            } ?>
                        </select>
                    </div>
                    <div class="col-sm-3" style="z-index:0!important;"> 
                        <select type="text" class="form-control form-control-sm single-select select2" name="branch_filter" id="branch_filter" onchange="applyfilter()">
                            <option value="">Select Branch</option>
                            <?php $branch = $conn->query("SELECT ID , Branch_name FROM `Branch` WHERE Deleted_At IS NULL");
                            while($row = mysqli_fetch_assoc($branch)) {
                                echo '<option value = "'.$row['ID'].'" >'.$row['Branch_name'].'</option>';
                            } ?>
                        </select>
                    </div>
                    <div class="col-sm-3" style="z-index:0!important;">
                        <select type="text" class="form-control form-control-sm single-select select2" name="vertical_filter" id="vertical_filter" onchange="applyfilter()">
                            <option value="">Select Vertical</option>
                            <?php $vertical = $conn->query("SELECT ID , Vertical_name FROM `Vertical` WHERE Deleted_At IS NULL");
                            while($row = mysqli_fetch_assoc($vertical)) {
                                echo '<option value = "'.$row['ID'].'" >'.$row['Vertical_name'].'</option>';
                            } ?>
                        </select>
                    </div>
                    <div class="col-sm-3" style="z-index:0!important;">
                        <select type="text" class="form-control form-control-sm single-select select2" name="department_filter" id="department_filter" onchange="applyfilter()">
                            <option value="">Select Department</option>
                            <?php $vertical = $conn->query("SELECT id,department_name FROM `Department` WHERE Deleted_At IS NULL");
                            while($row = mysqli_fetch_assoc($vertical)) {
                                echo '<option value = "'.$row['id'].'" >'.$row['department_name'].'</option>';
                            } ?>
                        </select>
                    </div>
                </div>
                <div class="table-responsive mt-3">
                    <table class="table align-middle" id="projectionTypeTable">
                        <thead class="table-secondary">
                            <tr>
                                <th>Projection Type</th>
                                <th>Organization</th>
                                <th>Branch</th>
                                <th>Vertical</th>
                                <th>Department</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div> 
            </div>
        </div>
    </div>
</main>
<!--end page main-->

<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-top.php'); ?>
<script src="/assets/plugins/select2/js/select2.min.js"></script>
<script src="/assets/js/form-select2.js"></script>
<script type="text/javascript">
var projectionTypeSettings = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    'ajax': {
        'url': '/app/projectionType/projectionType-server',
        'type': 'POST',
        'data' : function(d) {
            d.selectOrganization = $("#organization_filter").val(); 
            d.selectBranch = $("#branch_filter").val();
            d.selectVertical = $("#vertical_filter").val();
            d.selectDepartment = $("#department_filter").val();
        }
    },
    'columns': [{
        data: "projection_type",
    },{
        data : "organization"
    },{
        data : "branch"
    },{
        data : "vertical"
    },{
        data : "department"
    },{
        data : "created_at"
    },{
        data: "Action",   
        render: function(data, type, row) {
            var table = "Projection_type";
            var edit = ''; var del = '';
            <?php if(in_array('Projection Type Update',$_SESSION['permission'])) { ?>
            edit = '<div class="text-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit" onclick = "updateDetails(' + row.ID + ')"><i class="bi bi-pencil-fill"></i></div>';
            <?php } ?>
            <?php if(in_array('Projection Type Delete',$_SESSION['permission'])) { ?>
            del = '<div class="text-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete" onclick = "deleteDetails(' + row.ID + ',&#39;'+table+'&#39;)"><i class="bi bi-trash-fill"></i></div>';
            <?php } ?>
            return '<div class = "table-actions d-flex align-items-center gap-3 fs-6">' + edit + del + '</div>';
        }
    }],
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

var projectionTypeTrashSettings = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    'ajax': {
        'url': '/app/projectionType/projectionType-server',
        'type': 'POST',
        'data' : function(d) {
            d.projectionType = "deleteProjectionType";
            d.selectOrganization = $("#organization_filter").val(); 
            d.selectBranch = $("#branch_filter").val();
            d.selectVertical = $("#vertical_filter").val();
            d.selectDepartment = $("#department_filter").val();
        }
    },
    'columns': [{
        data: "projection_type",
    },{
        data : "organization"
    },{
        data : "branch"
    },{
        data : "vertical"
    },{
        data : "department"
    },{
        data : "created_at"
    },{
        data: "Action",   
        render: function(data, type, row) {
            var table = "Projection_type";
            var restore = '<button type="button" class="btn btn-info text-white px-4" onclick = "restoreDetails('+row.ID+',&#39;'+table+'&#39;)">Restore</button>';
            var del = '<button type="button" class="btn btn-danger px-4" onclick = "parmanentDeleteDetails('+row.ID+',&#39;'+table+'&#39;)">Delete</button>';
            return '<div class = "table-actions d-flex align-items-center gap-3 fs-6">' + restore+del + '</div>';
        }
    }],
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
    $('#projectionTypeTable').dataTable(projectionTypeSettings);
});

$("#trash_button").on('click',function(){
    $('#projectionTypeTable').dataTable(projectionTypeTrashSettings);
    $("#return_button").css('display','block');
    $("#trash_button").css('display','none');
});


$("#return_button").on('click',function(){
    $('#projectionTypeTable').dataTable(projectionTypeSettings);
    $("#return_button").css('display','none');
    $("#trash_button").css('display','block');
});

function applyfilter(){
    $('#projectionTypeTable').DataTable().ajax.reload(null,false);
}

function addProjectionType() {
    $.ajax({
        url: "/app/projectionType/insertAndupdateProjectionType",
        type: 'get',
        success: function(data) {
            $('#md-modal-content').html(data);
            $('#mdmodal').modal('show');
        }
    });
}

function updateDetails(id) {
    $.ajax({
        url: "/app/projectionType/insertAndupdateProjectionType",
        type: 'post',
        data: {
            id
        },
        success: function(data) {
            $('#md-modal-content').html(data);
            $('#mdmodal').modal('show');
        }
    });
}

</script>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-bottom.php'); ?>