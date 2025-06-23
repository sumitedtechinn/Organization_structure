<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-top.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-bottom.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/topbar.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/menu.php');?>
<style>
.raised_btn{
    background-color: #0388b8 !important;
    font-weight: 200;
    font-size: small;
    width: 120px;
    padding: 4%;
}
.vacancy_status{
    font-weight:200;
    font-size:small;
}
</style>
<!--start content-->
<main class="page-content">
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <h6 class="mb-0">Vacancies Details</h6>
                <div class="d-flex justify-content-end col-sm-3 gap-1">
                    <?php if(in_array('Vacancies Delete',$_SESSION['permission'])) { ?>
                    <div class="theme-icons sha dow-sm p-2 cursor-pointer rounded" title="Go to Trash" data-bs-toggle="tooltip" id = "trash_button">
                        <i class="bi bi-trash-fill"></i>
                    </div>
                    <button class="btn btn-primary" style="font-size: small;" id ="return_button">Go To Vacancy</button>
                    <?php } ?>
                    <?php if($_SESSION['role'] == '1' || $_SESSION['role'] == '3') { ?>
                    <div class="col-sm-2">
                        <input type="color" class="form-control form-control-color" name="node_color" id="node_color" title="Select Node Color" onchange="setNodeColor(this.value,'Vacancies')">
                    </div>
                    <?php } ?>
                    <?php if( in_array('Vacancies Create',$_SESSION['permission'])) { ?>
                    <div class="d-flex align-items-center theme-icons shadow-sm p-2 cursor-pointer rounded" title="Add" onclick="addVacancies()" data-bs-toggle="tooltip">
                        <i class="bi bi-plus-circle-fill" id="add_vacancies"></i>
                    </div>
                    <?php } ?>
                </div> 
            </div>
            <?php if($_SESSION['role'] == '1' || $_SESSION['role'] == '3') { ?>
            <div class="d-flex align-items-center justify-content-between gap-2 mt-3" id="filter_container">
            </div>
            <?php } ?>
            <div class="table-responsive mt-3">
                <table class="table align-middle w-100" id="vacanciesTable" style="color: #515B73!important;">
                    <thead class="table-primary">
                        <tr class="table_heading"> 
                            <td>Vacancy Info</td>
                            <td>Filled / Required</td>
                            <td>Organization Info</td>
                            <td>Raised By</td>
                            <td>Status</td>
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
    fetchNodeColor("Vacancies");
    var filter_data_field = ['organization','branch','vertical','department','designation'];
    $.ajax({
        url : "/app/common/getAllFilterData", 
        type : "post",
        contentType: 'json',  // Set the content type to JSON 
        data: JSON.stringify(filter_data_field), 
        dataType: 'json', 
        success : function(data) {
            let filterBox = document.getElementById("filter_container");
            for (const key in data) {
                filterBox.append(createSelectTag(key,data[key]));
                $("#"+key+"_filter").select2({
                    placeholder: 'Choose ' + key.charAt(0).toUpperCase() + key.slice(1,key.length), 
                    allowClear: true,
                    width: '100%'
                });
            }
        }   
    })
});

function createSelectTag(key,inner_content) {

    let div = document.createElement("div");
    div.className = "col-sm-2";
    let select = document.createElement("select");
    select.type = "text";
    select.classList.add("form-control","form-control-sm","single-select","select2");
    select.name = `${key}_filter`;
    select.id = `${key}_filter`;
    select.onchange = function() {
        reloadTable(this.id);
    }
    select.innerHTML = inner_content;
    div.append(select);
    return div;
}

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
                let designation = makeContent(data);
                let department = makeContent(row.department);
                return `<div style="font-size:small;">
                <p class = "mb-1"><span style="font-weight:500;">Designation : </span>${designation}</p>
                <p class = "mb-1"><span style="font-weight:500;">Department : </span>${department}</p>
                </div>`;
            } 
        },{
            data: "numofvacancies" ,
            render : (data,type,row) => `<div>${row.numofvacanciesfill}<span class="table_heading">/${data}</span></div>`
        },{
            data: "Department_info" ,
            render : function(data, type, row) {
                let branch = makeContent(row.branch);
                let vertical = makeContent(row.vertical);
                let organization = makeContent(row.organization);
                return `<div style="font-size:small;">
                <p class = "mb-1"><span style="font-weight:500;">Organization : </span>${organization}</p>
                <p class = "mb-1"><span style="font-weight:500;">Branch : </span>${branch}</p>
                <p class = "mb-1"><span style="font-weight:500;">Vertical : </span>${vertical}</p>
                </div>`;
            }
        },{
            data: "raised_by" ,
            render : (data,type,row) => `<span class="badge rounded-pill bg-info m-1 truncate-label raised_btn" data-bs-toggle="tooltip" title="${data}">${data}</span>`
        },{
            data: "status" , 
            render : function(data,type,row) {
                let status = (row.status === 'In Progress') ? '<span class="badge bg-warning text-dark vacancy_status">In Progress</span>' : '<span class="badge bg-success vacancy_status">Completed</span>';
                return status;
            }
        },{         
            data : "Action" ,
            render : function(data, type, row) {
                let table = "Vacancies";
                let edit = ''; let del = '';
                <?php if(in_array('Vacancies Update',$_SESSION['permission'])) { ?>
                edit = '<div class="text-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit" onclick = "updateDetails('+row.id+','+row.numofvacancies+',&#39;'+row.designation+'&#39;,&#39;'+row.branch+'&#39;,&#39;'+row.vertical+'&#39;,&#39;'+row.reaised_by+'&#39;)"><i class="bi bi-pencil-fill"></i></div>';
                <?php } ?>
                <?php if(in_array('Vacancies Delete',$_SESSION['permission'])) { ?>
                del = `<div class="text-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete" onclick = "checkDeleteCondition(${row.id},'${table}','${row.numofvacancies}')"><i class="bi bi-trash-fill"></i></div>`;
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

var vacanciesTrashSettings = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    "searching": false ,
    'ajax': {
        'url': '/app/vacancies/vacancies-server.php',
        'type': 'POST',
        'data' : function(d) {
            d.deletedVacancy = "deleteType"
        }
    },
    'columns': [
        {
            data: "designation" ,
            render : function(data, type, row) {
                let designation = makeContent(data);
                let department = makeContent(row.department);
                return `<div style="font-size:small;">
                <p class = "mb-1"><span style="font-weight:500;">Designation : </span>${designation}</p>
                <p class = "mb-1"><span style="font-weight:500;">Department : </span>${department}</p>
                </div>`;
            } 
        },{
            data: "numofvacancies" ,
            render : (data,type,row) => `<div>${row.numofvacanciesfill}<span class="table_heading">/${data}</span></div>`
        },{
            data: "Department_info" ,
            render : function(data, type, row) {
                let branch = makeContent(row.branch);
                let vertical = makeContent(row.vertical);
                let organization = makeContent(row.organization);
                return `<div style="font-size:small;">
                <p class = "mb-1"><span style="font-weight:500;">Organization : </span>${organization}</p>
                <p class = "mb-1"><span style="font-weight:500;">Branch : </span>${branch}</p>
                <p class = "mb-1"><span style="font-weight:500;">Vertical : </span>${vertical}</p>
                </div>`;
            }
        },{
            data: "raised_by" ,
            render : (data,type,row) => `<span class="badge rounded-pill bg-info m-1 truncate-label raised_btn" data-bs-toggle="tooltip" title="${data}">${data}</span>`
        },{
            data: "status" , 
            render : function(data,type,row) {
                let status = (row.status === 'In Progress') ? '<span class="badge bg-warning text-dark vacancy_status">In Progress</span>' : '<span class="badge bg-success vacancy_status">Completed</span>';
                return status;
            }
        },{         
            data : "Action" ,
            render : function(data, type, row) {
                let table = "Vacancies";
                let restore = `<div class="text-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Re-store" onclick = "restoreDetails(${row.id},'${table}')"><i class="fadeIn animated bx bx-sync" style = "font-size:larger;"></i></div>`;
                let del = `<div class="text-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Parmanent Delete" onclick = "parmanentDeleteDetails(${row.id},'${table}')"><i class="bi bi-trash-fill"></i></div>`;
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
    $("#return_button").css('display','none');
    $('#vacanciesTable').DataTable(vacanciesSettings);
});

$("#trash_button").on('click',function(){
    $('#vacanciesTable').DataTable(vacanciesTrashSettings);
    $("#return_button").css('display','block');
    $("#trash_button").css('display','none');
});


$("#return_button").on('click',function(){
    $('#vacanciesTable').DataTable(vacanciesSettings);
    $("#return_button").css('display','none');
    $("#trash_button").css('display','block');
});

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

function checkDeleteCondition(id,table,required_vacancy) {
    if(required_vacancy > 0) {
        Swal.fire({
            title : "Vacancy Delete Not Allow" , 
            text: "Only Vacancy with Zero required Are Allow",
            icon: 'warning',
        });
    } else {
        deleteDetails(id,table);
    }
}

</script>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer-bottom.php');?>