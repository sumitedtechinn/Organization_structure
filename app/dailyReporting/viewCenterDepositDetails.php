<?php session_start(); ?>

<!-- Modal -->
<div class="card-body">
    <div class="border p-4 rounded">
        <div class="card-title d-flex align-items-center justify-content-between">
            <h5 class="mb-0">View Center Deposit</h5>
            <a type="button" class="close" data-dismiss="modal" id = "hide-modal-view" aria-hidden="true"><i class="bi bi-x-circle-fill"></i></a>
        </div>
        <hr/>
        <div class="table-responsive mt-3">
            <table class="table table-striped align-middle w-100" id="viewCenterDepositTable">
                <thead class="table-light"> 
                    <tr>
                        <th>Sl.No</th>
                        <th>Center Name</th>
                        <th>Deposit Amount</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
        <hr/>
    </div>
</div>

<script type="text/javascript">

var viewCenterDepositSettings = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    "searching": false ,
    'ajax': {
        'url': '/app/dailyReporting/viewCenterDeposit-server',
        'type': 'POST',
        'data' : {
            "center_deposit_ids" : '<?=$_REQUEST['center_deposit_ids']?>'
        }
    },
    'columns': [{
            data: "slno" ,
            render : function(data,type,row) {
                return '<div>'+data+'</div>';
            }
        },{
            data : "center_name" , 
        },{
            data : "deposit_amount",
            render : function(data,type,row) {
                return '<div class = "fw-bold"><span>₹ </span>'+data+'</div>';
            }
        },{
            data : "deposit_date",
        },{
            data : "Action",
            render : function(data, type, row) {
                var del = '';
                const d = formateDate();
                let user_role = '';
                <?php if($_SESSION['role'] == '2') { ?>
                    user_role = 'user';
                <?php } else { ?>
                    user_role = 'admin';
                <?php } ?>
                if( user_role == 'user') {
                    if (row.create_date.localeCompare(d) == 0) {   
                        del = '<div class="text-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete" onclick = "deleteDepositAmount('+row.ID+',&#39;'+row.center_id+'&#39;)"><i class="bi bi-trash-fill"></i></div>';
                    }
                } else {
                    del = '<div class="text-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete" onclick = "deleteDepositAmount('+row.ID+',&#39;'+row.center_id+'&#39;)"><i class="bi bi-trash-fill"></i></div>';
                }
                return '<div class = "table-actions d-flex align-items-center gap-3 fs-6">' +  del + '</div>';
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
    $('#viewCenterDepositTable').DataTable(viewCenterDepositSettings);
});

$('#hide-modal-view').click(function() {
    $('.modal').modal('hide');
});

function deleteDepositAmount(id,center_id) {
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
            let dailyReport_id = '<?=$_REQUEST['daily_report_id']?>';
            $.ajax({
                url: "/app/dailyReporting/deleteCenterDeposit", 
                type: 'post',
                data: {
                    id,
                    center_id,
                    dailyReport_id
                },
                dataType: 'json',
                success: function(data) {
                    if (data.status == 200) {
                        $('.modal').modal('hide');
                        toastr.success(data.message);
                        $('.table').DataTable().ajax.reload(null, false);
                    } else {
                        $('.modal').modal('hide');
                        Swal.fire({
                            text : data.message,
                            title : data.title,
                            icon : 'error',
                        });
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