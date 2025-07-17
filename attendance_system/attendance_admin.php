<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-top.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-bottom.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/topbar.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/menu.php');?>

<style>
    .badge_style{
        font-size: smaller;
        padding: 0.5rem;
        font-weight:500;
        align-items: center;
        align-content: center;
    }
    .cell_style {
        background-color: #d9dbe936;
        border: 1px solid gray;
        border-radius: 5px;
        padding: 0.2rem;
    }
    .badge-present  { background-color: #28a745 !important; color: white; }
    .badge-absent   { background-color: #dc3545 !important; color: white; }
    .badge-leave    { background-color: #ffc107 !important; color: black; }
    .badge-weekoff  { background-color: #6c757d !important; color: white; }
    .badge-time     { background-color: #429ef5 !important; color: white; }
    .empty-badge {
        background-color: #6c6c6c45;
        min-height: 68px;
        min-width: 103px;   
    }
</style>
<main class="page-content">
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="mb-0">Attendance</h6>
                <div class="d-flex justify-content-end gap-2 col-sm-6">
                    <?php if(in_array('Attendance Delete',$_SESSION['permission'])) { ?>
                    <button class="btn btn-outline-danger btn-sm d-flex align-items-center" id="trash_button" data-bs-toggle="tooltip" title="Go to Trash">
                        <i class="bi bi-trash-fill me-1" id="trash_setting"></i>
                    </button>
                    <button class="btn btn-primary" style="font-size: small;" id ="return_button">Go To Setting</button>
                    <?php } ?>
                    <?php if(in_array('Attendance Create',$_SESSION['permission'])) { ?>
                    <div class="d-flex align-items-center theme-icons shadow-sm p-2 cursor-pointer rounded gap-2 bg-primary" title="Import Attendance" style="color: white;" onclick="openImportAttendanceModule()" data-bs-toggle = "tooltip">
                        <div class="font-16"><i class="lni lni-upload"></i></div>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-10 d-flex gap-2" id="filter_container">
                    <div class="col-sm-3">
                        <select type="text" class="form-control form-control-sm single-select select2" name = "month_filter" id="month_filter" onchange="reloadTable(this.value)"></select>
                    </div>
                    <div class="col-sm-3">
                        <select type="text" class="form-control form-control-sm single-select select2" name = "year_filter" id="year_filter" onchange="reloadTable(this.value)"></select>
                    </div>
                </div>
                <div class="col-sm-2">
                    <div class="col-sm-12">
                        <input type="text" id="attendance-search-table" class="form-control form-control-sm pull-right" placeholder="Search">
                    </div>
                </div>
            </div>
            <div class="table-responsive mt-3">
                <table class="table align-middle w-100" id="attendanceAdminTable" style="color: #515B73!important;">
                    <thead class="table-primary">
                        <tr class="table_heading"> 
                            <td class="text-center" id="user_name">User</td>
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

let column;
document.addEventListener("DOMContentLoaded", () => {
    makeOrdinalColumn();
    getAllFilterData();
    column = Array.from(document.querySelectorAll("#attendanceAdminTable thead.table-primary tr.table_heading td")).reduce((acc,param) => {
        let obj = {
            data : param.id , 
            render : (data,type,row) => (param.id == 'user_name') ? makeUserColumnCell(data,row.user_image) : makeSingleCell(data)  
        };
        acc.push(obj);
        return acc;
    } , []);
    attendanceAdminSetting.columns = column;
    $("#attendanceAdminTable").DataTable(attendanceAdminSetting);
})

var attendanceAdminSetting = {
    'processing' : true,
    'serverSide' : true,
    'serverMethod' : 'POST',
    'searching' : false,
    'lengthChange' : false,
    'ajax': {
        url : '/app/attendance_system/attendance/attendanceAdminServer', 
        type : 'POST',
        data : function(d) {
            d.searchText = document.getElementById("attendance-search-table").value;
            d.month = document.getElementById("month_filter").value;
            d.year = document.getElementById("year_filter").value;
        }  
    },
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

function makeUserColumnCell(user_name,user_image) {
    let user = makeContent(user_name);
    return `<div class="d-flex flex-column align-items-center gap-1 cursor-pointer">
        <img src="${user_image}" class="rounded-circle" width="44" height="44" alt="">
        <div class = "table_heading" style="color:#676363">${user}</div>
    </div>`;
}

function makeSingleCell(cell_data) {
    if(cell_data != null) {
        let attendance_details = cell_data.split("@@@@").reduce((acc,param) => {
            let [key,value] = param.split("=>");
            acc[key] = value;
            return acc;
        } , {});
        attendance_details.statusbg = attendance_details.status.toLowerCase().split(" ").join("");
        return `<div class = "d-flex justify-content-center align-item-center flex-column cell_style">
            <div class="mb-2">	
                <span class="badge badge-${attendance_details['statusbg']} badge_style cursor-pointer" style = "min-width:100px;" onclick = "showAllAttendanceDetails(${attendance_details['attendance_id']})">${attendance_details['status']}</span>
            </div>
            <div class = "d-flex gap-1">
                <div>	
                    <span class="badge badge-time badge_style" title="In Time" data-bs-toggle="tooltip">${attendance_details['in_time']}</span>
                </div>
                <div>	
                    <span class="badge badge-time badge_style" title="Out Time" data-bs-toggle="tooltip">${attendance_details['out_time']}</span>
                </div>
            </div>
        </div>`;
    } else {
        return `<div class="badge empty-badge badge_style">Not Uploaded</div>`;
    }
}

function reloadTable(value) {
    $("#attendanceAdminTable").DataTable(attendanceAdminSetting);
}

function getAllFilterData() {
    Array.from(document.getElementById("filter_container").querySelectorAll("select")).forEach((param) => {
        let filter = param.id.split("_")[0];
        filter = filter.charAt(0).toUpperCase() + filter.slice(1);
        let functionName = `make${filter}DropDown`;
        if (typeof window[functionName] === 'function') {
            window[functionName]();
        } else {
            console.warn(`Function ${functionName} is not defined`);
        }
        $('#'+param.id).select2({
            placeholder: 'Choose ' + filter,
            allowClear: true,
            width: '100%'
        })
    });
}

function makeMonthDropDown() {
    let month = document.getElementById("month_filter");
    let current_date = new Date();
    const selected = current_date.toLocaleDateString("default", {month : 'long'});
    for(let i = 0; i < 12 ; i++ ) {
        const date = new Date(2000, i);
        const monthName = date.toLocaleString('default', { month: 'long' });
        month.append(createOptionTag(i+1,monthName,selected));
    }
}

function makeYearDropDown() {
    let year = document.getElementById("year_filter");
    const date = new Date();
    let yearVal = date.toLocaleDateString("default" , {year : 'numeric'});
    const selected = yearVal;
    let i = 0;
    while(i < 2) {
        year.append(createOptionTag(yearVal,yearVal,selected));
        yearVal++;
        i++;
    }
}

function createOptionTag(value,name,selected = '') {
    let option = document.createElement("option");
    option.value = value;
    option.innerText = name;
    if(selected != '' && selected == name ) {
        console.log(value);
        option.selected = true; 
    }
    return option;
}

function makeOrdinalColumn() {
    let tableHeading = document.getElementsByClassName("table_heading")[0];
    let a = 1;
    while (a <= 31) {
        tableHeading.insertAdjacentHTML('beforeend',getOrdinal(a));
        a++;
    }
}

async function showAllAttendanceDetails(id) { 
    let url = "/app/attendance_system/attendance/showAllAttendanceDetails";
    const data = await postMethodWithTextResponse(url,{id});
    if (data != null) {
        $('#md-modal-content').html(data);
        $('#mdmodal').modal('show');
    }
}

function getOrdinal(n) {
    const s = {
        1 : 'st' , 
        2 : 'nd' ,
        3 : 'rd' ,
        other : 'th'
    }
    
    if( n >= 10 && n <= 20) {
        return `<td class="text-center" id="${n}">${n}<sup>${s['other']}</sup></td>`;
    } else {
        let mod = n % 10;
        if( mod == 1 || mod == 2 || mod == 3) {
            return `<td class="text-center" id="${n}">${n}<sup>${s[mod]}</sup></td>`;
        } else {
            return `<td class="text-center" id="${n}">${n}<sup>${s['other']}</sup></td>`;
        }
    }
}

async function openImportAttendanceModule() {
    let url = "/app/attendance_system/attendance/attendanceSheetUpload";
    const data = await getMethod(url);
    if (data != null) {
        $('#md-modal-content').html(data);
        $('#mdmodal').modal('show');
    }
}

// Debounce on serach 
var timer;
document.getElementById("attendance-search-table").addEventListener('input', (event) => {
    if(timer) clearTimeout(timer);
    timer = setTimeout(() => $("#attendanceAdminTable").DataTable(attendanceAdminSetting), 1000);
});

</script>