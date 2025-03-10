<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-top.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-bottom.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/topbar.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/menu.php');?>
<style>

thead {
    background-color: #f2f4f8;
    font-size: medium;
}

.badgeStyle{
    font-weight: 500 !important; 
    font-size :15px;
}

</style>

<main class="page-content">
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Leave Type</h5>
                <div class="d-flex justify-content-end gap-2 col-sm-6">
                    <?php if(in_array('Leave Type Delete',$_SESSION['permission'])) { ?>
                    <div class="theme-icons sha dow-sm p-2 cursor-pointer rounded" title="Go to Trash" data-bs-toggle="tooltip" id = "trash_button">
                        <i class="bi bi-trash-fill"></i>
                    </div>
                    <button class="btn btn-primary" style="font-size: small;" id ="return_button">Go To LeaveType</button>
                    <?php } ?>
                    <?php if(in_array('Leave Type Create',$_SESSION['permission'])) { ?>
                    <div class="d-flex align-items-center theme-icons shadow-sm p-2 cursor-pointer rounded" title="Add Leave"  onclick="addLeaveType()" data-bs-toggle="tooltip">
                        <i class="bi bi-plus-circle-fill" id="add_leave"></i>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <div class="table-responsive mt-3">
                <table class="table align-middle w-100" id="leaveTypeTable" style="color: #515B73!important;">
                    <thead class="table-primary">
                        <tr>
                            <th>Sr.No</th>
                            <th>Leave Type</th>
                            <th>No. of Leave</th>
                            <th>Carry Forward</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</main>
<!--end page main-->

<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer-top.php');?>
<script>

var leaveTypeSetting = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    'ajax': {
        'url': '/app/leaveType/leaveTypeServer', 
        'type': 'POST',
    },
    'columns': [{
            data: "slno",
        },{
            data: "leaveName" ,
            render : function(data,type,row) {
                return '<div class="badgeStyle">'+data+'</div>';
            }
        },{
            data: "numOfLeave", 
            render : function(data, type, row) {
                return '<div class="badgeStyle"><i class="bi bi-calendar3"></i> '+data+' </div>';
            }
        },{
            data: "leaveCarryForward", 
            render : function(data, type, row) {
                return '<div class="badgeStyle"><i class="bi bi-calendar3"></i> '+data+' </div>';
            }
        },{         
            data : "Action",
            render : function(data, type, row) {
                var edit = ''; var del = '';
                <?php if(in_array('Leave Type Update',$_SESSION['permission'])) { ?>
                    edit = '<div class="text-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit" onclick = "updateDetails('+row.ID+')"><i class="bi bi-pencil-fill"></i></div>';
                <?php } else { ?>
                    edit = '<div data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit Disabled"><i class="bi bi-pencil-fill"></i></div>';
                <?php } ?>
                <?php if(in_array('Leave Type Delete',$_SESSION['permission'])) { ?>
                    del = '<div class="text-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete" onclick = "deleteDetails('+row.ID+',&#39;leaveType&#39;)"><i class="bi bi-trash-fill"></i></div>';
                <?php } else { ?>
                    del = '<div data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete Disabled"><i class="bi bi-trash-fill"></i></div>';
                <?php } ?>
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

var leaveTypeTrashSetting = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    'ajax': {
        'url': '/app/leaveType/leaveTypeServer', 
        'type': 'POST',
        'data' : function (d) {
            d.leaveType = 'deleteLeaveType'; 
        }
    },
    'columns': [{
            data: "slno",
        },{
            data: "leaveName" ,
            render : function(data,type,row) {
                return '<div class="badgeStyle">'+data+'</div>';
            }
        },{
            data: "numOfLeave", 
            render : function(data, type, row) {
                return '<div class="badgeStyle"><i class="bi bi-calendar3"></i> '+data+' </div>';
            }
        },{
            data: "leaveCarryForward", 
            render : function(data, type, row) {
                return '<div class="badgeStyle"><i class="bi bi-calendar3"></i> '+data+' </div>';
            }
        },{        
            data : "Action" ,
            render : function(data, type, row) {
                var table = 'leaveType';
                var restore = '<button type="button" class="btn btn-info text-white px-4" onclick = "restoreDetails('+row.ID+',&#39;'+table+'&#39;)">Restore</button>';
                var del = '<button type="button" class="btn btn-danger px-4" onclick = "parmanentDeleteDetails('+row.ID+',&#39;'+table+'&#39;)">Delete</button>';
                return '<div class = "table-actions d-flex align-items-center gap-3 fs-6">' + restore+del + '</div>';
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
    $("#return_button").css('display','none');
    $("#leaveTypeTable").DataTable(leaveTypeSetting);
});

$("#trash_button").on('click',function(){
    $("#leaveTypeTable").DataTable(leaveTypeTrashSetting);
    $("#return_button").css('display','block');
    $("#trash_button").css('display','none');
});

$("#return_button").on('click',function(){
    $("#leaveTypeTable").dataTable(leaveTypeSetting);
    $("#return_button").css('display','none');
    $("#trash_button").css('display','block');
});

function addLeaveType() {
    $.ajax({
        url : "/app/leaveType/insertAndupdateLeaveType",
        type : 'get',
        success : function(data){
            $('#md-modal-content').html(data);
            $('#mdmodal').modal('show');
        }
    });
}

function updateDetails(leaveType_id) {
    $.ajax({
        url : "/app/leaveType/insertAndupdateLeaveType",
        type : 'post',
        data : {leaveType_id},
        success : function(data){
            $('#md-modal-content').html(data);
            $('#mdmodal').modal('show');
        }
    });
}
</script>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer-bottom.php');?>