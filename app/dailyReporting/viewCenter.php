<?php session_start(); ?>
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
            <table class="table table-striped align-middle w-100" id="viewCenterTable">
                <thead class="table-light">
                    <tr>
                        <th>Center</th>
                        <th>Contact</th>
                        <th>Projection Type</th>
                        <th>Doc Status</th>
                        <th>Authorization Amount</th>
                        <th>Updated Date</th>
                    </tr>
                </thead>
            </table>
        </div>
        <hr/>
    </div>
</div>

<script type="text/javascript">

var viewCenterSettings = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    'ajax': {
        'url': '/app/dailyReporting/viewCenter-server',
        'type': 'POST',
        'data' : {
            "center_ids" : '<?=$_REQUEST['ids']?>'
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
                } else if (data == 'Center Delete') {
                    return '<div class = "text-danger">'+data+'</div>';
                } else {
                    return '<div class = "text-success">'+data+'</div>';
                }
            }
        },{
            data : "authorization_amount",
            render : function(data,type,row) {
                if(data != 'None') {
                    return '<div><span>₹</span>'+data+'</div>';
                } else {
                    return '<div>Not Received</div>';
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
    $('#viewCenterTable').DataTable(viewCenterSettings);
});

$('#hide-modal-view').click(function() {
    $('.modal').modal('hide');
});

</script>