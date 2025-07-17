<?php 
$data = file_get_contents('php://input');
if(!empty($data)) {
    $_REQUEST = json_decode($data,true);
}
?>

<div class="card-body">
    <div class="border p-4 rounded">
        <div class="card-title d-flex align-items-center justify-content-between">
            <h6 class="mb-0" id="model_heading">Holiday List</h6> 
            <a type="button" class="close" data-dismiss="modal" id = "hide-modal" aria-hidden="true"><i class="bi bi-x-circle-fill"></i></a>
        </div>
        <hr/>
        <div id="holidayList_body">
            <div class="table-responsive mt-3">
                <table class="table align-middle w-100" id="holidayListTable" style="color: #515B73!important;">
                    <thead class="table-primary">
                        <tr class="table_heading"> 
                            <td>S.No.</td>
                            <td>Occasion</td>
                            <td>Date</td>
                            <td>Day</td>
                        </tr>
                    </thead>
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
    const url = "/app/attendance_system/attendanceSetting/fetchAndStoreAttendanceSetting";
    const payload = {
        id: '<?= $_REQUEST['id'] ?>',
        method: 'fetchHolidayList'
    };
    try {
        const data = await fetchData(url, payload);
        if (!data) return;
        const tbodyContainer = document.getElementById("tableList");
        tbodyContainer.innerHTML = ''; // Clear existing rows
        if (data.status === 200) {
            const result = JSON.parse(data.message);
            let slno = 1;
            Object.entries(result).forEach(([key, value]) => {
                const [day, date] = value.split('@@@@');
                const rowData = [slno++, key, date, day];
                tbodyContainer.append(makeRow(rowData));
            });
        } else {
            tbodyContainer.append(makeErrorRow(data.message, 4));
        }
    } catch (error) {
        console.error("Error fetching attendance settings:", error);
        document.getElementById("tableList").append(makeErrorRow("An error occurred while fetching data.", 4));
    }
});


function makeRow(rowData) {
    let tr = document.createElement('tr');
    rowData.forEach((param) => {
        let td = document.createElement('td');
        td.innerHTML = param;
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
