<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-top.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-bottom.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/topbar.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/menu.php');?>
<style>
.table_heading {
    font-size: 14px;
    font-weight: 500;
}
.hide{
    display: none !important;
}
.show {
    display: block !important;
}
.text-uppercase {
    text-transform: uppercase;
}
</style>
<main class="page-content">
    <div class="row gx-3">
        <div class="col-sm-8">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="mb-0">Assets Category</h6>
                        <div class="d-flex justify-content-end gap-2 col-sm-6">
                            <?php if(in_array('Category&Status Delete',$_SESSION['permission'])) { ?>
                            <button class="btn btn-outline-danger btn-sm d-flex align-items-center" id="trash_button_category" data-bs-toggle="tooltip" title="Go to Trash" onclick="trashSection(this)">
                                <i class="bi bi-trash-fill me-1" id="trash_category"></i>
                            </button>
                            <button class="btn btn-primary" style="font-size: small;" id ="return_button_category" onclick="returnSection(this)">Go To Category</button>
                            <?php } ?>
                            <?php if(in_array('Category&Status Create',$_SESSION['permission'])) { ?>    
                            <div class="d-flex align-items-center theme-icons shadow-sm p-2 cursor-pointer rounded" title="Add Category"  onclick="addAssetsCategoryOrStatus(event)" data-bs-toggle="tooltip">
                                <i class="bi bi-plus-circle-fill" id="add_category"></i>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                    <!-- Add category content here -->
                    <div class="table-responsive mt-3">
                        <table class="table align-middle w-100" id="assetsCategoryTable" style="color: #515B73!important;">
                            <thead class="table-primary">
                                <tr class="table_heading">
                                    <td>Sr No</td>
                                    <td>Category Name</td>
                                    <td>Category Prefix</td>
                                    <td>Actions</td>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="mb-0">Assets Status</h6>
                        <div class="d-flex justify-content-end gap-2 col-sm-6">
                            <?php if(in_array('Category&Status Delete',$_SESSION['permission'])) { ?>
                            <button class="btn btn-outline-danger btn-sm d-flex align-items-center" id="trash_button_status" data-bs-toggle="tooltip" title="Go to Trash" onclick="trashSection(this)">
                                <i class="bi bi-trash-fill me-1"></i>
                            </button>
                            <button class="btn btn-primary" style="font-size:small;" id ="return_button_status" onclick="returnSection(this)">Go To Status</button>
                            <?php } ?>
                            <?php if(in_array('Category&Status Create',$_SESSION['permission'])) { ?>
                            <div class="d-flex align-items-center theme-icons shadow-sm p-2 cursor-pointer rounded" title="Add Status" onclick="addAssetsCategoryOrStatus(event)" data-bs-toggle="tooltip">
                                <i class="bi bi-plus-circle-fill" id="add_status"></i>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                    <!-- Add status content here -->
                    <div class="table-responsive mt-3">
                        <table class="table align-middle w-100" id="assetsStatusTable" style="color: #515B73!important;">
                            <thead class="table-primary">
                                <tr class="table_heading">
                                    <td>Sr No</td>
                                    <td>Status Name</td>
                                    <td>Actions</td>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer-top.php');?>
<script src="../assets/js/helper.js"></script>
<script type="text/javascript"> 

var assetsCategorySetting = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    'ajax': {
        url : '/app/assetsManagment/assetsCategory/assetsCategoryServer',
        type : 'POST',
    },
    'columns': [{
            data: "slno",
        },{
            data: "category_name" ,
            render : function(data,type,row) {
                return `<div>${data}</div>`;
            }
        },{
            data: "category_prefix", 
            render : function(data, type, row) {
                return `<div>${data}</div>`;
            }
        },{         
            data : "Action",
            render : function(data, type, row) {
                var edit = ''; var del = '';
                <?php if(in_array('Category&Status Update',$_SESSION['permission'])) { ?>
                    edit = `<div class="text-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit" onclick = "updateDetails(${row.ID},&#39;assets_category&#39;)"><i class="bi bi-pencil-fill"></i></div>`;
                <?php } else { ?>
                    edit = '<div data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit Disabled"><i class="bi bi-pencil-fill"></i></div>';
                <?php } ?>
                <?php if(in_array('Category&Status Delete',$_SESSION['permission'])) { ?>
                    del = `<div class="text-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete" onclick = "deleteDetails(${row.ID},&#39;assets_category&#39;)"><i class="bi bi-trash-fill"></i></div>`;
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

var assetsCategoryTrashSetting = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    'ajax': {
        url : '/app/assetsManagment/assetsCategory/assetsCategoryServer',
        type : 'POST',
        data : function(d) {
            d.assetsType = "deleteAssets"
        }
    },
    'columns': [{
            data: "slno",
        },{
            data: "category_name" ,
            render : function(data,type,row) {
                return `<div>${data}</div>`;
            }
        },{
            data: "category_prefix", 
            render : function(data, type, row) {
                return `<div>${data}</div>`;
            }
        },{         
            data : "Action",
            render : function(data, type, row) {
                var table = 'assets_category';
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

var assetsStatusSetting = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    'ajax': {
        url : '/app/assetsManagment/assetsStatus/assetsStatusServer', 
        type : 'POST',
    },
    'columns': [{
            data: "slno",
        },{
            data: "status_name" ,
            render : function(data,type,row) {
                return `<div>${data}</div>`;
            }
        },{         
            data : "Action",
            render : function(data, type, row) {
                var edit = ''; var del = '';
                <?php if(in_array('Category&Status Update',$_SESSION['permission'])) { ?>
                    edit = `<div class="text-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit" onclick = "updateDetails(${row.ID},&#39;assets_status&#39;)"><i class="bi bi-pencil-fill"></i></div>`;
                <?php } else { ?>
                    edit = '<div data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit Disabled"><i class="bi bi-pencil-fill"></i></div>';
                <?php } ?>
                <?php if(in_array('Category&Status Delete',$_SESSION['permission'])) { ?>
                    del = `<div class="text-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete" onclick = "deleteDetails(${row.ID},&#39;assets_status&#39;)"><i class="bi bi-trash-fill"></i></div>`;
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

var assetsStatusTrashSetting = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    'ajax': {
        url : '/app/assetsManagment/assetsStatus/assetsStatusServer', 
        type : 'POST',
        data : function(d) {
            d.assetsType = "deleteAssets"
        }
    },
    'columns': [{
            data: "slno",
        },{
            data: "status_name" ,
            render : function(data,type,row) {
                return `<div>${data}</div>`;
            }
        },{         
            data : "Action",
            render : function(data, type, row) {
                var table = 'assets_status';
                let restore = '<div class="text-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Re-store" onclick = "restoreDetails('+row.ID+',&#39;'+table+'&#39;)"><i class="fadeIn animated bx bx-sync" style = "font-size:larger;"></i></div>';
                let del = '<div class="text-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Parmanent Delete" onclick = "parmanentDeleteDetails('+row.ID+',&#39;'+table+'&#39;)"><i class="bi bi-trash-fill"></i></div>';
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

document.addEventListener("DOMContentLoaded" , () => {
    $("#assetsCategoryTable").DataTable(assetsCategorySetting);
    $("#assetsStatusTable").DataTable(assetsStatusSetting);
    document.getElementById("return_button_category").classList.add("hide");
    document.getElementById("return_button_status").classList.add("hide");
})

function returnSection(element) {
    let card = (element.id.split("_"))[2];
    let sectionName = upperFirstLetter(card);
    let table_id = `assets${sectionName}Table`;
    let dataTableVar = `assets${sectionName}Setting`;
    $("#"+table_id).DataTable(window[dataTableVar]);
    addAndRemove(document.getElementById("return_button_"+card),document.getElementById("trash_button_"+card));
}

function trashSection(element) {
    let card = (element.id.split("_"))[2];
    console.log(card);
    let sectionName = upperFirstLetter(card);
    let table_id = `assets${sectionName}Table`;
    let dataTableVar = `assets${sectionName}TrashSetting`;
    $("#"+table_id).DataTable(window[dataTableVar]);
    addAndRemove(document.getElementById("trash_button_"+card),document.getElementById("return_button_"+card));
}

function addAndRemove(hideSection,showSection) {
    hideSection.classList.remove("show");
    hideSection.classList.add("hide");

    showSection.classList.remove("hide");
    showSection.classList.add("show");
}

const upperFirstLetter = (word) => word.charAt(0).toUpperCase() + word.slice(1,word.length);

async function addAssetsCategoryOrStatus(event) {
    let add_type = (event.target.id.split("_"))[1];
    add_type = add_type.charAt(0).toUpperCase() + add_type.slice(1,add_type.length);
    let url = `/app/assetsManagment/assets${add_type}/insertAndUpdateAssets${add_type}`;
    const data = await getMethod(url);
    if (data != null) {
        $('#md-modal-content').html(data);
        $('#mdmodal').modal('show');
    }
} 

async function updateDetails(id,table_name) {
    let update_type = (table_name.split("_")[1]);
    update_type = update_type.charAt(0).toUpperCase() + update_type.slice(1,update_type.length);
    let baseUrl = `/app/assetsManagment/assets${update_type}/insertAndUpdateAssets${update_type}`;
    const data = await postMethodWithTextResponse(baseUrl, {id});
    if (data != null) {
        $('#md-modal-content').html(data);
        $('#mdmodal').modal('show');
    }
}

</script>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer-bottom.php');?>  