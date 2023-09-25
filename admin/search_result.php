<?php
session_start();
include('secure.php');
include('../connect.php');
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include('header.php');
    $searchQuery = trim($_POST["search_query"]);
    // Perform the database search using a parameterized query
    // Perform the database search using a parameterized query
    $sql = "SELECT p.*, c.arabic_countries, pt.publisher_type
        FROM publishers AS p
        INNER JOIN countries AS c ON p.country_id = c.id
        LEFT JOIN publisher_types AS pt ON p.publisher_type_id = pt.id
        WHERE publisher_name LIKE ? AND 1 = 1";

    $stmt = mysqli_prepare($conn, $sql);
    $searchQueryWithWildcard = "%" . mysqli_real_escape_string($conn, $searchQuery) . "%";
    mysqli_stmt_bind_param($stmt, "s", $searchQueryWithWildcard);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $numResults = mysqli_num_rows($result);

    echo "<p class='text-center'>$numResults نتيجة : " . htmlspecialchars($searchQuery) . "</p>";
?>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                        <h6 class="text-white text-capitalize pe-3">نتائج البحث</h6>
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
    // Display the search results
    if ($numResults > 0) {
        $usernames = [];
        $sql_users = "SELECT id, username FROM users";
        $result_users = mysqli_query($conn, $sql_users);
        while ($user = mysqli_fetch_assoc($result_users)) {
            $usernames[$user['id']] = $user['username'];
        }

        $items = mysqli_fetch_all($result, MYSQLI_ASSOC);
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
                        <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($item["arabic_countries"]); ?></h6>
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
                        <p class="text-xs text-warning mb-0 text-bold"><?php echo htmlspecialchars($item["publisher_type"]); ?></p>

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
                                    <h5 class="modal-title" id="modalTitle">ملاحظات خاصة بدار النشر: <?php echo $item['publisher_name']; ?></h5>
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
                        <a href="update_publisher.php?id=<?php echo htmlspecialchars($item["id"]); ?>" class="btn badge-sm bg-gradient-primary">
                        <i class="material-icons-round align-middle" style="font-size: 18px;">edit</i>
                        </a>
                        <a href="delete_publisher.php?id=<?php echo htmlspecialchars($item["id"]);?>" class="btn badge-sm bg-gradient-danger"> <i class="material-icons-round align-middle" style="font-size: 18px;">delete</i></a>
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
  mysqli_close($conn);
  include('footer.php');
                               
    } else {
        echo "<p class='text-center'>لم يتم إيجاد أي نتيجة.</p>";
    }
    
    mysqli_close($conn);
    include('footer.php');
    ob_end_flush();
    exit();
} else {
    // if the admin access direct to page redirect it 
    header("Location: index.php");
    exit();
}
?>
                       
