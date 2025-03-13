<?php include($_SERVER['DOCUMENT_ROOT'].'/app/tickets/fetchTicketInformation.php'); ?>
<div class="card-header pt-2">
    <h5>Ticket Details</h4>
</div>
<div class="card-body p-0">
    <div class="border-bottom p-3">
        <div class="mb-3">
            <label class="form-label sidebarHeader">Priority</label>
            <select type="text" class="form-control form-control-sm single-select select2" name="priority" id="priority" data-custom-value = "<?=$ticket_details['priority_value']?>" onchange="updateTicketInfo(this.value,'<?=$id?>','updatePriority')" <?=$validationForNewStatus?> <?=$validationOnCategoryDepartmentAndPriority?>></select>
        </div>
        <div class="mb-3">
            <label class="form-label sidebarHeader">Ticket Status</label>
            <select type="text" class="form-control form-control-sm single-select select2" name="status" id="status" data-custom-value = "<?=$ticket_details['department']?>_<?=$ticket_details['status_value']?>" onchange="updateTicketInfo(this.value,'<?=$id?>','updateStatus')" <?=$validationForNewStatus?> <?=$validationForStatus?> ></select>
        </div>
        <div class="mb-3">
            <label class="form-label sidebarHeader">Ticket Category</label>
            <select type="text" class="form-control form-control-sm single-select select2" name="category" id="category" data-custom-value = "<?=$ticket_details['department']?>_<?=$ticket_details['category']?>" onchange="updateTicketInfo(this.value,'<?=$id?>','updateCategory')" <?=$validationForNewStatus?> <?=$validationOnCategoryDepartmentAndPriority?>></select>
        </div>
        <div class="mb-3">
            <label class="form-label sidebarHeader">Department</label>
            <select type="text" class="form-control form-control-sm single-select select2" name="department" id="department" data-custom-value="<?=$ticket_details['department']?>" onchange="updateTicketInfo(this.value,'<?=$id?>','updateDepartment')" <?=$validationForNewStatus?> <?=$validationOnCategoryDepartmentAndPriority?>></select>
        </div>
        <div class="mb-0">
            <label class="form-label sidebarHeader">Assign To</label>
            <select type="text" class="form-control form-control-sm single-select select2" name="assignTo" id="assignTo" data-custom-value = "<?=$ticket_details['department']?>_<?=$ticket_details['assign_to']?>" onchange="updateTicketInfo(this.value,'<?=$id?>','updateAssignToUser')" <?=$validationForNewStatus?> <?=$validationForAssignTo?>></select>
        </div>
    </div>
    <div class="d-flex align-items-center border-bottom p-3 gap-3">
        <img src="<?=$assignByUser_details['image']?>" class="rounded-circle" alt="Img" style="height: 65px; width: 65px;">
        <div>
            <div class="fw-semibold text-muted">Assign By</div>
            <div class="sideBarSmallHeader"><?=$assignByUser_details['name']?></div>
            <div class="text-muted"><?=$assignByUser_details['designation']?></div>
        </div>
    </div>

    <div class="d-flex align-items-center border-bottom p-3 gap-3">
        <img src="<?=$assignToUser_details['image']?>" class="rounded-circle" alt="Img" style="height: 65px; width: 65px;">
        <div>
            <div class="fw-semibold text-muted">Assign To</div>
            <div class="sideBarSmallHeader"><?=$assignToUser_details['name']?></div>
            <div class="text-muted"><?=$assignToUser_details['designation']?></div>
        </div>
    </div>

    <div class="border-bottom p-3">
        <div class="fw-bold text-primary">Ticket Created By</div>
        <div class="text-muted"><span class="fw-semibold">Name:</span> <?=$ticket_details['create_person_name']?></div>
        <div class="text-muted"><span class="fw-semibold">Email:</span> <?=$ticket_details['create_person_email']?></div>
        <div class="text-muted"><span class="fw-semibold">Contact No.:</span> <?=$ticket_details['create_person_number']?></div>
    </div>
</div>
