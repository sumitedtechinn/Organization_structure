<?php 
session_start(); 
?>
<style>
    .select2-container {
        z-index: 999999 !important;
    }
    .error {
        color: red;
        font-size: small;
    }
</style>

<!-- Modal -->
<div class="card-body">
    <div class="border p-4 rounded">
        <div class="card-title d-flex align-items-center justify-content-between">
            <h5 class="mb-0">View Closure</h5>
            <a type="button" class="close" data-dismiss="modal" id = "hide-modal-view" aria-hidden="true"><i class="bi bi-x-circle-fill"></i></a>
        </div>
        <hr/>
        <div class="table-responsive mt-3">
            <table class="table table-striped align-middle w-100" id="viewAllClosureTable">
                <thead class="table-light">
                    <tr>
                        <th>Center</th>
                        <th>Contact</th>
                        <th>Projection Type</th>
                        <th>Doc Status</th>
                        <th>Updated Date</th>
                    </tr>
                </thead>
            </table>
        </div>
        <hr/>
    </div>
</div>

<script type="text/javascript">

var viewAllClosureSettings = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    'ajax': {
        'url': '/app/addProjection/viewAllMonthlyClosure-server',
        'type': 'POST',
        'data' : function(d){
            d.organization_id = '<?=$_REQUEST['organization']?>';
            d.branch_id = '<?=$_REQUEST['branch']?>';
            d.vertical_id = '<?=$_REQUEST['vertical']?>';
            d.department_id = '<?=$_REQUEST['department']?>';
            d.projectionType = '<?=$_REQUEST['projectionType']?>';
            d.user = '<?=$_REQUEST['user']?>';
            d.month = '<?=$_REQUEST['month']?>';
            d.year = '<?=$_REQUEST['year']?>';
            d.selected_projectionType = '<?=$_REQUEST['selected_projectiontype']?>';
        }
    },
    'columns': [{
            data: "center_name",
            render : function(data,type,row) {
                return '<div style="font-size:small;"><p class = "mb-1"><b>Name : </b> '+data+'</p><p class = "mb-1" ><b>Email : </b>'+row.center_email+'</p></div>'
            }
        },{
            data: "contact" ,
            render : function(data,type,row) {
                var country_code = row.country_code;
                return '<div><b>'+country_code+' </b><span>'+data+'</span></div>';
            }
        },{
            data: "projection_type" ,
        },{
            data: "doc_status" ,
            render : function(data,type,row) {
                if (data == 'Doc Received' || data == 'Doc Prepare') {
                    return '<div class = "text-warning">'+data+'</div>';
                } else {
                    return '<div class = "text-success">'+data+'</div>';
                }
            }
        },{
            data : "last_update_date",
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
    $('#viewAllClosureTable').DataTable(viewAllClosureSettings);
});

$('#hide-modal-view').click(function() {
    $('.modal').modal('hide');
});