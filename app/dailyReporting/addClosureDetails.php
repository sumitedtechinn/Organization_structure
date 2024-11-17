<div class="card-body mb-2" name = "center_card_<?=$_REQUEST['center_count']?>" id = "center_card_<?=$_REQUEST['center_count']?>"> 
    <div class="border p-2 rounded">
        <div class="row mb-1">
            <div class="col-sm-6">
                <label for="name" class="col-sm-12 col-form-label">Center Name</label>
                <div class="col-sm-12">
                    <input type="text" class="form-control form-control-sm" name="center_name_<?=$_REQUEST['center_count']?>" id = "center_name_<?=$_REQUEST['center_count']?>" required value="<?php echo !empty($user_details) ? $user_details['Name'] : '' ?>" placeholder="Enter  name">
                </div>
            </div>
            <div class="col-sm-6">
                <label class="col-sm-12 col-form-label">Center Email</label>
                <div class="col-sm-12">
                    <input type="email" class="form-control form-control-sm" name="center_email_<?=$_REQUEST['center_count']?>" id = "center_email_<?=$_REQUEST['center_count']?>" required value="<?php echo !empty($user_details) ? $user_details['Email'] : '' ?>" placeholder="eg:-abc123@gmail.com">
                </div>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-sm-6">
                <label class="col-sm-12 col-form-label">Contact Number</label>
                <div class="col-sm-12">
                    <input type="text" class="form-control form-control-sm" required name="contact_number_<?=$_REQUEST['center_count']?>" id="contact_number_<?=$_REQUEST['center_count']?>" maxlength="15" value="<?php echo !empty($user_details) ? $user_details['Country_code'].$user_details['Mobile'] : '' ?>" onkeypress="return /[0-9]/i.test(event.key)">
                </div>
            </div>
            <div class="col-sm-6">
                <label class="col-sm-12 col-form-label">Projection Type</label>
                <div class="col-sm-12">
                    <select type="text" class="form-control form-control-sm single-select select2" required name="projection_type_<?=$_REQUEST['center_count']?>" id = "projection_type_<?=$_REQUEST['center_count']?>">
                    </select>
                </div>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-sm-12 d-flex justify-content-end gap-1">
                <button type="button" class="btn-sm btn-success" id = "add_center_card_<?=$_REQUEST['center_count']?>" onclick="addCenterCard(this.id)">Add More</button>
                <button type="button" class="btn-sm btn-danger" id = "remove_center_card_<?=$_REQUEST['center_count']?>" onclick="removeCenterCard(this.id)">Remove</button>
            </div>
        </div>
    </div>
</div>
<script src="/assets/plugins/select2/js/select2.min.js"></script>
<script src="/assets/js/form-select2.js"></script>
<script>

var phoneInputField = document.querySelector("#contact_number_<?=$_REQUEST['center_count']?>");
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
    var projectionType_form = 'center';
    $.ajax({
        url : "/app/common/projectionTypeList" ,
        type : "post" ,
        data : {
            projection_type,
            projectionType_form
        },
        success : function(data) {
            $("#projection_type_<?=$_REQUEST['center_count']?>").html(data);
        }
    })
});


</script>