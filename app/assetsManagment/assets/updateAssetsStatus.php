<?php 
$data = file_get_contents('php://input');
if(!empty($data)) {
    $_REQUEST = json_decode($data,true);
}
if(!isset($_REQUEST['assets_id'])) {
    exit("Assets id is required");
}
?>

<style>
label {
    font-size: 14px;
    font-weight: 500;
    color: #6d6868;
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
        <form role="form" id="form-changeAssetsStatus" action="/app/assetsManagment/assets/fetchAndStoreAssets" method="POST">
            <div class="row">
                <label for="assets_status" class="col-sm-12 col-form-label">Assets Status</label>
                <div class="col-sm-12">
                    <select type="text" class="form-control form-control-sm single-select select2" name = "assets_status" id="assets_status"></select>
                </div>
            </div>
            <hr/>
            <div class="row mb-2"> 
                <div class="col-sm-12 text-end">
                    <button type="submit" class="btn btn-primary btn-sm" style="font-size: small;" id="buttonText"></button>
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
    $('#form-changeAssetsStatus').validate({
    rules: {
        assets_status: {required:true},
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
    let url = "/app/assetsManagment/assets/fetchAndStoreAssets";
    const data = await fetchData(url , JSON.stringify({  
        assets_id : "<?=$_REQUEST['assets_id']?>",
        method : 'checkAssetsStatus'
    }))  
    if (data != null) {
        document.getElementById("buttonText").innerText = data.buttonText;
        document.getElementById("model_heading").innerText = data.model_heading;
        let formDataPresent = (Object.keys(data?.form_data).length > 0) ? true : false;
        let dropDownFiled = JSON.parse(data?.dropDownFiled);
        if(formDataPresent) {
            for (const key in data.form_data) {
                let formFiled = document.getElementById(key);
                if(formFiled.tagName == 'INPUT') {
                    formFiled.value = data.form_data[key];
                    if (key == "assets_code") formFiled.disabled = true;
                } else if (formFiled.tagName == 'TEXTAREA') {
                    formFiled.innerHTML = data.form_data[key];
                } else if (formFiled.tagName == 'SELECT') {
                    creatDropDown(dropDownFiled[key],key,data.form_data[key]);
                } 
            }
        }
    }
    $('#assets_status').select2({
        placeholder: 'Choose Status',
        allowClear: true,
        width: '100%',
        dropdownParent: $('#smmodal')
    });
})

document.getElementById("form-changeAssetsStatus").addEventListener("submit" , async function (e) {
    e.preventDefault();
    const form = document.getElementById("form-changeAssetsStatus");
    if(form.checkValidity()) {
        const disabledFiled = form.querySelectorAll(":disabled");
        disabledFiled.forEach(el => el.disabled = false);
        let fromData = new FormData(this);
        fromData.append("method","updateAssetStatus");
        fromData.append("id",'<?=$_REQUEST['assets_id'] ?? "" ?>')
        const data = await fetchData(this.action , fromData);
        if (data.status == 200) {
            $('.modal').modal('hide');
            toastr.success(data.message);
            $('#assetsTable').DataTable().ajax.reload(null, false);
        } else {
            toastr.error(data.message);
        }   
    }
});


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

</script>
