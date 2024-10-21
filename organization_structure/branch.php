<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-top.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-bottom.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/topbar.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/menu.php');?>
<?php 
$node_color = $conn->query("SELECT color FROM Branch LIMIT 1");
$node_color = mysqli_fetch_column($node_color);
?>

<!--start content-->
<main class="page-content">
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Branch Details</h5>
                <div class="d-flex justify-content-end gap-2 col-sm-6">
                    <div class="row gap-2">
                        <div class="col-sm-2 ">
                            <input type="color" class="form-control form-control-color" name="node_color" id="node_color" title="Select Node Color" value="<?php echo !is_null($node_color) ? $node_color : '' ?>"  onchange="setNodeColor(this.value,'Branch')">
                        </div>
                        <div class="col-sm-9" style="z-index:0!important;">
                            <select type="text" class="form-control form-control-sm single-select select2" name="organization_filter" id="organization_filter"></select>
                        </div>
                    </div>
                    <?php if(in_array('Branch Delete',$_SESSION['permission'])) { ?>
                    <div class="theme-icons sha dow-sm p-2 cursor-pointer rounded" title="Go to Trash" data-bs-toggle="tooltip" id = "trash_button">
                        <i class="bi bi-trash-fill"></i>
                    </div>
                    <button class="btn btn-primary" style="font-size: small;" id ="return_button">Go To Branch</button>
                    <?php } ?>
                    <?php if( in_array('Branch Create',$_SESSION['permission'])) { ?>
                    <div class="d-flex align-items-center theme-icons shadow-sm p-2 cursor-pointer rounded" title="Add"  onclick="addBranches()" data-bs-toggle="tooltip">
                    <i class="bi bi-plus-circle-fill" id="add_branch"></i>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <div class="table-responsive mt-3">
                <table class="table align-middle" id="branchTable">
                    <thead class="table-secondary">
                        <tr>
                            <th>Logo</th>
                            <th>Branch</th>
                            <th>Branch Head</th>
                            <th>Organization</th>
                            <th>Start Date</th>
                            <th>Address</th>
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
<script src="/assets/plugins/select2/js/select2.min.js"></script>
<script src="/assets/js/form-select2.js"></script>
<script type="text/javascript">

var branchSettings = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    'ajax': {
        'url': '/app/branch/branch-server',
        'type': 'POST',
        "data" : function(d) {
            d.organization_filter = $("#organization_filter").val();
        }
    },
    'columns': [{
            data: "image",
            render : function(data, type, row) {
                if(data != null) {
                    var logo = '<div class="d-flex align-items-center gap-3 cursor-pointer"><img src="'+data+'" class="rounded-circle" width="44" height="44" alt=""></div>';
                } else { 
                    var logo = '<div class="d-flex align-items-center gap-3 cursor-pointer"><img src="../../assets/images/sample_branch.jpg" class="rounded-circle" width="44" height="44" alt=""></div>';
                }
                return '<div class = "d-flex align-items-center gap-3 fs-6">'+logo+'</div>';
            }
        },{
            data: "Branch" ,
            render : function(data,type,row) {
                var name = row.Branch_name;
                var country_code = row.Country_code;
                var contact = row.Contact;
                return '<div style="font-size:small;"><p class = "mb-1"><b>Name : </b> '+name+'</p><p class = "mb-1"><b>Contact : </b>'+country_code+" "+contact+'</p></div>';
            }
        },{
            data: "Branch_head", 
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
            data: "organization_name" ,
            render : function(data,type,row) {
                return '<div><b>'+data+'</b></div>';
            } 
        },{
            data: "Start_date" , 
        },{
            data : "Address" ,
            render : function(data,type,row) {
                var country = row.Country;
                var state = row.State;
                var city = row.City;
                var locality = row.Address;
                return  '<div class = "text-wrap" style="font-size:small;min-width:150px!important"><p class = "mb-1"><b>Country : </b>'+country+'</p><p class = "mb-1"><b>State : </b>'+state+'</p><p class = "mb-1"><b>City : </b>'+city+'</p><p class = "mb-1 text-wrap"><b>Locality : </b>'+locality+'</p></div>';
            }
        },{         
            data : "Action",
            render : function(data, type, row) {
                var edit = '';
                var del = '';
                <?php if(in_array('Branch Update',$_SESSION['permission'])) { ?>
                     edit = '<div class="text-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit" onclick = "updateDetails('+row.ID+')"><i class="bi bi-pencil-fill"></i></div>';
                <?php } ?>
                <?php if(in_array('Branch Delete',$_SESSION['permission'])) { ?>
                     del = '<div class="text-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete" onclick = "checkAssignDetails('+row.ID+',&#39;Branch&#39;)"><i class="bi bi-trash-fill"></i></div>';
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

var branchTrashSettings = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    'ajax': {
        'url': '/app/branch/branch-server',
        'type': 'POST',
        'data' : function (d) {
            d.branchtype = 'deletebranch'; 
            d.organization_filter = $("#organization_filter").val();
        }
    },
    'columns': [{
            data: "image",
            render : function(data, type, row) {
                if(data != null) {
                    var logo = '<div class="d-flex align-items-center gap-3 cursor-pointer"><img src="'+data+'" class="rounded-circle" width="44" height="44" alt=""></div>';
                } else { 
                    var logo = '<div class="d-flex align-items-center gap-3 cursor-pointer"><img src="../../assets/images/sample_branch.jpg" class="rounded-circle" width="44" height="44" alt=""></div>';
                }
                return '<div class = "d-flex align-items-center gap-3 fs-6">'+logo+'</div>';
            }
        },{
            data: "Branch" ,
            render : function(data,type,row) {
                var name = row.Branch_name;
                var branch_head = row.Branch_head;
                var country_code = row.Country_code;
                var contact = row.Contact;
                return '<div style="font-size:small;"><p class = "mb-1"><b>Name : </b> '+name+'</p><p class = "text-wrap mb-1" style = "min-width:150px!important;" ><b>Branch Head : </b>'+branch_head+'</p><p class = "mb-1"><b>Contact : </b>'+country_code+" "+contact+'</p></div>';
            }
        },{
            data: "organization_name" , 
        },{
            data: "Start_date" , 
        },{
            data : "Address" ,
            render : function(data,type,row) {
                var country = row.Country;
                var state = row.State;
                var city = row.City;
                var locality = row.Address;
                return  '<div class = "text-wrap" style="font-size:small;min-width:200px!important"><p class = "mb-1"><b>Country : </b>'+country+'</p><p class = "mb-1"><b>State : </b>'+state+'</p><p class = "mb-1"><b>City : </b>'+city+'</p><p class = "mb-1 text-wrap"><b>Locality : </b>'+locality+'</p></div>';
            }
        },{         
            data : "Action" ,
            render : function(data, type, row) {
                var table = "Branch";
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
    $('#branchTable').dataTable(branchSettings);
});

$("#trash_button").on('click',function(){
    $('#branchTable').dataTable(branchTrashSettings);
    $("#return_button").css('display','block');
    $("#trash_button").css('display','none');
});


$("#return_button").on('click',function(){
    $('#branchTable').dataTable(branchSettings);
    $("#return_button").css('display','none');
    $("#trash_button").css('display','block');
});


$("#organization_filter").on('change',function(){
    $('#branchTable').dataTable(branchSettings);
})

$(document).ready(function(){
    var filter_data_field = ['organization'];
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


function addBranches() {
    $.ajax({
        url : "/app/branch/insertAndupdateBranch.php", 
        type : 'get',
        success : function(data){
            $('#lg-modal-content').html(data);
            $('#lgmodal').modal('show');
        }
    });
}

function updateDetails(branch_id) {
    $.ajax({
        url : "/app/branch/insertAndupdateBranch.php?branch_id="+branch_id, 
        type : 'get',
        success : function(data){
            $('#lg-modal-content').html(data);
            $('#lgmodal').modal('show');
        }
    });
}

</script>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer-bottom.php');?>