<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-top.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-bottom.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/topbar.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/menu.php');?>
<?php 
$node_color = $conn->query("SELECT color FROM Vertical LIMIT 1");
$node_color = mysqli_fetch_column($node_color);
?>

<!--start content-->
<main class="page-content">
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Verticals Details</h5>
                <div class="d-flex justify-content-end gap-2 col-sm-2">
                    <div class="col-sm-2 ">
                        <input type="color" class="form-control form-control-color" name="node_color" id="node_color" title="Select Node Color" value="<?php echo !is_null($node_color) ? $node_color : '' ?>"  onchange="setNodeColor(this.value,'Vertical')">
                    </div>
                    <div class="col-sm-12" style="z-index: 0 !important;">
                        <select type="text" class="form-control form-control-sm single-select select2" name="organization_filter" id="organization_filter" onchange="reloadTable(this.id)">
                        </select>
                    </div>
                    <div class="col-sm-12" style="z-index: 0 !important;">
                        <select type="text" class="form-control form-control-sm single-select select2" name="branch_filter" id="branch_filter" onchange="reloadTable(this.id)">
                        </select>
                    </div>
                    <?php if(in_array('Vertical Delete',$_SESSION['permission'])) { ?>
                    <div class="theme-icons shadow-sm p-2 cursor-pointer rounded" title="Go to Trash" data-bs-toggle="tooltip" id = "trash_button">
                        <i class="bi bi-trash-fill"></i>
                    </div>
                    <button class="btn btn-primary" style="font-size: small;" id ="return_button">Go To Vertical</button>
                    <?php } ?>
                    <?php if( in_array('Vertical Create',$_SESSION['permission'])) { ?>
                    <div class="d-flex align-items-center theme-icons shadow-sm p-2 cursor-pointer rounded" title="Add" onclick="addvertical()" data-bs-toggle="tooltip">
                    <i class="bi bi-plus-circle-fill" id="add_vertical"></i>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <div class="table-responsive mt-3">
                <table class="table align-middle" id="verticalTable">
                    <thead class="table-secondary">
                        <tr>
                            <th>Vertical</th>
                            <th>Organization</th>
                            <th>Branch</th>
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
    var filter_data_field = ['organization','branch'];
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

function reloadTable(id) {
    $('.table').DataTable().ajax.reload(null, false);
}

var verticalSettings = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    'ajax': {
        'url': '/app/vertical/vertical-server.php',
        'type': 'POST',
        'data': function(d) {
            d.organizationfilter = $("#organization_filter").val();
            d.branchfilter = $("#branch_filter").val();
        }
    },
    'columns': [{
            data: "Vertical",
            render : function(data, type, row) {
                if(row.image != null ) {
                    var image = '<div class="d-flex align-items-center gap-3 cursor-pointer"><img src="'+row.image+'" class="rounded-circle" width="44" height="44" alt=""></div>';
                } else { 
                    var image = '<div class="d-flex align-items-center gap-3 cursor-pointer"><img src="../../assets/images/sample_vertical.jpg" class="rounded-circle" width="44" height="44" alt=""></div>';
                }
                var name = '<div class = "d-flex align-items-center gap-3 fw-bold">'+row.Vertical_name+'</div>';
                return '<div class = "d-flex align-items-center gap-3 fs-6">'+image+name+'</div>';
            }
        },{
            data: "organization" ,
        },{
            data: "Branch_name" ,
        },{         
            data : "Action" ,
            render : function(data, type, row) {
                var table = "Vertical";
                var edit = '';var del = '';
                <?php if(in_array('Vertical Update',$_SESSION['permission'])) { ?>
                var edit = '<div class="text-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit" onclick = "updateDetails('+row.ID+')"><i class="bi bi-pencil-fill"></i></div>';
                <?php } ?>
                <?php if(in_array('Vertical Delete',$_SESSION['permission'])) { ?>
                var del = '<div class="text-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete" onclick = "checkAssignDetails('+row.ID+',&#039;'+table+'&#039;)"><i class="bi bi-trash-fill"></i></div>';
                <?php } ?>
                return '<div class = "table-actions d-flex align-items-center gap-3 fs-6">' +  edit+del + '</div>';
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

var verticalTrashSettings = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    'ajax': {
        'url': '/app/vertical/vertical-server.php',
        'type': 'POST',
        'data': function(d) {
            d.verticalType = "deleteVertical"
        } 
    },
    'columns': [{
            data: "Vertical",
            render : function(data, type, row) {
                if(row.image != null ) {
                    var image = '<div class="d-flex align-items-center gap-3 cursor-pointer"><img src="'+row.image+'" class="rounded-circle" width="44" height="44" alt=""></div>';
                } else { 
                    var image = '<div class="d-flex align-items-center gap-3 cursor-pointer"><img src="../../assets/images/sample_vertical.jpg" class="rounded-circle" width="44" height="44" alt=""></div>';
                }
                var name = '<div class = "d-flex align-items-center gap-3 fw-bold">'+row.Vertical_name+'</div>';
                return '<div class = "d-flex align-items-center gap-3 fs-6">'+image+name+'</div>';
            }
        },{
            data: "Program" ,
        },{
            data: "Branch_name" ,
        },{         
            data : "Action" ,
            render : function(data, type, row) {
                var table = "Vertical";
                var restore = '<button type="button" class="btn btn-info text-white px-4" onclick = "restoreDetails('+row.ID+',&#39;'+table+'&#39;)">Restore</button>';
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
    $('#verticalTable').dataTable(verticalSettings);
});

$("#trash_button").on('click',function(){
    $('#verticalTable').dataTable(verticalTrashSettings);
    $("#return_button").css('display','block');
    $("#trash_button").css('display','none');
});


$("#return_button").on('click',function(){
    $('#verticalTable').dataTable(verticalSettings);
    $("#return_button").css('display','none');
    $("#trash_button").css('display','block');
});

function addvertical() {
    $.ajax({
        url : "/app/vertical/insertAndupdateVertical.php", 
        type : 'get',
        success : function(data){
            $('#md-modal-content').html(data);
            $('#mdmodal').modal('show');
        }
    });
}

function updateDetails(vertical_id) {
    $.ajax({
        url : "/app/vertical/insertAndupdateVertical.php?vertical_id="+vertical_id, 
        type : 'get',
        success : function(data){
            $('#md-modal-content').html(data);
            $('#mdmodal').modal('show');
        }
    });
}

</script>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer-bottom.php');?>