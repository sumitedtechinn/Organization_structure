<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-top.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-bottom.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/topbar.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/menu.php');?>
<?php
$searchQuery = '';
if($_SESSION['role'] == '3') {
    $searchQuery .= "AND ID = '2'";
}
$roles = $conn->query("SELECT * FROM roles WHERE Deleted_At IS NULL $searchQuery");
$roles_details = [];
if ($roles->num_rows > 0 ) {
    $roles_details = mysqli_fetch_all($roles,MYSQLI_ASSOC);
}
?>

<main class="page-content">
    <div class="card-body">
        <h6 class="mb-0 text-uppercase">Role List</h6>
        <div class="my-3 border-top"></div>
        <div class="row row-cols-1 row-cols-lg-3 justify-content-start g-lg-4">
            <?php if(!empty($roles_details)) { 
                foreach ($roles_details as $role) { ?>
                    <div class="col">
                        <div class="card">
                            <div class="card-body">
                                <div>
                                    <h5 class="card-title"><?=$role['name']?></h5>
                                </div>
                                <p class="card-text">Customize <?=$role['name']?> role & responsibility</p>
                                <?php if(in_array('Role Update',$_SESSION['permission'])) { ?>
                                <button onclick="updateRolePermission('<?=$role['name']?>','<?=$role['ID']?>','<?=$role['guard_name']?>')" class="btn btn-info text-white" style="font-size: small;">Edit Permission</button>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php } ?>
            <?php if( in_array('Role Create',$_SESSION['permission'])) { ?>
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <div>
                            <h5 class="card-title">Create Role</h5>
                        </div>
                        <p class="card-text">Add role,if does not exist</p>
                        <button class="btn btn-info text-white" style="font-size: small;" onclick="addrole()">Add Role</button>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0" id = "table-heading">Roles Info</h5>
                <?php if(in_array('Role Delete',$_SESSION['permission'])) { ?>
                <div id="page-button">
                    <div class="theme-icons shadow-sm p-2 cursor-pointer rounded" title="Go to Trash" data-bs-toggle="tooltip" id = "trash_button">
                        <i class="bi bi-trash-fill"></i>
                    </div>
                    <button class="btn btn-primary" style="font-size: small;" id ="return_button">Go To Role</button>
                </div>
                <?php } ?>
            </div>
            <div class="table-responsive mt-3">
                <table class="table align-middle" id="roleTable">
                    <thead class="table-info">
                        <tr>
                            <th>Name</th>
                            <th>Guard Name</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</main>

<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer-top.php');?>
<script type="text/javascript">

var roleSettings = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    'ajax': {
        'url': '/app/roles/role-server.php',
        'type': 'POST',
        'data' : function(d) {
            d.roleType = "role";
        }
    },
    'columns': [
        {
            data: "name" ,
        },{
            data: "guard_name" ,
        },{
            data : "created_at"
        },{         
            data : "Action" ,
            render : function(data, type, row) {
                var table = "roles";
                var edit = '';var del = '';
                <?php if(in_array('Role Update',$_SESSION['permission'])) { ?>
                var edit = '<div class="text-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit" onclick = "updateDetails('+row.ID+')"><i class="bi bi-pencil-fill"></i></div>';
                <?php } ?>
                <?php if(in_array('Role Delete',$_SESSION['permission'])) { ?>
                var del = '<div class="text-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete" onclick = "deleteRoleDetails('+row.ID+',&#39;'+table+'&#39;)"><i class="bi bi-trash-fill"></i></div>';
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


var roleTrashSettings = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    'ajax': {
        'url': '/app/roles/role-server.php',
        'type': 'POST',
        'data' : function(d) {
            d.roleType = "deleteRole";
        }
    },
    'columns': [
        {
            data: "name" ,
        },{
            data: "guard_name" ,
        },{
            data : "created_at"
        },{         
            data : "Action" ,
            render : function(data, type, row) {
                var table = "roles";
                var restore = '<button type="button" class="btn btn-info text-white px-4" onclick = "restoreRoleDetails('+row.ID+',&#39;'+table+'&#39;)">Restore</button>';
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
    $('#roleTable').DataTable(roleSettings);
});

$("#trash_button").on('click',function(){
    $("#table-heading").html("Deleted Roles Info");
    $('#roleTable').DataTable(roleTrashSettings);
    $("#return_button").css('display','block');
    $("#trash_button").css('display','none');
});

$("#return_button").on('click',function(){
    $("#table-heading").html("Roles Info");
    $('#roleTable').DataTable(roleSettings);
    $("#return_button").css('display','none');
    $("#trash_button").css('display','block');
});


function addrole(){
    $.ajax({
        url : "/app/roles/addRole",
        type : 'get',
        success : function(data){
            $('#sm-modal-content').html(data);
            $('#smmodal').modal('show');
        }
    });
}

function updateRolePermission(fullname,id,guard_name) {
    $.ajax({
        url : "/app/roles/updateRolePermission",
        type : 'POST',
        data : {
            fullname,
            id,
            guard_name
        },
        success : function(data){
            $('#extra-large-modal-content').html(data);
            $('#extraLargeModal').modal('show');
        }
    });
}

function updateDetails(id) {

    $.ajax({
        url : "/app/roles/addRole" , 
        type : 'post' ,
        data : {
            id
        },
        success : function(data) {
            $('#sm-modal-content').html(data);
            $('#smmodal').modal('show');
        }
    });
}

function deleteRoleDetails(id,table) {
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
                    Window,location.reload();
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

function restoreRoleDetails(id,table) {
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
                  window.location.reload();
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

</script>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer-bottom.php');?>