<?php
## Database configuration
include '../../includes/db-config.php';
session_start();

$ticketCategory_details = [];

$data_field = file_get_contents('php://input'); // by this we get raw data
$data_field = json_decode($data_field,true);

if(isset($data_field['ticketCategory_id'])) {
    $id = mysqli_real_escape_string($conn,$data_field['ticketCategory_id']);
    $ticket = $conn->query("SELECT * FROM `ticket_category` WHERE id = '$id'");
    $ticketCategory_details = mysqli_fetch_assoc($ticket);
    $assignErpRole = '';
    if (!empty($ticketCategory_details['erpRole'])) {
        $assignErpRole = json_decode($ticketCategory_details['erpRole'],true);
        $assignErpRole = implode('@@',$assignErpRole);
    }
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
            <h5 class="mb-0"><?php echo !empty($ticketCategory_details) ? "Update" : "Create" ?> Ticket Category</h5>
            <a type="button" class="close" data-dismiss="modal" id = "hide-modal" aria-hidden="true"><i class="bi bi-x-circle-fill"></i></a>
        </div>
        <hr/> 
        <form role="form" id="form-ticketCategory" action="/app/ticketCategory/storeAndUpdateTicketCategory" method="POST">
            <div class="row mb-1">
                <label class="col-sm-4 col-form-label">Category Name</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control form-control-sm" name="name" value="<?php echo !empty($ticketCategory_details) ? $ticketCategory_details['name'] : "" ?>" placeholder="Enter Category Name"> 
                </div>
            </div>
            <div class="row mb-2">
                <label class="col-sm-4 col-form-label">Department</label>
                <div class="col-sm-8">
                    <select type="text" class="form-control form-control-sm single-select select2" name="department" id="department" <?php if(!empty($ticketCategory_details)) : ?> disabled <?php endif;?>> 
                    </select>
                </div>
            </div>
            <div class="row mb-2">
                <label class="col-sm-4 col-form-label">Assing ERP Role</label>
                <div class="col-sm-8">
                    <select type="text" class="form-control form-control-sm multiple-select select2" multiple="multiple" name="erpRole[]" id="erpRole" <?php if(!empty($ticketCategory_details)) : ?> disabled <?php endif;?>> 
                    </select>
                </div>
            </div>
            <div class="row mb-2">
                <label class="col-sm-4 col-form-label">Multiple Assignation</label>
                <div class="col-sm-8 mt-3 gap-1">
                    <input class="form-check-input" type="radio" name="multiple_assignation" value="1" <?php if(!empty($ticketCategory_details) && $ticketCategory_details['multiple_assignation'] == '1') : ?> checked <?php endif;?>>
                    <label class="form-check-label">Allow</label>
                    <input class="form-check-input" type="radio" name="multiple_assignation" value="0" <?php if(!empty($ticketCategory_details) && $ticketCategory_details['multiple_assignation'] == '0') : ?> checked <?php endif;?>>
                    <label class="form-check-label">Not Allow</label>
                </div>
            </div>
            <hr/>
            <div class="row mb-2">
                <div class="col-sm-12 text-end">
                    <button type="submit" class="btn btn-primary">
                    <?php echo !empty($ticketCategory_details) ? "Update" : "Create" ?>
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
    <?php if(!empty($ticketCategory_details) && !empty($assignErpRole)) { ?>
        createErpRoleOption('<?=$assignErpRole?>');
    <?php } else { ?>
        createErpRoleOption();
    <?php } ?>
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
    let selectedDepartment = '';
    <?php if(!empty($ticketCategory_details)) : ?>
        selectedDepartment = '<?=$ticketCategory_details['department']?>';
    <?php endif;?>
    const data = await fetchData("/app/tickets/getInputFieldData",{
        0 : {
            name : "department" ,
            value : selectedDepartment
        }
    });
    for (const key in data) {
        document.getElementById("department").innerHTML = data[key];
    }
}

function createErpRoleOption(slectedRole = null) {
    let erprole = ['Administrator','Center','Sub-Center','Student','University Head','Operations','Counsellor','Sub-Counsellor'];
    let selectedRoleArr = [];
    if (slectedRole !== null && slectedRole !== "") {
        selectedRoleArr = slectedRole.split("@@");
    }
    let select = document.getElementById("erpRole");
    let optionTag = erprole.map((param) =>  {
        let option = document.createElement("option");
        option.value = param;
        option.textContent = param;   
        if(selectedRoleArr.includes(param)) {
            option.setAttribute("selected","true");
        }
        select.append(option);
    });
}

$(function(){
    $('#form-ticketCategory').validate({
    rules: {
        name: {required:true},
        department : {required:true},
        multiple_assignation : {required:true}, 
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

$("#form-ticketCategory").on('submit',function(e){
    e.preventDefault();
    if ($("#form-ticketCategory").valid()) {
        var formData = new FormData(this);
        <?php if(!empty($ticketCategory_details)) { ?>
            formData.append("ID",<?=$data_field['ticketCategory_id']?>);
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