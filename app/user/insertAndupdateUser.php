<?php

## Database configuration
include '../../includes/db-config.php';
session_start();

$user_details = [];
if (isset($_REQUEST['id'])) {
    $user_details = $conn->query("SELECT * FROM users WHERE ID = ". $_REQUEST['id']);
    $user_details = mysqli_fetch_assoc($user_details);
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
<div class="card-body">
    <div class="border p-4 rounded">
        <div class="card-title d-flex align-items-center justify-content-between">
            <h5 class="mb-0">
                <?php if(!empty($user_details)) { ?>
                    Update User
                <?php } else {?>
                    Add User
                <?php } ?>    
            </h5>
            <a type="button" class="close" data-dismiss="modal" id = "hide-modal" aria-hidden="true"><i class="bi bi-x-circle-fill"></i></a>
        </div>
        <hr/>
        <form role="form" id="form-user" action="/app/user/storeAndupdateUser" method="POST">
            <div class="row mb-1">
                <div class="col-sm-6">
                    <label for="name" class="col-sm-12 col-form-label">Name</label>
                    <div class="col-sm-12">
                        <input type="text" class="form-control form-control-sm" name="name" value="<?php echo !empty($user_details) ? $user_details['Name'] : '' ?>" placeholder="Enter user name">
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Email</label>
                    <div class="col-sm-12">
                        <input type="email" class="form-control form-control-sm" name="user_email" value="<?php echo !empty($user_details) ? $user_details['Email'] : '' ?>" placeholder="eg:-abc123@gmail.com">
                    </div>
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Contact Number</label>
                    <div class="col-sm-12">
                        <input type="text" class="form-control form-control-sm" name="contact_number" id="contact_number" maxlength="15" value="<?php echo !empty($user_details) ? $user_details['Country_code'].$user_details['Mobile'] : '' ?>" onkeypress="return /[0-9]/i.test(event.key)">
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Date Of Joining</label>
                    <div class="col-sm-12">
                        <input type="date" class="form-control form-control-sm" value="<?php echo !empty($user_details) ? $user_details['DOJ'] : '' ?>" name="doj">
                    </div>
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Image</label>
                    <div class="col-sm-12">
                        <input class="form-control form-control-sm" type="file" accept="image/*" value="<?php echo !empty($user_details) ? $user_details['Photo'] : '' ?>" name="image"> 
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">PIN Code</label>
                    <div class="col-sm-12">
                        <input type="text" class="form-control form-control-sm" name="pin_code" maxlength="6" value="<?php echo !empty($user_details) ? $user_details['Pincode'] : '' ?>" placeholder="Eg :- 11XXXX00" onkeypress="return /[0-9]/i.test(event.key)">
                    </div>
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Country</label>
                    <div class="col-sm-12">
                        <select type="text" class="form-control form-control-sm single-select select2" name="country" id="country" onchange="getLocationList('state')"></select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">State</label>
                    <div class="col-sm-12">
                        <select type="text" class="form-control form-control-sm single-select select2" name="state" id="state" onchange="getLocationList('city')"></select>
                    </div>
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">City</label>
                    <div class="col-sm-12">
                        <select type="text" class="form-control form-control-sm single-select select2" name="city" id="city"></select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Address</label>
                    <div class="col-sm-12">
                        <textarea class="form-control" name="address"><?php echo !empty($user_details) ? $user_details['Address'] : '' ?></textarea>
                    </div>
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Role</label>
                    <div class="col-sm-12">
                        <select type="text" class="form-control form-control-sm single-select select2" name="role" id="role" onchange="checkUserOrganizationInfo(this.value)"></select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">BioMetric Id</label>
                    <div class="col-sm-12">
                        <input type="text" class="form-control form-control-sm" name="biometric_id" id="biometric_id" value="<?php echo !empty($user_details) ? $user_details['biometric_id'] : '' ?>" placeholder="Eg :- 32" onkeypress="return /[0-9]/i.test(event.key)">
                    </div>
                </div>     
            </div>
            <hr/>
            <div class="row mb-2">
                <div class="col-sm-12 text-end">
                    <button type="submit" class="btn btn-primary btn-sm">Register</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="/assets/plugins/jquery-validation/js/jquery.validate.js"></script>
<script src="/assets/plugins/select2/js/select2.min.js"></script>
<script src="/assets/js/form-select2.js"></script>
<script type="text/javascript">

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

function getLocationList(type) {
    var Id = ''; var table = '';
    <?php if(isset($_REQUEST['id'])) { ?>
        Id = <?=$_REQUEST['id']?>;
        table = 'users';
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

$(document).ready(function(){
    var role_id = '';
    <?php if(!empty($user_details)) { ?>
        role_id = '<?=$user_details['role']?>';
    <?php } ?>
    $.ajax({
        url: '/app/common/getRoles',
        type: 'post',
        data : {
            role_id
        },
        success: function(data) {
            $("#role").html(data);
            if(role_id != '') {
                $("#role").trigger('change');
            }
        }
    })
});

$(function(){
    $('#form-user').validate({
    rules: {
        name: {required:true},
        user_email : {required:true,email : true},
        contact_number : {
            required:true ,
            maxlength: 15,
            minlength : 10
        },
        doj : {required:true},
        pin_code : {
            required:true,
            maxlength: 6,
            minlength : 5
        },
        country : {required:true},
        city : {required:true},
        state : {required:true},
        address : {required:true},
        role : {required:true}
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

function checkUserOrganizationInfo(role_id) {
    <?php if(!empty($user_details)) { ?>
    var id = '<?=$_REQUEST['id']?>';
    $.ajax({
        url : "/app/common/checkAssignDetails",  
        type: 'post',
        data: {
            role_id,
            id,
            'search' : 'user_role'
        },
        dataType: 'json',
        success : function(data) {
            if( data.status == 400) {
                Swal.fire({
                    text: data.text,
                    title : data.title,
                    icon: 'warning',
                });
                $("#role").val(data.previous);
                $("#role").trigger('change');    
            }
        }
    });
    <?php } ?>
}

$('#hide-modal').click(function() {
    $('.modal').modal('hide');
});

$("#form-user").on('submit',function(e){
    e.preventDefault();
    if ($("#form-user").valid()) {
        var formData = new FormData(this);
        <?php if(isset($_REQUEST['id'])) { ?>
            formData.append("ID",<?=$_REQUEST['id']?>);
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

</script>
  
