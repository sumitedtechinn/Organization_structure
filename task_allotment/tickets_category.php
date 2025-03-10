<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-top.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-bottom.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/topbar.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/menu.php');?>
<style>
th{
    /* background-color: white !important; */
    font-weight: 500;
    width: 3rem;
}
</style>
<main class="page-content">
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><?=setPageHeader()?> </h5>
                <div class="d-flex justify-content-end gap-2 col-sm-6">
                    <?php if(in_array('Tickets Category Create',$_SESSION['permission'])) { ?>
                    <div class="d-flex align-items-center theme-icons shadow-sm p-2 cursor-pointer rounded gap-2 bg-primary" title="Add Category" style="color: white;" onclick="addNewCategory()" data-bs-toggle = "tooltip"><span>Add Category</span>
                        <i class="bi bi-plus-circle-fill" id="add_new_category"></i>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <div class="table-responsive mt-3">
                <table class="table align-middle w-100" id="ticketCategoryTable">
                    <thead class="table-primary">
                        <tr>
                            <th>Sl.No</th>
                            <th>Category Name</th>
                            <th>Department</th>
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

var ticketCategorySetting = {
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    'ajax': {
        'url': '/app/ticketCategory/ticketCategory-server', 
        'type': 'POST',
    },
    'columns': [
        {
            data: "sqNo",
            render : function(data,type,row) {
                return '<div class = "text-medium fw-medium text-secondary mb-1">'+data+'</div>';
            }
        },{
            data: "name",
            render : function(data,type,row) {
                return '<div class="text-medium fw-medium text-secondary mb-1">'+data+'</div>';
            } 
        },{
            data : "department",
            render : function(data,type,row) {
                let dept = (data.trim() !== '') ? data.split(',').map((param) => '<span class="badge rounded-pill bg-info" style  = "font-size: 12px;font-weight : 500 !important;">'+param+'</span>').join(" ") : "";
                return '<div class="d-flex justify-content-start align-item-center flex-wrap gap-1" style = "50rem;">'+dept+'</div>';
            }
        },{
            data : "created_at",
            render : function(data,type,row) {
                return '<div class = "text-medium fw-medium text-secondary mb-1">'+data+'</div>';
            }
        },{
            data : "Action",
            render : function(data, type, row) {
                var edit = '';
                var del = '';
                <?php if(in_array('Tickets Category Update',$_SESSION['permission'])) { ?>
                     edit = '<div class="text-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit" onclick = "updateDetails('+row.ID+')"><i class="bi bi-pencil-fill"></i></div>';
                <?php } ?>
                <?php if(in_array('Tickets Category Delete',$_SESSION['permission'])) { ?>
                     del = '<div class="text-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete" onclick = "checkAssignDetails('+row.ID+',&#39;ticket_category&#39;)"><i class="bi bi-trash-fill"></i></div>';
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

document.addEventListener("DOMContentLoaded",function(){
    $("#ticketCategoryTable").DataTable(ticketCategorySetting);
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

async function addNewCategory() {
    const data = await getMethod("/app/ticketCategory/insertAndUpdateTicketCategory");
    if( data != null) {
        $('#md-modal-content').html(data);
        $('#mdmodal').modal('show');
    }
}

async function updateDetails(ticketCategory_id) {
    const data = await postMethod("/app/ticketCategory/insertAndUpdateTicketCategory",{ticketCategory_id});
    if( data != null) {
        $('#md-modal-content').html(data);
        $('#mdmodal').modal('show');
    }
}

</script>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer-bottom.php');?>  
