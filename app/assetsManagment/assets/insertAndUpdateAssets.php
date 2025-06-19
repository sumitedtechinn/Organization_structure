<?php 
$data = file_get_contents('php://input');
if(!empty($data)) {
    $_REQUEST = json_decode($data,true);
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
        <form role="form" id="form-assets" action="/app/assetsManagment/assets/fetchAndStoreAssets" method="POST">
            <div class="row mb-1">
                <div class="col-sm-6">
                    <label for="brand_name" class="col-sm-12 col-form-label">Brand Name</label>
                    <div class="col-sm-12">
                        <input type="text" class="form-control form-control-sm" name="brand_name" id="brand_name" placeholder="eg :- Dell">
                    </div>
                </div>
                <div class="col-sm-6">
                    <label for="model_number" class="col-sm-12 col-form-label">Model Number</label>
                    <div class="col-sm-12">
                        <input type="text" class="form-control form-control-sm" name="model_number" id="model_number" placeholder="eg :- E7470">
                    </div>
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Assets Category</label>
                    <div class="col-sm-12">
                        <select type="text" class="form-control form-control-sm single-select select2" name = "assets_category" id="assets_category" onchange="getAssetsCode(this.value)"></select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <label for="assets_code" class="col-sm-12 col-form-label">Assets Code</label>
                    <div class="col-sm-12">
                        <input type="text" class="form-control form-control-sm" name="assets_code" id="assets_code" readonly placeholder="eg :- EDLP001">
                    </div>
                </div>
            </div>
            <div class="row mb-1">
                <label class="col-sm-12 col-form-label">Assets Description</label>
                <div class="col-sm-12">
                    <textarea class="form-control" name="assets_description" id = "assets_description" rows="2"></textarea>
                </div> 
            </div>
            <hr/>
            <div class="row mb-2"> 
                <div class="col-sm-12 text-end">
                    <button type="submit" class="btn btn-primary btn-sm" id="buttonText"></button>
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
    $('#form-assets').validate({
    rules: {
        brand_name: {required:true},
        model_number : {required:true},
        assets_category : {required:true}, 
        assets_code : {
            required:true ,
            readOnly : true
        },
        assets_description : {required:true}
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
        id : "<?=$_REQUEST['id'] ?? '' ?>",
        method : 'checkAssets'
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
                    formFiled.disabled = true;
                } 
            }
        } else {
            console.log("came to this section");
            for (const filedName in dropDownFiled) {
                creatDropDown(dropDownFiled[filedName],filedName,"");
            }
        }
    }
    $('#assets_category').select2({
        placeholder: 'Choose Category',
        allowClear: true,
        width: '100%',
        dropdownParent: $('#mdmodal')
    });
})

document.getElementById("form-assets").addEventListener("submit" , async function (e) {
    e.preventDefault();
    const form = document.getElementById("form-assets");
    if(form.checkValidity()) {
        let fromData = new FormData(this);
        fromData.append("method","insertOrUpdate");
        fromData.append("id",'<?=$_REQUEST['id'] ?? "" ?>')
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

async function getAssetsCode(assets_category) {

    const data = await fetchData("/app/assetsManagment/assets/fetchAndStoreAssets",JSON.stringify({
        "assets_category" : assets_category , 
        "method" : "getAssetsCode"
    }));
    if (data != null) {
        if(data.status == 200) {
            document.getElementById("assets_code").value = data?.message;
        } else {
            console.log(data.message);
        }
    }
}
</script>
