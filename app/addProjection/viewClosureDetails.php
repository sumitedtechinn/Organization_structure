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
            <table class="table table-striped align-middle w-100" id="viewClosureTable">
                <thead class="table-light">
                    <tr>
                        <th>Center</th>
                        <th>Contact</th>
                        <th>Projection Type</th>
                        <th>Doc Status</th>
                        <th>Updated Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
        <hr/>
    </div>
</div>

<script type="text/javascript">

var viewClosureSettings = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    'ajax': {
        'url': '/app/addProjection/viewClosure-server',
        'type': 'POST',
        'data' : {
            "projection_id" : <?=$_REQUEST['projection_id']?>
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
        },{         
            data : "Action" ,
            render : function(data, type, row) {
                var table = "Closure_details";
                var edit = ''; var del = '';
                if (row.user_id == <?=$_SESSION['ID']?>) {
                    var edit = '<div class="text-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit" onclick = "updateClosureDetails('+row.ID+','+row.projection_id+')"><i class="bi bi-pencil-fill"></i></div>';
                    var del = '<div class="text-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete" onclick = "deleteClosureDetails('+row.ID+',&#39;Closure_details&#39;)"><i class="bi bi-trash-fill"></i></div>';
                }
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
    $('#viewClosureTable').DataTable(viewClosureSettings);
});

$('#hide-modal-view').click(function() {
    $('.modal').modal('hide');
});

function updateClosureDetails(closure_id,projection_id) {
    $('#view-lgmodal').modal('hide');
    setTimeout(function(){
        $.ajax({
            url : "/app/addProjection/insertAndUpdateClosure",
            type : 'post', 
            data : {
                closure_id,
                projection_id
            },
            success : function(data) {
                $('#lg-modal-content').html(data);
                $('#lgmodal').modal('show');
            }
        })
    },true);
}

function deleteClosureDetails(id,table) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, Process.'
    }).then((isConfirm) => {
        if (isConfirm.value === true) {
            $.ajax({
                url: "/app/common/deleteData", 
                type: 'POST',
                dataType: 'json',
                data: {
                    id ,
                    table
                },
                success: function(data) {
                    if (data.status == 200) {
                    toastr.success(data.message);
                    $('#viewClosureTable').DataTable().ajax.reload(null, false);
                    $('#projectionTable').DataTable().ajax.reload(null, false);
                    } else {
                    toastr.error(data.message);
                    $('#viewClosureTable').DataTable().ajax.reload(null, false);
                    $('#projectionTable').DataTable().ajax.reload(null, false);
                    }
                }
            });
        } else {
            $('#viewClosureTable').DataTable().ajax.reload(null, false);
        }
    });  
}

</script>