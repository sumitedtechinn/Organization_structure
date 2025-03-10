<?php include($_SERVER['DOCUMENT_ROOT'].'/app/tickets/fetchTicketInformation.php'); ?>

<style>

#timer {
    font-size:14px;
    font-weight:400 !important;
    opacity: 0 ;
    transform: translateX(-10px);
    transition: all 0.5s ease-in-out;
}

#timer.show {
    opacity: 1;
    transform: translateY(0); /* Return to normal position */
}

.unique_id_style {
    padding: 0.5rem;
    font-size: 12px;
    font-weight: 500;
}

.description_style{
    padding: 1rem;
    color: #716f6f;
}

.attachment_style {
    background-color: #817d7dd9;
    padding: 0.5rem;
    font-size: 13px;
    font-weight: 500;
    margin-left: 1rem;
    margin-top: 0.2rem;
    margin-bottom: 1rem !important;
}

.priority_style{
    font-size: 11px;
    font-weight: 400;
    padding: 0.3rem;
}

.form_style {
    padding-left: 1rem;
    padding-right: 1rem;
    padding-bottom: 1rem;
}

#upload_button {
    color: white;
    padding: 0.17rem 0.5rem;
    font-size: larger;
}

.comment:nth-child(even) {
    background-color: #f2ecec24;
}
</style>

<div class="d-flex align-items-center justify-content-between flex-wrap p-3 pb-0 border-bottom">
    <div class="d-flex align-items-center flex-wrap">
        <div class="mb-3">
            <span class="badge bg-info rounded-pill mb-1 unique_id_style"><?=$ticket_details['unique_id']?></span>
            <div class="d-flex align-items-center mb-1">
                <h5 class="fw-semibold me-2 mt-1"><?=$ticket_details['task_name']?></h5>
                <span class="badge <?=$priority_color?> priority_style"><?=$ticket_details['priority']?></span>
            </div>
            <div class="d-flex align-items-center flex-wrap">
                <p class="d-flex align-items-center mb-1 me-4"><i class="lni lni-calendar me-1"></i>Updated <?=$ticket_details['update']?></p>
                <p class="d-flex align-items-center mb-1"><i class="lni lni-comments me-1"></i><?=$numOfComment?> Comments</p>
            </div>
        </div>
    </div>
    <div>
        <div class="d-flex-column align-items-center flex-wrap gap-1">
            <div class="badge rounded-pill mb-2" style = "font-size:14px;font-weight:400 !important;" id="timer" data-custom-value="<?=$ticket_details['deadline_date']?>_<?=$ticket_details['status_value']?>"></div>
            <div class="d-flex align-items-center theme-icons shadow-sm p-2 cursor-pointer rounded gap-2 bg-primary" title="View Ticket History" style="color: white;" onclick="viewTicketHistory('<?=$id?>')" data-bs-toggle = "tooltip"><span>Ticket History</span>
            </div>
        </div>
    </div>
</div>
<div class="border-bottom">
    <div class="description_style"><?=nl2br(htmlspecialchars($ticket_details['task_description']))?></div>
    <?php if(!empty($ticket_details['attachment'])) { ?>
    <div class="col-sm-12">
        <a href="<?=$ticket_details['attachment']?>" class="badge attachment_style" download ><?=getFileName($ticket_details['attachment'])?></a>
    </div>
    <?php } ?>
</div>
<div class="border-bottom">
    <div class="card-title d-flex align-items-center justify-content-start">
        <h5 class="description_style">Comment</h5>
    </div>
    <form action="/app/tickets/storeAndupdateTicket" id="comment_form" class="form_style" method="post">
        <div class="row mb-1">
            <div class="col-sm-12">
                <textarea class="form-control" name="comment" id="comment" rows="3"></textarea>
                <span style="color: red;font-size: small;" id="comment_empty"></span>
            </div>
        </div>
        <div class="row mb-1">
            <div class="d-flex align-items-center justify-content-start gap-1" id="attachment_box" style="margin-left: 0px;"></div>
        </div>
        <div class="row">
            <input type="file" name="attachment[]" id="attachment" accept="image/*,application/pdf" multiple style="display: none;" onchange = "handleFileUpload(event)">
            <div class="col-sm-12 d-flex align-items-center justify-content-start gap-1">
                <div class="d-flex align-items-center theme-icons shadow-sm cursor-pointer rounded gap-2 bg-primary" title="Attachment" id="upload_button" onclick="document.getElementById('attachment').click()" data-bs-toggle = "tooltip"><i class="fadeIn animated bx bx-upload"></i></div>
                <button class="btn btn-sm btn-primary" type="submit" id="insert_comment_<?=$data_field['id']?>"><i class="fadeIn animated bx bx-send"></i><span> Post </span></button>
            </div>
        </div>
    </form>    
</div>
<div class="nav_course_h" <?=$ticket_comment_style?>>
<?php if(!empty($comments)) { 
    foreach ($comments as $key => $value) { 
        $j = 1; ?>    
        <div class="border-bottom comment">
            <div class="bg-light-300 rounded p-3">
                <div class="d-flex align-items-center mb-3">
                    <span class="avatar avatar-xl me-2 flex-shrink-0"><img src="/../../assets/images/sample_user.jpeg" style="height: 4rem;" alt="Img"></span>
                    <div>
                        <h6 class="mb-1"><?=$value['user_name']?></h6>
                        <p><i class="ti ti-calendar-bolt me-1"></i><?=$value['time_ago']?></p>
                    </div>
                </div>
                <div>
                    <div class="mb-3">
                        <p><?=nl2br(htmlspecialchars($value['comment']))?></p>
                    </div>
                    <?php if(!empty($value['attachment'])) { ?>
                    <div class="col-sm-12">
                        <?php $all_attachment = json_decode($value['attachment'],true);
                        foreach ($all_attachment as $file) { ?>
                            <a href="<?=$file?>" class="badge attachment_style" download ><?=getFileName($file)?> <i class="bi bi-download"></i></a>    
                        <?php } ?>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    <?php } } ?>
</div>
