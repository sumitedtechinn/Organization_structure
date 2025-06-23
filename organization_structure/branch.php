<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-top.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-bottom.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/topbar.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/menu.php');?>
<!--start content-->
<main class="page-content">
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <h6 class="mb-0">Branch Details</h6>
                <div class="d-flex justify-content-end gap-2">
                    <?php if($_SESSION['role'] == '3' || $_SESSION['role'] == '1') { ?>
                    <div class="col-sm-2">
                        <input type="color" class="form-control form-control-sm form-control-color" name="node_color" id="node_color" title="Select Node Color" onchange="setNodeColor(this.value,'Branch')">
                    </div>
                    <?php if($_SESSION['role'] == '1') { ?>
                    <div class="col-sm-12">
                        <select type="text" class="form-control form-control-sm single-select select2" name="organization_filter" id="organization_filter"></select>
                    </div>
                    <?php } ?>
                    <?php } ?>
                    <?php if(in_array('Branch Delete',$_SESSION['permission'])) { ?>
                    <div class="theme-icons p-2 cursor-pointer rounded" title="Go to Trash" data-bs-toggle="tooltip" id = "trash_button">
                        <i class="bi bi-trash-fill"></i>
                    </div>
                    <button class="btn btn-primary btn-sm" id ="return_button" style="font-size: smaller;text-wrap:nowrap;">Go To Branch</button>
                    <?php } ?>
                    <?php if(in_array('Branch Create',$_SESSION['permission'])) { ?>
                    <div class="d-flex align-items-center theme-icons p-2 cursor-pointer rounded" title="Add"  onclick="addBranches()" data-bs-toggle="tooltip">
                    <i class="bi bi-plus-circle-fill" id="add_branch"></i>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <div class="table-responsive mt-3">
                <table class="table align-middle" id="branchTable" style="color: #515B73!important;">
                    <thead class="table-primary">
                        <tr class="table_heading">
                            <td>Logo</td>
                            <td>Branch</td>
                            <td>Branch Head</td>
                            <td>Organization</td>
                            <td>Start Date</td>
                            <td>Address</td>
                            <td>Actions</td>
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
                let image = (data != null) ? data : "../../assets/images/sample_branch.jpg";
                return `<div class ="d-flex align-items-center gap-3 fs-6"><div class="d-flex align-items-center gap-3 cursor-pointer"><img src="${image}" class="rounded-circle" width="44" height="44" alt=""></div></div>`;
            }
        },{
            data: "Branch" ,
            render : function(data,type,row) {
                let name = makeContent(row.Branch_name);
                let country_code = makeContent(row.Country_code);
                let contact = makeContent(row.Contact);
                return `<div style="font-size:small;">
                <p class = "mb-1"><span style="font-weight:500;">Name : </span>${name}</p>
                <p class = "mb-1"><span style="font-weight:500;">Contact : </span>+${country_code} ${contact}</p>
                </div>`;
            }
        },{
            data: "Branch_head", 
            render : function(data,type,row) {
                if(Array.isArray(data)) {
                    var head = '';
                    for (const key in data) {
                        for(const keys in data[key]['user_name']) {
                            head += `<p class = "mb-1"><span style="font-weight:500;">${data[key]['user_name'][keys]} : </span>${makeContent(data[key]['designation'])}</p>`;
                        }
                    }
                    return '<div>'+head+'</div>';
                } else {
                    return `<div class = "table_heading">${data}</div>`;    
                }
            }
        },{
            data: "organization_name" ,
            render : (data,type,row) => `<div class = "table_heading">${data}</div>`
        },{
            data: "Start_date" , 
        },{
            data : "Address" ,
            render : function(data,type,row) {
                let country = makeContent(row.Country);
                let state = makeContent(row.State);
                let city = makeContent(row.City);
                let locality = makeContent(row.Address);
                return `<div style="font-size:small;">
                <p class = "mb-1"><span style="font-weight:500;">Country : </span>${country}</p>
                <p class = "mb-1"><span style="font-weight:500;">State : </span>${state}</p>
                <p class = "mb-1"><span style="font-weight:500;">City : </span>${city}</p>
                <p class = "mb-1"><span style="font-weight:500;">Locality : </span>${locality}</p>
                </div>`;
            }
        },{         
            data: "Action",
            render : function(data, type, row) {
                let edit = ''; let del = '';let table = 'Branch';
                <?php if(in_array('Branch Update',$_SESSION['permission'])) { ?>
                    edit = updateButton(row.ID);
                <?php } else { ?>
                    edit = updateDisabledButton();
                <?php } ?>
                <?php if(in_array('Branch Delete',$_SESSION['permission'])) { ?>
                    del = deleteButton(row.ID , table , 'checkAssignDetails');
                <?php } else { ?>
                    del = deleteDisabledButton();
                <?php } ?>
                return `<div class="table-actions d-flex align-items-center gap-3 fs-6">${edit}${del}</div>`;
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
                let image = (data != null) ? data : "../../assets/images/sample_branch.jpg";
                return `<div class ="d-flex align-items-center gap-3 fs-6"><div class="d-flex align-items-center gap-3 cursor-pointer"><img src="${image}" class="rounded-circle" width="44" height="44" alt=""></div></div>`;
            }
        },{
            data: "Branch" ,
            render : function(data,type,row) {
                let name = makeContent(row.Branch_name);
                let country_code = makeContent(row.Country_code);
                let contact = makeContent(row.Contact);
                return `<div style="font-size:small;">
                <p class = "mb-1"><span style="font-weight:500;">Name : </span>${name}</p>
                <p class = "mb-1"><span style="font-weight:500;">Contact : </span>+${country_code} ${contact}</p>
                </div>`;
            }
        },{
            data: "Branch_head", 
            render : function(data,type,row) {
                if(Array.isArray(data)) {
                    var head = '';
                    for (const key in data) {
                        for(const keys in data[key]['user_name']) {
                            head += `<p class = "mb-1"><span style="font-weight:500;">${data[key]['user_name'][keys]} : </span>${makeContent(data[key]['designation'])}</p>`;
                        }
                    }
                    return '<div>'+head+'</div>';
                } else {
                    return `<div class = "table_heading">${data}</div>`;    
                }
            }
        },{
            data: "organization_name" ,
            render : (data,type,row) => `<div class = "table_heading">${data}</div>`
        },{
            data: "Start_date" , 
        },{
            data : "Address" ,
            render : function(data,type,row) {
                let country = makeContent(row.Country);
                let state = makeContent(row.State);
                let city = makeContent(row.City);
                let locality = makeContent(row.Address);
                return `<div style="font-size:small;">
                <p class = "mb-1"><span style="font-weight:500;">Country : </span>${country}</p>
                <p class = "mb-1"><span style="font-weight:500;">State : </span>${state}</p>
                <p class = "mb-1"><span style="font-weight:500;">City : </span>${city}</p>
                <p class = "mb-1"><span style="font-weight:500;">Locality : </span>${locality}</p>
                </div>`;
            }
        },{         
            data : "Action" ,
            render : function(data, type, row) {
                let table = "Branch";
                let restore = restoreButton(row.ID,table);
                let del = paramanentDeleteButton(row.ID,table);
                return `<div class = "table-actions d-flex align-items-center gap-3 fs-6">${restore}${del}</div>`;
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
    fetchNodeColor("Branch");
    $("#return_button").css('display','none');
    $('#branchTable').dataTable(branchSettings);
});

$("#trash_button").on('click',function(){
    $('#branchTable').dataTable(branchTrashSettings);
    $("#return_button").css('display','block');
    $("#trash_button").css('display','none');
});


$("#return_button").on('click',function(){
    $("#branchTable").dataTable(branchSettings);
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
                $("#"+key+"_filter").select2({
                    placeholder: 'Choose ' + key.charAt(0).toUpperCase() + key.slice(1,key.length), 
                    allowClear: true,
                    width: '100%'
                });
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