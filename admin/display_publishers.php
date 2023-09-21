<?php
session_start();
include('secure.php');
include('../connect.php');
$table = "publishers";

$itemsPerPage = 10; // Number of items per page

$currentPage = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? intval($_GET['page']) : 1;

// Get the total number of items in the database
$sql = "SELECT COUNT(*) AS total_items FROM $table";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$totalItems = $row['total_items'];

$totalPages = ceil($totalItems / $itemsPerPage);
$currentPage = max(1, min($currentPage, $totalPages));

$startIndex = ($currentPage - 1) * $itemsPerPage;

// Retrieve items for the current page
$result = mysqli_query($conn, "SELECT * FROM $table LIMIT $startIndex, $itemsPerPage");
$items = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Get the selected publisher type and country from the query parameters
$selectedPublisherType = isset($_GET['publisherType']) ? $_GET['publisherType'] : 'all';
$selectedCountry = isset($_GET['countryFilter']) ? $_GET['countryFilter'] : 'all';

// Construct the SQL query to count filtered items
$sqlCount = "SELECT COUNT(*) AS total_filtered_items FROM $table AS p WHERE 1 = 1";

// Initialize an array to store bind values and types
$bindParams = [];
$bindTypes = '';

// Check if a specific publisher type is selected
if ($selectedPublisherType !== 'all') {
    $sqlCount .= " AND p.publisher_type_id = ?";
    $bindTypes .= 'i'; // Assuming publisher_type_id is an integer
    $bindParams[] = &$selectedPublisherType;
}

// Check if a specific country is selected
if ($selectedCountry !== 'all') {
    $sqlCount .= " AND p.country_id = ?";
    $bindTypes .= 'i'; // Assuming country_id is an integer
    $bindParams[] = &$selectedCountry;
}

// Prepare and execute the count query
$countStmt = mysqli_prepare($conn, $sqlCount);

if (!empty($bindParams)) {
    $countStmt->bind_param($bindTypes, ...$bindParams);
}

mysqli_stmt_execute($countStmt);

$countResult = mysqli_stmt_get_result($countStmt);
$countRow = mysqli_fetch_assoc($countResult);
$totalFilteredItems = $countRow['total_filtered_items'];

// Calculate Total Pages
$totalPages = ceil($totalFilteredItems / $itemsPerPage);

// Construct the main SQL query for pagination
$sql = "SELECT p.*
        FROM publishers AS p
        WHERE 1 = 1";

// Initialize an array to store bind values and types
$bindParams = [];
$bindTypes = '';

// Check if a specific publisher type is selected
if ($selectedPublisherType !== 'all') {
    $sql .= " AND p.publisher_type_id = ?";
    $bindTypes .= 'i'; // Assuming publisher_type_id is an integer
    $bindParams[] = &$selectedPublisherType;
}

// Check if a specific country is selected
if ($selectedCountry !== 'all') {
    $sql .= " AND p.country_id = ?";
    $bindTypes .= 'i'; // Assuming country_id is an integer
    $bindParams[] = &$selectedCountry;
}

$sql .= " ORDER BY p.id DESC";
$sql .= " LIMIT $startIndex, $itemsPerPage";

$stmt = mysqli_prepare($conn, $sql);

// Bind parameters for the query prepared statement
if (!empty($bindParams)) {
    $stmt->bind_param($bindTypes, ...$bindParams);
}

// Execute the query
mysqli_stmt_execute($stmt);

// Get the result set
$result = mysqli_stmt_get_result($stmt);

// Fetch the items for the current page
$items = mysqli_fetch_all($result, MYSQLI_ASSOC);

include('header.php');
?>
    <div class="container-fluid py-4">
      <?php
    // Check if create_update_success session variable is set
        if (isset($_SESSION['create_update_success']) && $_SESSION['create_update_success'] === true) {
            echo '<div class="alert alert-success text-right text-white">تم إنشاء/تحديث العنصر بنجاح.</div>';
            // Unset the session variable to avoid displaying the message on page refresh
            unset($_SESSION['create_update_success']);
        }
        // Check if delete_success session variable is set
        if (isset($_SESSION['delete_success']) && $_SESSION['delete_success'] === true) {
            echo '<div class="alert alert-success text-right text-white">تم حذف العنصر بنجاح.</div>';
            // Unset the session variable to avoid displaying the message on page refresh
            unset($_SESSION['delete_success']);
        }
        // Check if item_not_found session variable is set
        if (isset($_SESSION['item_not_found']) && $_SESSION['item_not_found'] === true) {
            echo '<div class="alert alert-danger text-right text-white">العنصر غير موجود.</div>';
            // Unset the session variable to avoid displaying the message on page refresh
            unset($_SESSION['item_not_found']);
        }
        ?>
       
       <h4 class="mb-3">فلترة دور النشر</h4>
        <div class="input-group input-group-outline my-3">
            <a href="add_publisher.php" class="btn btn-secondary">إضـافة</a>
        </div>

        <form role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
            <h5 class="mb-3">فلترة</h5>
           <div class="input-group input-group-outline my-3">
                <select class="form-control" id="publisherType" name="publisherType">
                    <option value="all" <?php echo $selectedPublisherType === 'all' ? 'selected' : ''; ?>>-- All publisher Types --</option>
                    <?php
                    $pubTypeQuery = "SELECT id, publisher_type FROM publisher_types";
                    $pubtypeResult = mysqli_query($conn, $pubTypeQuery);
                    while ($pubTypeRow = mysqli_fetch_assoc($pubtypeResult)) {
                        $pubTypeId = $pubTypeRow['id'];
                        $pubTypeName = $pubTypeRow['publisher_type'];
                        $selected = ($selectedPublisherType === $pubTypeId) ? 'selected' : '';
                        echo "<option value=\"$pubTypeId\" $selected>$pubTypeName</option>";
                    }
                    ?>
                </select>
           </div>
           <div class="input-group input-group-outline my-3">
            <select class="form-control" id="countryFilter" name="countryFilter">
              <option value="all" <?php echo $selectedCountry === 'all' ? 'selected' : ''; ?>>-- All Countries --</option>
              <?php
              // Retrieve and populate the country options from your countries table
              $countryQuery = "SELECT id, arabic_countries FROM countries";
              $countryResult = mysqli_query($conn, $countryQuery);
              while ($countryRow = mysqli_fetch_assoc($countryResult)) {
                  $countryId = $countryRow['id'];
                  $countryName = $countryRow['arabic_countries'];
                  $selected = ($selectedCountry === $countryId) ? 'selected' : '';
                  echo "<option value=\"$countryId\" $selected>$countryName</option>";
              }
              ?>
          </select>
         </div>

            <div class="input-group input-group-outline my-3">

            </div>
            <div class="input-group input-group-outline my-3">
  
</div>

<div class="input-group input-group-outline my-3">
   
</div>
          <button type="submit"  class="btn bg-gradient-primary" >فلترة</button> 
          <button type="button" class="btn btn-secondary" id="clearFilter">مسح الفلتر</button>

        </form>
    <div class="row">
        <div class="col-12">
          <div class="card my-4">
            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
              <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                <h6 class="text-white text-capitalize pe-3">جدول دور النشر</h6>
              </div>
            </div>
            <div class="card-body px-0 pb-2">
              <div class="table-responsive p-0">
                <table class="table align-items-center mb-0 table-hover">
                  <thead>
                    <tr>
                      <th class="text-secondary text-lg font-weight-bolder opacity-7 ">دار النشر</th>
                      <th class="text-secondary text-lg font-weight-bolder opacity-7 pe-2">الهاتف</th>
                      <th class="text-secondary text-lg font-weight-bolder opacity-7 pe-2">الدولة</th>
                      <th class="text-secondary text-lg font-weight-bolder opacity-7 pe-2">العنوان</th>
                      <th class="text-secondary text-lg font-weight-bolder opacity-7 pe-2">الصنف</th>
                      <th class="text-secondary text-lg font-weight-bolder opacity-7 pe-2">اللغة</th>
                      <th class="text-secondary text-lg font-weight-bolder opacity-7 pe-2">من طرف</th>
                      <th class="text-secondary text-lg font-weight-bolder opacity-7 pe-2">ملاحظات</th>
                      <th class="text-center text-secondary text-lg font-weight-bolder opacity-7">الإجراءات</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php
                 
                    $usernames = [];
                    $sql_users = "SELECT id, username FROM users";
                    $result_users = mysqli_query($conn, $sql_users);
                    while ($user = mysqli_fetch_assoc($result_users)) {
                        $usernames[$user['id']] = $user['username'];
                    }
                    function getPublisherType($item) {
                      $publisherTypes = [];
                  
                      // Check the values of the boolean columns and add corresponding types to the array
                      if ($item["semi_school"]) {
                          $publisherTypes[] = "شبه مدرسية";
                      }
                      if ($item["university"]) {
                          $publisherTypes[] = "جامعية";
                      }
                      if ($item["children"]) {
                          $publisherTypes[] = "أطفال";
                      }
                      if ($item["novel"]) {
                          $publisherTypes[] = "رواية";
                      }
                  
                      // If no types are found, return "N/A" or any other default value
                      if (empty($publisherTypes)) {
                          return "N/A";
                      }
                  
                      // Join the types into a comma-separated string and return
                      return implode(", ", $publisherTypes);
                  }

                  function getPublisherLan($item) {
                    $languages = [];
                
                    // Check the values of the boolean columns and add corresponding languages to the array
                    if ($item["arabic_lan"]) {
                        $languages[] = "العربية";
                    }
                    if ($item["english_lan"]) {
                        $languages[] = "الإنجليزية";
                    }
                    if ($item["french_lan"]) {
                        $languages[] = "الفرنسية";
                    }
                    if ($item["other_lan"]) {
                      $languages[] = "لغة أخرى";
                  }
              
                    // If no languages are found, return "N/A" or any other default value
                    if (empty($languages)) {
                        return "N/A";
                    }
                
                    // Remove duplicate languages and join them into a comma-separated string
                    $uniqueLanguages = array_unique($languages);
                    return implode(", ", $uniqueLanguages);
                }
                
                foreach ($items as $item) {    
                ?>
                    <tr>
                    <td class="align-middle text-sm">
                            <h6 class="mb-0 text-sm pe-3"><?php echo htmlspecialchars($item["publisher_name"]);?></h6>
                            <p class="text-xs text-warning text-bold mb-0 pe-3"><?php echo htmlspecialchars($item["id"]);?>#</h6>
                      </td>
                      <td class="align-middle text-sm">
                        <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($item["phone"]);?></h6>
                        <p class="text-xs text-secondary text-bold mb-0"><?php echo htmlspecialchars($item["second_phone"]);?></p>
                        <p class="text-xs text-secondary mb-0"><?php echo htmlspecialchars($item["email"]);?></p>
                      </td>
                      <td class="align-middle text-sm">
                        <h6 class="mb-0 text-sm"><?php //echo htmlspecialchars($item["arabic_countries"]); ?></h6>
                      </td>
                      <td class="align-middle text-sm">
                      <h6 class="mb-0 text-sm"><?php
                        $address = htmlspecialchars($item["address"]);
                        $words = explode(' ', $address);
                        
                        $wordGroups = array_chunk($words, 4);
                        foreach ($wordGroups as $group) {
                            echo implode(' ', $group) . "<br>";
                        }
                        ?></h6>
                      <!-- Social Media Icons -->
                      <div class="ms-auto">
                          <?php if (!empty($item["fbLink"])) { ?>
                            <a href="<?php echo htmlspecialchars($item["fbLink"]); ?>" target="_blank">
                              <i class="fab fa-facebook"></i>
                            </a>
                          <?php } ?>
                          <?php if (!empty($item["instaLink"])) { ?>
                            <a href="<?php echo htmlspecialchars($item["instaLink"]); ?>" target="_blank">
                              <i class="fab fa-instagram"></i>
                            </a>
                          <?php } ?>
                          <?php if (!empty($item["tiktokLink"])) { ?>
                            <a href="<?php echo htmlspecialchars($item["tiktokLink"]); ?>" target="_blank">
                              <i class="fab fa-tiktok"></i>
                            </a>
                          <?php } ?>
                          <?php if (!empty($item["mapAddress"])) { ?>
                            <a href="<?php echo htmlspecialchars($item["mapAddress"]); ?>" target="_blank">
                              <i class="fas fa-map-marker-alt"></i>
                            </a>
                          <?php } ?>
                          <?php if (!empty($item["websiteLink"])) { ?>
                            <a href="<?php echo htmlspecialchars($item["websiteLink"]); ?>" target="_blank">
                              <i class="fas fa-globe"></i>
                            </a>
                          <?php } ?>
                        </div>
                      </td>
                      <td class="align-middle text-sm">
                        <h6 class="mb-0 text-sm"><?php echo htmlspecialchars(getPublisherType($item));?></h6>
                      </td>
                      <td class="align-middle text-sm">
                        <h6 class="mb-0 text-sm"><?php echo htmlspecialchars(getPublisherLan($item));?></h6>
                      </td>
                      <td class="align-middle text-sm">
                      <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($usernames[$item["inserted_by"]]); ?></h6>
                      <p class="text-xs text-secondary mb-0"><?php echo htmlspecialchars($item["created_at"]);?></p>
                      </td>
                      <td class="align-middle text-sm">
                    <h6 class="mb-0 text-sm">
                        <?php if (!empty($item['notes'])): ?>
                            <!-- Button trigger modal -->
                            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#exampleModal_<?php echo $item['id']; ?>">
                                <i class="fas fa-comment-alt align-middle" style="font-size: 18px;"></i>
                            </button>
                        <?php endif; ?>
                    </h6>
                    <!-- Modal -->
                    <div class="modal fade" id="exampleModal_<?php echo $item['id']; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalTitle">ملاحظات خاصة بمكتبة: <?php echo $item['library_name']; ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div id="modalContent">
                                    <?php
                        $words = explode(' ', $item['notes']); // Split note content into words
                        $chunkedWords = array_chunk($words, 9); // Group words into sets of 9
                        
                        foreach ($chunkedWords as $wordSet) {
                            echo '<div class="note-line">' . implode(' ', $wordSet) . '</div>'; // Display each set of words
                        }
                        ?>
                                    </div>
                                </div>
                                <div class="modal-footer d-flex justify-content-center">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">غلق</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </td>          
                      <td class="align-middle text-center">
                        <a href="update_library.php?id=<?php //echo htmlspecialchars($item["id"]); ?>&states=<?php //echo htmlspecialchars($item["states"]); ?>&province=<?php //echo htmlspecialchars($item["provinces"]); ?>&city=<?php //echo htmlspecialchars($item["cities"]); ?>" class="btn badge-sm bg-gradient-primary">
                        <i class="material-icons-round align-middle" style="font-size: 18px;">edit</i>
                        </a>
                        <a href="delete_library.php?id=<?php //echo htmlspecialchars($item["id"]);?>" class="btn badge-sm bg-gradient-danger"> <i class="material-icons-round align-middle" style="font-size: 18px;">delete</i></a>
                      </td>
                    </tr>
                    <?php
                }
                ?>
                  </tbody>
                </table>
                <?php
                include('../pagination.php');
                  ?>
              </div>
            </div>
          </div>
        </div>
      </div>
     <?php
 mysqli_close($conn);
include('footer.php');
?>