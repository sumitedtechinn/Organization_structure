<?php 
$data = file_get_contents('php://input');
if(!empty($data)) {
    $_REQUEST = json_decode($data,true);
}
?>
<div class="card-body">
    <div class="border p-4 rounded">
        <div class="card-title d-flex align-items-center justify-content-between">
            <h6 class="mb-0" id="model_heading"></h6>
            <a type="button" class="close" data-dismiss="modal" id = "hide-modal" aria-hidden="true"><i class="bi bi-x-circle-fill"></i></a>
        </div>
        <hr/>
        <form role="form" id="form-assetsCategory" action="/app/assetsManagment/assetsCategory/fetchAndStoreAssetsCategory" method="POST">
            <div class="row">
                <label for="name" class="col-sm-4 col-form-label">Category Name</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control form-control-sm" name="category_name" id="category_name" value="" placeholder="eg: laptop">
                </div>
            </div>
            <div class="row mb-1">
                <label class="col-sm-4 col-form-label">Category Prefix</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control form-control-sm text-uppercase" name="category_prefix" id="category_prefix" placeholder="eg:- EDLP">
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
    $('#form-assetsCategory').validate({
    rules: {
        category_name: {required:true},
        category_prefix : {required:true},
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
    let url = "/app/assetsManagment/assetsCategory/fetchAndStoreAssetsCategory";
    const data = await fetchData(url , JSON.stringify({
        id : "<?=$_REQUEST['id'] ?? '' ?>",
        method : 'checkCategory'
    }))  
    if (data != null) {
        document.getElementById("buttonText").innerText = data.buttonText;
        document.getElementById("model_heading").innerText = data.model_heading;
        if(Object.keys(data?.form_data).length > 0) {
            for (const key in data.form_data) {
                document.getElementById(key).value = data.form_data[key];    
            }
        }
    }
})

document.getElementById("form-assetsCategory").addEventListener("submit" , async function(e) {
    e.preventDefault();
    const form = document.getElementById("form-assetsCategory");
    if(form.checkValidity()) {
        let fromData = new FormData(this);
        fromData.append("method","insertOrUpdate");
        fromData.append("id",'<?=$_REQUEST['id'] ?? "" ?>')
        const data = await fetchData(this.action , fromData);
        if (data.status == 200) {
            $('.modal').modal('hide');
            toastr.success(data.message);
            $('#assetsCategoryTable').DataTable().ajax.reload(null, false);
        } else {
            toastr.error(data.message);
        }   
    }
});
</script>
