<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-top.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-bottom.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/topbar.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/menu.php');?>

<?php 

$node_color = $conn->query("SELECT color FROM organization LIMIT 1");
$node_color = mysqli_fetch_column($node_color);

?>

<!--start content-->
<main class="page-content">
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Organization Details</h5>
                <div class="d-flex justify-content-end gap-2 col-sm-2">
                    <div>
                        <input type="color" class="form-control form-control-color" name="node_color" id="node_color" title="Select Node Color" value="<?php echo !is_null($node_color) ? $node_color : '' ?>"  onchange="setNodeColor(this.value,'organization')">
                    </div>
                    <?php if(in_array('Organization Delete',$_SESSION['permission'])) { ?>
                    <div class="theme-icons sha dow-sm p-2 cursor-pointer rounded" title="Go to Trash" data-bs-toggle="tooltip" id = "trash_button">
                        <i class="bi bi-trash-fill"></i>
                    </div>
                    <button class="btn btn-primary" style="font-size: small;" id ="return_button">Go To Organization</button>
                    <?php } ?>
                    <?php if( in_array('Organization Create',$_SESSION['permission'])) { ?>
                    <div class="d-flex align-items-center theme-icons shadow-sm p-2 cursor-pointer rounded" title="Add"  onclick="addOrganization()" data-bs-toggle="tooltip">
                    <i class="bi bi-plus-circle-fill" id="add_organization"></i>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <div class="table-responsive mt-3">
                <table class="table align-middle" id="organizationTable">
                    <thead class="table-secondary">
                        <tr>
                            <th>Logo</th>
                            <th>Organization Name</th>
                            <th>Organization Head</th>
                            <th>Start Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</main>
<!--end page main-->

<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer-top.php');?>
<script type="text/javascript">

var organizationSettings = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    'ajax': {
        'url': '/app/organization/organization_server',
        'type': 'POST',
    },
    'columns': [{
            data: "logo",
            render : function(data, type, row) {
                if(data != null) {
                    var image = '<div class="d-flex align-items-center gap-3 cursor-pointer"><img src="'+data+'" class="rounded-circle" width="44" height="44" alt=""></div>';
                } else { 
                    var image = '<div class="d-flex align-items-center gap-3 cursor-pointer"><img src="../../assets/images/sample_branch.jpg" class="rounded-circle" width="44" height="44" alt=""></div>';
                }
                return '<div class = "d-flex align-items-center gap-3 fs-6">'+image+'</div>';
            }
        },{
            data: "organization_name" ,
            render : function(data,type,row) {
                return '<div class = "text-wrap" style = "width:250px;"><b>'+data+'</b></div>';
            }
        },{
            data: "organization_head", 
            render : function(data,type,row) {
                if(Array.isArray(data)) {
                    var head = '';
                    for (const key in data) {
                        for(const keys in data[key]['user_name']) {
                            head += '<p class = "mb-1"><b>'+data[key]['user_name'][keys]+' : </b> '+data[key]['designation']+'</p>';
                        }
                    }
                    return '<div>'+head+'</div>';
                } else {
                    return '<div><b>'+data+'</b></div>';    
                }
            }
        },{
            data: "Start_date" ,
        },{         
            data : "Action" ,
            render : function(data, type, row) {
                var edit = '';var del = '';
                var table = 'organization';
                <?php if(in_array('Organization Update',$_SESSION['permission'])) { ?>
                    edit = '<div class="text-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit" onclick = "updateDetails('+row.ID+')"><i class="bi bi-pencil-fill"></i></div>';
                <?php } ?>
                <?php if(in_array('Organization Delete',$_SESSION['permission'])) { ?>
                    del = '<div class="text-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete" onclick = "checkAssignDetails('+row.ID+',&#39;'+table+'&#39;)"><i class="bi bi-trash-fill"></i></div>';
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
            template: '<div class="tooltip custom-tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
        });
    },
    "aaSorting": []
};

var organizationTrashSettings = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    'ajax': {
        'url': '/app/organization/organization_server',
        'type': 'POST',
        'data' : function(d) {
            d.organizationtype = "deleteOrganization";
        }
    },
    'columns': [{
            data: "logo",
            render : function(data, type, row) {
                if(data != null) {
                    var image = '<div class="d-flex align-items-center gap-3 cursor-pointer"><img src="'+data+'" class="rounded-circle" width="44" height="44" alt=""></div>';
                } else { 
                    var image = '<div class="d-flex align-items-center gap-3 cursor-pointer"><img src="../../assets/images/sample_branch.jpg" class="rounded-circle" width="44" height="44" alt=""></div>';
                }
                return '<div class = "d-flex align-items-center gap-3 fs-6">'+image+'</div>';
            }
        },{
            data: "organization_name" ,
            render : function(data,type,row) {
                return '<div class = "text-wrap" style = "width:250px;">'+data+'</div>';
            }
        },{
            data: "organization_head" , 
            render : function(data,type,row) {
                var head = '';
                if(data.includes(",")) {
                    var names = data.split(",");
                    for(let key in names ){
                        head += '<span class="badge rounded-pill bg-info m-1" style="background-color:#0388b8 !important;font-weight:200!important;">'+names[key]+'</span>';
                    }
                } else {
                    head = '<span class="badge rounded-pill bg-info m-1" style="background-color:#0388b8 !important;font-weight:200!important;">'+data+'</span>';
                }
                return '<div class ="d-flex align-items-center gap-2 fs-6">'+head+'</div>';
            }
        },{
            data: "Start_date" ,
        },{         
            data : "Action" ,
            render : function(data, type, row) {
                var table = "organization";
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
            template: '<div class="tooltip custom-tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
        });
    },
    "aaSorting": []
};

$(document).ready(function() {
    $("#return_button").css('display','none');
    $('#organizationTable').dataTable(organizationSettings);
});

$("#trash_button").on('click',function(){
    $('#organizationTable').dataTable(organizationTrashSettings);
    $("#return_button").css('display','block');
    $("#trash_button").css('display','none');
});


$("#return_button").on('click',function(){
    $('#organizationTable').dataTable(organizationSettings);
    $("#return_button").css('display','none');
    $("#trash_button").css('display','block');
});


function addOrganization() {
    $.ajax({
        url : "/app/organization/insertAndupdateOrganization", 
        type : 'get',
        success : function(data){
            $('#md-modal-content').html(data);
            $('#mdmodal').modal('show');
        }
    });
}

function updateDetails(organization_id) {
    $.ajax({
        url : "/app/organization/insertAndupdateOrganization", 
        type : 'post',
        data : {
            organization_id
        },
        success : function(data){
            $('#md-modal-content').html(data);
            $('#mdmodal').modal('show');
        }
    });
}

</script>

<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer-bottom.php');?>