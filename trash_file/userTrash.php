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
                <button class="btn btn-primary" style="font-size: small;" id ="return_button">Go To Users</button>
            </div>
            <div class="table-responsive mt-3">
                <table class="table align-middle" id="userTable">
                    <thead class="table-secondary">
                        <tr>
                            <th>Photo</th>
                            <th>User</th>
                            <th>Password</th>
                            <th>Designation</th>
                            <th>Address</th>
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
                return '<div style="font-size:small;"><p class = "mb-1"><b>Name : </b> '+name+'</p><p class = "mb-1"><b>Contact : </b>'+country_code+" "+contact+'</p><p class = "mb-1"><b>Email : </b>'+email+'</p><p class = "mb-1"><b>DOJ : </b>'+doj+'</p></div>';
            }
        },{
            data: "password" ,
            render : function(data,type,row) {
                var pass = '<div class="row" style = "width:250px !important" ><div class="col-md-10"><input type="password" style="font-size:small; " class="form-control" disabled="" style="border: 0ch;" value="'+data+'" id="myInput'+ row.ID +'"></div><div class="col-md-2 mt-1"><i class = "bi bi-eye" onclick="showPassword('+ row.ID +')" ></i></div></div>';
                return pass;
            }
        },{
            data: "designation" ,
            render : function(data,type,row) {
                var department = row.department;
                var organization = row.organization_name;
                var vertical = row.vertical_name;
                var branch = '';
                if (row.branch_name != null) {
                    branch = '<p class = "mb-1"><b>Branch : </b>'+row.branch_name+'</p>';
                } else {
                    branch = '<p class = "mb-1 text-danger"><b>Branch : </b>Not Assigned</p>';
                }
                return '<div style="font-size:small;"><p class = "mb-1"><b>Organization : </b> '+organization+'</p>'+branch+'<p class = "mb-1"><b>Department : </b> '+department+'</p><p class = "mb-1 text-wrap" style = "width:200px;"><b>Designation : </b>'+data+'</p></div>';
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

$("#return_button").on('click',function(){
    window.location.href = "/organization_structure/users";
});

</script>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-bottom.php'); ?>