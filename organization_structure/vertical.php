<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-top.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-bottom.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/topbar.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/menu.php');?>

<!--start content-->
<main class="page-content">
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <h6 class="mb-0">Verticals Details</h6>
                <div class="d-flex justify-content-end gap-2 col-sm-2">
                    <?php if($_SESSION['role'] == '1' || $_SESSION['role'] == '3') { ?>
                    <div class="col-sm-2 me-2">
                        <input type="color" class="form-control form-control-sm form-control-color" name="node_color" id="node_color" title="Select Node Color" onchange="setNodeColor(this.value,'Vertical')">
                    </div>
                    <?php } ?>
                    <div class="col-sm-12">
                        <select type="text" class="form-control form-control-sm single-select select2" name="organization_filter" id="organization_filter" onchange="reloadTable(this.id)">
                        </select>
                    </div>
                    <div class="col-sm-12">
                        <select type="text" class="form-control form-control-sm single-select select2" name="branch_filter" id="branch_filter" onchange="reloadTable(this.id)">
                        </select>
                    </div>
                    <?php if(in_array('Vertical Delete',$_SESSION['permission'])) { ?>
                    <div class="theme-icons p-2 cursor-pointer rounded" title="Go to Trash" data-bs-toggle="tooltip" id = "trash_button">
                        <i class="bi bi-trash-fill"></i>
                    </div>
                    <button class="btn btn-primary" style="font-size: smaller;text-wrap:nowrap;" id ="return_button">Go To Vertical</button>
                    <?php } ?>
                    <?php if( in_array('Vertical Create',$_SESSION['permission'])) { ?>
                    <div class="d-flex align-items-center theme-icons cursor-pointer rounded" title="Add" onclick="addvertical()" data-bs-toggle="tooltip">
                    <i class="bi bi-plus-circle-fill" id="add_vertical"></i>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <div class="table-responsive mt-3">
                <table class="table align-middle w-100" id="verticalTable" style="color: #515B73!important;">
                    <thead class="table-primary">
                        <tr class="table_heading">
                            <td>Logo</td>
                            <td>Vertical</td>
                            <td>Organization</td>
                            <td>Branch</td>
                            <td>Action</td>
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
                $("#"+key+"_filter").select2({
                    placeholder: 'Choose ' + key.charAt(0).toUpperCase() + key.slice(1,key.length), 
                    allowClear: true,
                    width: '100%' , 
                });
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
            data: "image",
            render : function(data, type, row) {
                let image = (data != null || data != "") ? data : "../../assets/images/sample_vertical.jpg";
                return `<div class ="d-flex align-items-center gap-3 fs-6"><div class="d-flex align-items-center gap-3 cursor-pointer"><img src="${image}" class="rounded-circle" width="44" height="44" alt=""></div></div>`;
            }
        }, {
            data : "Vertical_name" , 
            render : (data,type,row) => `<div class = "table_heading">${data}</div>` 
        },{
            data: "organization" ,
            render : (data,type,row) => `<div class = "table_heading">${data}</div>`
        },{
            data: "Branch_name" ,
            render : (data,type,row) => `<div class = "table_heading">${data}</div>`
        },{         
            data : "Action" ,
            render : function(data, type, row) {
                let table = "Vertical";let edit = '';let del = '';
                <?php if(in_array('Vertical Update',$_SESSION['permission'])) { ?>
                    edit = updateButton(row.ID);
                <?php } else { ?>
                    edit = updateDisabledButton();
                <?php } ?>
                <?php if(in_array('Vertical Delete',$_SESSION['permission'])) { ?>
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
            data: "image",
            render : function(data, type, row) {
                let image = (data != null || data != "") ? data : "../../assets/images/sample_vertical.jpg";
                return `<div class ="d-flex align-items-center gap-3 fs-6"><div class="d-flex align-items-center gap-3 cursor-pointer"><img src="${image}" class="rounded-circle" width="44" height="44" alt=""></div></div>`;
            }
        }, {
            data : "Vertical_name" , 
            render : (data,type,row) => `<div class = "table_heading">${data}</div>` 
        },{
            data: "organization" ,
            render : (data,type,row) => `<div class = "table_heading">${data}</div>`
        },{
            data: "Branch_name" ,
            render : (data,type,row) => `<div class = "table_heading">${data}</div>`
        },{         
            data : "Action" ,
            render : function(data, type, row) {
                let table = "Vertical";
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
    fetchNodeColor("Vertical");
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