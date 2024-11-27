<?php 
include '../../includes/db-config.php';
session_start();

$designation_inside_option = '<option value = "" >Select</option>';
$designation_inside = $conn->query("SELECT * FROM `designation_inside`");
while($row = mysqli_fetch_assoc($designation_inside)) {
    $designation_inside_option .= '<option value = "'.$row['ID'].'" class = "text-muted">'.$row['designation_inside_type'].'</option>';
}

?>

<style>
    .select2-container {
        z-index: 999999 !important;
    }
    .error {
        color: red;
        font-size: small;
    }
    .hide {
        display: none !important;
    }

    .show {
        display: block !important;
    }
</style>


<div class="card-body">
    <div class="border p-4 rounded">
        <div class="card-title d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Select Designation Type</h5>
            <a type="button" class="close" data-dismiss="modal" id = "hide-modal" aria-hidden="true"><i class="bi bi-x-circle-fill"></i></a>
        </div>
        <hr/>
        <div class="row row-cols-1 justify-content-center">
            <div class="card">
                <div class="row g-0">
                    <div class="col-md-5">
                        <img src="/assets/images/designation.png" class="card-img">
                    </div>
                    <div class="col-md-7">
                        <div class="card-body" style="margin-top: 3.5rem;margin-left: 2rem;" id = "designation_inside_type">
                            <div class="card-title text-muted" style="font-weight: 500;">Designation create inside</div>
                            <div class="col-sm-10 card-text">
                                <div class="col-sm-12 shadow-lg bg-body rounded">
                                    <select type = "text" class = "form-control form-control-sm single-select select2" name = "designation_inside" id = "designation_inside" onchange = "checkSelectedData(this.value)">
                                        <?=$designation_inside_option?>
                                    </select>
                                </div>
                            </div>
                            <div class="hide" id="organization_box">
                                <div class="col-sm-10">
                                    <label class="col-sm-12 col-form-label text-muted" style="font-weight: 500;">Organization</label>
                                    <div class="col-sm-12 shadow-lg bg-body rounded">
                                        <select type = "text" class="form-control form-control-sm single-select select2" name="organization" id = "organization" onchange="getBranch(this.value)" required>
                                        </select>
                                    </div>
                                </div>
                                <span id="organization_empty" style="font-size: small;color: red;"></span>
                            </div>
                            <div class="hide" id = "branch_box">
                                <div class="col-sm-10">
                                    <label class="col-sm-12 col-form-label text-muted" style="font-weight: 500;">Branch</label>
                                    <div class="col-sm-12 shadow-lg bg-body rounded">
                                        <select type="text" class="form-control form-control-sm single-select select2" name="branch" id="branch" onchange="getVertical(this.value)" >
                                        </select>
                                    </div>
                                </div>
                                <span id="branch_empty" style="font-size: small;color: red;"></span>
                            </div>
                            <div class="hide" id = "vertical_box">
                                <div class="col-sm-10">
                                    <label class="col-sm-12 col-form-label text-muted" style="font-weight: 500;">Vertical</label>
                                    <div class="col-sm-12 shadow-lg bg-body rounded">
                                        <select type="text" class="form-control form-control-sm single-select select2" name="vertical" id="vertical" required>
                                        </select>
                                    </div>
                                </div>
                                <span id="vertical_empty" style="font-size: small;color: red;"></span>
                            </div>   
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-primary text-white" style="background-color: #0374ff !important;" id = "gotoCreatepage_button" disabled >Next <i class="bi bi-arrow-right-circle"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>


<script src="/assets/plugins/jquery-validation/js/jquery.validate.js"></script>
<script src="/assets/plugins/select2/js/select2.min.js"></script>
<script src="/assets/js/form-select2.js"></script>

<script type="text/javascript">

function getOrganizationOption() {
    $.ajax({
        url : "/app/common/organizationList",
        type : "post",
        data : {},
        success : function(data) {
            $("#organization").html(data);
        }
    });
}

function getBranch(organization_id) {
    $.ajax({
        url : "/app/common/branchList",
        type : "post",
        data : {
            organization_id
        },
        success : function(data) {
            $("#branch").html(data);
        }
    });
}

function getVertical(branch) {
    var organization_id = $("#organization").val();
    $.ajax({
        url : "/app/common/verticalList",
        type : "post",
        data : {
            organization_id,
            branch
        },
        success : function(data) {
            $("#vertical").html(data);
        }
    });
}

function checkSelectedData(designation_inside) {
    if(designation_inside == '')  { 
        $("#gotoCreatepage_button").prop('disabled',true);
    } else {
        $("#gotoCreatepage_button").prop('disabled',false);
    }
    if(designation_inside == '1') {
        if($('#organization_box').hasClass('show')) $('#organization_box').removeClass('show').addClass('hide'); 
        if($('#branch_box').hasClass('show')) $('#branch_box').removeClass('show').addClass('hide');
        if($('#vertical_box').hasClass('show')) $('#vertical_box').removeClass('show').addClass('hide');
        $("#designation_inside_type").css('margin-top', '3.5rem');
    } else if (designation_inside == '2') {  
        if ($('#organization_box').hasClass('hide')) {
            $('#organization_box').removeClass('hide').addClass('show'); // Show the filter
            $("#designation_inside_type").css('margin-top', '2rem');
        } 
        if($('#branch_box').hasClass('show')) $('#branch_box').removeClass('show').addClass('hide');
        if($('#vertical_box').hasClass('show')) $('#vertical_box').removeClass('show').addClass('hide');
        getOrganizationOption();
    } else if (designation_inside == '3') {
        if ($('#branch_box').hasClass('hide')) {
            if($('#organization_box').hasClass('hide')) $('#organization_box').removeClass('hide').addClass('show');
            $('#branch_box').removeClass('hide').addClass('show'); // Show the filter
            $("#designation_inside_type").css('margin-top', '1rem');
        }
        if($('#vertical_box').hasClass('show')) $('#vertical_box').removeClass('show').addClass('hide');
        getOrganizationOption();
    } else if (designation_inside == '4') {
        if ($('#vertical_box').hasClass('hide')) {
            if($('#organization_box').hasClass('hide')) $('#organization_box').removeClass('hide').addClass('show');
            if($('#branch_box').hasClass('hide')) $('#branch_box').removeClass('hide').addClass('show'); // Show the filter
            $('#vertical_box').removeClass('hide').addClass('show');
            $("#designation_inside_type").css('margin-top', '0rem');
        }
        getOrganizationOption();
    } 
}

function showAndHideFilter() {
    if ($('#advance_filter').hasClass('hide')) {
        $('#advance_filter').removeClass('hide').addClass('show'); // Show the filter
        $("#projection_summary").css('margin-top', '0rem');
    } else {
        $('#advance_filter').removeClass('show').addClass('hide'); // Hide the filter
        $("#projection_summary").css('margin-top', '0.6rem');
    }
}

$("#gotoCreatepage_button").on('click',function(){
    var data = {};
    var designation_inside = $("#designation_inside").val();
    if(designation_inside == '1') {
        data.designation_inside = designation_inside;
        gotoCreatePage(data);
    } else if (designation_inside == '2') {
        let organization = $("#organization").val();
        if(organization != '') {
            $("#organization_empty").html("");
            data.designation_inside = designation_inside;
            data.organization_id = organization;
            gotoCreatePage(data);
        } else {
            $("#organization_empty").html("please selecte the organization");
            return;
        }
    } else if (designation_inside == '3') {
        let organization = $("#organization").val();
        let branch = $("#branch").val();
        if(organization != '' && branch != '') {
            $("#organization_empty").html("");
            $("#branch_empty").html("");
            data.designation_inside = designation_inside;
            data.organization_id = organization;
            data.branch_id = branch;
            gotoCreatePage(data);
        } else {
            if($("#organization").val() == '') $("#organization_empty").html("please selecte the organization"); 
            if($("#branch").val() == '') $("#branch_empty").html("please selecte the branch");
            return;
        }
    } else if (designation_inside == '4')  {
        let organization = $("#organization").val();
        let branch = $("#branch").val();
        let vertical = $("#vertical").val();
        if(organization != '' && branch != '' && vertical != '') {
            $("#organization_empty").html("");
            $("#branch_empty").html("");
            $("#vertical_empty").html("");
            data.designation_inside = designation_inside;
            data.organization_id = organization;
            data.branch_id = branch;
            data.vertical_id = vertical;
            gotoCreatePage(data);
        } else {
            if($("#organization").val() == '') $("#organization_empty").html("please selecte the organization"); 
            if($("#branch").val() == '') $("#branch_empty").html("please selecte the branch");
            if($("#vertical").val() == '') $("#vertical_empty").html("please selecte the vertical");
            return;
        }
    }
});

function gotoCreatePage(data) {
    $('.modal').modal('hide');
    setTimeout(function(){
        $.ajax({
            url : "/app/designation/insertAndupdateDesignation",
            type : "post", 
            data : data,
            success : function(data) {
                $('#lg-modal-content').html(data);
                $('#lgmodal').modal('show');
            }
        });
    },500);
}

$('#hide-modal').click(function() {
    $('.modal').modal('hide');
});
</script>