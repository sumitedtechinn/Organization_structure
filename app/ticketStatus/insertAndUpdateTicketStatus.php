<?php
## Database configuration
include '../../includes/db-config.php';
session_start();

$ticketStatus_details = [];
$ticketStatus_departmetnsList = '';

$data_field = file_get_contents('php://input'); // by this we get raw data
$data_field = json_decode($data_field,true);

if(isset($data_field['ticketStatus_id'])) {
    $id = mysqli_real_escape_string($conn,$data_field['ticketStatus_id']);
    $ticket = $conn->query("SELECT * FROM `ticket_status` WHERE id = '$id'");
    $ticketStatus_details = mysqli_fetch_assoc($ticket);
    $ticketStatus_departmetns = json_decode($ticketStatus_details['department'],true);
    $ticketStatus_departmetnsList = implode(',',$ticketStatus_departmetns);
}

?>
<style>

.select2-container {
    z-index: 999999 !important;
}

.iti__country-list{
    z-index: 9999999 !important;
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
            <h5 class="mb-0"><?php echo !empty($ticketStatus_details) ? "Update" : "Create" ?> Ticket Status</h5>
            <a type="button" class="close" data-dismiss="modal" id = "hide-modal" aria-hidden="true"><i class="bi bi-x-circle-fill"></i></a>
        </div>
        <hr/> 
        <form role="form" id="form-ticketStatus" action="/app/ticketStatus/storeAndUpdateTicketStatus" method="POST">
            <div class="row mb-1">
                <label class="col-sm-4 col-form-label">Status Name</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control form-control-sm" name="name" value="<?php echo !empty($ticketStatus_details) ? $ticketStatus_details['name'] : "" ?>" placeholder="Enter Status Name"> 
                </div>
            </div>
            <div class="row mb-2">
                <label class="col-sm-4 col-form-label">Department</label>
                <div class="col-sm-8">
                    <select type="text" class="form-control form-control-sm multiple-select select2" multiple="multiple" name="department[]" id="department"> 
                    </select>
                </div>
            </div>
            <div class="row mb-1">
                <label class="col-sm-4 col-form-label">Select Status Colour</label>
                <div class="col-sm-8">
                    <input type="color" class="form-control form-control-color" name="color" title="Choose your color" value = "<?php echo !empty($ticketStatus_details) ? $ticketStatus_details['color'] : "" ?>">
                </div>
            </div>
            <hr/>
            <div class="row mb-2">
                <div class="col-sm-12 text-end">
                    <button type="submit" class="btn btn-primary">
                    <?php echo !empty($ticketStatus_details) ? "Update" : "Create" ?>
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

$(document).ready(function () {
    getDepartmentList();
});

async function fetchData(url,params) {
    try {
        const response = await fetch(url,{
            method : "POST" , 
            body : JSON.stringify(params)
        });
        if(!response.ok) {
            console.error(`HTTP error! Status: ${response.status}`);
        }
        const data = await response.json();
        return data;
    } catch (error) {
        console.error("There is problem with fetch operation.",error);
        return null;
    }
}

async function getDepartmentList() {
    let selectedDepartments = '';
    <?php if(!empty($ticketStatus_departmetnsList)) : ?>
        selectedDepartments = '<?=$ticketStatus_departmetnsList?>';
    <?php endif;?>
    const data = await fetchData("/app/tickets/getInputFieldData",{
        0 : {
            name : "ticketStatusDepartment" ,
            value : selectedDepartments
        }
    });
    for (const key in data) {
        document.getElementById("department").innerHTML = data[key];
    }
}

$(function(){
    $('#form-ticketStatus').validate({
    rules: {
        name: {required:true},
        department : {required:true},
        color : {required:true}
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

$('#hide-modal').click(function() {
    $('.modal').modal('hide');
});

$("#form-ticketStatus").on('submit',function(e){
    e.preventDefault();
    if ($("#form-ticketStatus").valid()) {
        var formData = new FormData(this);
        <?php if(!empty($ticketStatus_details)) { ?>
            formData.append("ID",<?=$data_field['ticketStatus_id']?>);
        <?php } ?>
        $.ajax({
            url: this.action,
            type: 'POST',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(data) {
                if (data.status == 200) {
                    $('.modal').modal('hide');
                    toastr.success(data.message);
                    $('.table').DataTable().ajax.reload(null, false);
                } else {
                    toastr.error(data.message);
                }
            }
        });
    }
});

</script>