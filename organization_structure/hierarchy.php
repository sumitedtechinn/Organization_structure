<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-top.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/header-bottom.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/topbar.php');?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/menu.php');?>

<style>

[data-n-id] rect:hover {
    filter: drop-shadow( 4px 5px 5px #aeaeae);
}

[data-l-id] path {
    stroke: #FFE5CF;
}

[data-ctrl-ec-id] circle {
    fill: #FFF7F7 !important; 
}

.boc-form-field {
    border: 2px solid #007bff;
    border-radius: 5px;
    padding: 10px;
    background-color: #f8f9fa;
}
.boc-form-field input {
    border: 1px solid #ced4da;
    padding: 8px;
    border-radius: 4px;
    width: 100%;
}
.boc-form-field label {
    font-weight: bold;
    color: #495057;
}

.page-content{
    padding: 0 !important;
}

.full-height{
    height: 80vh; 
}
#tree>svg {
    background-color: #2E2E2E;
}

</style>


<main class="page-content" >
<div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Hierarchy Chart</h5>
                <div class="theme-icons shadow-sm p-2 cursor-pointer rounded" title="Add" onclick="addDesignation()" data-bs-toggle="tooltip">
                    <i class="bi bi-person-plus-fill" id="add_user"></i>
                </div>
            </div>
        </div>
    </div>
    <div id="tree" class="container-fluid full-height d-flex justify-content-center align-items-center"></div>
</main>

<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer-top.php');?>
<script src="/assets/orgchart.js"></script>
<script>

OrgChart.scroll.chrome = { smooth: 12, speed: 120 }; // for scroll 
OrgChart.scroll.firefox = { smooth: 12, speed: 120 }; // for scroll 


async function fetchData() {
    try {
        const response = await fetch('/app/hierarchy/hierarchy-server');
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('There has been a problem with your fetch operation:', error);
    }
}

async function initializeChart() {
    const data = await fetchData();
    if (data) {
        new OrgChart("#tree", {
            template: "polina",
            enableSearch: true,
            // tags: {
            //     "srbdm" :{
            //         template : "srbdm"
            //     },
            //     "bdm" : {
            //         template : "bdm"
            //     },
            //     "manager" : {
            //         template : "manager"
            //     },
            //     "teamlead" : {
            //         template : "teamlead"
            //     },
            //     "counsellor" : {
            //         template : "counsellor"
            //     }
            // },
            toolbar: {
                zoom: true,
                fit: true,
                expandAll: true
            },
            showXScroll: true,
            nodeBinding: {  
                field_0: "Designation",
                field_1: "Code",
            },
            searchFields: ["Designation"],
            searchDisplayField: "title",
            nodes: data,
        });
    }
}

initializeChart();

function addDesignation() {
    $.ajax({
        url : "/app/hierarchy/insert&updatehierarchy" , 
        type : "get" , 
        success : function(data) {
            $('#lg-modal-content').html(data);
            $('#lgmodal').modal('show');
        }
    })
}
</script> 
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer-bottom.php');?>