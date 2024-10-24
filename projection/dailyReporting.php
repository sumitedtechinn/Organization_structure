<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-top.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-bottom.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/topbar.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/menu.php'); ?>

<!--start content-->
<main class="page-content">
    <div class="row justify-content-center">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Daily Reporting</h5>
                    <div class="d-flex justify-content-end gap-2 col-sm-2">
                        <?php if (in_array('Daily Reporting Delete', $_SESSION['permission'])) { ?>
                            <div class="theme-icons shadow-sm p-2 cursor-pointer rounded" title="Go to Trash" data-bs-toggle="tooltip" id="trash_button">
                                <i class="bi bi-trash-fill"></i>
                            </div>
                            <button class="btn btn-primary" style="font-size: small;" id="return_button">Daily Report</button>
                        <?php } ?>
                        <?php if ($_SESSION['role'] == '2') { ?>
                            <div class="d-flex align-items-center theme-icons shadow-sm p-2 cursor-pointer rounded" title="Add" onclick="addDailyReport()" data-bs-toggle="tooltip">
                                <i class="bi bi-plus-circle-fill" id="add_daily_report"></i>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-start gap-3 mt-3">
                    <div class="col-sm-3 card bg-light p-1 mb-1" style="z-index: 0 !important;">
                        <label class="col-form-label">Users</label>
                        <select type="text" class="form-control form-control-sm single-select select2" name="dailyReportUser_filter" id="dailyReportUser_filter" onchange="reloadTable(this.id)">
                        </select>
                    </div>
                    <div class="col-sm-3 card bg-light p-1 mb-1" style="z-index: 0 !important;">
                        <label class="col-form-label">Start Date</label>
                        <input type="date" class="form-control form-control-sm" name="start_date" id="start_date" onchange="reloadTable(this.id)">
                    </div>
                    <div class="col-sm-3 card bg-light p-1 mb-1" style="z-index: 0 !important;">
                        <label class="col-form-label">End Date</label>
                        <input type="date" class="form-control form-control-sm" name="end_date" id="end_date" onchange="reloadTable(this.id)">
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
                                <th>Doc Closed</th>
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
                d.selected_user = $("#dailyReportUser_filter").val();
                d.selected_start_date = $("#start_date").val();
                d.selected_end_date = $("#end_date").val();
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
                var del = '';
                <?php if (in_array('Daily Reporting Update', $_SESSION['permission'])) { ?>
                    edit = '<div class="text-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit" onclick = "updateDetails(' + row.id + ',&#39;' + row.createDate + '&#39;)"><i class="bi bi-pencil-fill"></i></div>';
                <?php } ?>
                <?php if (in_array('Daily Reporting Delete', $_SESSION['permission'])) { ?>
                    del = '<div class="text-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete" onclick = "checkUser(' + row.id + ',&#39;' + row.createDate + '&#39;)"><i class="bi bi-trash-fill"></i></div>';
                <?php } ?>
                return '<div class = "table-actions d-flex align-items-center gap-3 fs-6">' + edit + del + '</div>';
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

    var dailyReportTrashSettings = {
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'ajax': {
            'url': '/app/dailyReporting/dailyReport-server',
            'type': 'POST',
            'data': function(d) {
                d.deleteDailyReport = "delete-report";
                d.selected_user = $("#dailyReportUser_filter").val();
                d.selected_start_date = $("#start_date").val();
                d.selected_end_date = $("#end_date").val();
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
                var table = "daily_reporting";
                var restore = '<button type="button" class="btn btn-info text-white px-4" onclick = "restoreDetails(' + row.id + ',&#39;' + table + '&#39;)">Restore</button>';
                var del = '<button type="button" class="btn btn-danger px-4" onclick = "parmanentDeleteDetails(' + row.id + ',&#39;' + table + '&#39;)">Delete</button>';
                return '<div class = "table-actions d-flex align-items-center gap-2 fs-6">' + restore + del + '</div>';
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

    function getFilterData() {
        var filter_data_field = ['dailyReportUser'];
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
    }

    function reloadTable(id) {
        $('#dailyReportTable').DataTable(dailyReportSettings);
    }
    $(document).ready(function() {
        getFilterData();
        $("#return_button").css('display', 'none');
        $('#dailyReportTable').DataTable(dailyReportSettings);
    });

    $("#trash_button").on('click', function() {
        $('#dailyReportTable').DataTable(dailyReportTrashSettings);
        $("#return_button").css('display', 'block');
        $("#trash_button").css('display', 'none');
    });

    $("#return_button").on('click', function() {
        $('#dailyReportTable').DataTable(dailyReportSettings);
        $("#return_button").css('display', 'none');
        $("#trash_button").css('display', 'block');
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

    function checkUser(report_id, creatDate) {
        const d = new Date(Date.now()).toLocaleString().split(',')[0];
        const table = 'daily_reporting';
        <?php if ($_SESSION['role'] == '2') { ?>
            if (creatDate.localeCompare(d) == 0) {
                deleteDetails(report_id, table);
            } else {
                Swal.fire({
                    text: "Delete only allow on current date",
                    title: "Sorry, can't delete report",
                    icon: 'error',
                });
            }
        <?php } else { ?>
            deleteDetails(report_id, table);
        <?php } ?>
    }
</script>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-bottom.php'); ?>