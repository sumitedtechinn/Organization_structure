<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-top.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-bottom.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/topbar.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/menu.php');?>

<?php 

$optionTagForMonth = monthDropdown();
function monthDropdown() {
    $months_arr = ['1'=>'January','2'=>'February','3'=>'March','4'=>'April','5'=>'May','6'=>'June','7'=>'July','8'=>'August','9'=>'September','10'=>'October','11'=>'November','12'=>'December','13' => 'All Month'];
    $option = '<option value = "">Select</option>';
    $current_month = date("n");
    foreach($months_arr as $key=>$value) {
        if ($key == $current_month) {
            $option .= '<option value = "'.$key.'" selected>'.$value.'</option>';
        } else {
            $option .= '<option value = "'.$key.'">'.$value.'</option>';
        }
    }
    return $option;
}


$optionTagForYear = getYear($projection_details);

function getYear($projection_details) {
    $year = date("Y");
    $option = '<option value ="">Select Year</option>'; 
    $i = 0;
    while($i < 2) {
        if($i == 0) {
            $option .= "<option value='$year' selected >".$year."</option>";
        } else {
            $option .= "<option value='$year'>".$year."</option>";
        }
        $year++;$i++;
    }
    return $option;
}

$col_size = ($_SESSION['role'] == '3') ? "col-sm-3" : "col-sm-2";
$gap = ($_SESSION['role'] == '1') ? "gap-1" : "gap-1";
?>

<style>
    .error {
        color: red;
        font-size: small;
    }
    #organization_filter,#branch_filter,#vertical_filter,#department_filter,#projectionType_filter,#user_filter{
        background-color: #f2f2f2 !important;
    }

    .page-content {
        margin-top: 55px !important;
        padding: 1rem 1rem 2rem 1rem;
    }

    #projection_summary {
        padding-right: 1%;
        gap: 1.2rem ;
        margin-right: 0.6rem;
    }

    .textStyle{
        font-size: small;
        font-weight: 580;
        color: #353434;
    }

    .hide {
        display: none !important;
    }

    .show {
        display: block !important;
    }

</style>


<!--start content-->
<main class="page-content">
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Projection Table</h5>
                <div class="d-flex justify-content-end gap-2 col-sm-2">
                    <div class="col-sm-7 mt-1" style="z-index: 0 !important;">
                        <button type="button" class="btn btn-dark btn-sm" onclick="showAndHideFilter()">Advance Filter<i class="bi bi-funnel"></i></button>
                    </div>    
                    <div class="col-sm-6" style="z-index: 0 !important;">
                        <select type="text" class="form-control month_container form-control-sm single-select select2" name="month_filter" id="month_filter" onchange="reloadTable(this.id)">
                            <?=$optionTagForMonth?>   
                        </select>
                    </div>
                    <div class="col-sm-6" style="z-index: 0 !important;">
                        <select type="text" class="form-control month_container form-control-sm single-select select2" name="year_filter" id="year_filter" onchange="reloadTable(this.id)">
                            <?=$optionTagForYear?>   
                        </select>
                    </div>
                    <div class="d-flex align-items-center theme-icons shadow-sm p-2 cursor-pointer rounded" title="Add" onclick="addProjection()" data-bs-toggle="tooltip">
                        <i class="bi bi-plus-circle-fill" id="add_projection"></i>
                    </div>
                </div>
            </div>
            <div class="hide" id = "advance_filter">
                <div class="d-flex align-items-center justify-content-start <?=$gap?> mt-2 mb-2" style="padding-right: 1%;">
                    <?php if($_SESSION['role'] == '1' || $_SESSION['role'] == '3') { 
                        if($_SESSION['role'] != '3') {
                    ?>
                    <div class="col-sm-2 card bg-light p-1 mb-1" style="z-index: 0 !important;">
                        <label class="col-form-label" style="font-size: small;">Organization</label>
                        <select type="text" class="form-control form-control-sm single-select select2" name="organization_filter" id="organization_filter" onchange="reloadTable(this.id)">
                        </select>
                    </div>
                    <?php } ?>
                    <div class="col-sm-2 card bg-light p-1 mb-1" style="z-index: 0 !important;">
                        <label class="col-form-label" style="font-size: small;">Branch</label>
                        <select type="text" class="form-control form-control-sm single-select select2" name="branch_filter" id="branch_filter" onchange="reloadTable(this.id)">
                        </select>
                    </div>
                    <div class="col-sm-2 card bg-light p-1 mb-1" style="z-index: 0 !important;">
                        <label class="col-form-label" style="font-size: small;">Vertical</label>
                        <select type="text" class="form-control form-control-sm single-select select2" name="vertical_filter" id="vertical_filter" onchange="reloadTable(this.id)">
                        </select>
                    </div>
                    <div class="<?=$col_size?> card bg-light p-1 mb-1" style="z-index: 0 !important;">
                        <label class="col-form-label" style="font-size: small;">Department</label>
                        <select type="text" class="form-control form-control-sm single-select select2" name="department_filter" id="department_filter" onchange="reloadTable(this.id)">
                        </select>
                    </div>
                    <?php } ?>
                    <div class="<?=$col_size?> card bg-light p-1 mb-1" style="z-index: 0 !important;">
                        <label class="col-form-label" style="font-size: small;">Projection Type</label>
                        <select type="text" class="form-control form-control-sm single-select select2" name="projectionType_filter" id="projectionType_filter" onchange="reloadTable(this.id)">
                        </select>
                    </div>
                    <div class="col-sm-2 card bg-light p-1 mb-1" style="z-index: 0 !important;">
                        <label class="col-form-label" style="font-size: small;">User</label>
                        <select type="text" class="form-control form-control-sm single-select select2" name="user_filter" id="user_filter" onchange="reloadTable(this.id)">
                        </select>
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-start" id = "projection_summary" style="margin-top: 0.6rem;">
                <div class="col-sm-4 card bg-light mb-1" id="organization_info" style="padding: 1.2rem;"></div>
                <div class="col-sm-8">
                    <div class="row" style="margin-bottom: 0.45rem;" id = "center_projection">
                        <div class="col-sm-3 card bg-light p-1 mb-1" id = "center_total_projection">
                            <div class="textStyle text-center">Total Center Projection</div>
                            <div class="text-center mt-3 textStyle" id="center_total_projection_number"></div>
                        </div>
                        <div class="col-sm-3 card bg-light p-1 mb-1" id = "center_projection_completed">
                            <div class="textStyle text-center">Completed Center</div>
                            <div class="text-center mt-3 textStyle" id="center_completed_projection_number"></div>
                        </div>
                        <div class="col-sm-2 card bg-light p-1 mb-1" id = "center_projection_pending">
                            <div class="textStyle text-center">Pending Center</div>
                            <div class="text-center mt-3 textStyle" id="center_pending_projection_number"></div>
                        </div>
                        <div class="col-sm-4">
                            <div class="row">
                                <div class="col-sm-6 card bg-light p-1 mb-1" id = "center_projection_view">
                                    <div class="textStyle text-center">Center Projection</div>
                                    <div class="text-center mt-2 textStyle" id="pending_projection_number">
                                        <button type="button" class="btn btn-outline-info btn-sm px-2" style="font-size:small" onclick="viewAllMonthlyClosureDetails('center')">View All</button>
                                    </div>
                                </div>
                                <div class="col-sm-6 card bg-light p-1 mb-1" id = "center_deposit_view">
                                    <div class="textStyle text-center">Center Deposits</div>
                                    <div class="text-center mt-2 textStyle" id="center_deposit_button">
                                        <button type="button" class="btn btn-outline-info btn-sm px-2" style="font-size:small" onclick="viewAllMonthlyCenterDeposit('center')">View All</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row" id = "admission_projection">
                        <div class="col-sm-3 card bg-light p-1 mb-1" id = "admission_total_projection">
                            <div class="textStyle text-center">Total Admission Projection</div>
                            <div class="text-center mt-3 textStyle" id="admission_total_projection_number"></div>
                        </div>
                        <div class="col-sm-3 card bg-light p-1 mb-1" id = "admission_projection_completed">
                            <div class="textStyle text-center">Completed Admission</div>
                            <div class="text-center mt-3 textStyle" id="admission_completed_projection_number"></div>
                        </div>
                        <div class="col-sm-3 card bg-light p-1 mb-1" id = "admission_projection_pending">
                            <div class="textStyle text-center">Remaining Admission</div>
                            <div class="text-center mt-3 textStyle" id="admission_pending_projection_number"></div>
                        </div>
                        <div class="col-sm-3 card bg-light p-1 mb-1"  id = "admission_projection_view">
                            <div class="textStyle text-center">Admission Projection</div>
                            <div class="text-center mt-2 textStyle" id="pending_projection_number">
                                <button type="button" class="btn btn-outline-info btn-sm px-2" style="font-size:small" onclick="viewAllMonthlyAdmissionDetails('admission')">View All</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-responsive mt-2">
                <table class="table table-striped align-middle" id="projectionTable">
                    <thead class="table-secondary">
                        <tr>
                            <th>Image</th>
                            <th>User</th>
                            <th>Projection Type</th>                            
                            <th>Organization Info</th>
                            <th>Month/Year</th>
                            <th>Closure Pending</th>
                            <th>Closure Complete</th>
                            <th>View Closure</th>
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

var projectionSettings = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    "searching": false ,
    'ajax': {
        'url': '/app/addProjection/addProjection_server',
        'type': 'POST',
        'data' : function (d) {
            d.organizationFilter = $("#organization_filter").val();
            d.branchFilter = $("#branch_filter").val();
            d.verticalFilter = $("#vertical_filter").val();
            d.departmentFilter = $("#department_filter").val();
            d.selected_month = $('#month_filter').val(); 
            d.selected_user = $("#user_filter").val();
            d.selected_year = $("#year_filter").val();
            d.selected_projection_type = $("#projectionType_filter").val();
        }
    },
    'columns': [{
            data: "user_image",
            render : function(data,type,row) {
                var img = '<div class="d-flex align-items-center gap-3 cursor-pointer"><img src="'+data+'" class="rounded-circle" width="44" height="44" alt=""></div>';
                return img;
            }
        },{
            data: "user" ,
            render : function(data,type,row) {
                var name = (row.user_delete == 'Yes') ? '<span class="text-danger">'+data+'</span>' : '<span>'+data+'</span>';
                var designation = (row.designation_delete == 'Yes') ? '<span class="text-danger">'+row.designation+'</span>' : '<span>'+row.designation+'</span>';
                return  '<div style="font-size:small;"><p class = "mb-1"><b>Name : </b>'+name+'</p><p class = "mb-1 text-wrap" style = "width:250px;" ><b>Designation : </b>'+designation+'</p></div>';
            }
        },{
            data: "projection_type" ,
            render : function(data,type,row) {
                var projectionType = (row.projection_type_delete == 'Yes') ? '<div class="text-danger">'+data+'</div>' : '<div>'+data+'</div>';
                return projectionType;
            } 
        },{
            data: "Organization Info" ,
            render : function(data,type,row) {
                var organization = (row.organization_delete == 'Yes') ? '<span class = "text-danger">'+row.organization+'</span>' : '<span>'+row.organization+'</span>'; 
                var branch = (row.branch_delete == 'Yes') ? '<span class="text-danger">'+row.branch+'</span>' : '<span>'+row.branch+'</span>';
                var vertical = (row.vertical_delete == 'Yes') ? '<span class="text-danger">'+row.vertical+'</span>' : '<span>'+row.vertical+'</span>';
                var department = (row.department == 'Yes') ? '<span class="text-danger">'+row.department+'</span>' : '<span>'+row.department+'</span>';
                return '<div style="font-size:small;"><p class = "mb-1"><b>Organization : </b>'+organization+'</p><p class = "mb-1"><b>Branch : </b>'+branch+'</p><p class = "mb-1"><b>Vertical : </b>'+vertical+'</p><p class = "mb-1"><b>Department : </b>'+department+'</p></div>';
            }
        },{
            data : "month",
            render : function(data,type,row) {
                return '<div class="badge rounded-pill bg-info m-1" style="background-color:#1a2232 !important;font-weight:200!important;font-size:13px;">'+data+'/'+row.year+'</div>';
            }
        },{
            data : "numOfClosurePending", 
        },{
            data : "numOfClosure" ,
            render : function(data,type,row) {
                return '<div>'+row.numOfClosureComplete+'/<b>'+data+'</b></div>';
            }
        },{
            data : "View Clouser" , 
            render : function(data,type,row) {
                var projection_type_name = (row.projection_type.split(" "))[1];
                projection_type_name = projection_type_name.toLowerCase();
                var view = '<button type="button" class="btn btn-outline-info px-4" style="font-size:small" onclick = "viewClosureDetailsProjectionBias('+row.ID+',&#39;'+projection_type_name+'&#39;)">View </button>';
                return view;
            }
        },{         
            data : "Action" ,
            render : function(data, type, row) {
                var edit = ''; var del = '';
                if ( row.user_id != <?=$_SESSION['ID']?> ) {
                    edit = '<div class="text-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit" onclick = "updateProjectionDetails('+row.ID+')"><i class="bi bi-pencil-fill"></i></div>';            
                }
                return '<div class = "table-actions d-flex align-items-center gap-3 fs-6">' +  edit+del + '</div>';
            }
        }
    ],
    "dom": '<"row"<"col-sm-12 col-md-6 d-flex justify-content-start"><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>><"table-responsive"t><"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
    "destroy": true,
    "scrollCollapse": true,
    drawCallback: function(settings, json) {
        $('[data-toggle="tooltip"]').tooltip({
            template: '<div class="tooltip custom-tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'

        });
    },
    "aaSorting": []
};

$(document).ready(function(){
    $('#projectionTable').DataTable(projectionSettings);
    getFilterData();
});

function makeOrganizationInfoAndProjectionData() {
    let info_data = {organization : "" , branch : "" , vertical : "" , department : ""};
    <?php if($_SESSION['role'] == '2') { ?>
        info_data.organization = '<?=$_SESSION['organization_name']?>';
        info_data.branch = '<?=$_SESSION['branch_name']?>';
        info_data.vertical = '<?=$_SESSION['vertical_name']?>';
        info_data.department = '<?=$_SESSION['department_name']?>';
    <?php } elseif ($_SESSION['role'] == '3') { ?>
        info_data.organization = '<?=$_SESSION['organization_name']?>';
        var filter_arr = ['branch','vertical','department'];
        for (const value of filter_arr) {
            if($('#'+value+'_filter option').length > 0 && $('#'+value+'_filter').val().length > 0) {
                let selected_id = $('#'+value+'_filter').val();
                info_data[value] = $('#'+value+'_filter option[value="'+selected_id+'"]').text();
            } else {
                info_data[value] = "All";
            }
        }    
    <?php } else {?>
        var filter_arr = ['organization','branch','vertical','department'];
        for (const value of filter_arr) {
            if($('#'+value+'_filter option').length > 0 && $('#'+value+'_filter').val().length > 0) {
                let selected_id = $('#'+value+'_filter').val();
                info_data[value] = $('#'+value+'_filter option[value="'+selected_id+'"]').text();
            } else {
                info_data[value] = "All";
            }
        }
    <?php } ?>
    var info = '<div style="font-size:small;"><p class = "mb-2"><b>Organization : </b>'+info_data.organization+'</p><p class = "mb-2"><b>Branch : </b>'+info_data.branch+'</p><p class = "mb-2"><b>Vertical : </b>'+info_data.vertical+'</p><p class = "mb-2"><b>Department : </b>'+info_data.department+'</p></div>';
    $("#organization_info").html(info);
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
                filter_arr = ['vertical','department','projectionType','user'];
                for (const key in filter_arr) {
                    if($('#'+filter_arr[key]+'_filter option').length > 0) {
                        triggerChange(filter_arr[key]+'_filter');
                    }
                }
                reloadTableAndOrganizationInfo();
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
                filter_arr = ['department','projectionType','user'];
                for (const key in filter_arr) {
                    if($('#'+filter_arr[key]+'_filter option').length > 0) {
                        triggerChange(filter_arr[key]+'_filter');
                    }
                }
                reloadTableAndOrganizationInfo();
            }   
        });  
    } else if(id == 'vertical_filter' && $("#vertical_filter").val().length > 0 ){
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
                $("#department_filter").html(updateOptionTag(data));
                $("#department_filter").trigger('change');
                filter_arr = ['projectionType','user'];
                for (const key in filter_arr) {
                    if($('#'+filter_arr[key]+'_filter option').length > 0) {
                        triggerChange(filter_arr[key]+'_filter');
                    }
                }
                reloadTableAndOrganizationInfo();
            }   
        })  
    } else if( id == 'department_filter' && $("#department_filter").val().length > 0) {
        var organization_id = '';
        <?php if ($_SESSION['role'] == '3') { ?>
            organization_id = '<?=$_SESSION['Organization_id']?>';
        <?php } else { ?>
            organization_id = $("#organization_filter").val();
        <?php } ?>
        var branch_id = $("#branch_filter").val();
        var vertical_id = $("#vertical_filter").val();
        var department_id = $("#department_filter").val();
        $.ajax({
            url : "/app/common/projectionTypeList",
            type : "post", 
            data: {
                organization_id,
                branch_id,
                vertical_id,
                department_id
            },  
            success : function(data) {
                $("#projectionType_filter").html(data);
                reloadTableAndOrganizationInfo();
            }
        });
        $.ajax({
            url : "/app/common/departmentBieasUserList",
            type : "post", 
            data: {
                organization_id,
                branch_id,
                vertical_id,
                department_id
            },  
            success : function(data) {
                $("#user_filter").html(data);
                reloadTableAndOrganizationInfo();
            }
        });
    } else {
        reloadTableAndOrganizationInfo();
    }
}

function reloadTableAndOrganizationInfo() {
    $('.table').DataTable().ajax.reload(null, false);
    makeOrganizationInfoAndProjectionData();
    showMonthlyClosureDetails();
}

function triggerChange(id) {
    isUpdating = true;
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
                makeOrganizationInfoAndProjectionData();
                showMonthlyClosureDetails();
            }   
        });
    <?php }  elseif ($_SESSION['role'] == '3') { ?>
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
                makeOrganizationInfoAndProjectionData();
                showMonthlyClosureDetails();
            }   
        });
    <?php }  elseif ($_SESSION['role'] == '2') { ?>
        var organization_id = '<?=$_SESSION['Organization_id']?>';
        var branch_id = '<?=$_SESSION['Branch_id']?>';
        var vertical_id = '<?=$_SESSION['Vertical_id']?>';
        var department_id = '<?=$_SESSION['Department_id']?>';
        $.ajax({
            url : "/app/common/projectionTypeList",
            type : "post", 
            data: {
                organization_id,
                branch_id,
                vertical_id,
                department_id
            },  
            success : function(data) {
                $("#projectionType_filter").html(data);
                makeOrganizationInfoAndProjectionData();
                showMonthlyClosureDetails();
            }
        });
        $.ajax({
            url : "/app/common/departmentBieasUserList",
            type : "post", 
            data: {
                organization_id,
                branch_id,
                vertical_id,
                department_id
            },  
            success : function(data) {
                $("#user_filter").html(data);
                makeOrganizationInfoAndProjectionData();
                showMonthlyClosureDetails();
            }
        }); 
    <?php } ?>
}

function addProjection() {
    $.ajax({
        url: "/app/addProjection/insertAndupdateProjection",
        type: 'get',
        success: function(data) {
            $('#lg-modal-content').html(data);
            $('#lgmodal').modal('show');
        }
    });
}

function updateProjectionDetails(projection_id) {
    $.ajax({
        url: "/app/addProjection/insertAndupdateProjection",
        type: 'post',
        data : {
            projection_id
        },
        success: function(data) {
            $('#lg-modal-content').html(data);
            $('#lgmodal').modal('show');
        }
    });
}

function viewClosureDetailsProjectionBias(projection_id,type) {
    if(type == 'center') {
        $.ajax({
            url: "/app/addProjection/viewClosureDetails",
            type: 'post',
            data : {
                projection_id, 
            },
            success: function(data) {
                $('#lg-modal-content-viewtable').html(data);
                $('#view-lgmodal').modal('show');
            }
        });
    } else if (type == 'admission') {
        $.ajax({
            url: "/app/addProjection/viewAdmissionDetails",
            type: 'post',
            data : {
                projection_id, 
            },
            success: function(data) {
                $('#lg-modal-content-viewtable').html(data);
                $('#view-lgmodal').modal('show');
            }
        });
    }
} 

function viewAllMonthlyClosureDetails(type) {
    let info_data = {organization : "" , branch : "" , vertical : "" , department : "" , projectionType : "" , user : "" , month : "" , year : "", selected_projectiontype : type};
    var filter_arr = ['organization','branch','vertical','department','projectionType','user','month','year'];
    for (const value of filter_arr) {
        if($('#'+value+'_filter option').length > 0 && $('#'+value+'_filter').val().length > 0) {
            info_data[value] = $('#'+value+'_filter').val();
        } else {
            info_data[value] = "None";
        }
    }
    $.ajax({
        url: "/app/addProjection/viewAllMonthlyClosureDetails",
        type: 'post',
        data : info_data,
        success: function(data) {
            $('#lg-modal-content-viewtable').html(data);
            $('#view-lgmodal').modal('show');
        }
    });
}

function viewAllMonthlyCenterDeposit(type) {
    let info_data = {organization : "" , branch : "" , vertical : "" , department : "" , projectionType : "" , user : "" , month : "" , year : "", selected_projectiontype : type};
    var filter_arr = ['organization','branch','vertical','department','projectionType','user','month','year'];
    for (const value of filter_arr) {
        if($('#'+value+'_filter option').length > 0 && $('#'+value+'_filter').val().length > 0) {
            info_data[value] = $('#'+value+'_filter').val();
        } else {
            info_data[value] = "None";
        }
    }
    $.ajax({
        url: "/app/addProjection/getCenterDepositIds",
        type: 'post',
        data : info_data,
        dataType: 'json',
        success: function(data) {
            let center_deposit_id = '';
            if(data.status == '200') {
                center_deposit_id = data.center_deposit;  
            } 
            seeCenterDepositDetails(center_deposit_id);
        }
    });
}

function seeCenterDepositDetails(center_deposit_ids) {
    $.ajax({
        url: "/app/dailyReporting/viewCenterDepositDetails",
        type: 'post',
        data: {
            center_deposit_ids,
            'type' : 'monthly'
        },
        success: function(data) {
            $('#lg-modal-content').html(data);
            $('#lgmodal').modal('show');
        }
    });
}

function viewAllMonthlyAdmissionDetails(type) {
    let info_data = {organization : "" , branch : "" , vertical : "" , department : "" , projectionType : "" , user : "" , month : "" , year : "", selected_projectiontype : type};
    var filter_arr = ['organization','branch','vertical','department','projectionType','user','month','year'];
    for (const value of filter_arr) {
        if($('#'+value+'_filter option').length > 0 && $('#'+value+'_filter').val().length > 0) {
            info_data[value] = $('#'+value+'_filter').val();
        } else {
            info_data[value] = "None";
        }
    }
    $.ajax({
        url: "/app/addProjection/viewAllMonthlyAdmissionDetails",
        type: 'post',
        data : info_data,
        success: function(data) {
            $('#lg-modal-content-viewtable').html(data);
            $('#view-lgmodal').modal('show');
        }
    });
}

function showMonthlyClosureDetails() {
    let info_data = {organization : "" , branch : "" , vertical : "" , department : "" , projectionType : "" , user : "" , month : "" , year : ""};
    var filter_arr = ['organization','branch','vertical','department','projectionType','user','month','year'];
    for (const value of filter_arr) {
        if($('#'+value+'_filter option').length > 0 && $('#'+value+'_filter').val().length > 0) {
            info_data[value] = $('#'+value+'_filter').val();
        } else {
            info_data[value] = "None";
        }
    }
    $.ajax({
        url: "/app/addProjection/countMonthlyClosureOnFilter",
        type: 'post',
        data : info_data,
        dataType : "json",
        success: function(data) {
            for (const key in data) {
                $("#"+key).text(data[key]);
            }
        }
    });
}

function showAndHideFilter() {
    if ($('#advance_filter').hasClass('hide')) {
        $('#advance_filter').removeClass('hide').addClass('show'); // Show the filter
        $("#projection_summary").css('margin-top', '0rem');
    } else {
        $('#advance_filter').removeClass('show').addClass('hide'); // Hide the filter
        $("#projection_summary").css('margin-top', '0.6rem');
    }
}

</script>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer-bottom.php');?>