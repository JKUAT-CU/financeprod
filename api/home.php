<?php
include "navbar.php";
include "sidebar.php";
include "styles.php";
include "scripts.php";
?>

<script src="js/charts/transactions.js"></script>
<script src="js/image.js"></script>
<script src="js/scripts.js"></script>
<script src="js/charts/bar.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>

    <!-- <div class="container-fluid page-body-wrapper"> -->
      <div class="main-panel" style="padding-top: 2vh;">
        <!-- <div class="content-wrapper"> -->

          <!-- Row for the image and cards -->
          <div class="row">
            <div class="col-md-6 grid-margin stretch-card">
              <div class="card tale-bg" style="height: 50vh; width:60vh; overflow: hidden;">
                <div id="randomNatureImage" class="card-body" style="height: 100%; width: 100%; background-size: cover; background-position: center;"></div>
                <div class="weather-info">
                  <div class="d-flex">
                    <div>
                      <h2 class="mb-0 font-weight-normal"><i class="icon-sun me-2"></i><span id="temperature">31</span><sup>C</sup></h2>
                    </div>
                    <div class="ms-2">
                      <h4 class="location font-weight-normal" id="location">Kenya</h4>
                      <h6 class="font-weight-normal" id="dayOfWeek"></h6>
                      <h6 class="font-weight-normal" id="dateAndTime"></h6>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6 grid-margin transparent">
              <div class="row">
                <div class="col-md-6 mb-4 stretch-card transparent">
                  <div class="card card-tale">
                    <div class="card-body">
                      <p class="mb-4">Yearly Collection</p>
                      <p id="yearlyCollection" class="fs-30 mb-2"></p>
                    </div>
                  </div>
                </div>
                <div class="col-md-6 mb-4 stretch-card transparent">
                  <div class="card card-dark-blue">
                    <div class="card-body">
                      <p class="mb-4">Yearly Expense</p>
                      <p id="yearlyExpense" class="fs-30 mb-2"></p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-4 mb-lg-0 stretch-card transparent">
                  <div class="card card-light-blue">
                    <div class="card-body">
                      <p class="mb-4">Semester Collection</p>
                      <p id="semesterCollection" class="fs-30 mb-2"></p>
                    </div>
                  </div>
                </div>
                <div class="col-md-6 stretch-card transparent">
                  <div class="card card-light-danger">
                    <div class="card-body">
                      <p class="mb-4">Semester Expense</p>
                      <p id="semesterExpense" class="fs-30 mb-2"></p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- Row for the charts -->
          <div class="row mt-4">
            <div class="col-md-6 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                <div class="d-flex justify-content-between">
                    <p class="card-title">Income</p>
                  </div>
                  <canvas id="transactionChart"></canvas>
                </div>
              </div>
            </div>
            <div class="col-md-6 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex justify-content-between">
                    <p class="card-title">Income Vs Budget</p>
                  </div>
                   <div id="sales-chart-legend" class="chartjs-legend mt-4 mb-2"></div>
                  <canvas id="budgetCollectionChart"></canvas>
                </div>
              </div>
            </div>
          </div>
      </div>
      <!-- main-panel ends -->
    </div>

