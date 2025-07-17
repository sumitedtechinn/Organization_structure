<?php 
$data = file_get_contents('php://input');
if(!empty($data)) {
    $_REQUEST = json_decode($data,true);
}
?>
<style>
.truncate-column {
    display: inline-block;
    max-width: 225px; /* or use any fixed size */
    white-space: nowrap;
    overflow: hidden;      /* required for ellipsis */
    text-overflow: ellipsis;
    vertical-align: middle;
}
</style>
<div class="card-body">
    <div class="border p-4 rounded">
        <div class="card-title d-flex align-items-center justify-content-between">
            <h6 class="mb-0" id="model_heading">Attendance Details</h6> 
            <a type="button" class="close" data-dismiss="modal" id = "hide-modal" aria-hidden="true"><i class="bi bi-x-circle-fill"></i></a>
        </div>
        <hr/>
        <div id="holidayList_body">
            <div class="table-responsive mt-3">
                <table class="table align-middle w-100" id="holidayListTable" style="color: #515B73!important;">
                    <tbody id="tableList"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>

$('#hide-modal').click(function() {
    $('.modal').modal('hide');
});

async function fetchData(url , param) {
    try {
        let response = await fetch(url , {
            method : 'POST' , 
            body : JSON.stringify(param)
        })
        if(!response.ok) {
            throw new Error(`Http Error Status : ${response.status}`); 
        } 
        const data = response.json();
        return data;
    } catch (error) {
        console.error('Error : ', error);
    }
}

$(document).ready(async function () {
    const url = "/app/attendance_system/attendance/fetchAttendanceDetails";
    const payload = {
        id: '<?= $_REQUEST['id'] ?>',
        method: 'fetchAttendanceDetails'
    };
    try {
        const data = await fetchData(url, payload);
        if (!data) return;
        const tbodyContainer = document.getElementById("tableList");
        tbodyContainer.innerHTML = ''; // Clear existing rows
        if (data.status === 200) {
            const result = JSON.parse(data.message);
            for (const key in result) {
               tbodyContainer.append(makeRow([formatKey(key),result[key]])); 
            }
        } else {
            tbodyContainer.append(makeErrorRow(data.message, 2));
        }
    } catch (error) {
        console.error("Error fetching attendance settings:", error);
        document.getElementById("tableList").append(makeErrorRow("An error occurred while fetching data.", 4));
    }
});

function formatKey(value) {
    return value.split("_").map((param) => param.charAt(0).toUpperCase() + param.slice(1)).join(" ");
}

function makeRow(rowData) {
    let tr = document.createElement('tr');
    tr.classList.add("row");
    rowData.forEach((param) => {
        let td = document.createElement('td');
        td.innerHTML = param;
        td.classList.add("text-center", "col-sm-6", "truncate-column" , "table_heading");
        td.title = param;
        td.setAttribute("data-bs-toggle", "tooltip");
        tr.append(td);
    })
    return tr;
}

function makeErrorRow(message, colspan = 1) {
    const tr = document.createElement('tr');
    const td = document.createElement('td');
    td.innerText = message;
    td.colSpan = colspan;
    td.classList.add('text-center', 'text-danger'); // Optional styling
    tr.append(td);
    return tr;
}

</script>
