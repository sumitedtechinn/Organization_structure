<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-top.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-bottom.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/topbar.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/menu.php');?>
<style>
.table_heading {
    font-size: 14px;
    font-weight: 500;
}
.truncate-label {
    display: inline-block;
    max-width: 160px; /* or use any fixed size */
    white-space: nowrap;
    overflow: hidden;      /* required for ellipsis */
    text-overflow: ellipsis;
    vertical-align: middle;
}
</style>
<!--start content-->
<main class="page-content">
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <h6 class="mb-0">User Details</h6>
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
            <div class="d-flex align-items-center justify-content-between gap-1 mt-3" id="filter_container">
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
                <table class="table align-middle" id="userTable" style="color: #515B73!important;">
                    <thead class="table-primary">
                        <tr class="table_heading">
                            <td>Photo</td>
                            <td>User</td>
                            <td>Password</td>
                            <td>Organization Details</td>
                            <td>Address</td>
                            <td>Reporting</td>
                            <td>Assigned</td>
                            <td>Assets</td>
                            <td>Action</td>
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
    let filter_data_field = Array.from(document.getElementById("filter_container").querySelectorAll("select")).map((param) => param.id.split("_")[0]);
    $.ajax({
        url : "/app/common/getAllFilterData", 
        type : "post",
        contentType: 'application/json',  // Set the content type to JSON 
        data: JSON.stringify(filter_data_field), 
        dataType: 'json', 
        success : function(data) {
            for (const key in data) {
                $("#"+key+"_filter").html(data[key]);
                $("#"+key+"_filter").select2({
                    placeholder: 'Choose ' + key.charAt(0).toUpperCase() + key.slice(1,key.length), 
                    allowClear: true,
                    width: '100%'
                });
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
                return `<div class="d-flex align-items-center gap-3 cursor-pointer"><img src="${data}" class="rounded-circle" width="44" height="44" alt=""></div>`;
            }
        },{
            data: "User" ,
            render : function(data, type, row) {
                let name = makeContent(row.Name);
                let contact = makeContent(row.Contact);
                let country_code = (row.Country_code.length > 0) ? row.Country_code : "";
                let email = makeContent(row.Email);
                let doj = makeContent(row.doj);
                let designation_name = (row.organization_info_assign == 'No') ? '<span class="fw-bold text-danger">Not Assigned</span>': makeContent(row.designation);
                return `<div style="font-size:small;">
                <p class = "mb-1"><span style="font-weight:500;">Name : </span>${name}</p>
                <p class = "mb-1"><span style="font-weight:500;">Contact : </span>${country_code} ${contact}</p>
                <p class = "mb-1"><span style="font-weight:500;">Email : </span>${email}</p>
                <p class = "mb-1"><span style="font-weight:500;">DOJ : </span>${doj}</p>
                <p class = "mb-1"><span style="font-weight:500;">Designation : </span>${designation_name}</p>
                </div>`;
            }
        },{
            data: "password" ,
            render : function(data,type,row) {
                let password = `<div class="input-group input-group-sm">
				<input type="password" class="form-control" id = "myinput_${row.ID}" disabled value="${data}">
                <span class="input-group-text" onclick="showPassword(${row.ID})">
                <i class = "bi bi-eye"></i>
                </span></div>`;
								
                // var pass = '<div class="row" style = "width:175px !important" ><div class="col-md-10"><input type="password" style="font-size:small; " class="form-control" disabled="" style="border: 0ch;" value="'+data+'" id="myInput'+ row.ID +'"></div><div class="col-md-2 mt-1"><i class = "bi bi-eye " onclick="showPassword('+ row.ID +')" ></i></div></div>';
                return password;
            }
        },{
            data: "organization" ,
            render : function(data,type,row) {
                let department, organization, vertical, branch, role_name;
                if (row.role_name === 'admin' && row.organization_name !== null) {
                    department = makeContent('All');
                    organization = makeContent(row.organization_name);
                    vertical = makeContent(row.vertical_name);
                    branch = row.branch_name === null ? makeContent('All') : makeContent(row.branch_name);
                    role_name = makeContent(row.role_name.toUpperCase());
                } else {
                    department = row.department === null ? makeContent('Not Assigned') : makeContent(row.department);
                    organization = row.organization_name === null ? makeContent('Not Assigned') : makeContent(row.organization_name);
                    vertical = row.vertical_name === null ? makeContent('Not Assigned') : makeContent(row.vertical_name);
                    branch = row.branch_name === null ? makeContent('Not Assigned') : makeContent(row.branch_name);
                    role_name = makeContent(row.role_name.toUpperCase());
                }
                return `<div style="font-size:small;">
                <p class = "mb-1"><span style="font-weight:500;">Organization : </span>${organization}</p>
                <p class = "mb-1"><span style="font-weight:500;">Branch : </span>${branch}</p>
                <p class = "mb-1"><span style="font-weight:500;">Vertical : </span>${vertical}</p>
                <p class = "mb-1"><span style="font-weight:500;">Department : </span>${department}</p>
                <p class = "mb-1"><span style="font-weight:500;">Role : </span>${role_name}</p>
                </div>`;
            } 
        },{
            data: "Address" ,
            render : function(data,type,row) {
                let country = makeContent(row.Country);
                let state = makeContent(row.State);
                let city = makeContent(row.City);
                let locality = makeContent(row.Address);
                return `<div style="font-size:small;">
                <p class = "mb-1"><span style="font-weight:500;">Country : </span>${country}</p>
                <p class = "mb-1"><span style="font-weight:500;">State : </span>${state}</p>
                <p class = "mb-1"><span style="font-weight:500;">City : </span>${city}</p>
                <p class = "mb-1"><span style="font-weight:500;">Locality : </span>${locality}</p>
                </div>`;
            } 
        },{
            data: "Status",
            render : function(data,type,row) {
                let check_user = '';
                <?php if($_SESSION['role'] == '2') { ?>
                    check_user = 'disabled';
                <?php } ?>
                var assigned_person = (row.assinged_person === null) ? '<button style="font-size:smaller" class="btn btn-danger btn-sm '+check_user+'" onclick = "assignReportingPerson('+row.ID+',&#39;'+row.organization_info_assign+'&#39;)">Not Assigned</button>': '<button style="font-size:smaller" class="btn btn-success btn-sm '+check_user+'" onclick = "assignReportingPerson('+row.ID+',&#39;'+row.organization_info_assign+'&#39;)">Assigned</button>';
                return `<div style="font-size:small;">
                <p class = "mb-1">${assigned_person}</p>
                </div>`;
            } 
        },{
            data : "Assign" , 
            render : function(data,type,row) {
                var role_name = row.role_name;
                var check_user = '';
                <?php if($_SESSION['role'] == '2') { ?>
                    check_user = 'disabled';
                <?php } ?>
                var assign = '<div class="col"><button type="button" class="btn btn-info btn-sm '+check_user+'" style="font-size:smaller;color:white;" onclick = "assingOrganizationInfo('+row.ID+',&#39;'+role_name+'&#39;,&#39;'+row.organization_info_assign+'&#39;,&#39;'+row.designation_inside+'&#39;)">Assign</button></div>';
                return assign;
            }
        },{
            data : 'assets_assignation',
            render : function (data,type,row) {
                return `<button class = "btn btn-info btn-sm" style = "font-size :smaller;color:white;" onclick = showAssetsAssignationDetails(${row.ID})>Assets</button>`;
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
            },
            //visible : false
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

const makeContent = (content) => `<span class="truncate-label" data-bs-toggle="tooltip" title="${content}">${content}</span>`;

function showPassword(id) {
    <?php if($_SESSION['role'] == '2') { ?> return <?php } ?>
    let x = document.getElementById("myinput_".concat(id));
    x.type = (x.type === "password") ? "text" : "password";
}

function assingOrganizationInfo(id,role,organization_info_assign,page_type) {
    if(role == 'admin') {
        let postData = (organization_info_assign == 'Yes') ? {page_type,id} : {id};
        $.ajax({
            url : "/app/user/adminOrganizationInfo", 
            type : 'post',
            data : postData,
            success : function(data){
                $('#md-modal-content').html(data);
                $('#mdmodal').modal('show');
            } 
        });   
    } else {
        $.ajax({
            url : "/app/user/userOrganizationInfo", 
            type : 'post',
            data : {id},
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

function showAssetsAssignationDetails(id) {
    $.ajax({
        url : "/app/user/assetsAssignation",   
        type: 'POST',
        data: {id} ,
        success : function(data){
            $('#md-modal-content').html(data);
            $('#mdmodal').modal('show');
        }
    })
}

$("#trash_button").on('click',function(){
    window.location.href = "/trash_file/userTrash";
});

</script>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer-bottom.php');?>