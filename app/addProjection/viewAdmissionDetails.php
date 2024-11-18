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
                        <th>Admission Date</th>
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
    'ajax': {
        'url': '/app/addProjection/viewAdmission-server',
        'type': 'POST',
        'data' : function(d){
            d.projection_id = '<?=$_REQUEST['projection_id']?>';
        }
    },
    'columns': [{
            data: "adm_by" ,
            render : function(data,type,row) {
                return '<div>'+data+'</div>';
            }
        },{
            data : "projection_type"
        },{
            data: "adm_number",
        },{
            data : "adm_amount",
        },{
            data : "adm_date",
            render : function(data,type,row) {
                return '<div>'+data+'</div>';
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


</script>