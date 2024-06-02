<?php
 include "styles.php";
 ?>
    <div class="container-fluid page-body-wrapper">
    <!-- partial:partials/_sidebar.html -->
    <nav class="sidebar sidebar-offcanvas" id="sidebar">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">


<ul class="nav">
<li class="nav-item">
    <a class="nav-link" href="home.php">
    <i class="icon-grid menu-icon"></i>
    <span class="menu-title">Dashboard</span>
    </a>
</li>
<li class="nav-item">
    <a class="nav-link" data-bs-toggle="collapse" href="#income-elements" aria-expanded="false" aria-controls="income-elements">
        <i class="fas fa-money-bill-wave menu-icon"></i>
        <span class="menu-title">Income</span>
        <i class="menu-arrow"></i>
    </a>
    <div class="collapse" id="income-elements">
        <ul class="nav flex-column sub-menu">
            <li class="nav-item"><a class="nav-link" href="overview.php">Overview</a></li>
            <li class="nav-item"><a class="nav-link" href="accounts.php">Account Numbers</a></li>
            <!-- <li class="nav-item"><a class="nav-link" href="budgets.php">Budgets</a></li> -->
        </ul>
    </div>
</li>
<li class="nav-item">
    <a class="nav-link" data-bs-toggle="collapse" href="#expense-elements" aria-expanded="false" aria-controls="expense-elements">
        <i class="fas fa-coins menu-icon"></i>
        <span class="menu-title">Expenses</span>
        <i class="menu-arrow"></i>
    </a>
    <div class="collapse" id="expense-elements">
        <ul class="nav flex-column sub-menu">
            <li class="nav-item"><a class="nav-link" href="overexpense.php">Overview</a></li>
            <li class="nav-item"><a class="nav-link" href="expenseaccounts.php">Expense Accounts</a></li>
            <!-- <li class="nav-item"><a class="nav-link" href="Expensebudgets.php">Budgets</a></li> -->
        </ul>
    </div>
</li>
</ul>
</nav>