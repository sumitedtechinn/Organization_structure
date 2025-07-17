<?php 
$data = file_get_contents('php://input');
if(!empty($data)) {
    $_REQUEST = json_decode($data,true);
}
?>

<style>
label{
    font-weight: 500;
}
.remove-btn {
    color: black;
    cursor: pointer;
    transition: color 0.3s ease;
}
.remove-btn:hover {
    color: red;
}
.error {
    color: red;
    font-size: small;
}
</style>

<div class="card-body">
    <div class="border p-4 rounded">
        <div class="card-title d-flex align-items-center justify-content-between">
            <h6 class="mb-0" id="model_heading"></h6>
            <a type="button" class="close" data-dismiss="modal" id = "hide-modal" aria-hidden="true"><i class="bi bi-x-circle-fill"></i></a>
        </div>
        <hr/>
        <form role="form" id="form-attendanceSetting" action="/app/attendance_system/attendanceSetting/fetchAndStoreAttendanceSetting" method="POST">
            <div class="row mb-1">
                <div class="col-sm-6">
                    <label for="organization" class="col-form-label">Organization</label>
                    <div class="col-sm-12">
                        <select type="text" class="form-control form-control-sm single-select select2" name = "organization_id" id="organization_id"></select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <label for="weekoff" class="col-sm-12 col-form-label">Week Off</label>
                    <div class="d-flex gap-4 mt-1">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="week_off_saturday" name="week_off[]" value="saturday">
                            <span class="form-check-label" for="saturday">Saturday</span>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="week_off_sunday" name="week_off[]" value="sunday">
                            <span class="form-check-label" for="sunday">Sunday</span>
                        </div>
                    </div> 
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-sm-6">
                    <label for="in_time" class="col-form-label">Offical Timing</label>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="d-flex gap-2">
                                <label class="mt-1">In</label>
                                <input type="time" id="in_time" name="in_time" class="form-control form-control-sm" placeholder="In time">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex gap-2">
                                <label class="mt-1">Out</label>
                                <input type="time" id="out_time" name="out_time" class="form-control form-control-sm" placeholder="Out Time">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <label for="in_time" class="col-form-label">Relaxation Time</label>
                    <div class="col-sm-12">
                        <input class="form-control form-control-sm" type="time" id="relaxation_time" name="relaxation_time" placeholder="eg : 10:15">
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-between align-item-center mt-3">
                <div style="font-size: medium; font-weight: 500;">List Of Holiday</div>
                <div class="text-end">
                    <button type="button" class="btn btn-primary btn-sm" style="font-size: smaller;" id="addHoliday" onclick="insertHolidayRow()">Add Holiday</button>
                </div>
            </div>
            <div id="holiday_list_Container"></div>
            <hr/>
            <div class="row mb-2">
                <div class="col-sm-12 text-end">
                    <button type="submit" class="btn btn-primary btn-sm" id="buttonText"></button>
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

$(function(){
    $('#form-attendanceSetting').validate({
    rules: {
        organization_id: {required:true},
        week_off : {required:true},
        in_time : {required:true} , 
        out_time : {required:true},
        relaxation_time : {required: true}
    },
    highlight: function (element) {
        $(element).addClass('error');
        $(element).closest('.form-control').addClass('has-error');
    },
    unhighlight: function (element) {
        $(element).removeClass('error');
        $(element).closest('.form-control').removeClass('has-error');
    },
    errorPlacement: function (error, element) {
        if (element.attr("type") === "time" || element.parent('.input-group').length) {
            error.insertAfter(element.parent());
        } else if (element.is('select')) {
            error.insertAfter(element.next('.select2'));
        } else if (element.attr("type") === "checkbox") {
            error.insertAfter((element.parent()).parent());
        } else {
            error.insertAfter(element);
        }
    }
    });
})

async function fetchData(url , param) {
    let response = await fetch(url , {
        method : 'POST' , 
        body : param
    })
    if(!response.ok) {
        throw new Error(`Http Error Status : ${response.status}`); 
    } 
    const data = response.json();
    return data;
}

$(document).ready( async () => {
    window.row_count = 1;
    let url = "/app/attendance_system/attendanceSetting/fetchAndStoreAttendanceSetting";
    const data = await fetchData(url , JSON.stringify({
        id : "<?=$_REQUEST['id'] ?? '' ?>",
        method : 'checkAttendanceSetting'
    }))  
    if (data != null) {
        document.getElementById("buttonText").innerText = data.buttonText;
        document.getElementById("model_heading").innerText = data.model_heading;
        let formDataPresent = (Object.keys(data?.form_data).length > 0) ? true : false;
        let dropDownFiled = JSON.parse(data?.dropDownFiled);
        if(formDataPresent) {
            for (const key in data.form_data) {
                if(key != 'holiday') {
                    let formFiled = document.getElementById(key);
                    if(formFiled.tagName == 'INPUT') {
                        if(formFiled.type == 'checkbox') {
                            formFiled.checked = true;
                        } else {
                            formFiled.value = data.form_data[key];
                        }
                    } else if (formFiled.tagName == 'TEXTAREA') {
                        formFiled.innerHTML = data.form_data[key];
                    } else if (formFiled.tagName == 'SELECT') {
                        creatDropDown(dropDownFiled[key],key,data.form_data[key]);
                        formFiled.disabled = true;
                    }   
                } else {
                    let holidayList = JSON.parse(data.form_data[key]);
                    for (const name in holidayList) {
                        let row_number = row_count;
                        insertHolidayRow();
                        document.getElementById(`holiday_name_${row_number}`).value = name;
                        document.getElementById(`holiday_date_${row_number}`).value = holidayList[name];
                    }
                }
            }
        } else {
            for (const filedName in dropDownFiled) {
                creatDropDown(dropDownFiled[filedName],filedName,"");
                $(`#${filedName}`).select2({
                    placeholder: `Choose ${makePlaceHolder(filedName)}`,
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $('#lgmodal')
                });
            }
        }
    }
})

function creatDropDown(dropDownData,fieldName,selected) {
    let dropDownElement = document.getElementById(fieldName);
    dropDownElement.innerHTML = "";
    dropDownElement.append(document.createElement("option"));
    for (const id in dropDownData) {
        let option = document.createElement("option");
        option.innerText = dropDownData[id];
        option.value = id;
        if (selected != '' && selected == id) {
            option.setAttribute("selected","true");
        } 
        dropDownElement.append(option);
    } 
}

function insertHolidayRow() {

    let holiday_container = document.getElementById("holiday_list_Container");
    let hoilday_row = `<div class="row" id="holiday_row_${row_count}">
        <div class="col-sm-5">
            <label for="holiday_name_${row_count}" class="col-form-label">Holiday Name</label>
            <div class="col-sm-12">
                <input class="form-control form-control-sm" type="text" id="holiday_name_${row_count}" name="holiday_name_${row_count}" placeholder="eg : Holi">
            </div>
        </div>
        <div class="col-sm-5">
            <label for="holiday_date_${row_count}" class="col-form-label">Holiday Date</label>
            <div class="col-sm-12">
                <input class="form-control form-control-sm" type="date" id="holiday_date_${row_count}" name="holiday_date_${row_count}" placeholder="eg : select date">
            </div>
        </div>
        <div class="col-sm-2 font-22 remove-btn" style="margin-top:2rem;text-align: center;" id="remove_${row_count}" title="Remove" onclick="removeHoliday(this.id)">
            <i class="fadeIn animated bx bx-minus-circle"></i>
        </div>
    </div>`;

    holiday_container.insertAdjacentHTML("beforeend",hoilday_row);

    [`holiday_date_${row_count}`,`holiday_name_${row_count}`].forEach((param) => {
        let placeHolder = makePlaceHolder(param);
        $(`#${param}`).rules("add", {
            required: true,
            messages: {
                required: `Please select ${placeHolder}`
            }
        });
    })
    ++row_count;
}

function makePlaceHolder(param) {
    if (param.includes("_")) {
        var placeHolder = param.split("_").map((param,index,array) => (index != array.length-1) ? param.charAt(0).toUpperCase() + param.slice(1,param.length) : "").join(" ");
    } else {
        var placeHolder = param.charAt(0).toUpperCase() + param.slice(1);
    }
    return placeHolder;
}

function removeHoliday(id) {
    let row_num = id.split("_")[1];
    document.getElementById(`holiday_row_${row_num}`).remove();
}

document.getElementById("form-attendanceSetting").addEventListener("submit" , async function(e) {
    e.preventDefault();
    const form = document.getElementById("form-attendanceSetting");
    if(form.checkValidity()) {
        form.querySelectorAll(':disabled').forEach((param) => param.disabled = false);
        let fromData = new FormData(this);
        fromData.append("method","insertOrUpdate");
        fromData.append("id",'<?=$_REQUEST['id'] ?? "" ?>')
        const data = await fetchData(this.action , fromData);
        if (data.status == 200) {
            $('.modal').modal('hide');
            toastr.success(data.message);
            $('#attendanceSettingTable').DataTable().ajax.reload(null, false);
        } else {
            toastr.error(data.message);
        }   
    }
});
</script>
