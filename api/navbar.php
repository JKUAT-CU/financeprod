 <?php
 include "styles.php";
 ?>
<nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
<div class="navbar-brand-wrapper">
    <a class="navbar-brand brand-logo" href="home.php">
      <img src="logo4.jpeg" alt="logo" />
    </a>
    <a class="navbar-brand brand-logo-mini" href="home.php">
      <img src="logo4.jpeg" alt="logo" />
    </a>
    <a class="brand-text" href="home.ph/opt/lampp/htdocs/union/finance/pages/overexpense.php" >JKUATCU FINANCE</a>
  </div>
  <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
  <ul class="navbar-nav mr-lg-2">
  <li class="nav-item nav-search d-none d-lg-block">
    <div class="input-group">
      <div class="input-group-prepend hover-cursor" id="navbar-search-icon">
        <span class="input-group-text" id="search">
          <i class="icon-search"></i>
        </span>
      </div>
      <input type="text" class="form-control" id="navbar-search-input" placeholder="Search now" aria-label="search" aria-describedby="search">
    </div>
  </li>
</ul>
<script>
  document.getElementById("navbar-search-input").addEventListener("keyup", function(event) {
    if (event.keyCode === 13) { // Enter key is pressed
      var searchQuery = this.value.trim();
      if (searchQuery !== "") {
        // Perform search action, such as redirecting to search results page
        window.location.href = "search.php?q=" + encodeURIComponent(searchQuery);
      }
    }
  });
</script>

    <ul class="navbar-nav navbar-nav-right">
      <!-- User Profile -->
      <li class="nav-item nav-profile dropdown">
        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" id="profileDropdown">
          <img src="assets/images/faces/face28.jpg" alt="profile" />
        </a>
        <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
          <a class="dropdown-item">
            <i class="ti-settings text-primary"></i> Settings </a>
          <a class="dropdown-item">
            <i class="ti-power-off text-primary"></i> Logout </a>
        </div>
      </li>
      <li class="nav-item nav-settings d-none d-lg-flex">
        <a class="nav-link" href="#">
          <i class="icon-ellipsis"></i>
        </a>
      </li>
    </ul>
    <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
      <span class="icon-menu"></span>
    </button>
  </div>
</nav>
