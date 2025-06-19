<?php 
$data = file_get_contents('php://input');
if(!empty($data)) {
    $_REQUEST = json_decode($data,true);
}
?>
<div class="card-body">
    <div class="border p-4 rounded">
        <div class="card-title d-flex align-items-center justify-content-between">
            <h6 class="mb-0" id="model_heading">Assets History</h6> 
            <a type="button" class="close" data-dismiss="modal" id = "hide-modal" aria-hidden="true"><i class="bi bi-x-circle-fill"></i></a>
        </div>
        <hr/>
        <div id="assetsHistory_body">
            <div class="table-responsive mt-3">
                <table class="table align-middle w-100" id="assetshistoryTable" style="color: #515B73!important;">
                    <thead class="table-primary">
                        <tr class="table_heading"> 
                            <td>Sr No</td>
                            <td>User</td>
                            <td>Assigned On</td>
                            <td>Return On</td>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<script>

$('#hide-modal').click(function() {
    $('.modal').modal('hide');
});

$(document).ready(function() {
    $("#assetshistoryTable").DataTable(assetsHistorySetting);
});

var assetsHistorySetting = {
    'processing' : true,
    'serverSide' : true,
    'serverMethod' : 'POST',
    'ajax': {
        url : '/app/assetsManagment/assets/assetsHistoryServer', 
        type : 'POST',
        data : function(d) {
            d.assets_id = '<?=$_REQUEST['id']?>';
        }
    },
    'columns': [{
            data: "slno",
            render : (data,type,row) => `<div class = "table_heading">${data}</div>`
        },{
            data: "user" ,
            render : (data,type,row) => `<div>${data.toUpperCase()}</div>`
        },{
            data: "assigned_on", 
            render : (data,type,row) => (data != null) ? `<div>${data}</div>` : `<div>None</div>`  
        },{
            data: "return_on", 
            render : (data,type,row) => (data != null) ? `<div>${data}</div>` : `<div>None</div>`  
        }
    ],
    "dom": '<"row"<"col-sm-12 col-md-6 d-flex justify-content-start"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>><"table-responsive"t><"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
    "destroy": true,
    "scrollCollapse": true,
    drawCallback: function(settings, json) {
        $('[data-toggle="tooltip"]').tooltip({
            template: '<div class="tooltip custom-tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
        });
    },
    "aaSorting": []
};
</script>