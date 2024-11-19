<?php 

include '../../includes/db-config.php';
session_start();

$dailyReport_details = []; 
if (isset($_REQUEST['report_id'])) {
    $daliyReport = $conn->query("SELECT * FROM `daily_reporting` WHERE id = '".$_REQUEST['report_id']."' AND Deleted_At IS NULL");
    $dailyReport_details = mysqli_fetch_assoc($daliyReport);
}

$doc_close_id = '';
if(!empty($dailyReport_details['doc_close'])) {
    $doc_close_id = implode(',',json_decode($dailyReport_details['doc_close'],true));
}

$meeting_client = '';
$meeting_count = '';
$numOfMeeting = makeNumberOfMeetingDropDown();


function makeNumberOfMeetingDropDown() {
    global $dailyReport_details;
    global $meeting_client;
    global $meeting_count;
    $i = 0;
    $numOfMeeting = '';
    if(!empty($dailyReport_details['numofmeeting'])) {
        $numOfMeeting = is_numeric($dailyReport_details['numofmeeting']) ? $dailyReport_details['numofmeeting'] : count(json_decode($dailyReport_details['numofmeeting'],true));
        $meeting_count = $numOfMeeting;
        $meeting_client = '';
        if(!is_numeric($numOfMeeting)) {
            $meeting_client = json_decode($dailyReport_details['numofmeeting'],true);
            $meeting_client = implode(',',$meeting_client); 
        }
    }

    $option = '<option value = "">Select</option>';
    while($i < 10) {
        $i++;
        if ( !empty($dailyReport_details) &&  $i == $numOfMeeting) {
            $option .= "<option value ='$i' selected >$i</option>";
        } else {
            $option .= "<option value ='$i'>$i</option>";
        }
    }
    return $option;
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
                <div class="col-sm-12 mt-4 d-flex flex-row gap-1">
                    <button type="button" class="btn-sm btn-success" id = "add_new_closure">Add Doc Prepare</button>
                    <button type="button" class="btn-sm btn-success" id = "insert_addmission">Insert Admission</button>
                </div>
            </div>
            <div id = "add_admission"></div>
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
                        <select type="text" class="form-control form-control-sm multiple-select select2" multiple="multiple" name="doc_close[]" id="doc_close" onchange="handleClick(event)">
                        </select>
                    </div>
                </div>
            </div>
            <div id = "deal_close_amount_inputField" class="mt-1"></div>
            <div class="row mb-1">
                <div class="col-sm-6">
                    <label class="col-sm-12 col-form-label">Number Of Meetings</label>
                    <div class="row">
                        <div class="col-sm-3">
                            <select type="text" class="form-control form-control-sm single-select select2" name="numofmeeting" id = "numofmeeting" onchange="meetingInputfield(this.value)" <?php if(!empty($dailyReport_details)) { ?> disabled <?php } ?>>
                                <?=$numOfMeeting?>
                            </select>
                        </div>
                        <div class="col-sm-9" id="meetingInput"></div>
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

var admission_count = 0;
$("#insert_addmission").on('click',function(){
    admission_count++;
    $.ajax({ 
        url : "/app/dailyReporting/addAdmissionDetails",
        type : "post" ,
        data : {
            admission_count
        },
        success : function(data) {
            $("#add_admission").append(data);
        }
    })
})

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

function addAdmissionCard(id) {
    admission_count++;
    $.ajax({ 
        url : "/app/dailyReporting/addAdmissionDetails",
        type : "post" ,
        data : {
            admission_count
        },
        success : function(data) {
            $("#add_admission").append(data);
        }
    })
}

function removeCenterCard(id) {
    var remove_center_count = id.split("_")[3];
    $("#center_card_"+remove_center_count).remove();
}

function removeAdmissionCard(id) {
    var remove_admission_count = id.split("_")[3];
    $("#add_admission_card_"+remove_admission_count).remove();
}
 
function checkDocCloseAmount() {
    <?php if(!empty($dailyReport_details)) { ?> 
    <?php if (!empty($doc_close_id)) { ?>
        selectedOptions = '<?=$doc_close_id?>';
        createDocCloseAmountInputField(selectedOptions);
    <?php } else { ?>
        return false;
    <?php } } ?>
}

function handleClick(event) {
    let selectedOptions = Array.from(event.target.selectedOptions).map(option => option.value);
    selectedOptions = selectedOptions.join(",");
    createDocCloseAmountInputField(selectedOptions);                  
}

function createDocCloseAmountInputField(selectedOptions) {
    $.ajax({ 
        url : "/app/dailyReporting/addDealCloseAmount",
        type : "post",
        data : {selectedOptions},
        dataType: 'json',
        success : function(data) {
            for (const key in data) {
                if(key === 'insert') {
                    for (const value of data[key]) {
                        id = value['id'];
                        center_name = value['center_name'];
                        amount = value['amount'];
                        $("#deal_close_amount_inputField").append(getDealCloseAmountInputfield(id,center_name,amount));
                    }
                }
                if (key === 'remove') {
                    for (const value of data[key]) {
                        id = value['id'];
                        $("#center_create_amount_box_"+id).remove();    
                    }
                }
            }
        }
    })
}

function getDealCloseAmountInputfield(id,center_name,value) {
    let dealCloseAmountBox = '<div class="row mb-1" id = "center_create_amount_box_'+id+'">';
    dealCloseAmountBox += '<label class="col-sm-4 col-form-label">Center Created Amount for '+center_name+'</label>';
    dealCloseAmountBox += '<div class="col-sm-4">';
    dealCloseAmountBox += '<input type="text" class="form-control" name="center_create_amount_'+id+'" id = "center_create_amount_'+id+'" placeholder="eg:-10,000" onkeypress="return /[0-9]/i.test(event.key)" value = "'+value+'">';                
    dealCloseAmountBox += '</div></div>';
    return dealCloseAmountBox;
}

$(document).ready(function(){
    getCenterDocPrepareList();
    <?php if(!empty($dailyReport_details)) { ?>
        setTimeout(function(){
            getTodayCenterDocPrepareList();
            meetingInputfield();
        },1000);
    <?php } ?>
    setTimeout(function(){
        getCenterDocReceivedList();
        checkDocCloseAmount();
    },1000);
});

function getCenterDocPrepareList() {
    var doc_received_list = '';
    var user_id = '';
    <?php if(!empty($dailyReport_details)) { ?>
        var user_id = '<?=$dailyReport_details['user_id']?>';
    <?php } ?>
    <?php if(!empty($dailyReport_details) && !empty($dailyReport_details['doc_received'])) { ?>
        doc_received_list = '<?=$dailyReport_details['doc_received']?>';
    <?php } ?>
    var typeOfList = 'doc-prepare';
    $.ajax({
        url : "/app/common/docPrepareReceivedClosedList" ,
        type : "post",
        data : {
            typeOfList,
            doc_received_list,
            user_id
        },
        success : function(data) {
            $("#doc_received").append(data);
        }
    })
}

function getCenterDocReceivedList() {
    var doc_closed_list = '';
    var user_id = '';
    <?php if(!empty($dailyReport_details)) { ?>
        var user_id = '<?=$dailyReport_details['user_id']?>';
    <?php } ?>
    <?php if( !empty($dailyReport_details) && !empty($dailyReport_details['doc_close'])) { ?>
        doc_closed_list = '<?=$dailyReport_details['doc_close']?>';
    <?php } ?>
    var typeOfList = 'doc-received';
    $.ajax({
        url : "/app/common/docPrepareReceivedClosedList" ,
        type : "post",
        data : {
            typeOfList,
            doc_closed_list,
            user_id
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

function meetingInputfield(meetingCount = null) {
    var meeting_client = '';
    <?php if(!empty($dailyReport_details['numofmeeting'])) {?>
        meeting_client = '<?=$meeting_client?>';
        meetingCount = '<?=$meeting_count?>';
    <?php } ?>
    $.ajax({
        url : "/app/dailyReporting/makeInputField",
        type : "post",
        data : {
            meetingCount,
            meeting_client
        },
        success : function(data) {
            $("#meetingInput").html(data);
        }
    })
}

$('#hide-modal').click(function() {
    $('.modal').modal('hide');
    <?php if(isset($_SESSION['dealCloseCenterId'])) {
        unset($_SESSION['dealCloseCenterId']);
    } ?>
});

$('#lgmodal').on('hidden.bs.modal', function() {
    <?php if(isset($_SESSION['dealCloseCenterId'])) {
        unset($_SESSION['dealCloseCenterId']);
    } ?>
})

$("#form-dailyReporting").on('submit',function(e){
    e.preventDefault();
    if ($("#form-dailyReporting").valid()) {
        var formData = new FormData(this);
        <?php if(isset($_REQUEST['report_id'])) { ?>
            formData.append('id',<?=$_REQUEST['report_id']?>);
            formData.append('numofmeeting',<?=$_REQUEST['report_id']?>);
        <?php } ?>
        <?php if(!empty($dailyReport_details) && !empty($dailyReport_details['numofmeeting'])) { ?>
            formData.append('numofmeeting','<?=$meeting_count?>');
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
                            html : data.message,
                            title : "Daily report inserted",
                            icon : data.type,
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
                            html : data.message,
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
