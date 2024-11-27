<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-top.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-bottom.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/topbar.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/menu.php');?>

<style>

[data-n-id] rect:hover {
    filter: drop-shadow( 4px 5px 5px #aeaeae);
}

[data-l-id] path {
    stroke: #FFE5CF;
}

[data-ctrl-ec-id] circle {
    fill: #FFF7F7 !important; 
}

.boc-form-field {
    border: 2px solid #007bff;
    border-radius: 5px;
    padding: 10px;
    background-color: #f8f9fa;
}
.boc-form-field input {
    border: 1px solid #ced4da;
    padding: 8px;
    border-radius: 4px;
    width: 100%;
}
.boc-form-field label {
    font-weight: bold;
    color: #495057;
}

.page-content{
    padding: 0 !important;
}

.full-height{
    height: 80vh; 
}
#tree>svg {
    background-color: #2E2E2E;
}

</style>

<main class="page-content">
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h5 class="mb-0">Designation Details</h5>
                <div class="d-flex justify-content-end gap-2 col-sm-2">
                    <!-- <div class="col-sm-12" style="z-index: 0 !important;">
                        <select type="text" class="form-control form-control-sm single-select select2" name="organization_filter" id="organization_filter" onchange="reloadTable(this.id)">
                        </select>
                    </div>
                    <div class="col-sm-12" style="z-index: 0 !important;">
                        <select type="text" class="form-control form-control-sm single-select select2" name="branch_filter" id="branch_filter" onchange="reloadTable(this.id)">
                        </select>
                    </div>
                    <div class="col-sm-12" style="z-index: 0 !important;">
                        <select type="text" class="form-control form-control-sm single-select select2" name="department_filter" id="department_filter" onchange="reloadTable(this.id)">
                        </select>
                    </div> -->
                    <?php if( in_array('Department Create',$_SESSION['permission'])) { ?>
                    <div class="theme-icons shadow-sm p-2 cursor-pointer rounded" title="Add" onclick="addDesignation()" data-bs-toggle="tooltip">
                        <i class="bi bi-person-plus-fill" id="add_user"></i>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <div class="row mb-1">
                <div class="d-flex align-items-center justify-content-start <?=$gap?> mt-2 mb-2" style="padding-right: 1%;">
                    <?php if($_SESSION['role'] == '1' || $_SESSION['role'] == '3') { 
                        if($_SESSION['role'] != '3') {
                    ?>
                    <div class="col-sm-3 card bg-light p-1 mb-1" style="z-index: 0 !important;">
                        <label class="col-form-label" style="font-size:0.90rem;font-weight: 500;">Designation Inside Organization</label>
                        <select type="text" class="form-control form-control-sm single-select select2" name="organization_filter" id="organization_filter" onchange="reloadTable(this.id)">
                        </select>
                    </div>
                    <?php } ?>
                    <div class="col-sm-3 card bg-light p-1 mb-1" style="z-index: 0 !important;">
                        <label class="col-form-label" style="font-size:0.90rem;font-weight: 500;">Designation Inside Branch</label>
                        <select type="text" class="form-control form-control-sm single-select select2" name="branch_filter" id="branch_filter" onchange="reloadTable(this.id)">
                        </select>
                    </div>
                    <div class="col-sm-3 card bg-light p-1 mb-1" style="z-index: 0 !important;">
                        <label class="col-form-label" style="font-size:0.90rem;font-weight: 500;">Designation Inside Vertical</label>
                        <select type="text" class="form-control form-control-sm single-select select2" name="vertical_filter" id="vertical_filter" onchange="reloadTable(this.id)">
                        </select>
                    </div>
                    <div class="col-sm-3 card bg-light p-1 mb-1" style="z-index: 0 !important;">
                        <label class="col-form-label" style="font-size:0.90rem;font-weight: 500;">Designation Inside Department</label>
                        <select type="text" class="form-control form-control-sm single-select select2" name="department_filter" id="department_filter" onchange="reloadTable(this.id)">
                        </select>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <div class="row">
                <div class="table-responsive mt-3 col-sm-8">
                    <table class="table table-striped align-middle" id="designationTable">
                        <thead class="table-secondary">
                            <tr>
                                <th>Designation</th>
                                <th>Code</th>
                                <th>Org./Branch/Department</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div id="tree" class="col-sm-4 container-fluid full-height d-flex justify-content-center align-items-center"></div>    
            </div>
        </div>
    </div>
</main>

<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer-top.php');?>
<script src="/assets/plugins/select2/js/select2.min.js"></script>
<script src="/assets/js/form-select2.js"></script>
<script src="/assets/orgchart.js"></script>
<script>

$(document).ready(function(){
    var filter_data_field = ['organization','branch','vertical','department'];
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

OrgChart.scroll.chrome = { smooth: 12, speed: 120 }; // for scroll 
OrgChart.scroll.firefox = { smooth: 12, speed: 120 }; // for scroll 

var designationSettings = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    'ajax': {
        'url': '/app/designation/designation-dataTable-server',
        'type': 'POST',
        'data' : function(d) {
            d.departmentfilter = $('#department_filter').val();
            d.organizationfilter = $("#organization_filter").val();
            d.branchfilter = $("#branch_filter").val();
            d.verticalfilter = $("#vertical_filter").val();
        }
    },
    'columns': [{
            data: "designation_name",
        },{
            data: "code",
        },{
            data: "desigantion_inside" ,
            render : function(data,type,row) {
                if (data == 'department') {
                    return '<div>'+row.department+'</div>';
                } else if (data == 'organization') {
                    return '<div>'+row.organization+'</div>';
                } else if (data == 'branch') {
                    return '<div>'+row.branch+'</div>';
                } else if (data == 'vertical') {
                    return '<div>'+row.vertical+'</div>';
                }
            }
        },{         
            data : "Action",
            render : function(data, type, row) {
                var edit = ''; var del = ''; var table = 'Designation';
                <?php if(in_array('Department Update',$_SESSION['permission'])) { ?>
                var edit = '<div class="text-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit" onclick = "updateDetails('+row.ID+',&#039;'+row.desigantion_inside+'&#039;)"><i class="bi bi-pencil-fill"></i></div>';
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

let isUpdating = false;

function reloadTable(id) {
    if(isUpdating) return;
    var filter = ['organization_filter','branch_filter','vertical_filter','department_filter'];
    for (const key in filter) {
        if (id != filter[key]) {
            triggerChange(filter[key]);
        }
    }
    initializeChart();
    $('.table').DataTable().ajax.reload(null, false);
}

function triggerChange(id) {
    isUpdating = true;
    $("#"+id).val("");
    $("#"+id).trigger('change');
    isUpdating = false;
}

$(document).ready(function() {
    $('#designationTable').dataTable(designationSettings);
});

async function fetchData() {
    try {
        var search_id = '';
        var id_type = '';
        if($('#department_filter option').length > 0 && $('#department_filter').val().length > 0 ){
            search_id = $('#department_filter').val();
            id_type = "department";
        } else if ($('#organization_filter option').length > 0 && $('#organization_filter').val().length > 0 ) {
            search_id = $('#organization_filter').val();
            id_type = "organization";
        } else if($('#branch_filter option').length > 0 && $('#branch_filter').val().length > 0) {
            search_id = $('#branch_filter').val();
            id_type = "branch";
        } else if($('#vertical_filter option').length > 0 && $('#vertical_filter').val().length > 0) {
            search_id = $('#vertical_filter').val();
            id_type = "vertical";
        } 
        const response = await fetch('/app/designation/designation-orgchart-server?search_id='+search_id+'&id_type='+id_type);
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('There has been a problem with your fetch operation:', error);
    }
}

async function initializeChart() {
    const data = await fetchData();
    var tag = {}; var colors = []; var designations = []; 
    for (let x in data) {
        colors.push(data[x]['color']);
        designations.push(data[x]['Code']);
        var designation = data[x]['Code'];
        tag[designation] = {'template':designation};
    }
    
    for (let i = 0; i < designations.length; i++) {
        OrgChart.templates[designations[i]] = Object.assign({}, OrgChart.templates.ana);
        OrgChart.templates[designations[i]].size = [500,80];
        OrgChart.templates[designations[i]].node = 
            '<rect x="0" y="0" height="80" width="500" fill="'+colors[i]+'" stroke-width="1" stroke="#686868" rx="40" ry="40"></rect>';
        OrgChart.templates[designations[i]].field_0 = 
            `<text data-width="400" style="font-size: 25px;" fill="#ffffff" x="80" y="30" text-anchor="start">{val}</text>`;
        OrgChart.templates[designations[i]].field_1 = 
           `<text data-width="400" style="font-size: 20px;" fill="#ffffff" x="80" y="55" text-anchor="start">{val}</text>`;
        OrgChart.templates.polina.img_0 = 
            `<clipPath id="{randId}"><circle cx="0" cy="0" r="0"></circle></clipPath>
            <image preserveAspectRatio="xMidYMid slice" clip-path="url(#{randId})" xlink:href="{val}" x="0" y="0" width="0" height="0"></image>`;
    }
    if (data) {
        new OrgChart("#tree", {
            enableSearch: false,
            toolbar: {
                zoom: true,
                fit: true,
                expandAll: true
            },
            tags : tag,
            showXScroll: true,
            nodeBinding: {  
                field_0: "Designation",
                field_1: "Code",
            },
            searchFields: ["Designation"],
            searchDisplayField: "title",
            nodes: data,
            layout: OrgChart.tree,
            scaleInitial: OrgChart.match.boundary,
        });
    }
}

initializeChart();

function addDesignation() {
    $.ajax({
        url : "/app/designation/selectDesignationType" , 
        type : "get" , 
        success : function(data) {
            $('#lg-modal-content').html(data);
            $('#lgmodal').modal('show');
        }
    })
}

function updateDetails(id,desigantion_inside) {
    var url = capitalizeFirstLetter(desigantion_inside);
    $.ajax({
        url : "/app/designation/insertAndupdateDesignation", 
        type : "post", 
        data : {
            id
        },
        success : function(data) {
            $('#lg-modal-content').html(data);
            $('#lgmodal').modal('show');
        }
    });
}

function capitalizeFirstLetter(string) {
    if (!string) return string; // Check for empty string
    return string.charAt(0).toUpperCase() + string.slice(1);
}
</script> 
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer-bottom.php');?>