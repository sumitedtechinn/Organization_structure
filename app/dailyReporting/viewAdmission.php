<?php session_start(); ?>

<!-- Modal -->
<div class="card-body">
    <div class="border p-4 rounded">
        <div class="card-title d-flex align-items-center justify-content-between">
            <h5 class="mb-0">View Admission</h5>
            <a type="button" class="close" data-dismiss="modal" id = "hide-modal-view" aria-hidden="true"><i class="bi bi-x-circle-fill"></i></a>
        </div>
        <hr/>
        <div class="table-responsive mt-3">
            <table class="table table-striped align-middle w-100" id="viewAdmissionTable">
                <thead class="table-light">
                    <tr>
                        <th>Admission By</th>
                        <th>Projection Type</th>
                        <th>No. Admission</th>
                        <th>Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
        <hr/>
    </div>
</div>

<script type="text/javascript">

var viewAdmissionSettings = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    "searching": false ,
    'ajax': {
        'url': '/app/dailyReporting/viewAdmission-server',
        'type': 'POST',
        'data' : {
            "admission_ids" : '<?=$_REQUEST['admission_ids']?>'
        }
    },
    'columns': [{
            data: "adm_by" ,
            render : function(data,type,row) {
                return '<div class = "fw-bold">'+data+'</div>';
            }
        },{
            data : "projection_type"
        },{
            data: "adm_number",
        },{
            data : "adm_amount",
        },{
            data : "Action",
            render : function(data, type, row) {
                var table = "admission_details"; var edit = '';var del = '';
                edit = '<div class="text-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit" onclick = "updateAdmissionDetails('+row.ID+')"><i class="bi bi-pencil-fill"></i></div>';
                del = '<div class="text-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete" onclick = "checkAssignDetails('+row.ID+',&#39;'+table+'&#39;)"><i class="bi bi-trash-fill"></i></div>';
                return '<div class = "table-actions d-flex align-items-center gap-3 fs-6">' +  edit+del + '</div>';
            }
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

$(document).ready(function() {
    $('#viewAdmissionTable').DataTable(viewAdmissionSettings);
});

$('#hide-modal-view').click(function() {
    $('.modal').modal('hide');
});

function updateAdmissionDetails(id) {
    $('.modal').modal('hide');
    setTimeout(function(){
        $.ajax({ 
            url : "/app/dailyReporting/updateAdmissionDetails",
            type : "post" ,
            data : {
                id
            },
            success : function(data) {
                $('#lg-modal-content').html(data);
                $('#lgmodal').modal('show');
            }
        })
    },500);
}

</script>