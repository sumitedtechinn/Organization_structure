
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
                table
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

