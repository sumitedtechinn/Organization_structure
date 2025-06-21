<?php if(!isset($_REQUEST['id'])) {
    exit("User id is required");
} ?>
<div class="card-body">
    <div class="border p-4 rounded">
        <div class="card-title d-flex align-items-center justify-content-between">
            <h6 class="mb-0" id="model_heading">Assets Details</h6> 
            <a type="button" class="close" data-dismiss="modal" id = "hide-modal" aria-hidden="true"><i class="bi bi-x-circle-fill"></i></a>
        </div>
        <hr/>
        <div id="assetsDetails_body">
            <div class="table-responsive mt-3">
                <table class="table align-middle w-100" id="assetsDetailsTable" style="color: #515B73!important;">
                    <thead class="table-primary">
                        <tr class="table_heading"> 
                            <td>Category</td>
                            <td>Asset Code</td>
                            <td>Brand Name</td>
                            <td>Model Number</td>
                        </tr>
                    </thead>
                    <tbody id="assetsDetailsBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

$('#hide-modal').click(function() {
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
    let url = "/app/user/fetchAssetsAssignationDetails";
    const data = await fetchData(url , JSON.stringify({
        user_id : "<?=$_REQUEST['id']?>",
        method : 'fetchUserAssignedAssetsDetails'
    }))   
    if (data == null) return;
    let tableBody = document.getElementById("assetsDetailsBody");
    if (data.status == 200 && data.assets_assignation === true) {
        let assets_info = JSON.parse(data.message);
        for (const key in assets_info) {
            let row = createTableRow(assets_info[key]);
            tableBody.append(row);
        }
    } else {
        tableBody.append(createTableRow(data.message));
    }
})


function createTableRow(content) {
    let tr = document.createElement("tr");
    if(typeof content === 'object') {
        for (const key in content) {
            let td = document.createElement("td");
            td.innerHTML = `${content[key]}`;
            tr.append(td);    
        }
    } else {
        let td = document.createElement("td");
        td.colSpan = 4;
        td.innerHTML = `<h6 style="text-align:center;">${content}</h6>`;
        tr.append(td);
    }
    return tr;
}
</script>
