<?php 
session_start();
include('secure.php');
include('header.php');
include('../connect.php');

$sessionUserId = $_SESSION['id']; 
$userRole = $_SESSION['role'];
if ($userRole === 'admin') {
  // For admin users, count all publishers
  $sql = "SELECT COUNT(*) AS publisher_count FROM publishers";
} elseif ($userRole === 'member') {
  // For member users, count only their own publishers
  $sql = "SELECT COUNT(*) AS publisher_count FROM publishers WHERE inserted_by = $sessionUserId";
}
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$publisherCount = $row['publisher_count'];

$result_publisher_types = mysqli_query($conn, "SELECT COUNT(*) AS publisher_type_count FROM publisher_types");
$row = mysqli_fetch_assoc($result_publisher_types);
$publisherTypeCount = $row['publisher_type_count'];

$result_users = mysqli_query($conn, "SELECT COUNT(*) AS user_count FROM users");
$row = mysqli_fetch_assoc($result_users);
$userCount = $row['user_count'];

// Check if the welcome message should be shown
$showWelcomeMessage = false;
if (isset($_SESSION['username']) && isset($_SESSION['showWelcomeMessage']) && $_SESSION['showWelcomeMessage']) {
    $showWelcomeMessage = true;
    // Unset the flag to prevent showing the message on subsequent page loads
    $_SESSION['showWelcomeMessage'] = false;
}
    if ($userRole === 'admin') {
      // For admin users, retrieve all libraries
      $sql = "SELECT
      publishers.id,
      publishers.publisher_name,
      publishers.phone,
      publishers.created_at,
      countries.arabic_countries,
      users.username AS inserted_by
      FROM
      publishers
      LEFT JOIN
      countries ON publishers.country_id = countries.id
      LEFT JOIN
      users ON publishers.inserted_by = users.id
      ORDER BY
      publishers.id DESC
      LIMIT 5";
    } elseif ($userRole === 'member') {
      // For member users, retrieve only their own libraries
      $sql = "SELECT
      publishers.id,
      publishers.publisher_name,
      publishers.phone,
      publishers.created_at,
      countries.arabic_countries,
      users.username AS inserted_by
      FROM
      publishers
      LEFT JOIN
      countries ON publishers.country_id = countries.id
      LEFT JOIN
      users ON publishers.inserted_by = users.id
      WHERE
      inserted_by = $sessionUserId
      ORDER BY
      publishers.id DESC
      LIMIT 5";
    }
$result = mysqli_query($conn, $sql);
$items = mysqli_fetch_all($result, MYSQLI_ASSOC);

mysqli_close($conn);

?>
      <div class="container-fluid py-4">
      <div class="row">
<!-- Display welcome message if user is logged in -->
<?php if ($showWelcomeMessage) { ?>

       <div class="col-12 mb-4">
          <div class="alert alert-secondary text-center text-white">
          مرحبًا <?php echo $_SESSION['username']; ?>، أهلاً بك في لوحة التحكم عمل موفق &#x1F60A;
          </div>
        </div>
        <?php 
  } 
  ?>
        <div class="col-lg-3 col-sm-6 mb-lg-0 mb-4">
          <div class="card">
            <div class="card-header p-3 pt-2">
              <div class="icon icon-lg icon-shape bg-gradient-dark shadow-dark text-center border-radius-xl mt-n4 position-absolute">
                <i class="material-icons opacity-10">store</i>
              </div>
              <div class="text-start pt-1">
                <p class="text-sm mb-0 text-capitalize">دور النشر</p>
                <h4 class="mb-0"><?php echo $publisherCount; ?></h4>
              </div>
            </div>
            <hr class="dark horizontal my-0">
            <div class="card-footer p-3">
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-sm-6 mb-lg-0 mb-4">
          <div class="card">
            <div class="card-header p-3 pt-2">
              <div class="icon icon-lg icon-shape bg-gradient-primary shadow-primary text-center border-radius-xl mt-n4 position-absolute">
                <i class="material-icons opacity-10">segment</i>
              </div>
              <div class="text-start pt-1">
                <p class="text-sm mb-0 text-capitalize">أنواع دور النشر</p>
                <h4 class="mb-0"><?php echo $publisherTypeCount; ?></h4>
              </div>
            </div>
            <hr class="dark horizontal my-0">
            <div class="card-footer p-3">
             
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-sm-6 mb-lg-0 mb-4">
          <div class="card">
            <div class="card-header p-3 pt-2">
              <div class="icon icon-lg icon-shape bg-gradient-success shadow-success text-center border-radius-xl mt-n4 position-absolute">
                <i class="material-icons opacity-10">subject</i>
              </div>
              <div class="text-start pt-1">
                <p class="text-sm mb-0 text-capitalize">اللغات</p>
                <h4 class="mb-0">
                  <span class="text-danger text-sm font-weight-bolder ms-1"></span>
                3
                </h4>
              </div>
            </div>
            <hr class="dark horizontal my-0">
            <div class="card-footer p-3">
            
            </div>
          </div>
        </div>

        <div class="col-lg-3 col-sm-6">
          <div class="card">
            <div class="card-header p-3 pt-2">
              <div class="icon icon-lg icon-shape bg-gradient-info shadow-info text-center border-radius-xl mt-n4 position-absolute">
                <i class="material-icons opacity-10">person_add</i>
              </div>
              <div class="text-start pt-1">
                <p class="text-sm mb-0 text-capitalize">المشرفون</p>
                <h4 class="mb-0"><?php echo $userCount; ?></h4>
              </div>
            </div>
            <hr class="dark horizontal my-0">
            <div class="card-footer p-3">
            </div>
          </div>
        </div>
      </div>
      <div class="row mt-4">
        <div class="col-lg-4 col-md-6 mt-4 mb-4">
          <div class="card z-index-2 ">
            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2 bg-transparent">
              <div class="bg-gradient-warning shadow-warning border-radius-lg py-3 pe-1">
                <div class="chart">
                  <canvas id="chart-bars" class="chart-canvas" height="170"></canvas>
                </div>
              </div>
            </div>
            <div class="card-body">
              <h6 class="mb-0 ">عدد دور النشر حسب النوع</h6>
              <p class="text-sm ">آخر الإحصائيات</p>
              <hr class="dark horizontal">
              <div class="d-flex ">
                <i class="material-icons text-sm my-auto ms-1">schedule</i>
                <p class="mb-0 text-sm"> سنة 2023 </p>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-4 col-md-6 mt-4 mb-4">
          <div class="card z-index-2  ">
            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2 bg-transparent">
              <div class="bg-gradient-success shadow-success border-radius-lg py-3 pe-1">
                <div class="chart">
                  <canvas id="chart-line" class="chart-canvas" height="170"></canvas>
                </div>
              </div>
            </div>
            <div class="card-body">
              <h6 class="mb-0 "> عدد دور النشر - أدمن </h6>
              <p class="text-sm "> عدد دور النشر لكل أدمن </p>
              <hr class="dark horizontal">
              <div class="d-flex ">
                <i class="material-icons text-sm my-auto ms-1">schedule</i>
                <p class="mb-0 text-sm"> سنة 2023 </p>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-4 mt-4 mb-3">
          <div class="card z-index-2 ">
            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2 bg-transparent">
              <div class="bg-gradient-info shadow-info border-radius-lg py-3 pe-1">
                <div class="chart">
                  <canvas id="chart-line-tasks" class="chart-canvas" height="170"></canvas>
                </div>
              </div>
            </div>
            <div class="card-body">
            <h6 class="mb-0 ">عدد دور النشر حسب الدول</h6>
              <p class="text-sm ">آخر الإحصائيات</p>
              <hr class="info horizontal">
              <div class="d-flex ">
                <i class="material-icons text-sm my-auto me-1">schedule</i>
                <p class="mb-0 text-sm"> سنة 2023 </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row my-4">
        <div class="col-12 mb-md-0 mb-4">
          <div class="card">
            <div class="card-header pb-0">
              <div class="row mb-3">
                <div class="col-6">
                  <h6>دور النشر</h6>
                  <p class="text-sm font-weight-bold">
                  <i class="fa fa-check text-success" aria-hidden="true"></i>
                   آخر خمسة دور نشر تم إضافتهم
                  </p>
                </div>
              </div>
            </div>
            <div class="card-body p-0 pb-2">
              <div class="table-responsive">
                <table class="table table-hover align-items-center mb-0">
                  <thead>
                    <tr>
                      <th class="text-secondary text-lg font-weight-bolder opacity-7">المعرف</th>
                      <th class="text-secondary text-lg font-weight-bolder opacity-7 pe-2">دار النشر</th>
                      <th class="text-secondary text-lg font-weight-bolder opacity-7 pe-2">الدولة</th>
                      <th class="text-secondary text-lg font-weight-bolder opacity-7 pe-2">من طرف</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php
                foreach ($items as $item) {
                ?>
                    <tr>
                      <td class="align-middle text-sm">
                       <h6 class="mb-0 text-sm pe-4"><?php echo htmlspecialchars($item["id"]);?>#</h6>
                      </td>
                      <td>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($item["publisher_name"]);?></h6>
                            <p class="text-xs text-secondary mb-0"><?php echo htmlspecialchars($item["phone"]);?></p>
                          </div>
                      </td>
                      <td class="align-middle text-sm">
                        <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($item["arabic_countries"]); ?></h6>
                      </td>
                      <td class="align-middle text-sm">
                        <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($item["inserted_by"]);?></h6>
                        <p class="text-xs text-secondary mb-0"><?php echo htmlspecialchars($item["created_at"]);?></p>
                      </td>
                    </tr>
                    <?php
                }
                ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

<?php
include('footer.php');
?>