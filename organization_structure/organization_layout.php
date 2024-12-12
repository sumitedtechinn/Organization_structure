<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-top.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-bottom.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/topbar.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/menu.php');?>
<?php 
$col_length = ($_SESSION['role'] == '3') ? 'col-sm-4' : 'col-sm-3';
?>

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

#tree>svg {
    background-color: #3580b3;
    background-image: url("/assets/images/world_map.png");
    background-position: center;
    background-size: contain;
    background-repeat: no-repeat;
    height: 880px;
}

.swal2-popup{
    width: 45rem;
    padding: 1em 1em .3em;
}

</style>

<main class="page-content position-relative">
<?php if($_SESSION['role'] != '2') {?>
<div class="container-fluid d-flex justify-content-center align-items-center mt-3 gap-1">
    <?php if($_SESSION['role'] == '1') { ?>
    <div class="col-sm-3 card bg-light p-1 mb-1" style="z-index: 0 !important;">
        <label class="col-form-label">Organization</label>
        <select type="text" class="form-control form-control-sm single-select select2" name="organization_filter" id="organization_filter" onchange="reloadTable(this.id)">
        </select>
    </div>
    <?php } ?>
    <div class="<?=$col_length?> card bg-light p-1 mb-1" style="z-index: 0 !important;">
        <label class="col-form-label">Branch</label>
        <select type="text" class="form-control form-control-sm single-select select2" name="branch_filter" id="branch_filter" onchange="reloadTable(this.id)">
        </select>
    </div>
    <div class="<?=$col_length?> card bg-light p-1 mb-1" style="z-index: 0 !important;">
        <label class="col-form-label">Vertical</label>
        <select type="text" class="form-control form-control-sm single-select select2" name="vertical_filter" id="vertical_filter" onchange="reloadTable(this.id)">
        </select>
    </div>
    <div class="<?=$col_length?> card bg-light p-1 mb-1" style="z-index: 0 !important;">
        <label class="col-form-label">Department</label>
        <select type="text" class="form-control form-control-sm single-select select2" name="department_filter" id="department_filter" onchange="reloadTable(this.id)">
        </select>
    </div>
</div>
<?php } ?>
<div id="legent-content" style="display: none;">
    <div class="row d-flex flex-row text-white" id = "organization_box" style="display: none !important;">
        <div class="col-sm-2" style="width: 65%;">Organization</div>
        <div class="col-sm-2" style="width: 6%;" id="organization"></div> 
    </div>
    <div class="row d-flex flex-row text-white" id = "branch_box" style="display: none !important;">
        <div class="col-sm-2" style="width: 65%;">Branch</div>
        <div class="col-sm-2" style="width: 6%;" id="branch"></div>
    </div>
    <div class="row d-flex flex-row text-white" id = "vertical_box" style="display: none !important;">
        <div class="col-sm-2" style="width: 65%;">Vertical</div>
        <div class="col-sm-2" style="width: 6%;" id="vertical"></div>
    </div>
    <div class="row d-flex flex-row text-white" id= "department_box" style="display: none !important;">
        <div class="col-sm-2" style="width: 65%;">Department</div>
        <div class="col-sm-2" style="width: 6%;" id="department"></div>
    </div>
    <div class="row d-flex flex-row text-white cursor-pointer" id = "user_box" style="display: none !important;" onclick="showDetails('employee')"> 
        <div class="col-sm-2" style="width: 65%;">Employee</div>
        <div class="col-sm-2" style="width: 6%;" id="user"></div>
    </div>
    <div class="row d-flex flex-row text-white cursor-pointer" id = "vacancy_box" style="display: none !important;" onclick="showDetails('vacancy')">
        <div class="col-sm-2" style="width: 65%;">Vacancy</div>
        <div class="col-sm-2" style="width: 6%;" id="vacancy"></div>
    </div>
</div>
<div id="tree" class="container-fluid full-height d-flex justify-content-center align-items-center"></div>
</main>

<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer-top.php');?>
<script src="/assets/plugins/select2/js/select2.min.js"></script>
<script src="/assets/js/form-select2.js"></script>
<script src="/assets/orgchart.js"></script>
<script>

<?php if($_SESSION['role'] == '2') { ?>
    $(".full-height").height(90+"vh");
<?php } else { ?> 
    $(".full-height").height(82+"vh");
<?php } ?>

$(document).ready(function(){
    getFilterData();    
});

function getFilterData() {
    <?php if($_SESSION['role'] == '1') { ?>
        var filter_data_field = ['organization'];
        $.ajax({
            url : "/app/common/getAllFilterData", 
            type : "post",
            contentType: 'json',  // Set the content type to JSON 
            data: JSON.stringify(filter_data_field), 
            dataType: 'json', 
            success : function(data) {
                for (const key in data) {
                    $("#"+key+"_filter").html(updateOptionTag(data[key]));
                    $("#"+key+"_filter").trigger('change'); 
                }
            }   
        });
    <?php } elseif ($_SESSION['role'] == '3') { ?>
        var organization_id = '<?=$_SESSION['Organization_id']?>';
        $.ajax({
            url : "/app/common/branchList",
            type : "post", 
            data: {
                organization_id
            },  
            success : function(data) {
                $("#branch_filter").html(updateOptionTag(data));
                $("#branch_filter").trigger('change');
            }  
        });
    <?php } ?>
}

var isUpdating = false;

function reloadTable(id) {
    if(isUpdating) return;
    if(id == 'organization_filter') {
        var organization_id = $("#organization_filter").val();
        $.ajax({
            url : "/app/common/branchList",
            type : "post", 
            data: {
                organization_id
            },  
            success : function(data) {
                $("#branch_filter").html(updateOptionTag(data));
                $("#branch_filter").trigger('change');
                filter_arr = ['vertical','department'];
                for (const key in filter_arr) {
                    if($('#'+filter_arr[key]+'_filter option').length > 0) {
                        triggerChange(filter_arr[key]+'_filter');
                    }
                }
                initializeChart();
            }   
        });  
    } else if (id == 'branch_filter' && $("#branch_filter").val().length > 0) {
        var organization_id = '';
        <?php if ($_SESSION['role'] == '3') { ?>
            organization_id = '<?=$_SESSION['Organization_id']?>';
        <?php } else { ?>
            organization_id = $("#organization_filter").val();
        <?php } ?>
        var branch = $("#branch_filter").val();
        $.ajax({
            url : "/app/common/verticalList",
            type : "post", 
            data: {
                organization_id,
                branch
            }, 
            success : function(data) {
                $("#vertical_filter").html(updateOptionTag(data));
                $("#vertical_filter").trigger('change');
                filter_arr = ['department'];
                for (const key in filter_arr) {
                    if($('#'+filter_arr[key]+'_filter option').length > 0) {
                        triggerChange(filter_arr[key]+'_filter');
                    }
                }
                initializeChart();
            }   
        });  
    } else if(id == 'vertical_filter' && $("#vertical_filter").val().length > 0){
        var organization_id = '';
        <?php if ($_SESSION['role'] == '3') { ?>
            organization_id = '<?=$_SESSION['Organization_id']?>';
        <?php } else { ?>
            organization_id = $("#organization_filter").val();
        <?php } ?>
        var branch_id = $("#branch_filter").val();
        var vertical_id = $("#vertical_filter").val();
        $.ajax({
            url : "/app/common/departmentList",
            type : "post", 
            data: {
                organization_id,
                branch_id,
                vertical_id
            },  
            success : function(data) {
                $("#department_filter").html(data);
                initializeChart();
            }   
        });  
    } else {
        initializeChart();
    }
}

function triggerChange(id) {
    isUpdating = true;
    console.log(id);
    $("#"+id).val("");
    $("#"+id).trigger('change');
    isUpdating = false;
}

function updateOptionTag(strData) {
    let options = strData.split('</option>');
    options = options.filter((option) => (option != '') ? true : false);
    let count = 1;
    options = options.map((option) => {
        option += '</option>';
        if(count === 1) {
            if (!option.includes('value=""')) {
                option = option.replace('<option', '<option selected');
                count++;
            }
        }
        return option;
    });
    return options.join('');
}

async function fetchData() {
    try {
        var filter_data = {};
        filter_data.department_id =  ($('#department_filter option').length > 0 && $('#department_filter').val().length > 0 ) ? $('#department_filter').val() : '';
        <?php if ($_SESSION['role'] == '3') { ?>
            filter_data.organization_id = '<?=$_SESSION['Organization_id']?>';
        <?php } else { ?>
            filter_data.organization_id = ($('#organization_filter option').length > 0 && $('#organization_filter').val().length > 0 ) ? $('#organization_filter').val() : '';
        <?php } ?>
        filter_data.branch_id = ($('#branch_filter option').length > 0 && $('#branch_filter').val().length > 0) ? $('#branch_filter').val() : '';
        filter_data.vertical_id = ($('#vertical_filter option').length > 0 && $('#vertical_filter').val().length > 0)? $("#vertical_filter").val() : '';
        const response = await fetch('/app/organization_layout/layout-server' , {
            method: "POST",
            body: JSON.stringify(filter_data),
        });
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('There has been a problem with your fetch operation:', error);
    }
}


let nodeCount = {
    organization: {counting : 0,color : "",}, 
    branch : {counting : 0,color : "",}, 
    vertical : {counting : 0 , color : "",}, 
    department : { counting : 0 , color : "",}, 
    user : {counting : 0 , color : "",}, 
    vacancy : {counting : 0 , color : "",}
};

let employeeCount = {}; let vacancyCount = {}; let departmentData = {};

function incrementNodeCount(variableName,color) {
    if (nodeCount.hasOwnProperty(variableName)) {
        nodeCount[variableName]['counting']++;
        nodeCount[variableName]['color'] = color;
    }
}

function insertDepartmentData (id,name) {
    if(!(id in departmentData)) {
        departmentData[id] = name;
    }
}

function insertEmployeeData(department_id,designation_name,designation_code) {
    if (!(department_id in employeeCount)) {
        employeeCount[department_id] = {};
    }

    if (!(designation_code in employeeCount[department_id])) {
        employeeCount[department_id][designation_code] = {
            designation_name: designation_name,
            count: 1
        };
    } else {
        employeeCount[department_id][designation_code]['count']++;
    }
}

function insertVacancyData(department_id,designation_name,designation_code) {
    if (!(department_id in vacancyCount)) {
        vacancyCount[department_id] = {};
    }

    if (!(designation_code in vacancyCount[department_id])) {
        vacancyCount[department_id][designation_code] = {
            designation_name: designation_name,
            count: 1
        };
    } else {
        vacancyCount[department_id][designation_code]['count']++;
    }
}

async function initializeChart() {
    const data = await fetchData();
    var tag = {}; var colors = []; var designations = {};
    employeeCount = {}; vacancyCount = {}; departmentData = {};
    nodeCount.organization.counting = 0;
    nodeCount.branch.counting = 0;
    nodeCount.vertical.counting = 0;
    nodeCount.department.counting = 0;
    nodeCount.user.counting = 0;
    nodeCount.vacancy.counting = 0;
    for (let x in data) {
        let counting = (data[x]['id'].split("_"))[0];
        let color = data[x]['color'];
        incrementNodeCount(counting,color);
        if(counting == 'department') {
            let department_id = (data[x]['id'].split("_"))[1];
            let department_name = data[x]['Name'];
            insertDepartmentData(department_id,department_name);
        }
        if(counting == 'user' || counting == 'vacancy' ) {
            let depart_id = (data[x]['id'].split("_"))[2];
            let designation_name = data[x]['Designation'];
            let designation_code = '';
            if(counting == 'user') {
                designation_code = (data[x]['Code'].split("_"))[0];
                insertEmployeeData(depart_id,designation_name,designation_code);
            } else if (counting == 'vacancy') {
                designation_code = data[x]['Designation_code'];
                insertVacancyData(depart_id,designation_name,designation_code);
            }
        }
        if(addDesignation(data[x]['Code'],data[x]['color'])) {
            const newIndex = Object.keys(designations).length; // Get the next index
            designations[newIndex] = {
                designation: data[x]['Code'],
                color: data[x]['color']
            };
        }
        var designation = data[x]['Code'];
        tag[designation] = {'template':designation};
    }
    // console.log(nodeCount);
    //console.log(designations);
    //console.log(tag);
    
    function addDesignation(newDesignation, newColor) {
        for (let key in designations) {
            if (designations[key].designation === newDesignation) {
                return false; // Exit if designation is found
            } 
        } 
        return true;
    }

    for (const key in nodeCount) {
        if (Object.prototype.hasOwnProperty.call(nodeCount, key)) {
            const element = nodeCount[key]['counting'];
            if (element > 0) {
                if (key == 'user') {
                    $("#"+key+"_box").css({'display':'block',"background-color":'#859F3D'});
                } else {
                    $("#"+key+"_box").css({'display':'block',"background-color":nodeCount[key]['color']});
                }
                $("#"+key).html(element);
            } else {
                $("#"+key+"_box").css("display","none !important");
                $("#"+key).html(element);
            }
        }
    }

    for (let key in designations) {
        if ( designations[key].designation == 'organization' || designations[key].designation == 'branch' || designations[key].designation == 'vertical' || designations[key].designation == 'department' ) {
            OrgChart.templates[designations[key].designation] = Object.assign({}, OrgChart.templates.ana);
            OrgChart.templates[designations[key].designation].size = [200, 170];
            OrgChart.templates[designations[key].designation].node = 
                '<rect x="0" y="80" height="90" width="200" fill="'+designations[key].color+'"></rect><circle cx="100" cy="50" fill="#ffffff" r="50" stroke="#145DA0" stroke-width="2"></circle>';

            OrgChart.templates[designations[key].designation].img_0 = 
                `<clipPath id="{randId}"><circle cx="100" cy="50" r="45"></circle></clipPath>
                <image preserveAspectRatio="xMidYMid slice" clip-path="url(#{randId})" xlink:href="{val}" x="50" y="0" width="100" height="100"></image>`;

            OrgChart.templates[designations[key].designation].field_0 = 
                `<text data-width="185" style="font-size: 18px;" fill="#ffffff" x="100" y="125" text-anchor="middle">{val}</text>`;
            OrgChart.templates[designations[key].designation].field_1 = 
                `<text data-width="185" style="font-size: 14px;" fill="#ffffff" x="100" y="145" text-anchor="middle">{val}</text>`;

            OrgChart.templates[designations[key].designation].editFormHeaderColor = designations[key].color; // for edit form color
        } else {
            OrgChart.templates[designations[key].designation] = Object.assign({}, OrgChart.templates.ana);
            OrgChart.templates[designations[key].designation].size = [250, 120];

            OrgChart.templates[designations[key].designation].node = 
                '<rect x="0" y="0" height="{h}" width="{w}" fill="'+designations[key].color+'" stroke-width="1" stroke="#aeaeae" rx="7" ry="7"></rect>';

            OrgChart.templates[designations[key].designation].padding = [50, 20, 35, 20];
            OrgChart.templates[designations[key].designation].editFormHeaderColor = designations[key].color; // for edit form color
        }
    }
    
    if (data) {
        var chart = new OrgChart("#tree", {
            enableSearch: true,
            mouseScrool: OrgChart.action.scroll,
            tags: tag,  
            toolbar: {
                zoom: true,
                fit: true,
                expandAll: true
            },
            showXScroll: true,
            editForm: {
                titleBinding: "Name",
                photoBinding: "Image", 
                generateElementsFromFields: false,
                elements: [
                    { type: 'textbox', label: 'Full Name', binding: 'Name' },
                    { type: 'textbox', label: 'Designation', binding: 'Designation' },
                    { type: 'textbox', label: 'Duration', binding: 'Duration' },
                    { type: 'textbox', label: 'Address', binding: 'Address' }     
                ]
            }, 
            nodeBinding: {  
                field_0: "Name",
                field_1: "Designation",
                img_0: "Image"
            },
            searchFields: ["Name"],
            searchDisplayField: "title",
            nodes: data,
            layout: OrgChart.tree,
            scaleInitial: OrgChart.match.boundary,
        });

        chart.on('init', function () {
            var legent = document.createElement("div");
            legent.style.position = 'absolute';
            legent.style.top = '10px';
            legent.style.left = '30px';
            legent.style.color = '#757575';
            legent.innerHTML = document.querySelector('#legent-content').innerHTML;
            chart.element.appendChild(legent);
        });
    }
}

$(document).ready(function(){
    initializeChart();
});

function makeEmployeeAndVacancyData(type) {
    if(type == 'employee') {
        let makeEmplyeeDetailsTable = '<table class="table table-striped align-middle" style="font-size:0.7rem;"><thead class="table-secondary"><tr><th>Department</th><th>Designation</th><th>No. of Employee</th></tr></thead>';
        for (const key in employeeCount) {
            let depart_name = departmentData[key];
            for (const code in employeeCount[key]) {
                makeEmplyeeDetailsTable += '<tr>';
                makeEmplyeeDetailsTable += '<td>'+depart_name+'</td>';
                makeEmplyeeDetailsTable += '<td>'+employeeCount[key][code]['designation_name']+'</td>';
                makeEmplyeeDetailsTable += '<td>'+employeeCount[key][code]['count']+'</td>';
                makeEmplyeeDetailsTable += '</tr>';
            }
        }
        makeEmplyeeDetailsTable += '</table>';
        return makeEmplyeeDetailsTable;
    } else {
        let makevacancyDetailsTable =  '<table class="table table-striped align-middle" style="font-size:0.7rem;"><thead class="table-secondary"><tr><th>Department</th><th>Designation</th><th>No. of Vacancy</th></tr></thead>';
        for (const key in vacancyCount) {
            let depart_name = departmentData[key];
            for (const code in vacancyCount[key]) {
                makevacancyDetailsTable += '<tr>';
                makevacancyDetailsTable += '<td>'+depart_name+'</td>';
                makevacancyDetailsTable += '<td>'+vacancyCount[key][code]['designation_name']+'</td>';
                makevacancyDetailsTable += '<td>'+vacancyCount[key][code]['count']+'</td>';
                makevacancyDetailsTable += '</tr>';
            }
        }
        makevacancyDetailsTable += '</table>';
        return makevacancyDetailsTable;
    }
}

function showDetails(type) {
    let showData = makeEmployeeAndVacancyData(type);
    Swal.fire({
        html: showData,
        showClass: {
            popup: 'animate__animated animate__fadeInUp animate__faster'
        },
        hideClass: {
            popup: 'animate__animated animate__fadeOutDown animate__faster'
        },
    });
}

</script> 
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer-bottom.php');?>