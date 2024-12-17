<?php 
## Database configuration
include '../../includes/db-config.php';
session_start();

$data_field = file_get_contents('php://input'); // by this we get raw data
$data_field = json_decode($data_field,true);

function makeKey($key) {
    $key_arr = explode('_',$key);
    if($key_arr[1] == 'dealclose') {
        return "Center Closing Amount";
    } else {
        return ucfirst($key_arr[1]) . " Amount"; 
    }
}
?>

<!-- Modal -->
<div class="card-body">
    <div class="border p-4 rounded">
        <div class="card-title d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Amount Details</h5>
            <a type="button" class="close" data-dismiss="modal" id = "hide-modal-view" aria-hidden="true"><i class="bi bi-x-circle-fill"></i></a>
        </div>
        <div class="table-responsive mt-3">
            <table class="table table-default align-middle w-100" id="viewAdmissionTable">
                <thead class="table-light"> 
                    <tr>
                        <th>Payment Type</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($data_field as $key=>$value) { ?>
                    <tr>
                        <td><?php echo makeKey($key)?></td>
                        <td><span>₹</span><?=number_format($value,2,".",",")?></td> 
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>

$('#hide-modal-view').click(function() {
    $('.modal').modal('hide');
});
</script>