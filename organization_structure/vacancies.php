<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-top.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-bottom.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/topbar.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/menu.php');?>
<?php 
$node_color = $conn->query("SELECT color FROM Vacancies LIMIT 1");
$node_color = mysqli_fetch_column($node_color);
?>


<!--start content-->
<main class="page-content">
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Vacancies Details</h5>
                <div class="d-flex justify-content-end col-sm-3">
                    <div class="col-sm-2 ">
                        <input type="color" class="form-control form-control-color" name="node_color" id="node_color" title="Select Node Color" value="<?php echo !is_null($node_color) ? $node_color : '' ?>"  onchange="setNodeColor(this.value,'Vacancies')">
                    </div>
                    <?php if( in_array('Vacancies Create',$_SESSION['permission'])) { ?>
                    <div class="d-flex align-items-center theme-icons shadow-sm p-2 cursor-pointer rounded" title="Add" onclick="addVacancies()" data-bs-toggle="tooltip">
                        <i class="bi bi-plus-circle-fill" id="add_vacancies"></i>
                    </div>
                    <?php } ?>
                </div> 
            </div>
            <div class="d-flex align-items-center justify-content-between gap-1 mt-3">
                <div class="col-sm-2" style="z-index: 0 !important;">
                    <select type="text" class="form-control form-control-sm single-select select2" name="organization_filter" id="organization_filter" onchange="reloadTable(this.id)">
                    </select>
                </div>
                <div class="col-sm-2" style="z-index: 0 !important;">
                    <select type="text" class="form-control form-control-sm single-select select2" name="branch_filter" id="branch_filter" onchange="reloadTable(this.id)">
                    </select>
                </div>
                <div class="col-sm-2" style="z-index: 0 !important;">
                    <select type="text" class="form-control form-control-sm single-select select2" name="vertical_filter" id="vertical_filter" onchange="reloadTable(this.id)">
                    </select>
                </div>
                <div class="col-sm-2" style="z-index: 0 !important;">
                    <select type="text" class="form-control form-control-sm single-select select2" name="department_filter" id="department_filter" onchange="reloadTable(this.id)">
                    </select>
                </div>
                <div class="col-sm-2" style="z-index: 0 !important;">
                    <select type="text" class="form-control form-control-sm single-select select2" name="designation_filter" id="designation_filter" onchange="reloadTable(this.id)">
                    </select>
                </div>
            </div>
            <div class="table-responsive mt-3">
                <table class="table align-middle" id="vacanciesTable">
                    <thead class="table-secondary">
                        <tr>
                            <th>Designation</th>
                            <th>No. of Vacancies</th>
                            <th>Department Info</th>
                            <th>Raised By</th>
                            <th>status</th>
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
    var filter_data_field = ['organization','branch','vertical','department','designation'];
    $.ajax({
        url : "/app/common/getAllFilterData", 
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

function reloadTable() {
    $('.table').DataTable().ajax.reload(null, false);
}

function addVacancies() {
    $.ajax({
        url : "/app/vacancies/insertAndupdateVacancies.php", 
        type : 'get',
        success : function(data){
            $('#lg-modal-content').html(data);
            $('#lgmodal').modal('show');
        }
    });
}

var vacanciesSettings = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    "searching": false ,
    'ajax': {
        'url': '/app/vacancies/vacancies-server.php',
        'type': 'POST',
        'data' : function(d) {
            d.selectOrganization = $("#organization_filter").val(); 
            d.selectBranch = $("#branch_filter").val();
            d.selectVertical = $("#vertical_filter").val();
            d.selectDesignation = $("#designation_filter").val();
            d.selectDepartment = $("#department_filter").val(); 
        }
    },
    'columns': [
        {
            data: "designation" ,
            render : function(data, type, row) {
                var department = row.department;  
                return '<div style="font-size:small;"><p class = "mb-1"><b>Designation : </b> '+data+'</p><p class = "mb-1"><b>Department : </b>'+department+'</p></div>';
            } 
        },{
            data: "numofvacancies" ,
            render : function(data,type,row) {
                var vacancies = '<div>'+row.numofvacanciesfill+'/'+data+'</div>';
                return vacancies;
            }
        },{
            data: "Department_info" ,
            render : function(data, type, row) {
                var branch = row.branch ; 
                var vertical = row.vertical ; 
                var organization = row.organization ; 
                return '<div style="font-size:small;"><p class = "mb-1"><b>Organization : </b> '+organization+'</p><p class = "mb-1"><b>Branch : </b>'+branch+'</p><p class = "mb-1"><b>Vertical : </b>'+vertical+'</p></div>';
            }
        },{
            data: "raised_by" ,
            render : function(data,type,row) {
                raised_by = '<span class="badge rounded-pill bg-info m-1" style="background-color:#0388b8 !important;font-weight:200!important;font-size:15px;">'+data+'</span>'; 
                return raised_by;  
            } 
        },{
            data: "status" , 
            render : function(data,type,row) {
                var status = (row.status === 'In Progress') ? '<span class="badge bg-warning text-dark" style="font-weight:200!important;font-size:15px;">In Progress</span>' : '<span class="badge bg-success" style="font-weight:200!important;font-size:15px;">Completed</span>';
                return status;
            }
        },{         
            data : "Action" ,
            render : function(data, type, row) {
                var table = "Vacancies";
                var edit = '';
                <?php if(in_array('Vacancies Update',$_SESSION['permission'])) { ?>
                edit = '<div class="text-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit" onclick = "updateDetails('+row.id+','+row.numofvacancies+',&#39;'+row.designation+'&#39;,&#39;'+row.branch+'&#39;,&#39;'+row.vertical+'&#39;,&#39;'+row.reaised_by+'&#39;)"><i class="bi bi-pencil-fill"></i></div>';
                <?php } ?>
                return '<div class = "table-actions d-flex align-items-center gap-3 fs-6">' +  edit + '</div>';
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
    $('#vacanciesTable').DataTable(vacanciesSettings);
});

function applyfilter(){
    $('#vacanciesTable').DataTable().ajax.reload(null,false);
}

function updateDetails(id,numofvacancies,designation,branch,vertical,raisedby) {
    $.ajax({
        url : "/app/vacancies/insertAndupdateVacancies", 
        type : 'post',
        data : {id,numofvacancies,designation,branch,vertical,raisedby} , 
        success : function(data){
            $('#lg-modal-content').html(data);
            $('#lgmodal').modal('show');
        }
    });
}

</script>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer-bottom.php');?>