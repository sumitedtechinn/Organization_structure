<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-top.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-bottom.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/topbar.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/menu.php');?>

<!--start content-->
<main class="page-content">
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0">User Details</h5>
                <div class="d-flex justify-content-end gap-2 col-sm-2">
                    <?php if(in_array('User Delete',$_SESSION['permission'])) { ?>
                    <div class="theme-icons shadow-sm p-2 cursor-pointer rounded" title="Go to Trash" data-bs-toggle="tooltip" id = "trash_button">
                        <i class="bi bi-trash-fill"></i>
                    </div>
                    <?php } ?>
                    <?php if( in_array('User Create',$_SESSION['permission'])) { ?>
                    <div class="theme-icons shadow-sm p-2 cursor-pointer rounded" title="Add" onclick="addUser()" data-bs-toggle="tooltip">
                    <i class="bi bi-person-plus-fill" id="add_user"></i>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <?php if($_SESSION['role'] != '2') { ?>
            <div class="d-flex align-items-center justify-content-between gap-1 mt-3">
                <div class="col-sm-2" style="z-index: 0 !important;">
                    <select type="text" class="form-control form-control-sm single-select select2" name="organization_filter" id="organization_filter" onchange="reloadTable(this.id)">
                    </select>
                </div>
                <div class="col-sm-2" style="z-index: 0 !important;">
                    <select type="text" class="form-control form-control-sm single-select select2" name="branch_filter" id="branch_filter" onchange="reloadTable(this.id)">
                    </select>
                </div>
                <div class="col-sm-2" style="z-index: 0 !important;">
                    <select type="text" class="form-control form-control-sm single-select select2" name="vertical_filter" id="vertical_filter" onchange="reloadTable(this.id)">
                    </select>
                </div>
                <div class="col-sm-3" style="z-index: 0 !important;">
                    <select type="text" class="form-control form-control-sm single-select select2" name="department_filter" id="department_filter" onchange="reloadTable(this.id)">
                    </select>
                </div>
                <div class="col-sm-2" style="z-index: 0 !important;">
                    <select type="text" class="form-control form-control-sm single-select select2" name="designation_filter" id="designation_filter" onchange="reloadTable(this.id)">
                    </select>
                </div>
            </div>
            <?php } ?>
            <div class="table-responsive mt-3">
                <table class="table align-middle" id="userTable">
                    <thead class="table-secondary">
                        <tr>
                            <th>Photo</th>
                            <th>User</th>
                            <th>Password</th>
                            <th>Organization Details</th>
                            <th>Address</th>
                            <th>Status</th>
                            <th>Assigned</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</main>
<!--end page main-->

<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer-top.php');?>
<script src="/assets/plugins/select2/js/select2.min.js"></script>
<script src="/assets/js/form-select2.js"></script>
<script type="text/javascript">

$(document).ready(function(){
    var filter_data_field = ['organization','branch','vertical','department','designation'];
    $.ajax({
        url : "/app/common/getAllFilterData", 
        type : "post",
        contentType: 'json',  // Set the content type to JSON 
        data: JSON.stringify(filter_data_field), 
        dataType: 'json', 
        success : function(data) {
            for (const key in data) {
                $("#"+key+"_filter").html(data[key]);
            }
        }   
    })
});

function reloadTable() {
    $('.table').DataTable().ajax.reload(null, false);
}

function addUser() {
    $.ajax({
        url : "/app/user/insertAndupdateUser", 
        type : 'get',
        success : function(data){
            $('#lg-modal-content').html(data);
            $('#lgmodal').modal('show');
        }
    });
}

var UserSettings = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    'ajax': {
        'url': '/app/user/user-server.php',
        'type': 'POST',
        'data' : function(d) {
            d.organizationFilter = $("#organization_filter").val();
            d.branchFilter = $("#branch_filter").val();
            d.verticalFilter = $("#vertical_filter").val();
            d.departmentFilter = $("#department_filter").val();
            d.designationFilter = $("#designation_filter").val();
        }
    },
    'columns': [{
            data: "image",
            render : function(data,type,row) {
                var img = '<div class="d-flex align-items-center gap-3 cursor-pointer"><img src="'+data+'" class="rounded-circle" width="44" height="44" alt=""></div>'
                return img;
            }
        },{
            data: "User" ,
            render : function(data, type, row) {
                var name = row.Name;
                var contact = row.Contact;
                var country_code = '';
                if(row.Country_code.length > 0) {
                    var country_code = row.Country_code;
                }
                var email = row.Email;
                var doj = row.doj;
                var designation_name = (row.designation == null) ? '<span class="fw-bold text-danger">Not Assigned</span>': '<span>'+row.designation+'</span>';
                return '<div style="font-size:small;"><p class = "mb-1"><b>Name : </b> '+name+'</p><p class = "mb-1"><b>Contact : </b>'+country_code+" "+contact+'</p><p class = "mb-1"><b>Email : </b>'+email+'</p><p class = "mb-1"><b>DOJ : </b>'+doj+'</p><p class = "mb-1 text-wrap" style = "width:200px;"><b>Designation : </b>'+designation_name+'</p></div>';
            }
        },{
            data: "password" ,
            render : function(data,type,row) {
                var pass = '<div class="row" style = "width:250px !important" ><div class="col-md-10"><input type="password" style="font-size:small; " class="form-control" disabled="" style="border: 0ch;" value="'+data+'" id="myInput'+ row.ID +'"></div><div class="col-md-2 mt-1"><i class = "bi bi-eye " onclick="showPassword('+ row.ID +')" ></i></div></div>';
                return pass;
            }
        },{
            data: "organization" ,
            render : function(data,type,row) {
                if (row.role_name == 'admin' && row.organization_name != null ) {
                    var department = '<span>All</span>';
                    var organization = '<span>'+row.organization_name+'</span>';
                    var vertical = '<span>All</span>';
                    var branch = (row.branch_name == null) ? '<span>All</span>': '<span>'+row.branch_name+'</span>';
                } else {
                    var department = (row.department == null) ? '<span class="fw-bold text-danger">Not Assigned</span>': '<span>'+row.department+'</span>';
                    var organization = (row.organization_name == null) ? '<span class="fw-bold text-danger">Not Assigned</span>': '<span>'+row.organization_name+'</span>';
                    var vertical = (row.vertical_name == null) ? '<span class="fw-bold text-danger">Not Assigned</span>': '<span>'+row.vertical_name+'</span>';
                    var branch = (row.branch_name == null) ? '<span class="fw-bold text-danger">Not Assigned</span>': '<span>'+row.branch_name+'</span>';
                }
                return '<div style="font-size:small;"><p class = "mb-1"><b>Organization : </b> '+organization+'</p><p class = "mb-1"><b>Branch : </b> '+branch+'</p><p class = "mb-1"><b>Vertical : </b> '+vertical+'</p><p class = "mb-1"><b>Department : </b> '+department+'</p></div>';
            } 
        },{
            data: "Address" ,
            render : function(data,type,row) {
                var country = row.Country;
                var state = row.State;
                var city = row.City;
                var locality = row.Address;
                return  '<div style="font-size:small;"><p class = "mb-1"><b>Country : </b>'+country+'</p><p class = "mb-1"><b>State : </b>'+state+'</p><p class = "mb-1"><b>City : </b>'+city+'</p><p class = "mb-1 text-wrap" style = "width:300px !important "><b>Locality : </b>'+locality+'</p></div>';
            } 
        },{
            data: "Status",
            render : function(data,type,row) {
                var check_user = '';
                <?php if($_SESSION['role'] == '2') { ?>
                    check_user = 'disabled';
                <?php } ?>
                var assigned_person = (row.assinged_person === null) ? '<button style="font-size:smaller" class="btn btn-danger btn-sm '+check_user+'" onclick = "assignReportingPerson('+row.ID+',&#39;'+row.organization_info_assign+'&#39;)">Not Assigned</button>': '<button style="font-size:smaller" class="btn btn-success btn-sm '+check_user+'" onclick = "assignReportingPerson('+row.ID+',&#39;'+row.organization_info_assign+'&#39;)">Assigned</button>';
                return '<div style="font-size:small;"><p class = "mb-1" ><b>Reporting : </b>'+assigned_person+'</p></div>';
            } 
        },{
            data : "Assign" , 
            render : function(data,type,row) {
                var role_name = row.role_name;
                var check_user = '';
                <?php if($_SESSION['role'] == '2') { ?>
                    check_user = 'disabled';
                <?php } ?>
                var assign = '<div class="col"><button type="button" class="btn btn-info '+check_user+'" style="font-size:small;color:white;" onclick = "assingOrganizationInfo('+row.ID+',&#39;'+role_name+'&#39;,&#39;'+row.organization_info_assign+'&#39;,&#39;'+row.designation_inside+'&#39;)">Assign</button></div>';
                return assign;
            }
        },{         
            data : "Action" ,
            render : function(data, type, row) {
                var edit = ''; var del = '';
                <?php if(in_array('User Update',$_SESSION['permission'])) { ?>
                edit = '<div class="text-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit" onclick = "updateDetails('+row.ID+')"><i class="bi bi-pencil-fill"></i></div>';
                <?php } ?>
                <?php if(in_array('User Delete',$_SESSION['permission'])) { ?>
                del = '<div class="text-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete" onclick = "checkAssignDetails('+row.ID+',&#39;users&#39;)"><i class="bi bi-trash-fill"></i></div>';
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

$(document).ready(function() {
    $('#userTable').dataTable(UserSettings);
});

function showPassword(id) {
    <?php if($_SESSION['role'] == '2') { ?>
        return;
    <?php } ?>
    var x = document.getElementById("myInput".concat(id));
    if (x.type === "password") {
        x.type = "text";
    } else {
        x.type = "password";
    }
}

function assingOrganizationInfo(id,role,organization_info_assign,page_type) {
    if(role == 'admin') {
        if (organization_info_assign == 'Yes') {
            $.ajax({
                url : "/app/user/adminOrganizationInfo", 
                type : 'post',
                data : {
                    page_type,
                    id
                },
                success : function(data){
                    $('#md-modal-content').html(data);
                    $('#mdmodal').modal('show');
                }
            });    
        } else {
            $.ajax({
                url : "/app/user/selectOrganizationType", 
                type : 'post',
                data : {
                    id
                },
                success : function(data){
                    $('#md-modal-content').html(data);
                    $('#mdmodal').modal('show');
                }
            });
        }   
    } else {
        $.ajax({
            url : "/app/user/userOrganizationInfo", 
            type : 'post',
            data : {
                id
            },
            success : function(data){
                $('#md-modal-content').html(data);
                $('#mdmodal').modal('show');
            }
        });
    }
}

function assignReportingPerson(id,organization_info) {
    if (organization_info == 'Yes') {
        $.ajax({
            url : "/app/user/userAssingReporting", 
            type : 'post',
            data : {
                id
            },
            success : function(data){
                $('#md-modal-content').html(data);
                $('#mdmodal').modal('show');
            }
        });
    } else {
        Swal.fire({
            title : "Sorry Can't Assign" ,
            text: "Please assign user organizatio info",
            icon: 'warning',
        }); 
    }
}

function updateDetails(id) {
    $.ajax({
        url : "/app/user/insertAndupdateUser", 
        type : 'POST',
        data : {id},
        success : function(data){
            $('#lg-modal-content').html(data);
            $('#lgmodal').modal('show');
        }
    });
}

function deleteUserDetails(id,table) {
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

function checkUserAssignDetails(user_id,table) {
    $.ajax({
        url : "/app/user/checkUserAssign",  
        type: 'post',
        data: {
            user_id,
            'type' : 'delete'
        },
        dataType: 'json',
        success : function(data) {
            if( data.status == 400) {
                Swal.fire({
                    text: data.message,
                    title : "Sorry can't delete",
                    icon: 'warning',
                });    
            } else {
                deleteUserDetails(user_id,table);
            }
        }
    })
}

$("#trash_button").on('click',function(){
    window.location.href = "/trash_file/userTrash";
});

</script>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer-bottom.php');?>