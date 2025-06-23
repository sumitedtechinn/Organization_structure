<?php
## Database configuration
include '../../includes/db-config.php';
session_start();

$branch_details = [];
if (isset($_REQUEST['branch_id']) && !empty($_REQUEST['branch_id'])) {
    $branch = $conn->query('SELECT * FROM `Branch` where Branch.ID = '.$_REQUEST['branch_id']);
    $branch_details = mysqli_fetch_assoc($branch);
}

?>
<style>    

    .iti__country-list{
        z-index: 9999999 !important;
    }

    .error {
        color: red;
        font-size: small;
    }
</style>

<!-- Modal -->
<div class="card-body">
    <div class="border p-4 rounded">
        <div class="card-title d-flex align-items-center justify-content-between">
            <h5 class="mb-0">
                <?php if(!empty($branch_details)) { ?>
                    Update Branch
                <?php } else { ?>
                    Add Branch
                <?php }?>
            </h5>
            <a type="button" class="close" data-dismiss="modal" id = "hide-modal" aria-hidden="true"><i class="bi bi-x-circle-fill"></i></a>
        </div>
        <hr/>
        <form role="form" id="form-branch" action="/app/branch/storeAndupdateBranch.php" method="POST">
            <div class="row mb-1">
                <div class="col-sm-6">
                    <label for="name" class="col-sm-12 col-form-label">Name</label>
                    <div class="col-sm-12">
                        <input type="text" class="form-control form-control-sm" name="name" value="<?php echo (count($branch_details) > 0) ? $branch_details['Branch_name'] : '' ?>" placeholder="Enter Branch Name">
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Image</label>
                    <div class="col-sm-12">
                        <?php if(!empty($branch_details['image'])) { ?>
                            <img src="<?=$branch_details['image']?>" width="50px" class="mb-2" alt="uploaded image">
                        <?php } ?>
                        <input class="form-control form-control-sm " type="file" accept="image/*" name="image"> 
                    </div>
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Contact Number</label>
                    <div class="col-sm-12">
                        <input type="text" class="form-control form-control-sm" name="contact_number" id="contact_number" value="<?php echo (count($branch_details) > 0) ? $branch_details['Country_code'].$branch_details['Contact'] : '' ?>" placeholder="83XXXXXXXX" maxlength="15" onkeypress="return /[0-9]/i.test(event.key)">
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Organization</label>
                    <div class="col-sm-12">
                    <select type="text" class="form-control form-control-sm single-select select2" name="organization" onchange="validateUpdateOrganization(this.value)" id="organization" ></select>
                    </div>
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">PIN Code</label>
                    <div class="col-sm-12">
                        <input type="text" class="form-control form-control-sm" name="pin_code" value="<?php echo (count($branch_details) > 0) ? $branch_details['Pin_code'] : '' ?>" placeholder="Eg :- 11XXXX00" maxlength="6" onkeypress="return /[0-9]/i.test(event.key)">
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Country</label>
                    <div class="col-sm-12">
                        <select type="text" class="form-control form-control-sm single-select select2" value="<?php echo (count($branch_details) > 0) ? $branch_details['Country'] : '' ?>" name="country" id="country" onchange="getLocationList('state')"></select>
                    </div>
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">State</label>
                    <div class="col-sm-12">
                        <select type="text" class="form-control form-control-sm single-select select2" value="<?php echo (count($branch_details) > 0) ? $branch_details['State'] : '' ?>" name="state" id="state" onchange="getLocationList('city')"></select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">City</label>
                    <div class="col-sm-12">
                        <select type="text" class="form-control form-control-sm single-select select2" value="<?php echo (count($branch_details) > 0) ? $branch_details['City'] : '' ?>" name="city" id="city"></select>
                    </div>
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Start Date</label>
                    <div class="col-sm-12">
                        <input type="date" class="form-control form-control-sm" value="<?php echo (count($branch_details) > 0) ? $branch_details['Start_date'] : '' ?>" name="start_date">
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Address</label>
                    <div class="col-sm-12">
                        <textarea class="form-control" name="address" rows="1"><?php echo (count($branch_details) > 0) ? $branch_details['Address'] : '' ?></textarea>
                    </div>
                </div>
            </div>
            <hr/>
            <div class="row mb-2">
                <div class="col-sm-12 text-end">
                    <button type="submit" class="btn btn-primary"><?= (!empty($branch_details)) ? "Update" : "Register"?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="/assets/plugins/jquery-validation/js/jquery.validate.js"></script>
<!-- <script src="/assets/plugins/select2/js/select2.min.js"></script>
<script src="/assets/js/form-select2.js"></script> -->
<script>

var phoneInputField = document.querySelector("#contact_number");
var phoneInput = intlTelInput(phoneInputField, {
    // initialCountry: "auto",
    geoIpLookup: function(callback) {
    fetch("https://ipapi.co/json")
        .then(function(res) {
        return res.json();
        })
        .then(function(data) {
        condole.log(data.country_code);
        callback(data.country_code);
        })
        .catch(function() {
        callback("us");
        });
    },
    utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
    placeholderNumberType: "MOBILE",
    autoPlaceholder: "aggressive",
    separateDialCode: true,
    nationalMode: true,
    preferredCountries: ["in"],
    // dropdownContainer: document.body,
    customPlaceholder: function(selectedCountryPlaceholder, selectedCountryData) {
    selectedCountryPlaceholder = selectedCountryPlaceholder.length > 0 &&
        selectedCountryPlaceholder[0] === '0' ? selectedCountryPlaceholder.slice(1) :
        selectedCountryPlaceholder;
    var maskRenderer = selectedCountryPlaceholder.replace(/\d/g, '9');
    return "ex: " + selectedCountryPlaceholder;
    },
});

getLocationList('country');

$(function(){
    $('#form-branch').validate({
    rules: {
        name: {required:true},
        organization : {required:true},
        contact_number : {
            required:true ,
            maxlength: 15,
            minlength : 10
        },
        start_date : {required:true},
        pin_code : {
            required:true,
            maxlength: 6,
            minlength : 5
        },
        country : {required:true},
        city : {required:true},
        state : {required:true},
        address : {required:true},
    },
    highlight: function (element) {
        $(element).addClass('error');
        $(element).closest('.form-control').addClass('has-error');
    },
    unhighlight: function (element) {
        $(element).removeClass('error');
        $(element).closest('.form-control').removeClass('has-error');
    }
    });
})

$('#hide-modal').click(function() {
    $('.modal').modal('hide');
});

$(document).ready(function(){
    let filter_data_field = Array.from(document.getElementById("form-branch").querySelectorAll("select")).map((param) => param.id).forEach(param => {
        $("#"+param).select2({
            placeholder: 'Choose ' + param.charAt(0).toUpperCase() + param.slice(1,param.length), 
            allowClear: true,
            width: '100%' , 
            dropdownParent: $('#lgmodal')
        });
    });
    var organization_id = '';
    <?php if(isset($branch_details['organization_id'])) { ?>
        organization_id = <?=$branch_details['organization_id']?>;
    <?php } ?>
    $.ajax({
        url : "/app/common/organizationList" , 
        type : 'post',
        data : {
            organization_id
        },
        success : function(data) {
            $("#organization").html(data);
            if(organization_id != '') {
                $("#organization").trigger('change');
            }
        }
    });
});

$("#form-branch").on('submit',function(e){
    e.preventDefault();
    if ($("#form-branch").valid()) {
        var formData = new FormData(this);
        <?php if(count($branch_details)) { ?>
            formData.append("ID",<?=$branch_details['ID']?>);
        <?php } ?>
        formData.append("country_code",$(".iti__selected-dial-code").text());
        $.ajax({
            url: this.action,
            type: 'post',
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

function getLocationList(type) {
    var Id = ''; var table = '';
    <?php if(isset($_REQUEST['branch_id'])) { ?>
        Id = <?=$_REQUEST['branch_id']?>;
        table = 'Branch';
    <?php } ?>
    let country = '', state = '' ;
    if( type === 'city') {
        country = $("#country").val();
        state = $("#state").val(); 
    } else if ( type == 'state') {
        country = $("#country").val();
    }
    $.ajax({
        url: '/app/common/Location?country='+country+"&state="+state+"&Id="+Id+"&table="+table,
        type: 'GET',
        success: function(data) {
            $("#"+type).html(data);
            if(Id != '') {
                $("#"+type).trigger('change');
            }
        }
    })
}

function validateUpdateOrganization(organization_id) {
    <?php if(isset($_REQUEST['branch_id'])) { ?>
        var id = '<?=$_REQUEST['branch_id']?>';
        var search = 'Branch';
        $.ajax({
            url : "/app/common/checkAssignDetails" ,
            type : "post" , 
            data : {
            id,
            search,
            organization_id,
            "type" : "update_branch"
            },
            dataType : "json",
            success : function(data) {
                if(data.status == 400) {
                    Swal.fire({
                        title : data.title,
                        text : data.text,
                        icon: 'error',
                    });
                    $("#organization").val(data.previous);
                    $("#organization").trigger('change');
                }
            }
        })
    <?php } ?>
}

</script>
  
