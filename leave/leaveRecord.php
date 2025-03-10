<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-top.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-bottom.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/topbar.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/menu.php');?>
<style>

thead {
    font-size: medium;
}
.badgeStyle{
    font-weight: 200 !important ; 
    font-size :14px ;
}

.cardleave {
    font-weight: 500; 
    font-size :17px;
}

.filter {
    font-weight: 500; 
    font-size :15px;
}

.filterCard{
    margin-right: 1.4rem;
    background-color: #dcdee21a;
    padding-left: 0.3rem !important;
    padding-right: 0.5rem !important;
}

.show{
    display: block;
}

.hide{
    display: none;
}
</style>

<main class="page-content">
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="mb-0">Leave Record</h5>
                <div class="d-flex justify-content-end gap-2 col-sm-6">
                    <?php if(in_array('Leave Record Create',$_SESSION['permission'])) { ?>
                    <div class="d-flex align-items-center theme-icons shadow-sm p-2 cursor-pointer rounded gap-2 bg-primary" title="Apply Leave" style="color: white;" onclick="applyLeave()" data-bs-toggle = "tooltip"><span>Apply Leave</span>
                        <i class="bi bi-plus-circle-fill" id="apply_leave"></i>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <!-- start row-->  
            <div class="show" id="leave_record_block">
                <div class="row row-cols-1 row-cols-lg-2 row-cols-xl-2 row-cols-xxl-4">
                    <div class="col">
                        <div class="card overflow-hidden radius-5">
                            <div class="card-body" style="background-color: #dcdee21a;">
                                <div class="d-flex align-items-stretch justify-content-between overflow-hidden">
                                    <div>
                                        <div class="cardleave">Full Day Leave <span style="font-size: small;">(Monthly)</span></div>
                                        <div style="color: gray;">Used : <span id="fullDayLeaveUsed"></span> | Available : <span id="fullDayLeaveAvailable"></span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card overflow-hidden radius-5">
                            <div class="card-body" style="background-color: #dcdee21a;">
                                <div class="d-flex align-items-stretch justify-content-between overflow-hidden">
                                    <div>
                                        <div class="cardleave">Half Day Leave <span style="font-size: small;">(Monthly)</span></div>
                                        <div style="color: gray;">Used : <span id="halfDayLeaveUsed"></span> | Available : <span id="halfDayLeaveAvailiable"></span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card overflow-hidden radius-5">
                            <div class="card-body" style="background-color: #dcdee21a;">
                                <div class="d-flex align-items-stretch justify-content-between overflow-hidden">
                                    <div>
                                        <div class="cardleave">Restricted Leave <span style="font-size: small;">( Yearly ) </span></div>
                                        <div style="color: gray;">Used : <span id="restrictedLeaveUsed"></span> | Available : <span id="restrictedLeaveAvailiable"></span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End row -->
            <!-- Filter -->
            <div class="hide" id="filter_block">
                <div class = "row d-flex align-items-center justify-content-start mb-2" style="margin-left: 0.2rem; margin-right: 0.4rem;">
                    <?php if($_SESSION['role'] == '1' || ($_SESSION['role'] == '2' && $_SESSION['Department_id'] == '8') || $_SESSION['role'] == '3') { ?>
                    <div class="col-sm-3 card p-1 mb-1 mr-3 filterCard" style="z-index: 0 !important;">
                        <label class = "col-form-label filter">Department</label>
                        <select type = "text" class="form-control form-control-sm single-select select2" name="department_filter" id="department_filter" onchange="reloadTable(this.id)">
                        </select>
                    </div>
                    <?php } ?>
                    <div class="col-sm-2 card p-1 mb-1 filterCard" style="z-index: 0 !important;">
                        <label class="col-form-label filter">User</label>
                        <select type="text" class="form-control form-control-sm single-select select2" name="user_filter" id="user_filter" onchange="reloadTable(this.id)">
                        </select>
                    </div>
                    <div class="col-sm-2 card p-1 mb-1 filterCard" style="z-index: 0 !important;">
                        <label class="col-form-label filter" style="font-size: small;">Date Range</label>   
                        <input type="text" name="daterange_filter" id="daterange_filter" class="form-control" onchange="reloadTable(this.id)"/>
                    </div>
                    <div class="col-sm-2 card p-1 mb-1 filterCard" style="z-index: 0 !important;">
                        <div class="filter mb-1 mt-2 ps-1">Approved Request</div>
                        <div class="mb-3 ps-1" style="color: gray;">Request : <span id="approvalRequest"></span> | Total Days : <span id="approvalNumberOfDay"></span></div>
                    </div>
                    <div class="col-sm-2 card p-1 mb-1 filterCard" style="z-index: 0 !important;">
                        <div class="filter mb-1 mt-2 ps-1">Pending Request</div>
                        <div class="mb-3 ps-1" style="color: gray;">Request : <span id="pendingRequest"></span> | Total Days : <span id="pendingNumberOfDay"></span></div>
                    </div>
                </div>
            </div>            
            <!-- End Filter -->
            <ul class="nav nav-tabs nav-primary" role="tablist">
                <li class="nav-item" role="presentation" <?php if($_SESSION['role'] == '1') { ?>style="display: none;"  <?php } ?>>
                    <a class="nav-link <?php echo ($_SESSION['role'] != '1') ? 'active' : ''; ?>" data-bs-toggle="tab" href="#myLeaveRecord" role="tab" aria-selected="<?php echo ($_SESSION['role'] != '1') ? 'true' : 'false'; ?>" onclick="activeTab()">
                        <div class="d-flex align-items-center">
                            <div class="tab-icon" style="padding-right: 5px;"><i class="bi bi-calendar-event"></i></div>
                            <div class="tab-title">My Leave</div>
                        </div>
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link <?php echo ($_SESSION['role'] == '1') ? 'active' : ''; ?>" data-bs-toggle="tab" href="#requestedLeaveRecord" role="tab" aria-selected="<?php echo ($_SESSION['role'] == '1') ? 'true' : 'false'; ?>" onclick="activeTab()">
                        <div class="d-flex align-items-center">
                            <div class="tab-icon"><i class='bx bx-user-pin font-18 me-1'></i></div>
                            <div class="tab-title">Requested Leave</div>
                        </div>
                    </a>
                </li>
            </ul>
            <div class="tab-content py-3">
                <div class="tab-pane fade <?php echo ($_SESSION['role'] != '1') ? 'show active' : ''; ?>" id="myLeaveRecord" role="tabpanel">
                    <div class="table-responsive mt-3">
                        <table class="table align-middle w-100" id="myLeaveRecordTable" style="color: #515B73!important;">
                            <thead class="table-primary">
                                <tr>
                                    <th>User</th>
                                    <th>Leave Type</th>
                                    <th>Leave Date</th>
                                    <th>No. of Days</th>
                                    <th>Applied On</th>
                                    <th>Reviewed By</th>
                                    <th>Status</th>
                                    <th>Leave Details</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade <?php echo ($_SESSION['role'] == '1') ? 'show active' : ''; ?>" id="requestedLeaveRecord" role="tabpanel">
                    <div class="table-responsive mt-3">
                        <table class="table align-middle w-100" id="requestedLeaveRecordTable" style="color: #515B73!important;">
                            <thead class="table-primary" >
                                <tr>
                                    <th>User</th>
                                    <th>Leave Type</th>
                                    <th>Leave Date</th>
                                    <th>No. of Days</th>
                                    <th>Applied On</th>
                                    <th>Reviewed By</th>
                                    <th>Status</th>
                                    <th>Leave Details</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>    
</main>
<!--end page main-->

<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer-top.php');?>
<script src="/assets/plugins/select2/js/select2.min.js"></script>
<script src="/assets/js/form-select2.js"></script>
<script type="text/javascript">

var myLeaveSetting = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    'ajax': {
        'url': '/app/leaveRecord/leaveRecord-server', 
        'type': 'POST',
        "data" : function(d) {
            d.leaveRecord = "myLeave";
        }
    },
    'columns': [{
            data: "image",
            render : function(data,type,row) {
                var img = '<div class="d-flex align-items-center gap-3 cursor-pointer"><img src="'+data+'" class="rounded-circle" width="44" height="44" alt=""></div>';
                var user_name = '<div style = "font-weight:500!important;font-size:15px;">'+row.user_name+'</div>';
                return '<div class = "d-flex align-items-center gap-3 fs-6">'+ img+user_name + '</div>';
            }
        },{
            data: "leave_type" ,
        },{
            data: "leave_date", 
        },{
            data: "numOfDays", 
            render : function(data,type,row) {
                return '<div><i class="bi bi-calendar3"></i><b> '+data+'</b></div>';
            }
        },{
            data: "applied_on", 
        },{
            data: "approved_by", 
            render : function(data, type, row) {
                return  ( data == null || data.length <= 0 ) ? '<div class="badge rounded-pill bg-warning m-1 badgeStyle" >Pending</div>' : '<div class="badge rounded-pill bg-info m-1 badgeStyle" style="background-color:#495c83  !important;">'+row.approved_by_user_name+'</div>';
            }
        },{
            data : "status",
            render : function(data,type,row) {
                let message = ''; let background = '';
                if (data == '1') {
                    message = "Approved";
                    background = "bg-success";
                } else if (data == '2') {
                    message = "Dis-Approved";
                    background = "bg-danger";
                } else if (data == '3') {
                    message = "Pending";
                    background = "bg-warning";
                } else if (data == '4') {
                    message = "Withdraw";
                    background = "bg-secondary";
                }
                return '<div class="badge rounded-pill '+background+' m-1 badgeStyle">'+message+'</div>';
            }
        },{
            data  : "view leave" ,
            render : function (data,type,row) {
                let message = row.mail_body.replace(/[\r\n]+/g, '<br>');
                let file_path = row.supported_document;
                let view = '<div class="badge rounded-pill bg-info badgeStyle d-flex align-items-center justify-content-center p-2" style="cursor: pointer; margin-left: 0.5rem;margin-right: 3rem;padding-top: 0.5rem;" onclick="viewLeaveDetails('+row.ID+', &#39;'+row.mail_subject+'&#39;, &#34;'+message+'&#34;, &#39;'+file_path+'&#39;,&#39;myleave&#39;,&#39;'+row.status+'&#39;)"><i class="bi bi-chat-square-dots-fill me-1"></i><span>View</span></div>';
                return view;
            }
        },{         
            data : "Action",
            render : function(data, type, row) {
                var edit = '';
                <?php if(in_array('Leave Record Update',$_SESSION['permission'])) { ?>
                    edit = '<div class="text-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit" onclick = "updateDetails('+row.ID+',&#39;'+row.status+'&#39;)"><i class="bi bi-pencil-fill"></i></div>';
                <?php } ?>
                return '<div class = "table-actions d-flex align-items-center gap-3 fs-6">'+edit+'</div>';
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

var requestedLeaveSetting = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    'ajax': {
        'url': '/app/leaveRecord/leaveRecord-server', 
        'type': 'POST',
        "Content-Type" :  "application/json",
        "data" : function(d) {
            d.leaveRecord = "requestedLeave";
            d.departmentFilter = $("#department_filter").val();
            d.userFilter = $("#user_filter").val();
            d.selected_date = $("#daterange_filter").val();
        }
    },
    'columns': [{
            data: "image",
            render : function(data,type,row) {
                var img = '<div class="d-flex align-items-center gap-3 cursor-pointer"><img src="'+data+'" class="rounded-circle" width="44" height="44" alt=""></div>';
                var user_name = '<div style = "font-weight:500!important;font-size:15px;">'+row.user_name+'</div>';
                return '<div class = "d-flex align-items-center gap-3 fs-6">'+ img+user_name + '</div>';
            }
        },{
            data: "leave_type",
            render : function(data,type,row) {
                if( row.pendingRequest != "none") {
                    $("#pendingRequest").text(row.pendingRequest);
                    $("#pendingNumberOfDay").text(row.pendingNumberOfDay);
                    $("#approvalRequest").text(row.approvalRequest);
                    $("#approvalNumberOfDay").text(row.approvalNumberOfDay);
                }
                return data;
            }
        },{
            data: "leave_date", 
        },{
            data: "numOfDays", 
            render : function(data,type,row) {
                return '<div><i class="bi bi-calendar3"></i><b> '+data+'</b></div>';
            }
        },{
            data: "applied_on", 
        },{
            data: "approved_by", 
            render : function(data, type, row) {
                return  ( data == null || data.length <= 0 ) ? '<div class="badge rounded-pill bg-warning m-1 badgeStyle" >Pending</div>' : '<div class="badge rounded-pill bg-info m-1 badgeStyle" style="background-color:#495c83  !important;">'+row.approved_by_user_name+'</div>';
            }
        },{
            data : "status",
            render : function(data,type,row) {
                let message = ''; let background = '';
                if (data == '1') {
                    message = "Approved";
                    background = "bg-success";
                } else if (data == '2') {
                    message = "Dis-Approved";
                    background = "bg-danger";
                } else if (data == '3') {
                    message = "Pending";
                    background = "bg-warning";
                } else if (data == '4') {
                    message = "Withdraw";
                    background = "bg-secondary";
                }
                return '<div class="badge rounded-pill '+background+' m-1 badgeStyle">'+message+'</div>';
            }
        },{
            data  : "view leave" ,
            render : function (data,type,row) {
                let message = row.mail_body.replace(/[\r\n]+/g, '<br>');
                let subject = row.mail_subject.replace(/[\r\n]+/g, '<br>');
                let file_path = row.supported_document;
                let view = '<div class="badge rounded-pill bg-info badgeStyle d-flex align-items-center justify-content-center p-2" style="cursor: pointer; margin-left: 0.5rem;margin-right: 3rem;padding-top: 0.5rem;" onclick="viewLeaveDetails('+row.ID+', &#39;'+subject+'&#39;, &#34;'+message+'&#34;, &#39;'+file_path+'&#39;,&#39;requestedLeave&#39;,&#39;'+row.status+'&#39;)"><i class="bi bi-chat-square-dots-fill me-1"></i><span>View</span></div>';
                return view;
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

$(document).ready(function(){
    $("#myLeaveRecordTable").DataTable(myLeaveSetting);
    $("#requestedLeaveRecordTable").DataTable(requestedLeaveSetting);
    $('#daterange_filter').daterangepicker({
        startDate : new Date(new Date().getTime() - (20 * 24 * 60 * 60 * 1000)), 
        endDate: new Date(new Date().getTime() + (30 * 24 * 60 * 60 * 1000)),
        locale: {
            format: "MM/DD/YYYY"
        },    
    });
    getFilterData();
    activeTab();
    getUserLeaveDetails();
    //updateUserLeaveStatus();
});

function activeTab() {
    const activeTab = document.querySelector(".nav-link.active");
    if (activeTab.innerText.trim() == "My Leave") {
        if($("#leave_record_block").hasClass("hide")) {
            $("#leave_record_block").removeClass("hide");
            $("#leave_record_block").addClass("show");
            $("#filter_block").removeClass("show");
            $("#filter_block").addClass("hide");
        } else if (!$("#leave_record_block").hasClass("show")) {
            $("#leave_record_block").addClass("show");
        }
    } else {
        if($("#filter_block").hasClass("hide")) {
            $("#filter_block").removeClass("hide");
            $("#filter_block").addClass("show");
            $("#leave_record_block").removeClass("show");
            $("#leave_record_block").addClass("hide");
        } else if (!$("#filter_block").hasClass("show")) {
            $("#filter_block").addClass("show");
        }
    }
}

function reloadTable() {
    $('#requestedLeaveRecordTable').DataTable().ajax.reload(null, false);
}

function getFilterData() {
    var filter_data_field = ['department','user'];
    $.ajax({
        url : "/app/leaveRecord/filter",
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
}

function applyLeave() {
    $.ajax({
        url : "/app/leaveRecord/insertAndupdateLeave", 
        type : 'get', 
        success : function(data){
            $('#md-modal-content').html(data);
            $('#mdmodal').modal('show');
        }
    });
}

function updateDetails(id,status) {
    if (status == '3') {
        $.ajax({
            url : "/app/leaveRecord/insertAndupdateLeave", 
            type : 'post',
            data : {
                "leave_id" : id
            },
            success : function(data){
                $('#md-modal-content').html(data);
                $('#mdmodal').modal('show');
            }
        });
    } else {
        Swal.fire({
            title: "Update Not Allow!",
            text: "Sorry..!! Leave status updated",
            icon: 'error',
        })
    }
}

async function fetchData(url,option) {
    try {
        const response = await fetch(url,{
            method: "POST",
            body : JSON.stringify(option)
        });
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('There has been a problem with your fetch operation:', error);
        return null;
    }
}

async function getUserLeaveDetails() {
    const url = "/app/leaveRecord/getUserLeaveDetails";
    const option = {"requestType" : "userLeaveDetails"};
    const data = await fetchData(url,option);
    if(data.status == 200) {
        delete data['status'];
        for (const key in data) {
            $("#"+key).text(data[key]);
        }
    } 
}

function viewLeaveDetails(id,mail_subject,mail_body,file_path,type,status) {
    $.ajax({
        url : "/app/leaveRecord/viewLeaveDetails", 
        type : 'post', 
        data : {
            "leave_id" : id,
            mail_subject,
            mail_body,
            file_path,
            type, 
            status
        },
        success : function(data){
            $('#md-modal-content').html(data);
            $('#mdmodal').modal('show');
        }
    });
}

</script>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer-bottom.php');?>