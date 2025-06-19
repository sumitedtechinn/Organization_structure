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
                <h5 class="mb-0">User Details</h5>
                <button class="btn btn-primary" style="font-size: small;" id ="return_button">Go To Users</button>
            </div>
            <div class="table-responsive mt-3">
                <table class="table align-middle" id="userTable" style="color: #515B73!important;">
                    <thead class="table-primary">
                        <tr class="table_heading">
                            <td>Photo</td>
                            <td>User</td>
                            <td>Password</td>
                            <td>Designation</td>
                            <td>Address</td>
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
<script type="text/javascript">

var UserSettings = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    'ajax': {
        'url': '/app/user/user-server.php',
        'type': 'POST',
        'data' : function(d) {
            d.usertype = 'deleteUser';
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
                var pass = '<div class="row" style = "width:175px !important" ><div class="col-md-10"><input type="password" style="font-size:small; " class="form-control" disabled="" style="border: 0ch;" value="'+data+'" id="myInput'+ row.ID +'"></div><div class="col-md-2 mt-1"><i class = "bi bi-eye " onclick="showPassword('+ row.ID +')" ></i></div></div>';
                return pass;
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
            data : "Action" ,
            render : function(data, type, row) {
                var table = "users";
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
    $('#userTable').dataTable(UserSettings);
});


function showPassword(id) {
    var x = document.getElementById("myInput".concat(id));
    if (x.type === "password") {
        x.type = "text";
    } else {
        x.type = "password";
    }
}

const makeContent = (content) => `<span class="truncate-label" data-bs-toggle="tooltip" title="${content}">${content}</span>`;

$("#return_button").on('click',function(){
    window.location.href = "/organization_structure/users";
});

</script>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-bottom.php'); ?>