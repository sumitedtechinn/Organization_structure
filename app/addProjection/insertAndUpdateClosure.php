<?php

## Database configuration
include '../../includes/db-config.php';
session_start();

$closure_details = [];
if(isset($_REQUEST['closure_id'])) {
    $closure_id = mysqli_real_escape_string($conn,$_REQUEST['closure_id']);
    $closure_details = $conn->query("SELECT * FROM Closure_details WHERE id = '$closure_id'");
    $closure_details = mysqli_fetch_assoc($closure_details);
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
</style>

<!-- Modal -->
<div class="card-body">
    <div class="border p-4 rounded">
        <div class="card-title d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Update Closure</h5>
            <a type="button" class="close" data-dismiss="modal" id = "hide-modal" aria-hidden="true"><i class="bi bi-x-circle-fill"></i></a>
        </div>
        <hr/>
        <form role="form" id="form-closure" action="/app/addProjection/storeAndupdateClosure" method="POST">
            <div class="row mb-1">
                <div class="col-sm-6">
                    <label for="name" class="col-sm-12 col-form-label">Center Name</label>
                    <div class="col-sm-12">
                        <input type="text" class="form-control form-control-sm" name="center_name" id = "center_name" value="<?php echo !empty($closure_details) ? $closure_details['center_name'] : '' ?>" placeholder="Enter center name">
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Center Email</label>
                    <div class="col-sm-12">
                        <input type="email" class="form-control form-control-sm" name="center_email" id = "center_email"  value="<?php echo !empty($closure_details) ? $closure_details['center_email'] : '' ?>" placeholder="eg:-abc123@gmail.com">
                    </div>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Contact Number</label>
                    <div class="col-sm-12">
                        <input type="text" class="form-control form-control-sm" name="contact_number" id="contact_number" maxlength="15" value="<?php echo !empty($closure_details) ? $closure_details['country_code'].$closure_details['contact'] : '' ?>" onkeypress="return /[0-9]/i.test(event.key)">
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Projection Type</label>
                    <div class="col-sm-12">
                        <select type="text" class="form-control form-control-sm single-select select2" name="projection_type" id = "projection_type">
                        </select>
                    </div>
                </div>
            </div>
            <hr/>
            <div class="row mb-2">
                <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </div>
        </form>
    </div>
</div>


<script src="/assets/plugins/jquery-validation/js/jquery.validate.js"></script>
<script src="/assets/plugins/select2/js/select2.min.js"></script>
<script src="/assets/js/form-select2.js"></script>
<script>

$(function(){
    $('#form-closure').validate({
    rules: {
        center_name: {required:true},
        center_email : {
            required:true , 
            email : true
        }, 
        contact_number : {
            required:true ,
            maxlength: 10,
            minlength : 10
        }, 
        projection_type : {required:true},
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

$(document).ready(function(){
    var projection_type = '';
    <?php if(!empty($closure_details)) { ?>
        projection_type = '<?=$closure_details['projectionType']?>';
    <?php } ?>
    $.ajax({
        url : "/app/common/projectionTypeList" ,
        type : "post" ,
        data : {
            projection_type
        },
        success : function(data) {
            $("#projection_type").html(data);
            <?php if(!empty($closure_details)) { ?>
                $("#projection_type").trigger('change');
            <?php } ?>
        }
    })
});


$("#form-closure").on('submit',function(e){
    e.preventDefault();
    if ($("#form-closure").valid()) {
        var formData = new FormData(this);
        <?php if(isset($_REQUEST['closure_id'])) { ?>
            formData.append('closure_id',<?=$_REQUEST['closure_id']?>);
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
                    $('#projectionTable').DataTable().ajax.reload(null, false);  
                } else {
                    toastr.error(data.message);
                }
            }
        });
    }
});


