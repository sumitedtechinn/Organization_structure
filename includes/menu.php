<?php 
## Database configuration
include 'db-config.php';
$pages = $conn->query("SELECT * FROM pages");
$pages = mysqli_fetch_all($pages,MYSQLI_ASSOC);

$page_type = [];

foreach ($pages as $key => $value) {
    $page_type[$value['Type']][] = $value;
}


function checkPageTypePermission($type) : bool {
    global $page_type;
    $page_present = false;
    foreach ($page_type[$type] as $value) {
        if(in_array( $value['Name'].' View',$_SESSION['permission'])) {
            $page_present = true;
            break;
        }
    }
    return $page_present;
}

?>

<aside class="sidebar-wrapper" data-simplebar="true">
    <div class="sidebar-header">
        <div>
            <h4 class="logo-text">Edtech</h4>
        </div>
        <div class="toggle-icon ms-auto"><i class="bi bi-list"></i></div>
    </div>
    <!--navigation-->
    <ul class="metismenu" id="menu">
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bi bi-house-fill"></i></div>
                <div class="menu-title">Dashboard</div>
            </a>
            <ul></ul>
        </li>
        <?php if (checkPageTypePermission('roleAndPermission')) { ?>
        <li>
        <a href="javascript:;" class="has-arrow">
            <div class="parent-icon"><i class="bi bi-shield-check"></i></div>
            <div class="menu-title">Role&Permission</div>
        </a>
            <ul>
            <?php foreach ($page_type['roleAndPermission'] as $page) {  ?>
                <?php if (in_array( $page['Name'].' View',$_SESSION['permission'])) { ?>
                <li>
                    <a style="font-size:small;" href="/<?=$page['Type']?>/<?=$page['Slug']?>"><i class="bi bi-square"></i><?=$page['Name']?></a>
                </li>
                <?php } ?>
            <?php } ?>
            </ul>
        </li> 
        <?php } ?>
        <?php if(checkPageTypePermission('organization_structure')) { ?>
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bi bi-building"></i></div>
                <div class="menu-title">Organization Structure</div>
            </a>
            <ul>
            <?php foreach ($page_type['organization_structure'] as $page) { ?>
                <?php if (in_array( $page['Name'].' View',$_SESSION['permission'])) { ?>
                <li>
                    <a style="font-size: small;" href="/<?=$page['Type']?>/<?=$page['Slug']?>"><i class="bi bi-square"></i><?=$page['Name']?></a>
                </li>
                <?php } ?>
            <?php } ?>
            </ul>
        </li>
        <?php } ?>
        <?php if(checkPageTypePermission('projection')) {?>
        <li>
        <a href="javascript:;" class="has-arrow">
            <div class="parent-icon"><i class="bi bi-list-task"></i></div>
            <div class="menu-title">Projection</div>
        </a>
            <ul>
            <?php foreach ($page_type['projection'] as $page) { ?>
                <?php if (in_array( $page['Name'].' View',$_SESSION['permission'])) { ?>
                <li>
                    <a style="font-size:small;" href="/<?=$page['Type']?>/<?=$page['Slug']?>"><i class="bi bi-square"></i><?=$page['Name']?></a>
                </li>
                <?php } ?>
            <?php } ?>
            </ul>
        </li>
        <?php } ?>
    </ul>
<!--end navigation-->
</aside>