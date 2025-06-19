<?php if(!isset($_REQUEST['id'])) {
    exit("User id is required");
} ?>
<style>
    .error {
        color: red;
        font-size: small;
    }
    .remove-btn {
        color: black;
        cursor: pointer;
        transition: color 0.3s ease;
    }
    .remove-btn:hover {
        color: red;
    }
</style>
<div class="card shadow-sm border-0" style="margin-bottom: 0px !important;">
    <div class="border p-4 rounded bg-light">
        <!-- Header Section -->
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h6 class="mb-0" id="model_heading"></h6>
            <div class="d-flex gap-2 align-items-center">
                <button type="button" class="btn btn-sm btn-success" onclick="makeSingleRow()">
                    <i class="bi bi-plus-circle"></i> Add Assets
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal" id="hide-modal">
                    <i class="bi bi-x-circle-fill" style="margin-left: 0px;" ></i>
                </button>
            </div>
        </div>
        <hr class="my-3"/>
        <!-- Form Section -->
        <form role="form" id="form-assetsAssignation" action="/app/user/fetchAssetsAssignationDetails" method="POST">
            <div id="assignAssetsContainer" class="mb-4"></div>
            <div id="emptyContainer" class="d-flex align-items-center justify-content-center" style="font-size: x-large;color: #8080808c;">
                <h6>Assign Assets To Users</h6>
            </div>
            <hr class="my-3"/>
            <div class="text-end">
                <button type="submit" class="btn btn-primary btn-sm px-4" id="buttonText"></button>
            </div>
        </form>
    </div>
</div>


<script src="/assets/plugins/jquery-validation/js/jquery.validate.js"></script>

<script type="text/javascript">

$('#hide-modal').click(function() {
    document.activeElement?.blur();
    $('.modal').modal('hide');
});

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
    window.box_num = 1;
    window.assets_category = {};
    let url = "/app/user/fetchAssetsAssignationDetails";
    const data = await fetchData(url , JSON.stringify({
        user_id : "<?=$_REQUEST['id']?>",
        method : 'fetchUserAssets'
    }))   
    if (data != null) {
        document.getElementById("buttonText").innerText = data.buttonText;
        document.getElementById("model_heading").innerText = data.model_heading;
        if (data?.dropDownFiled != '') {
            let dropDownFiled = JSON.parse(data?.dropDownFiled);
            for (const filedName in dropDownFiled) {
                assets_category = dropDownFiled[filedName];
            }
        }
        if(Object.keys(data?.form_data).length > 0) {
            document.getElementById("emptyContainer").innerHTML = "";
            for (const category_id in data.form_data) {
                let current_row = box_num;
                makeSingleRow(category_id);
                document.getElementById(`assets_category_${current_row}`).disabled = true;
                getAssetsOnCategory(category_id,`assets_category_${current_row}`,data.form_data[category_id]);
            }
        }
    }
})

function makeSingleRow(selected = '') {
    if (document.getElementById("emptyContainer").innerHTML != '') document.getElementById("emptyContainer").innerHTML = "";
    const row = document.createElement("div");
    row.classList.add("row");
    row.id = `box_${box_num}`;
    ["assets_category", "assets"].forEach(name => {
        row.append(makeSelectTag(name, box_num));
    });
    row.append(makeRemoveButton("remove",box_num));
    const assignAssetsContainer = document.getElementById("assignAssetsContainer");
    assignAssetsContainer.append(row); 

    const filter_assets_category = checkSelectedAssetsCategory();
    creatDropDown(filter_assets_category,`assets_category_${box_num}`,selected);
    $(`#assets_category_${box_num}`).select2({
        placeholder: 'Choose Category',
        allowClear: true,
        width: '100%'
    });
    $(`#assets_${box_num}`).select2({
        placeholder: 'Choose Assets',
        allowClear: true,
        width: '100%'
    });
    ++box_num;
}

function makeSelectTag(name,box_num) {
    const outerDiv = document.createElement("div");
    outerDiv.classList.add("col-sm-5");
    const label = document.createElement("label");
    label.classList.add("col-sm-12","col-form-label");
    label.textContent = name.includes("_") ? name.split("_").map(ele => ele.charAt(0).toUpperCase() + ele.slice(1)).join(" ") : name.charAt(0).toUpperCase() + name.slice(1);
    const inner_div = document.createElement("div");
    inner_div.classList.add("col-sm-12");
    const select = document.createElement("select");
    select.type = "text";
    select.classList.add("form-control","form-control-sm","single-select","select2");
    select.name = `${name}_${box_num}`;
    select.id = `${name}_${box_num}`;
    if(name == "assets_category") {
        select.onchange = function() {
            getAssetsOnCategory(this.value,this.id);
        }
    }
    
    inner_div.append(select);
    outerDiv.append(label);
    outerDiv.append(inner_div);
    return outerDiv;
}

function makeRemoveButton(name,box_num) {
    let div = document.createElement("div");
    div.classList.add("font-22","col-sm-2", "remove-btn");
    div.style.marginTop = "2rem";
    div.id = `${name}_${box_num}`;
    div.title = "Remove";
    div.onclick = function () {
        removeAssets(this.id);
    };
    let i = document.createElement("i");
    i.classList.add("fadeIn","animated","bx","bx-minus-circle");
    div.append(i);
    return div;
}

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

function checkSelectedAssetsCategory() {
    if(box_num == 1) return assets_category;
    let filter = assets_category;
    Array.from(document.querySelectorAll("select[id^='assets_category_']")).forEach((param) => {
        if (param.value != '' && filter.hasOwnProperty(param.value)) {
            delete filter[param.value];   
        }
    });
    return filter;
}
  
function removeAssets(button_id) {
    let removeBoxNum = button_id.split("_")[1];
    document.getElementById(`box_${removeBoxNum}`).remove();
}
 
async function getAssetsOnCategory(category_id,id,selected = '') {
    let assetsBoxNum = id.split("_")[2];
    let url = "/app/user/fetchAssetsAssignationDetails";
    const data = await fetchData(url , JSON.stringify({
        category_id ,
        user_id : "<?=$_REQUEST['id']?>",
        method : 'fetchAssets'
    })) 
    if(data != null && data.status == 200) {
        let assets = JSON.parse(data.message);
        creatDropDown(assets,`assets_${assetsBoxNum}`,selected);
    }   
}

$(function() {
    // Initialize validate
    let validator = $('#form-assetsAssignation').validate({
        highlight: function (element) {
            $(element).addClass('error');
            $(element).closest('.form-control').addClass('has-error');
        },
        unhighlight: function (element) {
            $(element).removeClass('error');
            $(element).closest('.form-control').removeClass('has-error');
        }
    });

    // Dynamically add rules to all select fields matching prefix
    $("select[id^='assets_category_'], select[id^='assets_']").each(function() {
        $(this).rules("add", {
            required: true,
            messages: {
                required: "This field is required"
            }
        });
    });
});

document.getElementById("form-assetsAssignation").addEventListener("submit" , async function(e) {
    e.preventDefault();
    const form = $("#form-assetsAssignation");
    if(form.valid()) {
        let fromData = new FormData(this);
        fromData.append("method","insertOrUpdate");
        fromData.append("user_id",'<?=$_REQUEST['id']?>')
        const data = await fetchData(this.action , fromData);
        if (data.status == 200) {
            $('.modal').modal('hide');
            toastr.success(data.message);
            $('#userTable').DataTable().ajax.reload(null, false);
        } else {
            toastr.error(data.message);
        }   
    }
});


</script>
