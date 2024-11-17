<?php 

## Database configuration
include '../../includes/db-config.php';
session_start();

$meetingCount = mysqli_real_escape_string($conn,$_REQUEST['meetingCount']);
$meetingClient = [];
if(isset($_REQUEST['meeting_client']) && !empty($_REQUEST['meeting_client'])) {
    $meetingClient = explode(',',$_REQUEST['meeting_client']);
}
?>

<?php for($i = 1 ; $i <= $meetingCount; $i++) { ?>
<div class="row">
    <label for="name" class="col-sm-3 col-form-label">Clint <?=$i?></label>
    <div class="col-sm-9">
        <input type="text" class="form-control form-control-sm" name="client_<?=$i?>" value="<?php echo !empty($meetingClient) ? trim($meetingClient[$i-1]) : '' ?>">
    </div>
</div>
<?php } ?>