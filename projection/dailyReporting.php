<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-top.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-bottom.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/topbar.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/menu.php'); ?>

<?php 
$col_size = ($_SESSION['role'] == '3') ? "col-sm-3" : "col-sm-2";
$gap = ($_SESSION['role'] == '1') ? "gap-1" : "gap-1";
?>

<!--start content-->
<main class="page-content">
    <div class="row justify-content-center">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="mb-1">Daily Reporting</h5>
                    <div class="d-flex justify-content-end gap-2 col-sm-2">
                        <?php if ($_SESSION['role'] == '2') { ?>
                            <div class="d-flex align-items-center theme-icons shadow-sm p-2 cursor-pointer rounded" title="Add" onclick="addDailyReport()" data-bs-toggle="tooltip">
                                <i class="bi bi-plus-circle-fill" id="add_daily_report"></i>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-start <?=$gap?> mt-2" style="padding-right: 1%;">
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
                    <div class="col-sm-2 card bg-light p-1 mb-1" style="z-index: 0 !important;">
                        <label class="col-form-label" style="font-size: small;">User</label>
                        <select type="text" class="form-control form-control-sm single-select select2" name="user_filter" id="user_filter" onchange="reloadTable(this.id)">
                        </select>
                    </div>
                    <div class="col-sm-2 card bg-light p-1 mb-1" style="z-index: 0 !important;">
                        <label class="col-form-label" style="font-size: small;">Date Range</label>   
                        <input type="text" name="daterange" id="daterange" class="form-control" onchange="reloadTable(this.id)"/>
                    </div>
                </div>
                <div class="table-responsive mt-3">
                    <table class="table table-striped align-middle" id="dailyReportTable">
                        <thead class="table-secondary">
                            <tr>
                                <th>Image</th>
                                <th>User</th>
                                <th>Report Date</th>
                                <th>Total Call</th>
                                <th>New Call</th>
                                <th>No. Meetings</th>
                                <th>Doc Prepare</th>
                                <th>Doc Received</th>
                                <th>Deal Closed</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>
<!--end page main-->

<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-top.php'); ?>
<script src="/assets/plugins/select2/js/select2.min.js"></script>
<script src="/assets/js/form-select2.js"></script>
<script type="text/javascript">
var dailyReportSettings = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    'ajax': {
        'url': '/app/dailyReporting/dailyReport-server',
        'type': 'POST',
        'data' : function (d) {
            d.organizationFilter = $("#organization_filter").val();
            d.branchFilter = $("#branch_filter").val();
            d.verticalFilter = $("#vertical_filter").val();
            d.departmentFilter = $("#department_filter").val(); 
            d.selected_user = $("#user_filter").val();
            d.selected_date = $("#daterange").val();
        }
    },
    'columns': [{
        data: "user_image",
        render: function(data, type, row) {
            if (data != null) {
                var image = '<div class="d-flex align-items-center gap-3 cursor-pointer"><img src="' + data + '" class="rounded-circle" width="44" height="44" alt=""></div>';
            } else {
                var image = '<div class="d-flex align-items-center gap-3 cursor-pointer"><img src="../../assets/images/sample_user.jpg" class="rounded-circle" width="44" height="44" alt=""></div>';
            }
            return '<div class = "d-flex align-items-center gap-3 fs-6">' + image + '</div>';
        }
    }, {
        data: "user_name",
        render: function(data, type, row) {
            var user = (row.user_delete == 'No') ? '<div class = "text-wrap" style = "width:150px;">' + data + '</div>' : '<div class = "text-wrap text-danger" style = "width:150px;">' + data + '</div>'
            return user;
        }
    }, {
        data: "date",
    }, {
        data: "total_call",
        render: function(data, type, row) {
            return '<div><i class="bi bi-telephone"></i><b> ' + data + '</b></div>';
        }
    }, {
        data: "new_call",
        render: function(data, type, row) {
            return '<div><i class="bi bi-telephone"></i><b> ' + data + '</b></div>';
        }
    }, {
        data: "numofmeeting",
        render: function(data, type, row) {
            return '<div><i class="bi bi-calendar-event"></i><b> ' + data + '</b></div>';
        }
    }, {
        data: "doc_prepare",
        render: function(data, type, row) {
            if (Array.isArray(data)) {
                let centers = '';
                let i = 0;
                for (let key in data) {
                    i++;
                    if (data[key]['center_delete'] == 'Yes') {
                        centers += '<span class="badge rounded-pill bg-info m-1" style="background-color:#e3382a !important;font-weight:200!important;font-size:13px;">' + data[key]['center_name'] + '</span>';
                    } else {
                        centers += '<span class="badge rounded-pill bg-info m-1" style="background-color:#1a2232 !important;font-weight:200!important;font-size:13px;">' + data[key]['center_name'] + '</span>';
                    }
                    if (i % 2 == 0) {
                        centers += '<br>';
                    }
                }
                return '<div>' + centers + '</div>';
            } else {
                return '<div>' + data + '</div>';
            }
        }
    }, {
        data: "doc_received",
        render: function(data, type, row) {
            if (Array.isArray(data)) {
                let centers = '';
                let i = 0;
                for (let key in data) {
                    i++;
                    if (data[key]['center_delete'] == 'Yes') {
                        centers += '<span class="badge rounded-pill bg-info m-1" style="background-color:#e3382a !important;font-weight:200!important;font-size:13px;">' + data[key]['center_name'] + '</span>';
                    } else {
                        centers += '<span class="badge rounded-pill bg-info m-1" style="background-color:#1a2232 !important;font-weight:200!important;font-size:13px;">' + data[key]['center_name'] + '</span>';
                    }
                    if (i % 2 == 0) {
                        centers += '<br>';
                    }
                }
                return '<div>' + centers + '</div>';
            } else {
                return '<div>' + data + '</div>';
            }
        }
    }, {
        data: "doc_close",
        render: function(data, type, row) {
            if (Array.isArray(data)) {
                let centers = '';
                let i = 0;
                for (let key in data) {
                    i++;
                    if (data[key]['center_delete'] == 'Yes') {
                        centers += '<span class="badge rounded-pill bg-info m-1" style="background-color:#e3382a !important;font-weight:200!important;font-size:13px;">' + data[key]['center_name'] + '</span>';
                    } else {
                        centers += '<span class="badge rounded-pill bg-info m-1" style="background-color:#1a2232 !important;font-weight:200!important;font-size:13px;">' + data[key]['center_name'] + '</span>';
                    }
                    if (i % 2 == 0) {
                        centers += '<br>';
                    }
                }
                return '<div>' + centers + '</div>';
            } else {
                return '<div>' + data + '</div>';
            }
        }
    }, {
        data: "Action",
        render: function(data, type, row) {
            var edit = '';
            <?php if (in_array('Daily Reporting Update', $_SESSION['permission'])) { ?>
                edit = '<div class="text-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit" onclick = "updateDetails(' + row.id + ',&#39;' + row.createDate + '&#39;)"><i class="bi bi-pencil-fill"></i></div>';
            <?php } ?>
            return '<div class = "table-actions d-flex align-items-center gap-3 fs-6">'+edit+'</div>';
        }
    }],
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
                $("#branch_filter").html(data);
                filter_arr = ['vertical','department','user'];
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
                $("#vertical_filter").html(data);
                filter_arr = ['department','user'];
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
                $("#department_filter").html(data);
                filter_arr = ['user'];
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
}

function triggerChange(id) {
    isUpdating = true;
    console.log(id);
    $("#"+id).val("");
    $("#"+id).trigger('change');
    isUpdating = false;
}

function getFilterData() {
    <?php if ($_SESSION['role'] == '1') { ?>
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
                $("#branch_filter").html(data);
            }   
        });
    <?php }  elseif ($_SESSION['role'] == '2') { ?>
        var organization_id = '<?=$_SESSION['Organization_id']?>';
        var branch_id = '<?=$_SESSION['Branch_id']?>';
        var vertical_id = '<?=$_SESSION['Vertical_id']?>';
        var department_id = '<?=$_SESSION['Department_id']?>';
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
            }
        });
    <?php } ?>
}

$(document).ready(function(){
    const dt_permission = $('#dailyReportTable').DataTable(dailyReportSettings);
    $('#daterange').daterangepicker({
        startDate: new Date(new Date().getTime() - (10 * 24 * 60 * 60 * 1000)),
        endDate: new Date(),
        locale: {
            format: "MM/DD/YYYY"
        },    
    });
    getFilterData();
});

function addDailyReport() {
    $.ajax({
        url: "/app/dailyReporting/insertAndupdateDailyReporting",
        type: 'get',
        success: function(data) {
            $('#lg-modal-content').html(data);
            $('#lgmodal').modal('show');
        }
    });
}

function updateDetails(report_id, creatDate) {
    const d = new Date(Date.now()).toLocaleString().split(',')[0];
    <?php if ($_SESSION['role'] == '2') { ?>
        if (creatDate.localeCompare(d) == 0) {
            $.ajax({
                url: "/app/dailyReporting/insertAndupdateDailyReporting",
                type: 'post',
                data: {
                    report_id
                },
                success: function(data) {
                    $('#lg-modal-content').html(data);
                    $('#lgmodal').modal('show');
                }
            });
        } else {
            Swal.fire({
                text: "Update only allow on current date",
                title: "Sorry, can't update report",
                icon: 'error',
            });
        }
    <?php } else { ?>
        $.ajax({
            url: "/app/dailyReporting/insertAndupdateDailyReporting",
            type: 'post',
            data: {
                report_id
            },
            success: function(data) {
                $('#lg-modal-content').html(data);
                $('#lgmodal').modal('show');
            }
        });
    <?php } ?>
}

</script>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-bottom.php'); ?>