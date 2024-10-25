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

#tree>svg {
    background-color: #2E2E2E;
}

</style>

<main class="page-content position-relative">
<?php if($_SESSION['role'] != '2') {?>
<div class="container-fluid d-flex justify-content-center align-items-center mt-3 gap-1">
    <div class="col-sm-3 card bg-light p-1 mb-1" style="z-index: 0 !important;">
        <label class="col-form-label">Organization</label>
        <select type="text" class="form-control form-control-sm single-select select2" name="organization_filter" id="organization_filter" onchange="reloadTable(this.id)">
        </select>
    </div>
    <div class="col-sm-3 card bg-light p-1 mb-1" style="z-index: 0 !important;">
        <label class="col-form-label">Branch</label>
        <select type="text" class="form-control form-control-sm single-select select2" name="branch_filter" id="branch_filter" onchange="reloadTable(this.id)">
        </select>
    </div>
    <div class="col-sm-3 card bg-light p-1 mb-1" style="z-index: 0 !important;">
        <label class="col-form-label">Vertical</label>
        <select type="text" class="form-control form-control-sm single-select select2" name="vertical_filter" id="vertical_filter" onchange="reloadTable(this.id)">
        </select>
    </div>
    <div class="col-sm-3 card bg-light p-1 mb-1" style="z-index: 0 !important;">
        <label class="col-form-label">Department</label>
        <select type="text" class="form-control form-control-sm single-select select2" name="department_filter" id="department_filter" onchange="reloadTable(this.id)">
        </select>
    </div>
</div>
<?php } ?>
<div id="legent-content" style="display: none;">
    <div class="row d-flex flex-row text-white" id = "organization_box" style="display: none !important;">
        <div class="col-sm-2 mt-1" style="width: 16px;height:16px; display: inline-block; border-radius: 15px;">
        </div>
        <div class="col-sm-2" style="width: 65%;">Organization</div>
        <div class="col-sm-2" style="width: 6%;" id="organization"></div> 
    </div>
    <div class="row d-flex flex-row text-white" id = "branch_box" style="display: none !important;">
        <div class="col-md-2 mt-1" style="width: 16px;height:16px; background-color:#F57C00; display: inline-block; border-radius: 15px;">
        </div>
        <div class="col-sm-2" style="width: 65%;">Branch</div>
        <div class="col-sm-2" style="width: 6%;" id="branch"></div>
    </div>
    <div class="row d-flex flex-row text-white" id = "vertical_box" style="display: none !important;">
        <div class="col-md-2 mt-1" style="width: 16px;height:16px; background-color:#FFCA28; display: inline-block; border-radius: 15px;">
        </div>
        <div class="col-sm-2" style="width: 65%;">Vertical</div>
        <div class="col-sm-2" style="width: 6%;" id="vertical"></div>
    </div>
    <div class="row d-flex flex-row text-white" id= "department_box" style="display: none !important;">
        <div class="col-md-2 mt-1" style="width: 16px;height:16px; background-color:#FFCA28; display: inline-block; border-radius: 15px;">
        </div>
        <div class="col-sm-2" style="width: 65%;">Department</div>
        <div class="col-sm-2" style="width: 6%;" id="department"></div>
    </div>
    <div class="row d-flex flex-row text-white" id = "user_box" style="display: none !important;"> 
        <div class="col-md-2 mt-1" style="width: 16px;height:16px; background-color:#FFCA28; display: inline-block; border-radius: 15px;">
        </div>
        <div class="col-sm-2" style="width: 65%;">Employee</div>
        <div class="col-sm-2" style="width: 6%;" id="user"></div>
    </div>
    <div class="row d-flex flex-row text-white" id = "vacancy_box" style="display: none !important;">
        <div class="col-md-2 mt-1" style="width: 16px;height:16px; background-color:#FFCA28; display: inline-block; border-radius: 15px;">
        </div>
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
    var filter_data_field = ['organization'];
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

function reloadTable(id) {
    if(id == 'organization_filter') {
        var organization_id = $("#organization_filter").val();
        $.ajax({
            url : "/app/common/branchList",
            type : "post", 
            data: {
                organization_id
            },  
            success : function(data) {
                $("#branch_filter").html(data);
                if($('#vertical_filter option').length > 0) {
                    $('#vertical_filter').val("");
                    $('#vertical_filter').trigger('change');
                }
                if($("#department_filter option").length > 0 ) {
                    $('#department_filter').val("");
                    $('#department_filter').trigger('change');
                }
                initializeChart();
            }   
        });  
    } else if (id == 'branch_filter' && $("#branch_filter").val().length > 0) {
        var organization_id = $("#organization_filter").val();
        var branch = $("#branch_filter").val();
        $.ajax({
            url : "/app/common/verticalList",
            type : "post", 
            data: {
                organization_id,
                branch
            }, 
            success : function(data) {
                if($("#department_filter option").length > 0 ) {
                    $('#department_filter').val("");
                    $('#department_filter').trigger('change');
                }
                $("#vertical_filter").html(data);
                initializeChart();
            }   
        });  
    } else if(id == 'vertical_filter' && $("#vertical_filter").val().length > 0){
        var organization_id = $("#organization_filter").val();
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

async function fetchData() {
    try {
        var filter_data = {};
        filter_data.department_id =  ($('#department_filter option').length > 0 && $('#department_filter').val().length > 0 ) ? $('#department_filter').val() : '';
        filter_data.organization_id = ($('#organization_filter option').length > 0 && $('#organization_filter').val().length > 0 ) ? $('#organization_filter').val() : '';
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
    organization: {
        counting : 0,
        color : "",
    }, branch : {
        counting : 0,
        color : "",
    }, vertical : {
        counting : 0 , 
        color : "",
    }, department : {
        counting : 0 , 
        color : "",
    }, user : {
        counting : 0 , 
        color : "",
    }, vacancy : {
        counting : 0 , 
        color : "",
    }
};

function incrementNodeCount(variableName,color) {
    if (nodeCount.hasOwnProperty(variableName)) {
        nodeCount[variableName]['counting']++;
        nodeCount[variableName]['color'] = color;
    }
}

async function initializeChart() {
    const data = await fetchData();
    var tag = {}; var colors = []; var designations = [];
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
        if(!colors.includes(data[x]['color'])) {
            colors.push(data[x]['color']);    
        }
        if(!designations.includes(data[x]['Code'])) {
            designations.push(data[x]['Code']);    
        }
        var designation = data[x]['Code'];
        tag[designation] = {'template':designation};
    }
    // console.log(colors);
    // console.log(designations);
    // console.log(tag);
    
    for (const key in nodeCount) {
        if (Object.prototype.hasOwnProperty.call(nodeCount, key)) {
            const element = nodeCount[key]['counting'];
            if (element > 0) {
                if (key == 'user') {
                    $("#"+key+"_box").css({'display':'block'});
                } else {
                    $("#"+key+"_box").css({'display':'block',"background-color":nodeCount[key]['color']});
                }
                $("#"+key).html(element);
            } else {
                $("#"+key+"_box").css("display","none");
                $("#"+key).html(element);
            }
        }
    }

    for (let i = 0; i < designations.length; i++) {
        if ( designations[i] == 'organization' || designations[i] == 'branch' || designations[i] == 'vertical' || designations[i] == 'department' ) {
            OrgChart.templates[designations[i]] = Object.assign({}, OrgChart.templates.ana);
            OrgChart.templates[designations[i]].size = [200, 170];
            OrgChart.templates[designations[i]].node = 
                '<rect x="0" y="80" height="90" width="200" fill="'+colors[i]+'"></rect><circle cx="100" cy="50" fill="#ffffff" r="50" stroke="#145DA0" stroke-width="2"></circle>';

            OrgChart.templates[designations[i]].img_0 = 
                `<clipPath id="{randId}"><circle cx="100" cy="50" r="45"></circle></clipPath>
                <image preserveAspectRatio="xMidYMid slice" clip-path="url(#{randId})" xlink:href="{val}" x="50" y="0" width="100" height="100"></image>`;

            OrgChart.templates[designations[i]].field_0 = 
                `<text data-width="185" style="font-size: 18px;" fill="#ffffff" x="100" y="125" text-anchor="middle">{val}</text>`;
            OrgChart.templates[designations[i]].field_1 = 
                `<text data-width="185" style="font-size: 14px;" fill="#ffffff" x="100" y="145" text-anchor="middle">{val}</text>`;

            OrgChart.templates[designations[i]].editFormHeaderColor = colors[i]; // for edit form color
        } else {
            OrgChart.templates[designations[i]] = Object.assign({}, OrgChart.templates.ana);
            OrgChart.templates[designations[i]].size = [250, 120];

            OrgChart.templates[designations[i]].node = 
                '<rect x="0" y="0" height="{h}" width="{w}" fill="'+colors[i]+'" stroke-width="1" stroke="#aeaeae" rx="7" ry="7"></rect>';

            OrgChart.templates[designations[i]].padding = [50, 20, 35, 20];
            OrgChart.templates[designations[i]].editFormHeaderColor = colors[i]; // for edit form color
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
            // collapse: {
            //     level: 1,
            //     allChildren: true
            // }, 
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

</script> 
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer-bottom.php');?>