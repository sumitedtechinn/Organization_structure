<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-top.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-bottom.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/topbar.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/menu.php');?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<style>

table th {
    background-color: #3461ff !important;
    font-weight: 500;
    width: 3rem;
    color: white !important;
}

.siteUrlStyle{
    border-radius: 20px;
    font-size: smaller;
}

.viewButton {
    border-radius: 10px;
    color: white;
    padding: 3% 6%;
    font-size: smaller;
    border: 1px solid whitesmoke;
}

.messageButton {
    background-color: #91f1faf7;
    color: #524e4e;
    font-size: small;
    font-weight: 500;
}

.messageButton:hover {
    background-color:rgba(101, 208, 218, 0.97) ;
}

.personalInfo {
    font-size: small;
    padding-left: 1rem;
    padding-top: 0.5rem;
}

.extraDetails {
    background-color: #f2c44e80;
    font-size: small;
    font-weight: 500;
    color: #524e4e;
}

.extraDetails:hover {
    background-color:rgba(195, 157, 60, 0.5);
}

.imageBoxStyle {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 1rem;
    opacity: 0.1;
}

#websiteTable_paginate {
    padding-right: 0.5rem;
    padding-bottom: 0.2rem;
}

#leadsTable_paginate {
    padding-right: 0.5rem;
}

.dataTables_info {
    padding-bottom: 0.5rem;
    padding-left: 0.5rem;
}

#website_heading{
    font-size: larger;
    font-weight: 500;
    padding-top: 0.3rem;
    color: #4974a8;
    text-transform: uppercase;
}

.show{
    display: block;
}

.hide {
    display: none;
}

</style>
<main class="page-content">
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><?=setPageHeader()?> </h5>
                <div class="d-flex justify-content-end gap-2 col-sm-6">
                    <button class="btn btn-link hide" id="export_button" data-bs-toggle="tooltip" data-bs-title="Download Data in CSV" onclick="exportData()"> <i class="fadeIn animated bx bx-export" style="font-size: larger;"></i>
                    </button>
                    <?php if(in_array('Tickets Create',$_SESSION['permission'])) { ?>
                    <div class="d-flex align-items-center theme-icons shadow-sm p-2 cursor-pointer rounded gap-2 bg-primary" title="New Ticket" style="color: white;" onclick="addNewWebsite()" data-bs-toggle = "tooltip"><span>Add New Website</span>
                        <i class="bi bi-plus-circle-fill" id="add_new_website"></i>
                    </div>
                    <?php } ?>
                </div>
            </div>           
        </div>
    </div>
    <div class="row">
        <div class="col-md-4"> 
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-end">
                        <div class="col-sm-4">
                            <input type="text" id="website-search-table" class="form-control pull-right" placeholder="Search">
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="table-responsive mt-3">
                    <table class="table align-middle w-100" id="websiteTable">
                        <thead class="table-primary">
                            <tr>
                                <th>WebSite Name</th>
                                <th>Website URL</th>
                                <th>View Leads</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-8" style="padding-left: 0px !important;">
            <div class="card" id="leads_information_tab">
                <div class="card-header">
                    <div class="d-flex justify-content-between">
                        <div class="col-sm-8" id="website_heading"></div>
                        <div class="col-sm-4">
                            <input type="text" id="leads-search-table" class="form-control pull-right" placeholder="Search">
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="table-responsive mt-3">
                    <table class="table align-middle w-100" id="leadsTable">
                        <thead class="table-primary">
                            <tr>
                                <th>Personal Info</th>
                                <th>Subject</th>
                                <th>Message</th>
                                <th>Extra Details</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                    </table>
                    <div class="imageBoxStyle">
                        <img src="/assets/images/leadLogo.png" id="leadsBox" style="width: 650px;" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer-top.php');?>
<script type="text/javascript">

var websiteSetting = {
    'processing': true,
    'serverSide': true,
    lengthChange: false,
    searching: false,
    'serverMethod': 'post',
    'ajax': {
        'url': '/app/leads/websiteServer',
        'type': 'POST',
        data : function(d) {
            d.searchtext = document.getElementById("website-search-table").value;
        }
    },
    'columns': [
        {
            data: "websiteName",
            render : function(data,type,row) {
                return '<div class = "fw-medium mb-1">'+data+'</div>';
            }
        },{
            data: "websiteUrl",
            render : function(data,type,row) {
                return '<div class = "d-flex align-item-center justify-content-center p-2 siteUrlStyle" style="background-color:#17b7d5b5"><a href="'+data+'" target = "_blank" title="'+data+'" data-bs-toggle = "tooltip" style = "color:white;">SITE URL</a></div>';
            } 
        },{
            data : "view" ,
            render : function(data,type,row) {
                let sqNo = row.rowCount;
                let view = '<button class="view-btn viewButton bg-success" id = "view_btn_'+sqNo+'" data-id = "'+row.ID+'" data-websiteUrl = "'+row.websiteUrl+'" data-websiteName = "'+row.websiteName+'" onclick = "viewWebsiteLeadsDetails(this)">VIEW</button>';
                return '<div class="d-flex justify-content-center align-items-center">'+view+'</div>';
            }
        }
    ],
    rowCallback: function(row, data) {
        if(row.getAttribute("class") == "even") {
            console.log(row.getAttribute("class"));
        }
    }, 
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

let leadsId;let leadUrl;let leadWebsiteName;

let leadsSetting = {
    'processing': true,
    'serverSide': true,
    'lengthChange' : false,
    'serverMethod': 'post',
    searching: false,
    'pageLength': 5,  
    'ajax': {
        url: "/app/leads/leadsServer",
        type : 'POST',
        data : function(d) {
            d.leadsId = leadsId;
            d.leadUrl = leadUrl;
            d.searchtext = document.getElementById("leads-search-table").value;
        }
    },
    'columns': [
        {
            data: "personalInfo",
            render : function(data, type, row) {
                var name = row.name;
                var contact = row.phone;
                var email = row.email;
                return '<div class = "personalInfo"><p class = "mb-1 text-wrap"><b>Name : </b> '+name+'</p><p class = "mb-1"><b>Contact : </b>+91 '+contact+'</p><p class = "mb-1"><b>Email : </b>'+email+'</p></div>';
            }
        },{
            data: "subject",
            render : function(data,type,row) {
                if(data == '') data = "None";
                return '<div class = "text-wrap">'+data+'</div>';
            } 
        },{
            data : "message" ,
            render : function(data,type,row) {
                return '<button class = "btn messageButton" type = "button" data-message = "'+data+'" onclick = "viewMessage(this)">Message</button>';
            }
        },{
            data : "extraDetails" , 
            render : function(data,type,row) {
                let consumeColumn = ['ID','name','phone','email','subject','message','created_at'];
                let extradetailskey = Object.keys(row).filter((param) => !consumeColumn.includes(param));
                let attribute;
                extradetailskey.forEach(key => {
                    if (typeof row[key] !== 'undefined' && row[key] !== null) {
                        attribute += ` data-${key}="${row[key]}"`;
                    }
                });
                return '<div class = "personalInfo"><button class = "btn extraDetails" type = "button" '+attribute+' onclick = "viewOtherInfo(this)">Extra</button></div>';
            }
        },{
            data : "created_at" ,
            render : function(data,type,row) {
                return '<div class = "fw-medium mb-1">'+data+'</div>';
            }
        } 
    ],
    rowCallback: function(row, data) {
        if (data.rowCount%2 == 0) {
            console.log("dvnjcdj");
        }
    },  
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
    $("#websiteTable").DataTable(websiteSetting);
});

$('#leadsTable').on('xhr.dt', function(e, settings, json, xhr){
    let leadbox = document.getElementById("leadsBox");
    leadbox.src = "";
    exportButton();
});

function exportButton() {
    if(typeof leadUrl == 'undefined' && typeof leadsId == 'undefined') return;
    let export_button = document.getElementById("export_button");
    if(export_button.classList.contains('show')) return;
    if(export_button.classList.contains('hide')) {
        export_button.classList.remove('hide');
        export_button.classList.add('show');
    }
}

async function getMethod(url) {
    try {
        let response = await fetch(url ,{
            method : 'GET'
        })
        if(!response.ok) {
            throw new Error(`HTTP error! Status : ${response.status}`);
        }
        let result = await response.text();
        return result;
    } catch (error) {
        console.error('There has been a problem with your fetch operation:',error);
    }
}

// Debouncing apply on search
let websiteSearchInput = document.getElementById("website-search-table");
let timer1;
websiteSearchInput.addEventListener('input', (event) => {
  const searchText = event.target.value.toLowerCase();
  if(timer1) clearTimeout(timer1);
  timer1 = setTimeout(()=> {
    $("#websiteTable").DataTable(websiteSetting);
  }, 1000)
});

// Debouncing apply on search
let leadsSearchInput = document.getElementById("leads-search-table");
let timer2;
leadsSearchInput.addEventListener('input', (event) => {
  const searchText = event.target.value.toLowerCase();
  if(timer2) clearTimeout(timer2);
  timer2 = setTimeout(()=> {
    $("#leadsTable").DataTable(leadsSetting);
  }, 1000)
});

async function addNewWebsite() {
    let url = "/app/leads/insertAndUpdateWebsite";
    let data = await getMethod(url);
    if (data != null) {
        $('#md-modal-content').html(data);
        $('#mdmodal').modal('show');
    }
}

function viewWebsiteLeadsDetails(button) {
    leadsId = button.getAttribute('data-id');
    leadUrl = button.getAttribute("data-websiteUrl");
    leadUrl += "admin/api/leads/FetchLeads.php";
    let leadbox = document.getElementById("leadsBox");
    leadbox.src = "/assets/images/loader-gif.gif";
    leadbox.parentElement.style.opacity = 0.5;
    setTimeout(() => {
        $("#leadsTable").DataTable(leadsSetting)
        leadWebsiteName = button.getAttribute("data-websiteName");
        document.getElementById("website_heading").textContent = leadWebsiteName;    
    }, 1000);
}

async function viewMessage(button) {
    let message = button.getAttribute("data-message");
    if(message == '') {
        message = "Message not present in the lead";
    }
    let data = await getMethod("/app/leads/showLeadMessage");
    if (data != null) {
        $('#md-modal-content').html(data);
        $('#mdmodal').modal('show');
        document.getElementById("model_heading").textContent = "Lead Message";
        document.getElementById("content_box").textContent = message;
    }
}

async function viewOtherInfo(button) { 
    let attribute = Array.from(button.attributes).filter((param) => param.name.startsWith("data-")).map((param) => param.name);
    let tableRow = attribute.map((param) => {
        let value = button.getAttribute(param);
        value = (value === 'null' ||  value == '') ? "Empty Record" : value;
        let row = "<tr>";
        row += '<td scope = "row">';
        row += (param.split("-"))[1].split("_").map((ele) => ele.charAt(0).toUpperCase() + ele.slice(1,ele.length)).join(" ");
        row += '</td>';
        row += '<td class = "text-wrap text-center">';
        row += value;
        row += '</td></tr>';
        return row;
    });
    let table;
    if(tableRow.length != 0) {
        table = tableRow.reduce((acc,row) => {
            acc += row;
            return acc;
        },'<table class = "table">' );
    } else {
        table = '<table class = "table"><tr><td scope = "row">No extra details are present</td></tr>';
    }
    table += '</table>'; 
    let data = await getMethod("/app/leads/showLeadMessage");
    if (data != null) {
        $('#md-modal-content').html(data);
        $('#mdmodal').modal('show');
        document.getElementById("model_heading").textContent = "Extra Details";
        document.getElementById("content_box").innerHTML = table;
    }
}

function exportData() {
    let filename =  leadWebsiteName + " Leads";
    let searchValue = document.getElementById("leads-search-table").value;
    $.ajax({
        url: '/app/leads/leadsServer',
        type: 'POST',
        data : {
            draw: 1,
            start: 0,
            leadsId: leadsId,
            leadUrl: leadUrl,
            searchtext: searchValue
        },
        success: async function (response) {
            var response = await JSON.parse(response);
            if (response && response.aaData && response.aaData.length > 0) {
                let headers = ['Name','Email','Contact','Subject','Message','Address','Leads Date'];
                let csvData = convertToCSV(response.aaData, headers);
                downloadCSV(csvData, filename + '.csv');
            } else {
                alert("No data available for export.");
            }
        },
        error: function () {
            alert("Error while fetching data.");
        }
    });
}


function convertToCSV(data, headers) {
    let csvRows = [headers.join(',')]; // Add headers to CSV
    data.forEach(row => {
        let address = (typeof row?.address == 'undefined') ? row.addres : "Empty Data"; 
        csvRows.push([
            `"${row.name}"`,
            `"${row.email}"`,
            `"${row.phone}"`,
            `"${row.subject}"`,
            `"${row.message}"`,
            `"${address}"`,
            `"${row.created_at}"`
        ].join(','));
    });

    return csvRows.join('\n');
}

function downloadCSV(csvData, filename) {
    let blob = new Blob([csvData], { type: 'text/csv' });
    let link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    link.click();
}
</script>

</script>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer-bottom.php');?>  