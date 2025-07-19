<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-top.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-bottom.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/topbar.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/menu.php');?>
<main class="page-content">
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="mb-0">Attendance Setting</h6>
                <div class="d-flex justify-content-end gap-2 col-sm-6">
                    <?php if(in_array('Assets Delete',$_SESSION['permission'])) { ?>
                    <button class="btn btn-outline-danger btn-sm d-flex align-items-center" id="trash_button" data-bs-toggle="tooltip" title="Go to Trash">
                        <i class="bi bi-trash-fill me-1" id="trash_setting"></i>
                    </button>
                    <button class="btn btn-primary" style="font-size: small;" id ="return_button">Go To Setting</button>
                    <?php } ?>
                    <?php if(in_array('Assets Create',$_SESSION['permission'])) { ?>
                    <div class="d-flex align-items-center theme-icons shadow-sm p-2 cursor-pointer rounded gap-2 bg-primary" title="Add Setting" style="color: white;" onclick="addSetting()" data-bs-toggle = "tooltip"><span>Add Setting</span>
                        <i class="bi bi-plus-circle-fill" id="add_setting"></i>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <div class="d-flex justify-content-end">
                <div class="col-sm-2">
                    <input type="text" id="setting-search-table" class="form-control form-control-sm pull-right" placeholder="Search">
                </div>
            </div>
            <div class="table-responsive mt-3">
                <table class="table align-middle w-100" id="attendanceSettingTable" style="color: #515B73!important;">
                    <thead class="table-primary">
                        <tr class="table_heading"> 
                            <td>Organization</td>
                            <td>In Time</td>
                            <td>Out Time</td>
                            <td>Relaxation Time</td>
                            <td>WeekOff</td>
                            <td>Holiday List</td>
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

var attendanceSetupSetting = {
    'processing' : true,
    'serverSide' : true,
    'serverMethod' : 'POST',
    'searching' : false,
    'lengthChange' : false,
    'ajax': {
        url : '/app/attendance_system/attendanceSetting/attendanceSetting-server', 
        type : 'POST',
        data : function(d) {
            d.searchText = document.getElementById("setting-search-table").value;
        }  
    },
    'columns': [{
            data: "organization_name",
            render : (data,type,row) => `<div class = "table_heading">${data}</div>`
        },{
            data: "in_time" ,
            render : (data,type,row) => showTimeInStyle(data,"bg-success")
        },{
            data: "out_time", 
            render : (data,type,row) => showTimeInStyle(data,"bg-success")
        },{
            data: "relaxation_time",
            render : (data,type,row) => showTimeInStyle(data,"bg-warning")
        },{
            data: "week_off", 
            render : (data,type,row) => {
                let days = JSON.parse(data);
                let badge = `<div class = "d-flex justify-content start gap-1">`
                badge += days.reduce((acc,ele) => {
                    ele = ele.charAt(0).toUpperCase() + ele.slice(1);
                    acc += `<span class="badge bg-secondary" style="font-size: small;font-weight:500;">${ele}</span>`
                    return acc;
                } , "")
                badge += `</div>`;
                return badge;                
            } 
        },{
            data : "holidayList", 
            render : (data,type,row) => {  
                return (data > 0) ? `<button class="btn btn-info btn-sm" style="font-size:smaller;color:white;" onclick="showHolidayList(${row.ID})">Holidays List</button>` : `<button class="btn btn-secondry btn-sm" style="font-size:smaller;color:white;">Not Set</button>`;  
            } 
        },{         
            data : "Action",
            render : function(data, type, row) {
                let edit = ''; let del = '';let table = 'attendance_setting';
                <?php if(in_array('Attendance Settings Update',$_SESSION['permission'])) { ?>
                    edit = updateButton(row.ID);
                <?php } else { ?>
                    edit = updateDisabledButton();
                <?php } ?>
                <?php if(in_array('Attendance Settings Delete',$_SESSION['permission'])) { ?>
                    del = deleteButton(row.ID , table , 'checkDeleteCondition');
                <?php } else { ?>
                    del = deleteDisabledButton();
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

var attendanceTrashSetupSetting = {
    'processing' : true,
    'serverSide' : true,
    'serverMethod' : 'POST',
    'searching' : false,
    'lengthChange' : false,
    'ajax': {
        url : '/app/attendance_system/attendanceSetting/attendanceSetting-server', 
        type : 'POST',
        data : function(d) {
            d.searchText = document.getElementById("setting-search-table").value;
            d.attendanceSetting = "deleteType";
        }  
    },
    'columns': [{
            data: "organization_name",
            render : (data,type,row) => `<div class = "table_heading">${data}</div>`
        },{
            data: "in_time" ,
            render : (data,type,row) => showTimeInStyle(data,"bg-success")
        },{
            data: "out_time", 
            render : (data,type,row) => showTimeInStyle(data,"bg-success")
        },{
            data: "relaxation_time",
            render : (data,type,row) => showTimeInStyle(data,"bg-warning")
        },{
            data: "week_off", 
            render : (data,type,row) => {
                let days = JSON.parse(data);
                let badge = `<div class = "d-flex justify-content start gap-1">`
                badge += days.reduce((acc,ele) => {
                    ele = ele.charAt(0).toUpperCase() + ele.slice(1);
                    acc += `<span class="badge bg-secondary" style="font-size: small;font-weight:500;">${ele}</span>`
                    return acc;
                } , "")
                badge += `</div>`;
                return badge;                
            } 
        },{
            data : "holidayList", 
            render : (data,type,row) => {  
                return (data > 0) ? `<button class="btn btn-info btn-sm" style="font-size:smaller;color:white;" onclick="showHolidayList(${row.ID})">Holidays List</button>` : `<button class="btn btn-secondry btn-sm" style="font-size:smaller;color:white;">Not Set</button>`;  
            } 
        },{         
            data : "Action",
            render : function(data, type, row) {
                let table = "attendance_setting";
                let restore = restoreButton(row.ID,table);
                let del = paramanentDeleteButton(row.ID,table);
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
    $("#attendanceSettingTable").DataTable(attendanceSetupSetting);
    //getAllFilterData();
});

$("#trash_button").on('click',function(){
    $("#attendanceSettingTable").DataTable(attendanceTrashSetupSetting);
    $("#return_button").css('display','block');
    $("#trash_button").css('display','none');
});

$("#return_button").on('click',function(){
    $("#attendanceSettingTable").DataTable(attendanceSetupSetting);
    $("#return_button").css('display','none');
    $("#trash_button").css('display','block');
});

function showTimeInStyle(time,background) {
    let modified_time = time.split(":").filter((ele,index,array) => (index != array.length -1)).join(":");
    return `<div class="d-flex align-items-center p-2 cursor-pointer rounded">
        <div class="font-18">	
            <i class="lni lni-alarm-clock"></i>
        </div>
        <div class="ms-2">	
            <span class="badge ${background}" style="font-size: small;padding: 0.5rem;font-weight:500">${modified_time}</span>
        </div>
    </div>`;
}

async function showHolidayList(id) {
    let url = "/app/attendance_system/attendanceSetting/showHolidayList"; 
    const data = await postMethodWithTextResponse(url,{id});
    if(data != null) {
        $('#md-modal-content').html(data);
        $('#mdmodal').modal('show');
    }
}

async function addSetting() {
    let url = `/app/attendance_system/attendanceSetting/insertAndUpdateAttendanceSetting`;
    const data = await getMethod(url);
    if (data != null) {
        $('#lg-modal-content').html(data);
        $('#lgmodal').modal('show');
    }
}

async function updateDetails(id) {
    let url = "/app/attendance_system/attendanceSetting/insertAndUpdateAttendanceSetting";
    const data = await postMethodWithTextResponse(url,{id});
    if(data != null) {
        $('#lg-modal-content').html(data);
        $('#lgmodal').modal('show');
    }
}

async function checkDeleteCondition(id,table) {
    let url = `/app/attendance_system/attendanceSetting/fetchAndStoreAttendanceSetting`;
    const data = await postMethodWithJsonResponse(url,{
        id,
        method : "checkAttendanceSettingDeleteCondition" 
    });
    if (data != null) {
        if(data.status == 200) {
            if (data.message == "not_allow") {
                Swal.fire({
                    title : "Setting Delete Not Allow" , 
                    text: "Attendance assign on this Organization Setting",
                    icon: 'warning',
                });
            } else {
                deleteDetails(id,table);
            }
        } else {
            toastr.error(data.message);
        }
    }
}

// Debounce on serach 
var timer;
document.getElementById("setting-search-table").addEventListener('input', (event) => {
    if(timer) clearTimeout(timer);
    timer = setTimeout(() => $("#attendanceSettingTable").DataTable(attendanceSetupSetting) , 1000);
});

</script>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer-bottom.php');?> 