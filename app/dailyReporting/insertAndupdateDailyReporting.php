<?php 

include '../../includes/db-config.php';
session_start();

$dailyReport_details = []; 
if (isset($_REQUEST['report_id'])) {
    $daliyReport = $conn->query("SELECT * FROM `daily_reporting` WHERE id = '".$_REQUEST['report_id']."' AND Deleted_At IS NULL");
    $dailyReport_details = mysqli_fetch_assoc($daliyReport);
}

?>
<style>
    .select2-container {
        z-index: 999999 !important;
    }
    label{
        font-weight: 500;
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
            <h5 class="mb-0">
                <?php if (!empty($dailyReport_details)) { ?>
                    Update
                <?php } else { ?>
                    Add
                <?php } ?>  
                Daily Report</h5>
            <a type="button" class="close" data-dismiss="modal" id = "hide-modal" aria-hidden="true"><i class="bi bi-x-circle-fill"></i></a>
        </div>
        <hr/>
        <form role="form" id="form-dailyReporting" action="/app/dailyReporting/storeAndupdateDailyReporting" method="POST">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Total Call</label>
                    <div class="col-sm-12">
                        <input type="text" class="form-control" name="total_call" id = "total_call" value="<?php echo !empty($dailyReport_details) ? $dailyReport_details['total_call'] : '' ?>" placeholder="eg:-10">
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">New Call</label>
                    <div class="col-sm-12">
                        <input type="text" class="form-control" name="new_call" id = "new_call" value="<?php echo !empty($dailyReport_details) ? $dailyReport_details['new_call'] : '' ?>" placeholder="eg:-10">
                    </div>
                </div>
            </div>
            <?php if(!empty($dailyReport_details)) { ?>
            <div class="row mb-1">
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Doc Prepare</label>
                    <div class="col-sm-12">
                        <select type="text" class="form-control form-control-sm multiple-select select2" multiple="multiple" name="doc_prepare[]" id = "doc_prepare">
                        </select>
                    </div>
                </div>
            </div>
            <?php } ?>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <div class="col-sm-12  mt-4">
                        <button type="button" class="btn-sm btn-success " id = "add_new_closure">Add Doc Prepare</button>
                    </div>
                </div>
            </div>
            <div id = "add_closure"></div>
            <div class="row mb-1">
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Doc Received</label>
                    <div class="col-sm-12">
                        <select type="text" class="form-control form-control-sm multiple-select select2" multiple="multiple" name="doc_received[]" id = "doc_received">
                        </select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Deal Close</label>
                    <div class="col-sm-12">
                        <select type="text" class="form-control form-control-sm multiple-select select2" multiple="multiple" name="doc_close[]" id="doc_close">
                        </select>
                    </div>
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Number Of Meetings</label>
                    <div class="col-sm-12">
                        <input type="text" class="form-control" name="numofmeeting" id = "numofmeeting" value="<?php echo !empty($dailyReport_details) ? $dailyReport_details['numofmeeting'] : '' ?>" placeholder="eg:-5">
                    </div>
                </div>
                <div class="col-sm-6">
                <label class="col-sm-12 col-form-label">Report Date</label>
                    <div class="col-sm-12">
                        <input type="date" class="form-control form-control-sm" value="<?php echo !empty($dailyReport_details) ? $dailyReport_details['date'] : date('Y-m-d'); ?>" <?php if(!empty($dailyReport_details)) { ?> disabled <?php } ?> name="report_date">
                    </div>
                </div>
            </div>
            <hr/>
            <div class="row mb-2">
                <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary">
                    <?php if (!empty($dailyReport_details)) { ?>
                        Update
                    <?php } else { ?>
                        Register
                    <?php } ?>    
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="/assets/plugins/jquery-validation/js/jquery.validate.js"></script>
<script src="/assets/plugins/select2/js/select2.min.js"></script>
<script src="/assets/js/form-select2.js"></script>
<script>

$(function(){
    $('#form-dailyReporting').validate({
    rules: {
        total_call: {required:true}, 
        new_call : {required:true} , 
    },
    highlight: function (element) {
        $(element).addClass('error');
        $(element).closest('.form-control').addClass('has-error');
    },
    unhighlight: function (element) {
        $(element).removeClass('error');
        $(element).closest('.form-control').removeClass('has-error');
    }
    });
})

var center_count = 0;
$("#add_new_closure").on('click',function(){
    center_count++;
    $.ajax({
        url : "/app/dailyReporting/addClosureDetails" ,
        type : "post" ,
        data : {
            center_count
        },
        success : function(data) {
            $("#add_closure").append(data);
        }
    })
});

function addCenterCard(id) {
    center_count++;
    $.ajax({
        url : "/app/dailyReporting/addClosureDetails" ,
        type : "post" ,
        data : {
            center_count
        },
        success : function(data) {
            $("#add_closure").append(data);
        }
    });
}

function removeCenterCard(id) {
    var remove_center_count = id.split("_")[3];
    $("#center_card_"+remove_center_count).remove();
}

$(document).ready(function(){
    getCenterDocPrepareList();
    <?php if(!empty($dailyReport_details)) { ?>
        setTimeout(function(){
            getTodayCenterDocPrepareList();
        },1000);
    <?php } ?>
    setTimeout(function(){
        getCenterDocReceivedList();
    },1000);
});

function getCenterDocPrepareList() {
    var doc_received_list = '';
    <?php if(!empty($dailyReport_details) && !empty($dailyReport_details['doc_received'])) { ?>
        doc_received_list = '<?=$dailyReport_details['doc_received']?>';
    <?php } ?>
    var typeOfList = 'doc-prepare';
    $.ajax({
        url : "/app/common/docPrepareReceivedClosedList" ,
        type : "post",
        data : {
            typeOfList,
            doc_received_list
        },
        success : function(data) {
            $("#doc_received").append(data);
        }
    })
}

function getCenterDocReceivedList() {
    var doc_closed_list = '';
    <?php if( !empty($dailyReport_details) && !empty($dailyReport_details['doc_close'])) { ?>
        doc_closed_list = '<?=$dailyReport_details['doc_close']?>';
    <?php } ?>
    var typeOfList = 'doc-received';
    $.ajax({
        url : "/app/common/docPrepareReceivedClosedList" ,
        type : "post",
        data : {
            typeOfList,
            doc_closed_list
        },
        success : function(data) {
            $("#doc_close").append(data);
        }
    })
}

function getTodayCenterDocPrepareList() {
    var doc_prepare_list = '';
    <?php if( !empty($dailyReport_details) && !empty($dailyReport_details['doc_prepare'])) { ?>
        doc_prepare_list = '<?=$dailyReport_details['doc_prepare']?>';
    <?php } ?>
    var typeOfList = 'today-doc-prepare';
    $.ajax({
        url : "/app/common/docPrepareReceivedClosedList" ,
        type : "post",
        data : {
            typeOfList,
            doc_prepare_list
        },
        success : function(data) {
            $("#doc_prepare").append(data);
        }
    })
}



$('#hide-modal').click(function() {
    $('.modal').modal('hide');
});

$("#form-dailyReporting").on('submit',function(e){
    e.preventDefault();
    if ($("#form-dailyReporting").valid()) {
        var formData = new FormData(this);
        <?php if(isset($_REQUEST['report_id'])) { ?>
            formData.append('id',<?=$_REQUEST['report_id']?>);
        <?php } ?>
        formData.append("country_code",$(".iti__selected-dial-code").text());
        $.ajax({
            url: this.action,
            type: 'post',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(data) {
                if (data.status == 200) {
                    if(data.type == 'success') {
                        toastr.success(data.message);
                    } else if (data.type == 'warning') {
                        Swal.fire({
                            text: data.message,
                            title : "Daily report inserted",
                            icon: data.type,
                        });
                    }
                    $('.modal').modal('hide');
                    getFilterData();
                    $('#dailyReportTable').DataTable().ajax.reload(null, false);
                } else {
                    if (data.type == 'error') {
                        Swal.fire({
                            text: data.message,
                            title : "Daily Report status",
                            icon: data.type,
                        });
                    } else if (data.type == 'warning') {
                        Swal.fire({
                            text: data.message,
                            title : "Daily report inserted",
                            icon: data.type,
                        });
                        $('.modal').modal('hide');
                        getFilterData();
                        $('#dailyReportTable').DataTable().ajax.reload(null, false);
                    }
                }
            }
        });
    }
});

</script>
