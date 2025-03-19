<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-top.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-bottom.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/topbar.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/menu.php');?>

<style>

.dataTables_length {
    display: none !important;
}

.ticket_idformat {
    font-size: medium;
    font-weight: 500;
    margin-left: 0.4rem;
}

.view-btn {
    background: #3461ff;
    color: white;
    padding: 2px 8px;
    border-radius: 5px;
    font-weight: 500;
    transition: 0.3s;
    box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.2);
    align-items: center;
    border: none;
}

.view-btn:hover {
    background: #138496;
}

[aria-label^="Ticket Id"] , [aria-label^="Ticket Name"] , [aria-label^="Ticket Details"] , [aria-label^="Status"] {
    /* background-color: white !important; */
    font-weight: 500;
    width: 3rem;
}

.paginate_button.previous a, .paginate_button.next a,.paginate_button.active a {
    padding: 3px;
    font-size: 12px;
    margin-right: 5px;
}

#ticketTable_info {
    font-size: small;
    padding-bottom: 4px;
    padding-left: 4px;
}

.dataTables_filter {
    margin-right: 0.7rem;
}

.sidebarHeader {
    font-size: medium;
    font-weight: 500;
    color: #6f6c6c;
    margin-bottom: 0.5rem;
}

.sideBarSmallHeader{
    font-size: 16px;
    font-weight: 500;
    color: grey;
}

.nav_course_h::-webkit-scrollbar {
  width: 2px;
  /* Adjust scrollbar width */
}

.nav_course_h::-webkit-scrollbar-thumb {
  background-color:rgba(106, 125, 167, 0.48);
  /* Scrollbar thumb color */
  border-radius: 10px;
  /* Rounded edges */
}

.nav_course_h::-webkit-scrollbar-track {
  background-color: #f8f8f8;
  /* Track color */
}

.statusStyle{
    border-radius: 20px;
    font-size: smaller;
}

</style>
<main class="page-content">
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><?=setPageHeader()?> </h5>
                <div class="d-flex justify-content-end gap-2 col-sm-6">
                    <?php if(in_array('Tickets Create',$_SESSION['permission'])) { ?>
                    <div class="d-flex align-items-center theme-icons shadow-sm p-2 cursor-pointer rounded gap-2 bg-primary" title="New Ticket" style="color: white;" onclick="addNewTicket()" data-bs-toggle = "tooltip"><span>Add New Ticket</span>
                        <i class="bi bi-plus-circle-fill" id="add_new_ticket"></i>
                    </div>
                    <?php } ?>
                </div>
            </div>           
        </div>
    </div>
    <div class="row">
        <div class="col-md-4"> 
            <div class="card">
                <div class="table-responsive mt-3">
                    <table class="table align-middle w-100" id="ticketTable">
                        <thead class="table-primary">
                            <tr>
                                <th>Id</th>
                                <th>Ticket Name</th>
                                <th>Status</th>
                                <th>Ticket Details</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6" style="padding-left: 0px !important;">
            <div class="card" id="ticket_information_tab"></div>
        </div>
        <div class="col-md-2" style="padding-left: 0px !important;">
            <div class="card" id="ticket_sidebar"></div>
        </div> 
    </div>
</main>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer-top.php');?>
<script src="/assets/plugins/select2/js/select2.min.js"></script>
<script src="/assets/js/form-select2.js"></script>
<script type="text/javascript">

var ticketSetting = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    'ajax': {
        'url': '/app/tickets/ticket-server', 
        'type': 'POST',
    },
    'columns': [
        {
            data: "unique_id",
            render : function(data,type,row) {
                return '<div class = "fw-medium mb-1">'+data+'</div>';
            }
        },{
            data: "task_name",
            render : function(data,type,row) {
                let task_name = '<div class="text-medium fw-medium text-secondary mb-1">'+data+'</div>';
                return '<div class ="col-sm-12">'+task_name+'</div><div class = "d-flex justify-content-between"><span class="text-muted small">Created At: '+row.create_date+'</span></div>';
            } 
        },{
            data: "status",
            render : function(data,type,row) {
                let statusaName = row.statusName;
                let statusColor = row.statusColor;
                return '<div class = "d-flex align-item-center justify-content-center p-2 statusStyle" style="background-color:'+statusColor+'">'+statusaName+'</div>';
            }
        },{
            data : "view" ,
            render : function(data,type,row) {
                let sqNo = row.sqNo;
                let view = '<button class="view-btn" id = "view_btn_'+sqNo+'" data-value = "'+row.ID+'" onclick = "viewTicketDetails('+row.ID+')">View</button>';
                return '<div class="d-flex justify-content-center align-items-center">'+view+'</div>';
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

document.addEventListener("DOMContentLoaded",function(){
    $("#ticketTable").DataTable(ticketSetting);
    const fetchTicketDetails = new Promise((resolve,reject) => {
        setTimeout(async function(){
            let ticketElement = document.getElementById("view_btn_1");    
            if (ticketElement) {
                let ticket_id = ticketElement.getAttribute("data-value");    
                await viewTicketDetails(ticket_id);
                resolve("Ticket details fetch");
            } else {
                reject("Element with ID 'view_btn_1' not found.");
            }
        },250);
    });
});

async function getMethod(url) {
    try {
        let response = await fetch(url, {
            method : "GET",         
        });
        if(!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        const data = await response.text();
        return data;
    } catch (error) {
        console.error('There has been a problem with your fetch operation:', error);
        return null;
    }
}

async function postMethod(url,params) {
    try {
        let response = await fetch(url, {
            method : "POST",         
            body : JSON.stringify(params)
        });
        if(!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        const data = await response.text();
        return data;
    } catch (error) {
        console.error('There has been a problem with your fetch operation:', error);
        return null;
    }
}

async function fetchData(url,params) {
    try {
        let response = await fetch(url,{
            method : "POST" , 
            body : JSON.stringify(params)
        });
        if(!response.ok) {
            console.error(`HTTP error! Status: ${response.status}`);
        }
        const data = await response.json();
        return data;   
    } catch (error) {
        console.error('There has been a problem with your fetch operation:', error);
        return null;
    }
}

async function addNewTicket() {
    let url = "/app/tickets/insertAndupdateTickets"; 
    const data = await getMethod(url);
    if( data != null) {
        $('#lg-modal-content').html(data);
        $('#lgmodal').modal('show');
    }
} 

async function viewTicketDetails(id) {
    await Promise.all([
        fetchTicketDiscription("/app/tickets/viewTicketDescriptionDetails",id),
        fitchTicketSideBar("/app/tickets/viewTicketSideBar",id)
    ]);
    showDeadLine();
}

async function fetchTicketDiscription(url,id) {
    const data = await postMethod(url,{id});
    if( data != null) {
        document.getElementById("ticket_information_tab").innerHTML = data;
    }
}

async function fitchTicketSideBar(url,id) {
    const data = await postMethod(url,{id});
    if( data != null) {
        document.getElementById("ticket_sidebar").innerHTML = data;
        getSideBarInputFieldData();
    }
}

async function getSideBarInputFieldData() {
    let dropDownFiled = [...document.querySelectorAll("#priority,#status,#category,#department,#assignTo")].map((param) => {
        return { name : param.name , value : param.getAttribute("data-custom-value")}
    });
    const data = await fetchData("/app/tickets/getInputFieldData",dropDownFiled);
    for (const key in data) {
        document.getElementById(key).innerHTML = data[key];
    }
}

async function updateTicketInfo(selectedValue,ticket_id,methodName) {
    if (methodName == "updateAssignToUser") {
        let status = document.getElementById("status").getAttribute("data-custom-value").split("_").filter((param,index,array) => (index == array.length-1)).join(" ");
        if (status == '1') {
            const checkAssignUserTicket = await checkUserAssignTicketStatus(selectedValue);
            if (checkAssignUserTicket.status == '400') {
                passWarningMessage("Assignation Not Allow",checkAssignUserTicket.message);
                viewTicketDetails(ticket_id);
                return;
            }
            const deadLine_response =  await setDeadLineDate(ticket_id);
            if (deadLine_response.status == '400') {
                toastr.error(deadLine_response.message);
                return viewTicketDetails(ticket_id);
            }
            let updateSelectedValue = '7';
            await fetchData("/app/tickets/storeAndupdateTicket",{"selectedValue" : updateSelectedValue,ticket_id,"method":"updateStatus"});
        }
    }
    if(methodName == 'updateStatus') {
        let status = document.getElementById("status").value;
        let previousStatus = document.getElementById("status").getAttribute("data-custom-value").split("_").filter((param,index,array) => (index == array.length-1)).join(" ");
        // Condition for change the Status from Hold to Any Other Stats 
        if (previousStatus == '6') {
            /**
             * 1) If status in hold then Review and Close Not Possible
             * 2) And for other then that show Privious Deadline with update option 
             */
            if ( status == '4' || status == '5') {
                passWarningMessage("Status Change Not Allow","Status change from hold to review or close are Nnot allowed");
                viewTicketDetails(ticket_id);
                return ;
            }
            const deadLine_response =  await setDeadLineDate(ticket_id);
            if (deadLine_response.status == '400') {
                toastr.error(deadLine_response.message);
                return viewTicketDetails(ticket_id);
            }

        } // Condition for status change to review
        else if (status == '4') {
            //Give one alert notice to user before updating the status
            const response = await passReConfirmationMessage();
            if(!response) {
                viewTicketDetails(ticket_id);
                return;
            }

        }  // Condition for status change to close
        else if (status == '5') {
            /**
             * 1) First check request user is assign by user or not
             * 2) If assign_by then one notice for confermation and if not then just pass the message  
             */
            const userStatus = await fetchData("/app/tickets/storeAndupdateTicket",{ticket_id,"method":"checkUserIsAssignByUserOrNot"});
            if (userStatus.status == 200) {
                const response = await passReConfirmationMessage();
                if(!response) {
                    viewTicketDetails(ticket_id);
                    return;
                }
            } else {
                passWarningMessage("Not Allow To Close",data.message);
                viewTicketDetails(ticket_id);
                return ;    
            }
         
        }  // Condition for status change to hold
        else if (status == '6') {
            /**
             * 1) Need to pass the reason and Person Name
             */
            const holdTicketFormResponse = await holdTicketForm(ticket_id,status);
            if (holdTicketFormResponse.status == '200') {
                toastr.success(holdTicketFormResponse.message);
            } else {
                toastr.error(holdTicketFormResponse.message);
                viewTicketDetails(ticket_id);
                return;
            }
        } // Condition for status change to Re-open
        else if (status == '8') {
            /**
             * 1) Show Privious Deadline with update option 
             */
            const deadLine_response =  await setDeadLineDate(ticket_id);
            if (deadLine_response.status == '400') {
                toastr.error(deadLine_response.message);
                return viewTicketDetails(ticket_id);
            }
        }  
    }
    const data = await fetchData("/app/tickets/storeAndupdateTicket",{selectedValue,ticket_id,"method":methodName});
    if( data != null) {
        if (data.status == 200) {
            toastr.success(data.message);
            viewTicketDetails(ticket_id);
            $('.table').DataTable().ajax.reload(null, false);
        } else {
            toastr.error(data.message);
        }
    }
}

async function holdTicketForm(ticket_id,status){
    const data = await getMethod("/app/tickets/viewHoldTicketForm");
    if( data != null) {
        return new Promise((resolve,reject) => {

            $('#md-modal-content').html(data);
            $('#mdmodal').modal('show');

            // Handle form submission
            $("#form-holdTicket").off("submit").on("submit", function (e) {
                e.preventDefault();
                if ($("#form-holdTicket").valid()) {
                    let comment = document.getElementById("hold_ticket_comment").value;
                    let user_name = document.getElementById("hold_by").value;
                    let final_comment = `${comment} \nHold By : ${user_name}`;
                    $("#spinner").css("display", "block");
                    $.ajax({
                        url: this.action,
                        type: "POST",
                        contentType : "application/json" , 
                        data: JSON.stringify({
                            comment: final_comment,
                            method: "insertComment",
                            ticket_id: ticket_id
                        }),
                        dataType: "json",
                        success: function (response) {
                            $("#spinner").css("display", "none");
                            if (response.status == 200) {
                                $(".modal").modal("hide");
                                resolve(response);
                            } else {
                                toastr.error(response.message);
                                reject(response);
                            }
                        },
                        error: function (err) {
                            toastr.error("Something went wrong!");
                            reject(err);
                        }
                    });
                }
            });

            // Handle Cancel form submission
            $("#cancelsetDeadline").on('click',function(){
                let response = {status : 400 , message : "Hold Comment not insert"};
                resolve(response);
            });
        });
    }
}

async function passReConfirmationMessage() {
    let result = await Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, Process.'
    });
    return (result.isConfirmed) ? true : false;
}

function passWarningMessage(title,text) {
    Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        confirmButtonColor: '#3085d6',
    });
}

/**
 *  Check User Already Assign the New Development on Any Ticket
 */
async function checkUserAssignTicketStatus(params) {
    const data = await fetchData("/app/tickets/storeAndupdateTicket",{"assignTo" : params,"method":"checkUserStatusAsDevelopment"});
    if(data != null) {
        return data;
    }
}

let countDownInterval;

function showDeadLine() {   
    let timer = document.getElementById("timer");
    const countDownMessage  = {
        '5' : {class : "bg-success" , message : "Ticket Close"} , 
        '4' : {class : "bg-warning" , message : "Ticket In-review"} ,
    }
    const [deadline_date,status] = timer.getAttribute("data-custom-value").split("_");
    if (deadline_date == "deadLine not set") {
        timer.style.display = "none";
        return;
    }
    countDownDate = new Date(deadline_date).getTime();
    if(countDownInterval) {
        clearInterval(countDownInterval);
    }

    if( status == '5' || status == '4') {
        timer.classList.add( countDownMessage[status]['class'],"show");
        timer.innerHTML = countDownMessage[status]['message'];
        return;
    }

    countDownInterval = setInterval(function() {
    let now = new Date().getTime();
    let distance = countDownDate - now;

    // If the count down is finished, write some text
    if (distance < 0) {
        clearInterval(countDownInterval);
        timer.innerHTML = "DeadLine Exceed";
        timer.classList.add("bg-danger","show");
        return;
    }

    // Time calculations for days, hours, minutes and seconds
    var days = Math.floor(distance / (1000 * 60 * 60 * 24));
    var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    var seconds = Math.floor((distance % (1000 * 60)) / 1000);
    
    timer.innerHTML = days + "d " + hours + "h "
    + minutes + "m " + seconds + "s ";

    timer.classList.add((days > 1) ? "bg-success" : "bg-warning" ,"show");
    }, 1000);
}

async function viewTicketHistory(ticket_id) {
    const data = await postMethod("/app/tickets/viewTicketHistory",{ticket_id});
    if( data != null) {
        $('#extra-large-modal-content').html(data);
        $('#extraLargeModal').modal('show');
    }
}

async function setDeadLineDate(ticked_id) {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: "/app/tickets/setDeadLineDate",
            type: "POST",
            data : {ticked_id} ,
            success: function (data) {
                if (data) {
                    $('#md-modal-content').html(data);
                    $('#mdmodal').modal('show');

                    // Handle form submission
                    $("#form-deadline").off("submit").on("submit", function (e) {
                        e.preventDefault();
                        if ($("#form-deadline").valid()) {
                            var formData = new FormData(this);
                            formData.append("ticket_id",ticked_id);
                            $("#spinner").css("display", "block");
                            $.ajax({
                                url: this.action,
                                type: "POST",
                                data: formData,
                                cache: false,
                                contentType: false,
                                processData: false,
                                dataType: "json",
                                success: function (response) {
                                    $("#spinner").css("display", "none");
                                    if (response.status == 200) {
                                        $(".modal").modal("hide");
                                        resolve(response);
                                    } else {
                                        toastr.error(response.message);
                                        reject(response);
                                    }
                                },
                                error: function (err) {
                                    toastr.error("Something went wrong!");
                                    reject(err);
                                }
                            });
                        }
                    });

                    // Handle Cancel form submission
                    $("#cancelsetDeadline").on('click',function(){
                        let response = {status : 400 , message : "DeadLine Date Not Set"};
                        resolve(response);
                    });
                }
            },
            error: function (err) {
                reject(err);
            }
        });
    });
}


function handleFileUpload(event) {
    let selectedFiles = [];
    let files = event.target.files; // Get selected files
    for (let i = 0; i < files.length; i++) {
        selectedFiles.push(files[i]); // Store each file
    }
    selectedFiles.forEach((file) => {
        let card = document.createElement("div");
        card.id = file.name;
        card.style.marginLeft = "0";
        let selectFileName = file.name.split("/").filter((param,index,array) => index == array.length-1);
        card.classList.add("badge", "attachment_style" , "gap-1");
        let nameContainer = document.createElement("span");
        nameContainer.innerHTML = selectFileName[0];
        let removeSpan = document.createElement("span");
        removeSpan.innerText = "x";
        removeSpan.classList.add("remove-btn", "ms-2", "cursor-pointer");
        removeSpan.style.color = "white";
        removeSpan.style.cursor = "pointer";
        removeSpan.onclick = function () {
            removeFile(card.id);
        };
        card.append(nameContainer);
        card.append(removeSpan);
        document.getElementById("attachment_box").append(card);
        localStorage.setItem(card.id,file);
    })
}

function removeFile(id) {
    document.getElementById(id).remove();
    localStorage.removeItem(id);
}

$(document).ready(function(){
    $(document).on("submit", "#comment_form",function(e) {
        e.preventDefault();
        let comment = document.getElementById("comment").value;
        if(comment == '') {
            document.getElementById("comment_empty").textContent = "Please insert any comment";
            $("#comment").focus()
            return false;
        } else {
            document.getElementById("comment_empty").textContent = "";
        }
        let formData = new FormData(this);
        console.log(e);
        formData.append("method","insertComment");
        let ticket_id = $(document.activeElement).attr("id").split("_").filter((param,key,array) => array.length-1 == key);
        formData.append("ticket_id",ticket_id[0]);
        $.ajax({
            url: "/app/tickets/storeAndupdateTicket",
            type: 'post',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(data) {
                if (data.status == 200) {
                    toastr.success(data.message);
                    viewTicketDetails(ticket_id[0]);
                } else {
                    toastr.error(data.message);
                }
            }
        });
    })
})

</script>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer-bottom.php');?>  
