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
                        <th>Received Amount</th>
                        <th>Deposit Amount</th>
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
            render : function(data,type,row) {
                if(data != '') {
                    return '<div><span>₹</span>'+data+'</div>';
                } else {
                    return '<div>-----</div>';
                }
            }
        },{
            data : "deposit_amount",
            render : function(data,type,row) {
                if(data != '') {
                    return '<div><span>₹</span>'+data+'</div>';
                } else {
                    return '<div>-----</div>';
                }
            }
        },{
            data : "Action",
            render : function(data, type, row) {
                var edit = '';var del = '';
                const d = formateDate();
                let user_role = '';
                <?php if($_SESSION['role'] == '2') { ?>
                    user_role = 'user';
                <?php } else { ?>
                    user_role = 'admin';
                <?php } ?>
                if( user_role == 'user') {
                    if (row.create_date.localeCompare(d) == 0) {
                        edit = '<div class="text-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit" onclick = "updateAdmissionDetails('+row.ID+')"><i class="bi bi-pencil-fill"></i></div>';
                        del = '<div class="text-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete" onclick = "deleteAdmissionDetails('+row.ID+')"><i class="bi bi-trash-fill"></i></div>';
                    }
                } else {
                    edit = '<div class="text-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit" onclick = "updateAdmissionDetails('+row.ID+')"><i class="bi bi-pencil-fill"></i></div>';
                    del = '<div class="text-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete" onclick = "deleteAdmissionDetails('+row.ID+')"><i class="bi bi-trash-fill"></i></div>';
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
    $('#viewAdmissionTable').DataTable(viewAdmissionSettings);
});

$('#hide-modal-view').click(function() {
    $('.modal').modal('hide');
});

function updateAdmissionDetails(id) {
    $('.modal').modal('hide');
    if(id != undefined) {
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
}

function deleteAdmissionDetails(id) {
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
                url: "/app/dailyReporting/deleteAdmissionDetails", 
                type: 'POST',
                dataType: 'json',
                data: {id},
                success: function(data) {
                    if (data.status == 200) {
                        toastr.success(data.message);
                        $('.table').DataTable().ajax.reload(null, false);
                    } else {
                        toastr.error(data.message);
                        $('.table').DataTable().ajax.reload(null, false);
                    }
                }
            });
        } else {
            $('.table').DataTable().ajax.reload(null, false);
        }
    });
}

function formateDate() {
    const today = new Date();
    const yyyy = today.getFullYear();
    let mm = today.getMonth() + 1; // Months start at 0!
    let dd = today.getDate();

    if (dd < 10) dd = '0' + dd;
    if (mm < 10) mm = '0' + mm;

    const formattedToday = dd + '/' + mm + '/' + yyyy;
    return formattedToday;
}

</script>