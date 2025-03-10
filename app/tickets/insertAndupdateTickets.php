<?php
## Database configuration
include '../../includes/db-config.php';
session_start();

$ticket_details = [];
if(isset($_REQUEST['ticket_id'])) {
    $id = mysqli_real_escape_string($conn,$_REQUEST['ticket_id']);
    $ticket = $conn->query("SELECT * FROM `ticket_record` WHERE id = '$id'");
    $ticket_details = mysqli_fetch_assoc($ticket);
}
?>
<style>

.select2-container {
    z-index: 999999 !important;
}

.iti__country-list{
    z-index: 9999999 !important;
}

.label_text{
    font-weight: 500;
    font-size: 16px;
}

.error {
    color: red;
    font-size: small;
}
</style>

<!-- Modal -->
<div class="card-body" >
    <div class="border p-4 rounded">
        <div class="card-title d-flex align-items-center justify-content-between">
            <h5 class="mb-0"><?php echo !empty($ticket_details) ? "Update" : "Create" ?> Ticket</h5>
            <a type="button" class="close" data-dismiss="modal" id = "hide-modal" aria-hidden="true"><i class="bi bi-x-circle-fill"></i></a>
        </div>
        <hr/>
        <form role="form" id="form-ticket" action="/app/tickets/storeAndupdateTicket" method="POST">
            <div class="row mb-1">
                <div class="col-sm-6 ">
                    <label for="task_name" class="col-sm-12 col-form-label label_text">Task Name</label>
                    <div class="col-sm-12">
                        <input type="text" class="form-control form-control-sm" name="task_name" value="<?php echo !empty($ticket_details) ? $ticket_details['task_name'] : "" ?>" placeholder="Enter Task Name">
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label label_text">Department</label>
                    <div class="col-sm-12">
                        <select type="text" class="form-control form-control-sm single-select select2" name="ticket_department" id="ticket_department" onchange="getCategory(this.value)"></select>
                    </div>
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label label_text">Priority</label>
                    <div class="col-sm-12">
                        <select type="text" class="form-control form-control-sm single-select select2" name="ticket_priority" id="ticket_priority"></select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label label_text">Category</label>
                    <div class="col-sm-12">
                        <select type="text" class="form-control form-control-sm single-select select2" name="ticket_category" id="ticket_category"></select>
                    </div>
                </div>
            </div>
            <div class="row mb-1">
                <label class="col-sm-12 col-form-label label_text">Task Description</label>
                <div class="col-sm-12">
                    <textarea class="form-control" name="task_description" id="task_description" rows="3"><?php echo !empty($ticket_details) ? $ticket_details['task_description'] : "" ?></textarea>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-12">
                    <label class="col-sm-12 col-form-label label_text">Attachment</label>
                    <div class="col-sm-12">
                        <input class="form-control form-control-sm " type="file" accept="image/*" name="attachment">
                    </div>
                </div>
            </div>
            <hr/>
            <div class="row mb-2">
                <div class="col-sm-12 text-end">
                    <button type="submit" class="btn btn-primary">
                    <?php echo !empty($ticket_details) ? "Update" : "Create" ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="/assets/plugins/jquery-validation/js/jquery.validate.js"></script>
<script src="/assets/plugins/select2/js/select2.min.js"></script>
<script src="/assets/js/form-select2.js"></script>
<script type="text/javascript">  

$('#hide-modal').click(function() {
    $('.modal').modal('hide');
});

$(document).ready(function(){
    getInputDropDownData();
});

async function fetchData(url,params) {
    try {
        let response = await fetch(url,{
            method : "POST" , 
            body : JSON.stringify(params)
        });
        if(!response.ok) {
            console.error(`HTTP error! Status: ${response.status}`);
        }
        const data = response.json();
        return data;   
    } catch (error) {
        console.error('There has been a problem with your fetch operation:', error);
        return null;
    }
}

async function getInputDropDownData() {
    let dropDownFiled = {};
    <?php if(isset($_REQUEST['ticket_id']) && !empty($_REQUEST['ticket_id'])) { ?>
        let selectValues = {
            "selectedCategory" : '<?=$ticket_details['department']?>'+'_'+'<?=$ticket_details['category']?>',
            "selectedPriority" : '<?=$ticket_details['priority']?>',
            "selectedDepartment" : '<?=$ticket_details['department']?>'
        };
        dropDownFiled = [...document.querySelectorAll("#category,#priority,#department")].map((param) => {
            let tagName = selected + param.name.charAt(0).toUpperCase + param.name.slice(1);
            console.log(tagName);
            return { name : param.name , value : selectValues[tagName]}
        });
    <?php } else { ?>
        dropDownFiled = [...document.querySelectorAll("#ticket_priority,#ticket_department")].map((param) => ({name : (param.name.split("_").filter((e,index,array) => index == array.length-1))[0] , value : param.value}));
    <?php } ?>
    const data = await fetchData("/app/tickets/getInputFieldData",dropDownFiled);
    for (const key in data) {
        document.getElementById("ticket_"+key).innerHTML = data[key];
    }
}

async function getCategory(department_id) {
    let dropDownFiled = {
        0 : {
            name : "category" , 
            value : department_id+"_"
        }
    };
    const data = await fetchData("/app/tickets/getInputFieldData",dropDownFiled);
    for (const key in data) {
        document.getElementById("ticket_"+key).innerHTML = data[key];
    }
}

$(function(){
    $('#form-ticket').validate({
    rules: {
        task_name: {required:true},
        ticket_category : {required:true},
        ticket_priority : {required:true},
        ticket_department : {required:true},
        task_description : {required:true},
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
        if (element.parent('.input-group').length) {
            error.insertAfter(element.parent());
        } else if (element.is('select')) {
            error.insertAfter(element.next('.select2'));
        } else {
            error.insertAfter(element);
        }
    }
    });
})

$("#form-ticket").on("submit",function(e){
    e.preventDefault();
    if($("#form-ticket").valid()) {
        var formData = new FormData(this);
        <?php if(!empty($ticket_details)) { ?>
            formData.append("ticket_id",'<?=$_REQUEST['ticket_id']?>');
        <?php } ?>
        formData.append("requestfrom","edtech");
        $("#spinner").css('display','block');
        $.ajax({
            url: this.action,
            type: 'post',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(data) {
                $("#spinner").css('display','none');
                if (data.status == 200) {
                    $('.modal').modal('hide');
                    toastr.success(data.message);
                    $('.table').DataTable().ajax.reload(null, false);
                    setTimeout(function(){
                        let ticket_id = document.getElementById("view_btn_1").getAttribute("data-value");    
                        viewTicketDetails(ticket_id);
                    },250);
                } else {
                    $('.modal').modal('hide');
                    toastr.error(data.message);
                }
            }
        });
    }
})
</script>