<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-top.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-bottom.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/topbar.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/menu.php');?>
<style>
.user-btn {
    position: relative;
    cursor: pointer;
    color: white;
    font-size: small;
    overflow: visible;
}

/* Tooltip base */
.user-tooltip {
    position: absolute;
    top: -160%;
    left: 110%;
    background-color: #ffffff;
    border: 1px solid #e0e0e0;
    padding: 12px;
    width: 220px;
    box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.12);
    z-index: 9999;
    text-align: center;
    border-radius: 10px;

    opacity: 0;
    visibility: hidden;
    transform: translateY(10px);
    transition: opacity 0.3s ease, visibility 0.3s ease, transform 0.3s ease;
}

/* Tooltip visible state */
.user-btn.show-tooltip .user-tooltip {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.user-tooltip img {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    object-fit: cover;
    display: block;
    margin: 0 auto 10px auto;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}

.user-info {
    font-size: 12px;
    font-family: 'Segoe UI', sans-serif;
    color: #333;
}

.user-info p {
    margin: 6px 0;
    line-height: 1.3;
}

.user-info span {
    font-weight: 600;
    color: #444;
}

</style>
<main class="page-content">
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="mb-0">Assets</h6>
                <div class="d-flex justify-content-end gap-2 col-sm-6">
                    <?php if(in_array('Assets Delete',$_SESSION['permission'])) { ?>
                    <button class="btn btn-outline-danger btn-sm d-flex align-items-center" id="trash_button" data-bs-toggle="tooltip" title="Go to Trash">
                        <i class="bi bi-trash-fill me-1" id="trash_category"></i>
                    </button>
                    <button class="btn btn-primary" style="font-size: small;" id ="return_button">Go To Assets</button>
                    <?php } ?>
                    <?php if(in_array('Assets Create',$_SESSION['permission'])) { ?>
                    <div class="d-flex align-items-center theme-icons shadow-sm p-2 cursor-pointer rounded gap-2 bg-primary" title="Add Assets" style="color: white;" onclick="addAssets()" data-bs-toggle = "tooltip"><span>Add Assets</span>
                        <i class="bi bi-plus-circle-fill" id="add_assets"></i>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-10 d-flex gap-2" id="filter_container">
                    <div class="col-sm-3">
                        <select type="text" class="form-control form-control-sm single-select select2" name = "assets_category_filter" id="assets_category_filter" onchange="reloadTable(this.value)"></select>
                    </div>
                    <div class="col-sm-3">
                        <select type="text" class="form-control form-control-sm single-select select2" name = "assets_status_filter" id="assets_status_filter" onchange="reloadTable(this.value)"></select>
                    </div>
                    <div class="col-sm-3">
                        <select type="text" class="form-control form-control-sm single-select select2" name = "user_filter" id="assets_user_filter" onchange="reloadTable(this.value)"></select>
                    </div>
                </div>
                <div class="col-sm-2">
                    <div class="col-sm-12">
                        <input type="text" id="assets-search-table" class="form-control form-control-sm pull-right" placeholder="Search">
                    </div>
                </div>
            </div>
            <div class="table-responsive mt-3">
                <table class="table align-middle w-100" id="assetsTable" style="color: #515B73!important;">
                    <thead class="table-primary">
                        <tr class="table_heading"> 
                            <td>Assets Code</td>
                            <td>Brand Name</td>
                            <td>Model Number</td>
                            <td>Assets Category</td>
                            <td>Assets Assign To</td>
                            <td>Assets Status</td>
                            <td>Description</td>
                            <td>Assets History</td>
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

var assetsSetting = {
    'processing' : true,
    'serverSide' : true,
    'serverMethod' : 'POST',
    'searching' : false,
    'lengthChange' : false,
    'ajax': {
        url : '/app/assetsManagment/assets/assetsServer',
        type : 'POST',
        data : function(d) {
            d.searchText = document.getElementById("assets-search-table").value;
            d.assetsCategory = document.getElementById("assets_category_filter").value;
            d.assetsStatus = document.getElementById("assets_status_filter").value;
            d.assetsUser = document.getElementById("assets_user_filter").value;
        }  
    },
    'columns': [{
            data: "assets_code",
            render : function(data,type,row) {
                return `<div class = "table_heading">${data}</div>`;
            }
        },{
            data: "brand_name" ,
            render : function(data,type,row) {
                return `<div>${data.toUpperCase()}</div>`;
            }
        },{
            data: "model_number", 
            render : function(data, type, row) {
                return `<div>${data}</div>`;
            }
        },{
            data: "assets_category_name",
            render : function(data, type, row) {
                let assets_logo = {
                    'laptop' : "fadeIn animated bx bx-laptop",
                    'mobile' : "fadeIn animated bx bx-mobile",
                    'device' : "fadeIn animated bx bx-devices"
                };
                let normalizedData = data.toLowerCase();
                let assets_icon = assets_logo[normalizedData] || assets_logo.device;
                return `<div style="display: flex; align-items: center; gap: 8px;"><i class="${assets_icon}" style="font-size: large;"></i><span>${data}</span></div>`;
            }
        },{
            data: "assets_assign_to", 
            render : function(data, type, row) {
                let button;
                if(data != 'Not Assign') {
                    let userInfo = JSON.parse(data);
                    let image = userInfo['image'];
                    let name = makeContent(userInfo['Name']);
                    let department = makeContent(userInfo['department_name']);
                    let designation = makeContent(userInfo['designation_name']);
                    button = `<button class="user-btn btn btn-sm bg-success" onmouseover="showUserBox(this)" onmouseout="hideUserBox(this)"> Assign
                    <div class="user-tooltip">
                    <img src="${image}" alt="User"/>
                    <div class="user-info">
                    <p class = "mb-1"><span style="font-weight:500;">Name : </span>${name}</p>
                    <p class = "mb-1"><span style="font-weight:500;">Designation : </span>${designation}</p>
                    <p class = "mb-1"><span style="font-weight:500;">Department : </span>${department}</p>
                    </div></div>
                    </button>`;
                } else {
                    button = `<button class = "btn btn-sm bg-info" style = "font-size: smaller;color:white;">${data}</button>`;
                }
                return button;
            },
            createdCell: function(td, cellData, rowData, rowIndex, colIndex) {
                $(td).addClass('assign-cell');
            }
            
        },{
            data: "assets_status_name", 
            render : function(data, type, row) {
                let badge_bg = {
                    'backup' : "badge bg-secondary",
                    'use' : "badge bg-success",
                    'repair' : "badge bg-warning",
                    'retired' : "badge bg-danger" 
                }
                let keys = Object.keys(badge_bg);
                let selectkey = keys.filter( word => data.toLowerCase().includes(word) ? true : false);
                let edit = '';
                if(selectkey[0] != 'use') {
                    edit = `<div class="font-14" onclick = "changeAssetsStatus(${row.ID})"><i class="fadeIn animated bx bx-edit"></i></div>`;
                }
                let badge_val = badge_bg[selectkey[0]];
                return `<div class = "d-flex gap-4"><div class = "${badge_val}" style = "font-size: smaller;font-weight: 400;min-width:80px;">${data}</div>${edit}</div>`;
            }
        },{
            data: "assets_description", 
            render : function(data, type, row) {
                return `<div class="text-info" style="font-size:x-large;" onclick="viewAssetsDescription('${data}')"><i class = "fadeIn animated bx bx-info-circle" data-bs-toggle="tooltip" title="View Description"></i></div>`;
            }
        },{
            data : "assets_history" , 
            render : function(data,type,row) {
                return `<div onclick = showAssetsHistory(${row.ID})><button class = "btn btn-sm bg-info" style="font-size:small;color:white !important;">view</button>`;
            }
        },{         
            data : "Action",
            render : function(data, type, row) {
                var edit = ''; var del = '';
                <?php if(in_array('Assets Update',$_SESSION['permission'])) { ?>
                    edit = `<div class="text-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit" onclick = "updateDetails(${row.ID},&#39;assets&#39;)"><i class="bi bi-pencil-fill"></i></div>`;
                <?php } else { ?>
                    edit = '<div data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit Disabled"><i class="bi bi-pencil-fill"></i></div>';
                <?php } ?>
                <?php if(in_array('Assets Delete',$_SESSION['permission'])) { ?>
                    del = `<div class="text-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete" onclick = "checkAssetDeleteCondition(${row.ID},&#39;assets&#39;)"><i class="bi bi-trash-fill"></i></div>`;
                <?php } else { ?>
                    del = '<div data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete Disabled"><i class="bi bi-trash-fill"></i></div>';
                <?php } ?>
                return `<div class = "table-actions d-flex align-items-center gap-3 fs-6">${edit+del}</div>`;
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

var assetsTrashSetting = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'POST',
    'searching' : false,
    'ajax': { 
        url : '/app/assetsManagment/assets/assetsServer',
        type : 'POST',
        data : function(d) {
            d.assetsType = "deleteAssets";
            d.searchText = document.getElementById("assets-search-table").value;
            d.assetsCategory = document.getElementById("assets_category_filter").value;
            d.assetsStatus = document.getElementById("assets_status_filter").value;
            d.assetsUser = document.getElementById("assets_user_filter").value;
        }
    },
    'columns': [{
            data: "assets_code",
            render : (data,type,row) => `<div class = "table_heading">${data}</div>`
        },{
            data: "brand_name" ,
            render : (data,type,row) => `<div>${data.toUpperCase()}</div>`
        },{
            data: "model_number", 
            render : (data,type,row) => `<div>${data}</div>`
        },{
            data: "assets_category_name",
            render : function(data, type, row) {
                let assets_logo = {
                    'laptop' : "fadeIn animated bx bx-laptop",
                    'mobile' : "fadeIn animated bx bx-mobile",
                    'device' : "fadeIn animated bx bx-devices"
                };
                let normalizedData = data.toLowerCase();
                let assets_icon = assets_logo[normalizedData] || assets_logo.device;
                return `<div style="display: flex; align-items: center; gap: 8px;"><i class="${assets_icon}" style="font-size: large;"></i><span>${data}</span></div>`;
            }
        },{
            data: "assets_assign_to", 
            render : function(data, type, row) {
                let button;
                if(data != 'Not Assign') {
                    let userInfo = JSON.parse(data);
                    let image = userInfo['image'];
                    let name = makeContent(userInfo['Name']);
                    let department = makeContent(userInfo['department_name']);
                    let designation = makeContent(userInfo['designation_name']);
                    button = `<button class="user-btn btn btn-sm bg-success" onmouseover="showUserBox(this)" onmouseout="hideUserBox(this)"> Assign
                    <div class="user-tooltip">
                    <img src="${image}" alt="User"/>
                    <div class="user-info">
                    <p class = "mb-1"><span style="font-weight:500;">Name : </span>${name}</p>
                    <p class = "mb-1"><span style="font-weight:500;">Designation : </span>${designation}</p>
                    <p class = "mb-1"><span style="font-weight:500;">Department : </span>${department}</p>
                    </div></div>
                    </button>`;
                } else {
                    button = `<button class = "btn btn-sm bg-info" style = "font-size: smaller;color:white;">${data}</button>`;
                }
                return button;
            }
        },{
            data: "assets_status_name", 
            render : function(data, type, row) {
                let badge_bg = {
                    'backup' : "badge bg-secondary",
                    'use' : "badge bg-success",
                    'repair' : "badge bg-warning",
                    'retired' : "badge bg-danger" 
                }
                let keys = Object.keys(badge_bg);
                let selectkey = keys.filter( word => data.toLowerCase().includes(word) ? true : false);
                let badge_val = badge_bg[selectkey[0]];
                return `<div class = "${badge_val}" style = "font-size: smaller;font-weight: 400;">${data}</div>`;
            }
        },{
            data: "assets_description", 
            render : function(data, type, row) {
                return `<div class="text-info" style="font-size:x-large;" onclick="viewAssetsDescription('${data}')"><i class = "fadeIn animated bx bx-info-circle" data-bs-toggle="tooltip" title="View Description"></i></div>`;
            }
        },{
            data : "assets_history" , 
            render : function(data,type,row) {
                return `<div onclick = showAssetsHistory(${row.ID})><button class = "btn btn-sm bg-info" style="font-size:small;color:white !important;">view</button>`;
            }
        },{         
            data : "Action",
            render : function(data, type, row) {
                var table = 'assets';
                let restore = '<div class="text-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Re-store" onclick = "restoreDetails('+row.ID+',&#39;'+table+'&#39;)"><i class="fadeIn animated bx bx-sync" style = "font-size:larger;"></i></div>';
                let del = '<div class="text-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Parmanent Delete" onclick = "parmanentDeleteDetails('+row.ID+',&#39;'+table+'&#39;)"><i class="bi bi-trash-fill"></i></div>'
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
    $("#assetsTable").DataTable(assetsSetting);
    getAllFilterData();
});

$("#trash_button").on('click',function(){
    $("#assetsTable").DataTable(assetsTrashSetting);
    $("#return_button").css('display','block');
    $("#trash_button").css('display','none');
});

$("#return_button").on('click',function(){
    $("#assetsTable").DataTable(assetsSetting);
    $("#return_button").css('display','none');
    $("#trash_button").css('display','block');
});

// Debouncing apply on search
var timer1;
document.getElementById("assets-search-table").addEventListener('input', (event) => {
    const searchText = event.target.value.toLowerCase();
    if(timer1) clearTimeout(timer1);
    timer1 = setTimeout(()=> {
        $("#assetsTable").DataTable(assetsSetting);
    }, 1000)
});

function reloadTable(value) {
    $("#assetsTable").DataTable(assetsSetting);
}

async function addAssets() {
    let url = `/app/assetsManagment/assets/insertAndUpdateAssets`;
    const data = await getMethod(url);
    if (data != null) {
        $('#md-modal-content').html(data);
        $('#mdmodal').modal('show');
    }
}

async function updateDetails(id) {
    let url = `/app/assetsManagment/assets/insertAndUpdateAssets`;
    const data = await postMethodWithTextResponse(url,{id});
    if (data != null) {
        $('#md-modal-content').html(data);
        $('#mdmodal').modal('show');
    }
}

async function changeAssetsStatus(assets_id) {
    let url = `/app/assetsManagment/assets/updateAssetsStatus`;
    const data = await postMethodWithTextResponse(url,{assets_id});
    if (data != null) {
        $('#sm-modal-content').html(data);
        $('#smmodal').modal('show');
    }
}

async function showAssetsHistory(id) { 
    let url = `/app/assetsManagment/assets/showAssetHistory`;
    const data = await postMethodWithTextResponse(url,{id});
    if (data != null) {
        $('#md-modal-content').html(data);
        $('#mdmodal').modal('show');
    }
}

async function viewAssetsDescription(description_body) {
    let url = `/app/assetsManagment/assets/assetsDescriptionView`;
    const data = await getMethod(url);
    if (data != null) {
        $('#md-modal-content').html(data);
        document.getElementById("description_body").innerHTML = description_body;
        document.getElementById("model_heading").innerHTML = "Assets Description";
        $('#mdmodal').modal('show');
    }
}

async function getAllFilterData() {
    let filterInputFiled = Array.from(document.getElementById("filter_container").querySelectorAll("select")).map((param) => param.id).join("@@@@"); 
    const inputFieldData = await postMethodWithJsonResponse("/app/assetsManagment/assets/fetchAndStoreAssets", {
        method : "getAllFilterData",
        inputField : filterInputFiled
    });
    if (inputFieldData != null) {
        for (const key in inputFieldData) {
            let dropDown = JSON.parse(inputFieldData[key]);
            let dropDownContainer = document.getElementById(key);
            dropDownContainer.append(document.createElement("option"));
            for (const id in dropDown) {
                let option = document.createElement("option");
                option.value = id;
                option.innerHTML = dropDown[id];
                dropDownContainer.append(option);
            }
            $('#'+key).select2({
                placeholder: 'Choose ' + (key.split("_"))[1],
                allowClear: true,
                width: '100%'
            });
        }
    }
}

async function checkAssetDeleteCondition(id,table_name) {
    let url = `/app/assetsManagment/assets/fetchAndStoreAssets`;
    const data = await postMethodWithJsonResponse(url,{
        id,
        method : "checkAssetsDeleteCondition" 
    });
    if (data != null) {
        if(data.status == 200) {
            if (data.message == "not_allow") {
                Swal.fire({
                    title : "Assets Delete Not Allow" , 
                    text: "Only Assets In Retired State Are Allow",
                    icon: 'warning',
                });
            } else {
                deleteDetails(id,table_name);
            }
        } else {
            toastr.error(data.message);
        }
    }
}

function showUserBox(el) {
    showTimer = setTimeout(() => {
        el.classList.add('show-tooltip');
    }, 50);
}

function hideUserBox(el) {
    hideTimer = setTimeout(() => {
        el.classList.remove('show-tooltip');
    }, 50);
}
</script>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer-bottom.php');?>  