<header class="top-header">
  <nav class="navbar navbar-expand gap-3 d-flex flex-wrap justify-content-between align-items-center">
    <div class="top-navbar">
      <ul class="navbar-nav">
        <li>
          <div class="page-breadcrumb">
            <div>
              <img src="/assets/images/logo-menu.jpg" height="55px" width="170px" alt="logo icon">
            </div>
        </li>
      </ul>
    </div>
    <div class="top-navbar-right ms-auto">
      <ul class="navbar-nav align-items-center gap-1">
        <li class="nav-item dropdown dropdown-large">
          <a class="nav-link dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown">
            <div class="notifications" onclick="seeAllNotification()">
              <span class="notify-badge align-item-center" id="notify_badge_number"></span>
              <i class="bi bi-bell-fill fs-4"></i>
            </div>
          </a>
          <div class="dropdown-menu dropdown-menu-end p-0">
            <div class="p-2 border-bottom m-2">
              <h5 class="h5 mb-0">Notifications</h5>
            </div>
            <div class="header-notifications-list p-2 ps">
              <div class="ps__rail-x" style="left: 0px; bottom: 0px;">
                <div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div>
              </div>
              <div class="ps__rail-y" style="top: 0px; right: 0px;">
                <div class="ps__thumb-y" tabindex="0" style="top: 0px; height: 0px;"></div>
              </div>
            </div>
            <div class="p-2">
              <div>
                <hr class="dropdown-divider">
              </div>
              <a class="dropdown-item" href="#">
                <div class="text-center">View All Notifications</div>
              </a>
            </div>
          </div>
        </li>
      </ul>
    </div>
    <div class="dropdown dropdown-user-setting">
      <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown">
        <div class="user-setting d-flex align-items-center gap-3">
          <img src="<?= $_SESSION['Photo'] ?>" class="user-img" alt="">
          <div class="d-none d-sm-block">
            <p class="user-name mb-0"><?= $_SESSION['Name'] ?></p>
            <small class="mb-0 dropdown-user-designation">
              <?php
              if ($_SESSION['role'] == 1) {
                $guard_name = $conn->query("SELECT guard_name FROM `roles` WHERE ID = '" . $_SESSION['role'] . "'");
                $guard_name = mysqli_fetch_column($guard_name);
                echo $guard_name;
              } else {
                $designation_code = $conn->query("SELECT Designation.code as `designation_code` FROM `users` LEFT JOIN Designation ON Designation.ID = users.Designation_id WHERE users.Designation_id = '" . $_SESSION['Designation_id'] . "'");
                $designation_code = mysqli_fetch_column($designation_code);
                echo $designation_code;
              } ?>
            </small>
          </div>
        </div>
      </a>
      <ul class="dropdown-menu dropdown-menu-end">
        <li>
          <a class="dropdown-item" href="pages-user-profile.html">
            <div class="d-flex align-items-center">
              <div class=""><i class="bi bi-person-fill"></i></div>
              <div class="ms-3"><span>Profile</span></div>
            </div>
          </a>
        </li>
        <li>
          <a class="dropdown-item" href="javascript:void(0)" onclick="changePassword()">
            <div class="d-flex align-items-center">
              <div class=""><i class="lni lni-unlock"></i></div>
              <div class="ms-3"><span>Change Password</span></div>
            </div>
          </a>
        </li>
        <li>
          <hr class="dropdown-divider">
        </li>
        <li>
          <a class="dropdown-item" href="\logout">
            <div class="d-flex align-items-center">
              <div class=""><i class="bi bi-lock-fill"></i></div>
              <div class="ms-3"><span>Logout</span></div>
            </div>
          </a>
        </li>
      </ul>
    </div>
  </nav>
</header>

<!-- Modals -->
<div class="modal fade slide-up" id="smmodal" tabindex="-1" role="dialog" data-keyboard="false" data-backdrop="static" aria-hidden="false">
  <div class="modal-dialog modal-sm">
    <div class="modal-content-wrapper">
      <div class="modal-content" id="sm-modal-content">
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  function changePassword() {
    $.ajax({
      url: '/app/changePassword/changePassword',
      type: 'get',
      success: function(data) {
        $('#md-modal-content').html(data);
        $('#mdmodal').modal('show');
      }
    });
  }
</script>