
<!-- Modals -->
<div class="modal fade slide-up" id="smmodal" tabindex="-1" role="dialog" data-bs-keyboard="false" data-bs-backdrop="static" aria-hidden="false">
  <div class="modal-dialog modal-sm">
    <div class="modal-content-wrapper">
      <div class="modal-content" id="sm-modal-content">
      </div>
    </div>
  </div>
</div>

<div class="modal fade slide-up" id="mdmodal" tabindex="-1" role="dialog" data-bs-keyboard="false" data-bs-backdrop="static" aria-hidden="false">
  <div class="modal-dialog modal-md">
    <div class="modal-content-wrapper">
      <div class="modal-content" id="md-modal-content">
      </div>
    </div>
  </div>
</div>

<div class="modal fade slide-up" id="lgmodal" tabindex="-1" role="dialog" data-bs-keyboard="false" data-bs-backdrop="static" aria-hidden="false">
  <div class="modal-dialog modal-lg">
    <div class="modal-content-wrapper">
      <div class="modal-content" id="lg-modal-content">
      </div>
    </div>
  </div>
</div>

<div class="modal fade slide-up" id="view-lgmodal" tabindex="-1" role="dialog" data-keyboard="false" data-backdrop="static" aria-hidden="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content-wrapper">
            <div class="modal-content" id="lg-modal-content-viewtable">
            </div>
        </div>
    </div>
</div>

<div class="modal fade fill-in" id="fullmodal" tabindex="-1" role="dialog" data-keyboard="false" data-backdrop="static" aria-hidden="true">
  <button aria-label="" type="button" class="close" data-dismiss="modal" aria-hidden="true">
    <i class="pg-icon">close</i>
  </button>
  <div class="modal-dialog" style="min-width: 100% !important">
    <div class="modal-content" style="display: inline-block" id="full-modal-content">

    </div>
  </div>
</div>

<!-- Modal this theme -->
<div class="modal fade" id="extraLargeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" id = "extra-large-modal-content"></div>
    </div>
</div>

<!-- Modal End -->
</div>
<!--end wrapper-->

<!-- Bootstrap bundle JS -->
<script src="/assets/js/bootstrap.bundle.min.js"></script>
<!--plugins-->
<script src="/assets/js/jquery.min.js"></script>
<script src="/assets/plugins/simplebar/js/simplebar.min.js"></script>
<script src="/assets/plugins/metismenu/js/metisMenu.min.js"></script>
<script src="/assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script>
<script src="/assets/plugins/vectormap/jquery-jvectormap-2.0.2.min.js"></script>
<script src="/assets/plugins/vectormap/jquery-jvectormap-world-mill-en.js"></script>
<script src="/assets/js/pace.min.js"></script>
<!--app -->
<script src="/assets/js/app.js"></script>
<!-- Toastr CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
<script src="/assets/plugins/datatable/js/jquery.dataTables.min.js"></script>
<script src="/assets/plugins/datatable/js/dataTables.bootstrap5.min.js"></script>
<script src="/assets/js/table-datatable.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-datetimepicker@2.5.21/build/jquery.datetimepicker.full.min.js"></script>
<script type="text/javascript">

function getVerticalList(vertical_id=null) {
  var organization_id = $("#organization").val();
  var branch = $('#branch').val();
  $.ajax({
    url : "/app/common/verticalList" ,
    type : "POST" , 
    data : {
      branch,
      organization_id,
      vertical_id
    },
    success : function(data) {
      $("#vertical").html(data);
      if ($("#vertical").val().length > 0 ) {
        $("#vertical").trigger('change');
      }
    }
  });
}

function checkAssignDetails(id,search) {
    $.ajax({
        url : "/app/common/checkAssignDetails" ,
        type : "post", 
        data : {
          id,
          search,
        },
        dataType : "json",
        success : function(data) {
            if(data.status == 200) {
                deleteDetails(id,search);
            } else {
                Swal.fire({
                    title : data.title,
                    text : data.text ,
                    icon: 'error',
                })
            }
        }
    })
}

function deleteDetails(id,table) {
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
                    $('.table').DataTable().ajax.reload(null, false);
                    if(table == 'Designation') {
                      initializeChart();
                    }
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

function restoreDetails(id,table) {
  Swal.fire({
        title: 'Are you sure?',
        text: "You want to restore this!",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, Process.'
    }).then((isConfirm) => {
        if (isConfirm.value === true) {
          $.ajax({
              url : "/app/common/restoreData" ,
              type : "post",
              data : {
                  id , 
                  table
              },
              dataType: 'json',
              success : function(data) {
                if (data.status == 200) {
                  toastr.success(data.message);
                  $('.table').DataTable().ajax.reload(null, false);
                  if(table == 'Designation') {
                    initializeChart();
                  }
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

function parmanentDeleteDetails(id,table) {
  Swal.fire({
    title: 'Are you sure?',
    text: "Data completely remove from record!",
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, Process.'
    }).then((isConfirm) => {
        if (isConfirm.value === true) {
          $.ajax({
              url : "/app/common/hardDeleteData",
              type : "post" , 
              data : {
                  id,
                  table
              },
              dataType: 'json',
              success : function(data) {
                if (data.status == 200) {
                  toastr.success(data.message);
                  $('.table').DataTable().ajax.reload(null, false);
                } else {
                  toastr.error(data.message);
                  $('.table').DataTable().ajax.reload(null, false);
                }
              }
            })
          } else {
            $('.table').DataTable().ajax.reload(null, false);
          }
    });  
}

function setNodeColor(color,table) {
  Swal.fire({
        title: 'Are you sure?',
        text: "Want to update the node color!",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, Process.'
    }).then((isConfirm) => {
        if (isConfirm.value === true) {
          $.ajax({
            url : "/app/common/setNodeColor",
            type : "post" , 
            data : {
                color,
                table,
                method : "setNodeColor"
            },
            dataType: 'json',
            success : function(data) {
              if (data.status == 200) {
                toastr.success(data.message);
                $("#node_color").val(color);
              } else {
                toastr.error(data.message);
              }
            }
          })   
        } else {
          var previous = '';
          <?php if( isset($node_color) && !is_null($node_color)) { ?>
            previous = '<?=$node_color?>' ; 
          <?php } else { ?>
            previous = '#00000';
          <?php } ?>
          $("#node_color").val(previous);
        }
    });
}

function fetchNodeColor(table) {
  $.ajax({
    url : "/app/common/setNodeColor",
    type : "post" , 
    data : {
      table,
      method : "fetchNodeColor"
    },
    dataType: 'json',
    success : function(data) {
      if (data.status == 200) {
        $("#node_color").val(data.message);
      } else {
        toastr.error(data.message);
      }
    }
  })
}


async function seeAllNotification() {
  let session_fetchData = await fetchSessionData(["numOfTicketNotSeen","notificationCount"]);
  let notification_badge = session_fetchData.notificationCount;
  let ticket_notification = session_fetchData.numOfTicketNotSeen;
  let data = {heading:"",message:"",url:"",logo:""};
  let showAllNotification = [];
  if(ticket_notification > 0) {
    data.heading = "New Ticket";
    data.message = `${ticket_notification} new ticket generated`;
    data.url = "/task_allotment/tickets";
    data.logo = ["lni","lni-ticket"];
    let ticket_anchor = addAnchorTag(data);
    showAllNotification.push(ticket_anchor);
  }
  let notificationListTag = document.getElementsByClassName("header-notifications-list")[0];
  let notificationBadgeEle = document.getElementById("notify_badge_number");
  if (notification_badge > 0) {
    notificationBadgeEle.innerText = notification_badge;
    showAndHideBadge(notificationBadgeEle,"showBadge","hideBadge");
  } else {
    showAndHideBadge(notificationBadgeEle,"hideBadge","showBadge");
  }
  const itemsToRemove = notificationListTag.querySelectorAll('a.list_of_notification');
  itemsToRemove.forEach(item => item.remove());
  showAllNotification.map((param) => notificationListTag.append(param));
}

function addAnchorTag(data) {
  let anchor = makeElement("a",["dropdown-item","list_of_notification"]);
  anchor.href = data.url;
  let firstdiv = makeElement("div",["d-flex", "align-items-center"]);
  let notificationLogoDiv = makeElement("div",["notification-box", "bg-light-primary", "text-primary"]);
  let icon =  makeElement("i",data.logo);
  notificationLogoDiv.append(icon);
  let notificationContentDiv = makeElement("div",["ms-3","flex-grow-1"]);
  let header = makeElement("h6",["mb-0","dropdown-msg-user"]);
  header.innerText = data.heading;
  let message = makeElement("small",["mb-0", "dropdown-msg-text", "text-secondary" ,  "d-flex" ,  "align-items-center"]);
  message.innerText = data.message;
  notificationContentDiv.append(header,message);
  firstdiv.append(notificationLogoDiv,notificationContentDiv);
  anchor.append(firstdiv);
  return anchor;
}

function makeElement(tag,listClass) {
  let ele = document.createElement(tag);
  listClass.map((param) => ele.classList.add(param));
  return ele;
}

(function(){
  let notification_badge = <?=$_SESSION['notificationCount']?>;
  let notificationBadgeEle = document.getElementById("notify_badge_number");
  if (notification_badge > 0) {
    notificationBadgeEle.innerText = notification_badge;
    showAndHideBadge(notificationBadgeEle,"showBadge","hideBadge");
  } else {
    showAndHideBadge(notificationBadgeEle,"hideBadge","showBadge");
  }
})()

async function checkNotificationBadge() {
  let session_fetchData = await fetchSessionData(["notificationCount"]);
  let notification_badge = session_fetchData.notificationCount;
  let notificationBadgeEle = document.getElementById("notify_badge_number");
  if (notification_badge > 0) {
    document.getElementById("notify_badge_number").innerText = notification_badge;
    showAndHideBadge(notificationBadgeEle,"showBadge","hideBadge");
  } else {
    showAndHideBadge(notificationBadgeEle,"hideBadge","showBadge");
  }
}

function showAndHideBadge(notificationBadgeEle,addClass,removeClass) {
  if(notificationBadgeEle.classList.contains(removeClass)) {
    notificationBadgeEle.classList.remove(removeClass);
    notificationBadgeEle.classList.add(addClass);
  } else if (!notificationBadgeEle.classList.contains(addClass)) {
    notificationBadgeEle.classList.add(addClass);
  }
}

function fetchSessionData(sessionRequiredKey) {
  return new Promise((resolve,reject) => {
    $.ajax({
      url: "/app/common/getSessionData", 
      type: 'POST',
      data : {sessionRequiredKey},
      dataType: 'json',
      success: function(response) {
        resolve(response);
      },
      error : function(response) {
        reject("Issue to get the data");
      }
    });
  });
}

function checkNewData() {
  $.ajax({
    url: '/app/common/notificationUpdate',
    type: 'POST',
    data : {searchNotification : "Ticket"},
    dataType: 'json',
    success: function(data) {
      seeAllNotification();
    }
  });
}

setInterval(checkNewData, 5000*60); // Check every 5 seconds
</script>

<style>

div.dataTables_length {
    float: left;
    margin-bottom: 20px;
}

div.dataTables_filter {
    float: right;
}

div.dataTables_info {
    float: left;
}

div.dataTables_paginate {
    float: right;
}

div.DTTT {
    float: left;
    margin-right: 50px;
    margin-bottom: 20px;
}

div.buttons {
    clear: both;
}

</style>

