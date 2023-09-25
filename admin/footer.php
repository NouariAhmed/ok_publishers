
    </div>
  </main>
  <div class="fixed-plugin">
    <a class="fixed-plugin-button text-dark position-fixed px-3 py-2">
      <i class="material-icons py-2">settings</i>
    </a>
    <div class="card shadow-lg">
      <div class="card-header pb-0 pt-3">
        <div class="float-end">
          <h5 class="mt-3 mb-0">Okacha Publishers Dashboard</h5>
          <p>See our dashboard options.</p>
        </div>
        <div class="float-start mt-4">
          <button class="btn btn-link text-dark p-0 fixed-plugin-close-button">
            <i class="material-icons">clear</i>
          </button>
        </div>
        <!-- End Toggle Button -->
      </div>
      <hr class="horizontal dark my-1">
      <div class="card-body pt-sm-3 pt-0">
        <!-- Sidebar Backgrounds -->
        <div>
          <h6 class="mb-0">Sidebar Colors</h6>
        </div>
        <a href="javascript:void(0)" class="switch-trigger background-color">
          <div class="badge-colors my-2 text-end">
            <span class="badge filter bg-gradient-primary active" data-color="primary" onclick="sidebarColor(this)"></span>
            <span class="badge filter bg-gradient-dark" data-color="dark" onclick="sidebarColor(this)"></span>
            <span class="badge filter bg-gradient-info" data-color="info" onclick="sidebarColor(this)"></span>
            <span class="badge filter bg-gradient-success" data-color="success" onclick="sidebarColor(this)"></span>
            <span class="badge filter bg-gradient-warning" data-color="warning" onclick="sidebarColor(this)"></span>
            <span class="badge filter bg-gradient-danger" data-color="danger" onclick="sidebarColor(this)"></span>
          </div>
        </a>
        <!-- Sidenav Type -->
        <div class="mt-3">
          <h6 class="mb-0">Sidenav Type</h6>
          <p class="text-sm">Choose between 2 different sidenav types.</p>
        </div>
        <div class="d-flex">
          <button class="btn bg-gradient-dark px-3 mb-2 active" data-class="bg-gradient-dark" onclick="sidebarType(this)">Dark</button>
          <button class="btn bg-gradient-dark px-3 mb-2 ms-2" data-class="bg-transparent" onclick="sidebarType(this)">Transparent</button>
          <button class="btn bg-gradient-dark px-3 mb-2 me-2" data-class="bg-white" onclick="sidebarType(this)">White</button>
        </div>
        <p class="text-sm d-xl-none d-block mt-2">You can change the sidenav type just on desktop view.</p>
        <!-- Navbar Fixed -->
        <div class="mt-3 d-flex">
          <h6 class="mb-0">Navbar Fixed</h6>
          <div class="form-check form-switch me-auto my-auto">
            <input class="form-check-input mt-1 float-end me-auto" type="checkbox" id="navbarFixed" onclick="navbarFixed(this)">
          </div>
        </div>
        <hr class="horizontal dark my-3">
        <div class="mt-2 d-flex">
          <h6 class="mb-0">Light / Dark</h6>
          <div class="form-check form-switch me-auto my-auto">
            <input class="form-check-input mt-1 float-end me-auto" type="checkbox" id="dark-version" onclick="darkMode(this)">
          </div>
        </div>
        <hr class="horizontal dark my-sm-4">
      </div>
    </div>
  </div>
  <?php
  
  include('../connect.php');
  $sessionUserId = $_SESSION['id']; 
  $userRole = $_SESSION['role'];
  if ($userRole === 'admin') {
    // Query to get the count of publishers for each publisher type
    $sql = "SELECT publisher_types.publisher_type, COUNT(publishers.id) AS publisher_count
    FROM publisher_types
    LEFT JOIN publishers ON publisher_types.id = publishers.publisher_type_id
    GROUP BY publisher_types.publisher_type";
} elseif ($userRole === 'member') {
       // Query to get the count of publishers for each publisher type
       $sql = "SELECT publisher_types.publisher_type, COUNT(publishers.id) AS publisher_count
       FROM publisher_types
       LEFT JOIN publishers ON publisher_types.id = publishers.publisher_type_id
       WHERE publishers.inserted_by = $sessionUserId
       GROUP BY publisher_types.publisher_type";                  
}

$result = mysqli_query($conn, $sql);

$labels = [];
$data = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $labels[] = $row['publisher_type'];
        $data[] = $row['publisher_count'];
    }

    mysqli_free_result($result);
}
if ($userRole === 'admin') {
  // Query to get the count of publishers inserted by each user
  $sql = "SELECT users.username, COUNT(publishers.id) AS publisher_count
          FROM users
          LEFT JOIN publishers ON users.id = publishers.inserted_by
          GROUP BY users.username";
} elseif ($userRole === 'member') {
  // Query to get the count of publishers inserted by each user
$sql = "SELECT users.username, COUNT(publishers.id) AS publisher_count
FROM users
LEFT JOIN publishers ON users.id = publishers.inserted_by
WHERE publishers.inserted_by = $sessionUserId
GROUP BY users.username";               
}

$result = mysqli_query($conn, $sql);

$userLabels = [];
$publisherCountsByUser = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $userLabels[] = $row['username'];
        $publisherCountsByUser[] = $row['publisher_count'];
    }

    mysqli_free_result($result);
}
if ($userRole === 'admin') {
//Query to get the count of publishers for each country
$sql = "SELECT countries.arabic_countries, COUNT(publishers.id) AS publisher_count
        FROM countries
        LEFT JOIN publishers ON countries.id = publishers.country_id
        GROUP BY countries.arabic_countries";
} elseif ($userRole === 'member') {
//Query to get the count of publishers for each country
$sql = "SELECT countries.arabic_countries, COUNT(publishers.id) AS publisher_count
        FROM countries
        LEFT JOIN publishers ON countries.id = publishers.country_id
        WHERE publishers.inserted_by = $sessionUserId
        GROUP BY countries.arabic_countries";
}
$result = mysqli_query($conn, $sql);

$countryLabels = [];
$countryData = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $countryLabels[] = $row['arabic_countries'];
        $countryData[] = $row['publisher_count'];
    }

    mysqli_free_result($result);
}
  ?>
                        <!-- logOut Modal -->
                        <div class="modal fade" id="logOutModal" tabindex="-1" aria-labelledby="logOutModalLabel" aria-hidden="true">
                          <div class="modal-dialog">
                              <div class="modal-content">
                                  <div class="modal-header"> 
                                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                  </div>
                                  <div class="modal-body text-center">
                                      <h5 class="modal-title mb-3">
                                        <i class="fas fa-sign-out-alt fa-rotate-180 fa-lg text-warning" style="font-size: 150px;"></i>
                                      </h5>
                                      <p>هل أنت متأكد من تسجيل الخروج؟</p>
                                  </div>
                                  <div class="modal-footer d-flex justify-content-center">
                                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">غلق</button>
                                      <a class="btn btn-warning" href="../logout.php">تسجيل الخروج</a>
                                  </div>
                              </div>
                          </div>
                        </div>
  <!--   Core JS Files   -->
  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/chartjs.min.js"></script>
  <script>
    
    var ctx = document.getElementById("chart-bars").getContext("2d");

    new Chart(ctx, {
      type: "bar",
            data: {
                labels: <?php echo json_encode($labels);?>,
                datasets: [{
                    label: "عدد دور النشر",
                    tension: 0.4,
                    borderWidth: 0,
                    borderRadius: 4,
                    borderSkipped: false,
                    backgroundColor: "rgba(255, 255, 255, .8)",
                    data: <?php echo json_encode($data); ?>,
                    maxBarThickness: 6
                }],
            },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          }
        },
        interaction: {
          intersect: false,
          mode: 'index',
        },
        scales: {
          y: {
            grid: {
              drawBorder: false,
              display: true,
              drawOnChartArea: true,
              drawTicks: false,
              borderDash: [5, 5],
              color: 'rgba(255, 255, 255, .2)'
            },
            ticks: {
              suggestedMin: 0,
              suggestedMax: 500,
              beginAtZero: true,
              padding: 10,
              font: {
                size: 14,
                weight: 300,
                family: "Roboto",
                style: 'normal',
                lineHeight: 2
              },
              color: "#fff"
            },
          },
          x: {
            grid: {
              drawBorder: false,
              display: true,
              drawOnChartArea: true,
              drawTicks: false,
              borderDash: [5, 5],
              color: 'rgba(255, 255, 255, .2)'
            },
            ticks: {
              display: true,
              color: '#f8f9fa',
              padding: 10,
              font: {
                size: 14,
                weight: 300,
                family: "Roboto",
                style: 'normal',
                lineHeight: 2
              },
            }
          },
        },
      },
    });


    var ctx2 = document.getElementById("chart-line").getContext("2d");

    new Chart(ctx2, {
  type: "bar", 
  data: {
    labels: <?php echo json_encode($userLabels); ?>,
    datasets: [{
      label: "عدد دور النشر",
      backgroundColor: "rgba(255, 255, 255, .8)",
      data: <?php echo json_encode($publisherCountsByUser); ?>,
      borderWidth: 0,
      borderRadius: 4,
      maxBarThickness: 50
    }],
  },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          }
        },
        interaction: {
          intersect: false,
          mode: 'index',
        },
        scales: {
          y: {
            grid: {
              drawBorder: false,
              display: true,
              drawOnChartArea: true,
              drawTicks: false,
              borderDash: [5, 5],
              color: 'rgba(255, 255, 255, .2)'
            },
            ticks: {
              display: true,
              color: '#f8f9fa',
              padding: 10,
              font: {
                size: 14,
                weight: 300,
                family: "Roboto",
                style: 'normal',
                lineHeight: 2
              },
            }
          },
          x: {
            grid: {
              drawBorder: false,
              display: false,
              drawOnChartArea: false,
              drawTicks: false,
              borderDash: [5, 5]
            },
            ticks: {
              display: true,
              color: '#f8f9fa',
              padding: 10,
              font: {
                size: 14,
                weight: 300,
                family: "Roboto",
                style: 'normal',
                lineHeight: 2
              },
            }
          },
        },
      },
    });

    var ctx3 = document.getElementById("chart-line-tasks").getContext("2d");

    new Chart(ctx3, {
      type: "bar",
            data: {
                labels: <?php echo json_encode($countryLabels);?>,
                datasets: [{
                    label: "عدد دور النشر",
                    tension: 0.4,
                    borderWidth: 0,
                    borderRadius: 4,
                    borderSkipped: false,
                    backgroundColor: "rgba(255, 255, 255, .8)",
                    data: <?php echo json_encode($countryData); ?>,
                    maxBarThickness: 6
                }],
            },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          }
        },
        interaction: {
          intersect: false,
          mode: 'index',
        },
        scales: {
          y: {
            grid: {
              drawBorder: false,
              display: true,
              drawOnChartArea: true,
              drawTicks: false,
              borderDash: [5, 5],
              color: 'rgba(255, 255, 255, .2)'
            },
            ticks: {
              suggestedMin: 0,
              suggestedMax: 500,
              beginAtZero: true,
              padding: 10,
              font: {
                size: 14,
                weight: 300,
                family: "Roboto",
                style: 'normal',
                lineHeight: 2
              },
              color: "#fff"
            },
          },
          x: {
            grid: {
              drawBorder: false,
              display: true,
              drawOnChartArea: true,
              drawTicks: false,
              borderDash: [5, 5],
              color: 'rgba(255, 255, 255, .2)'
            },
            ticks: {
              display: true,
              color: '#f8f9fa',
              padding: 10,
              font: {
                size: 14,
                weight: 300,
                family: "Roboto",
                style: 'normal',
                lineHeight: 2
              },
            }
          },
        },
      },
    });

    
  </script>
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
    
  </script>
  <!-- Github buttons -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="../assets/js/material-dashboard.min.js?v=3.0.0"></script>
</body>

</html>