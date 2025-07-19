<style>
    label {
        font-weight: 500;
    }
    .error {
        color: red;
        font-size: small;
    }
</style>
<div class="card-body">
    <div class="border p-4 rounded">
        <div class="card-title d-flex align-items-center justify-content-between">
            <h6 class="mb-0" id="model_heading">Upload Attendance</h6>
            <a type="button" class="close" data-dismiss="modal" id = "hide-modal" aria-hidden="true"><i class="bi bi-x-circle-fill"></i></a>
        </div>
        <hr/>
        <form role="form" id="form-uploadAttendanceSheet" action="/app/attendance_system/attendance/insertAttendanceSheetData" method="POST" enctype="multipart/form-data">
            <div class="row">
                <label for="name" class="col-form-label">Upload Attendance Sheet</label>
                <div class="col-sm-12">
                    <input class="form-control form-control-sm" type="file" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" id="attendance_sheet" name="attendance_sheet" required>
                </div>
            </div>
            <hr/>
            <div class="row mb-2">
                <div class="col-sm-12 text-end">
                    <button type="submit" class="btn btn-primary btn-sm" id="buttonText">Upload</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="/assets/plugins/jquery-validation/js/jquery.validate.js"></script>
<script type="text/javascript">

$('#hide-modal').click(function() {
    $('.modal').modal('hide');
});

$("#form-uploadAttendanceSheet").on("submit", function () {
    $('.modal').modal('hide');
    setTimeout(() => {
        $("#attendanceAdminTable").DataTable(attendanceAdminSetting);
    } , 1000);
});

$(function(){
    $('#form-uploadAttendanceSheet').validate({
    rules: {
        attendance_sheet: {required:true},
    },
    highlight: function (element) {
        $(element).addClass('error');
        $(element).closest('.form-control').addClass('has-error');
    },
    unhighlight: function (element) {
        $(element).removeClass('error');
        $(element).closest('.form-control').removeClass('has-error');
    },
    });
})

// async function fetchData(url , param) {
//     let response = await fetch(url , {
//         method : 'POST' , 
//         body : param
//     })
//     if(!response.ok) {
//         throw new Error(`Http Error Status : ${response.status}`); 
//     } 
//     const data = await response.text();
//     return data;
// }

// document.getElementById("form-uploadAttendanceSheet").addEventListener("submit" , async function(e) {
//     e.preventDefault();
//     const form = document.getElementById("form-uploadAttendanceSheet");
//     if(form.checkValidity()) {
//         let fromData = new FormData(this);
//         fromData.append("method","insertAttendanceData");
//         const data = await fetchData(this.action , fromData);
//         if (data.status == 200) {
//             $('.modal').modal('hide');
//             toastr.success(data.message);
//             //$('#attendanceSettingTable').DataTable().ajax.reload(null, false);
//         } else {
//             toastr.error(data.message);
//         }   
//     }
// })

</script>