<?php
include('../connect.php');
// Initialize variables
$publisher_name = $address = $phone = $second_phone = $email = $websiteLink = $fbLink = $instaLink = $mapAddress = $tiktokLink = $notes =  $novel = $semi_school = $university = $children = $arabic_lan = $english_lan = $french_lan = $other_lan = $publisher_type_id = $country_id = "";
$publisher_name_err = $phone_err = $second_phone_err = $email_err = $register_err = "";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Get the form data
  $publisher_name = trim($_POST["publisher_name"]);
  $address = trim($_POST["address"]);
  $publisher_type_id = trim($_POST["publisher_type_id"]);
  $country_id = trim($_POST["country_id"]);

  $phone = trim($_POST["phone"]);
  $second_phone = trim($_POST["second_phone"]);
  $email = trim($_POST["email"]);
  $websiteLink = trim($_POST["websiteLink"]);

  $fbLink = trim($_POST["fbLink"]);
  $instaLink = trim($_POST["instaLink"]);
  $mapAddress = trim($_POST["mapAddress"]);
  $tiktokLink = trim($_POST["tiktokLink"]);

  $semi_school = isset($_POST["semi_school"]) ? 1 : 0;
  $university = isset($_POST["university"]) ? 1 : 0;
  $children = isset($_POST["children"]) ? 1 : 0;
  $novel = isset($_POST["novel"]) ? 1 : 0;

  $arabic_lan = isset($_POST["arabic_lan"]) ? 1 : 0;
  $english_lan = isset($_POST["english_lan"]) ? 1 : 0;
  $french_lan = isset($_POST["french_lan"]) ? 1 : 0;
  $other_lan = isset($_POST["other_lan"]) ? 1 : 0;

  $notes = trim($_POST["notes"]);

   // Validate library name
    if (empty($publisher_name)) {
        $publisher_name_err = "يرجى إدخال اسم دار النشر.";
    } elseif (!preg_match("/^[\p{L}\p{N}_\s]+$/u", $publisher_name)) {
        $publisher_name_err = "اسم دار النشر يجب أن يحتوي على حروف.";
    }

  $phonePattern = "/^\+?\d{1,4}?\s?\(?\d{1,4}?\)?[0-9\- ]+$/";

  // Validate primary phone
  if (!empty($phone) && !preg_match($phonePattern, $phone)) {
      $phone_err = "رقم هاتف غير صالح.";
  } else {
      // Check if phone number already exists in the database (in phone or second_phone column)
      $existingPhoneQuery = "SELECT id, publisher_name FROM publishers WHERE phone = ? OR second_phone = ?";
      $stmt_existingPhone = mysqli_prepare($conn, $existingPhoneQuery);
      mysqli_stmt_bind_param($stmt_existingPhone, "ss", $phone, $phone);
      mysqli_stmt_execute($stmt_existingPhone);
      mysqli_stmt_store_result($stmt_existingPhone);
      if (mysqli_stmt_num_rows($stmt_existingPhone) > 0) {
          mysqli_stmt_bind_result($stmt_existingPhone, $existingLibaryId, $existingLibraryName);
          mysqli_stmt_fetch($stmt_existingPhone);
          $phone_err = "رقم الهاتف مستخدم بالفعل مع دار نشر: $existingLibraryName (رقم دار النشر: $existingLibaryId)";
      }
      mysqli_stmt_close($stmt_existingPhone);
  }
  
  // Validate secondary phone
  if (!empty($second_phone) && !preg_match($phonePattern, $second_phone)) {
      $second_phone_err = "رقم هاتف ثانوي غير صالح.";
  } else {
      // Check if secondary phone number already exists in the database (in phone or second_phone column)
      if (!empty($second_phone)) {
          $existingSecondPhoneQuery = "SELECT id, publisher_name FROM publishers WHERE phone = ? OR second_phone = ?";
          $stmt_existingSecondPhone = mysqli_prepare($conn, $existingSecondPhoneQuery);
          mysqli_stmt_bind_param($stmt_existingSecondPhone, "ss", $second_phone, $second_phone);
          mysqli_stmt_execute($stmt_existingSecondPhone);
          mysqli_stmt_store_result($stmt_existingSecondPhone);
          if (mysqli_stmt_num_rows($stmt_existingSecondPhone) > 0) {
              mysqli_stmt_bind_result($stmt_existingSecondPhone, $existingLibaryId, $existingLibraryName);
              mysqli_stmt_fetch($stmt_existingSecondPhone);
              $second_phone_err = "رقم الهاتف الثانوي مستخدم بالفعل مع دار نشر: $existingLibraryName (رقم دار النشر: $existingLibaryId)";
          }
          mysqli_stmt_close($stmt_existingSecondPhone);
      }
  }

  // Validate email
  if (!empty($email)) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_err = "يرجى إدخال عنوان إيميل صالح.";
    } 
    }

  // If there are no errors, proceed with registration
  if (empty($publisher_name_err) && empty($phone_err) && empty($second_phone_err) && empty($email_err)) {
    include('../connect.php');
    session_start();
    $user_id = $_SESSION['id'];
       
        // Insert the publisher data into the database
        $insert_query = "INSERT INTO publishers (publisher_name, address, phone, second_phone, email, fbLink, instaLink, mapAddress, tiktokLink, websiteLink, created_at, notes, inserted_by, publisher_type_id, country_id, semi_school, university, children, novel, arabic_lan, english_lan, french_lan, other_lan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, "sssssssssssiiiiiiiiiii", $publisher_name, $address, $phone, $second_phone, $email, $fbLink, $instaLink, $mapAddress, $tiktokLink, $websiteLink, $notes, $user_id, $publisher_type_id, $country_id, $semi_school, $university, $children, $novel, $arabic_lan, $english_lan, $french_lan, $other_lan);
        
        
        mysqli_stmt_execute($stmt);

    // Store the success message in a session variable
    $_SESSION['register_success_msg'] = "تم إضافة دار النشر بنجاح.";
    // Registration successful, redirect to login page or dashboard
    header("Location: add_publisher.php");
    exit();
    mysqli_stmt_close($stmt_insert_user);
    // Close the connection
    mysqli_close($conn);
  }
}
session_start();
include('secure.php');
$register_success_msg = isset($_SESSION['register_success_msg']) ? $_SESSION['register_success_msg'] : "";
include('header.php');
?>
    <div class="container-fluid py-4">
          <!-- Display the flash message if it exists -->
<?php if (isset($_SESSION['register_success_msg'])) { ?>
    <div class="progress-container">
        <div class="progress-bar" id="myProgressBar">
            <div class="progress-text">يتم إضافة دار النشر</div>
        </div>
    </div>
    <div class="alert alert-success mt-3 text-white" role="alert" id="successMessage" style="display: none;">
        <?php echo $_SESSION['register_success_msg']; ?>
    </div>
    <style>
        .progress-container {
            height: 30px;
            background-color: #f5f5f5;
            border-radius: 5px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            width: 0;
            background-color: #4caf50;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: white;
            position: relative;
        }

        .progress-text {
            position: absolute;
        }
    </style>
<script>
    var progressContainer = document.querySelector(".progress-container");
    var progressBar = document.getElementById("myProgressBar");
    var progressText = document.querySelector(".progress-text");
    var successMessage = document.getElementById("successMessage");

    // Simulate progress
    var progress = 0;
    var interval = setInterval(function () {
        progress += 10;
        progressBar.style.width = progress + "%";
        progressText.textContent = "يتم إضافة دار النشر " + progress + "%";
        if (progress >= 100) {
            clearInterval(interval);
            progressContainer.style.display = "none"; // Hide the entire progress container
            successMessage.style.display = "block";
        }
    }, 250);
</script>
 <?php 
$sessionUserId = $_SESSION['id']; 
$userRole = $_SESSION['role'];

// Check if the user has reached 100 libraries
$userPublisherCount = 5; 
if ($userRole === 'admin') {
    $sql = "SELECT COUNT(*) AS publisher_count FROM publishers WHERE inserted_by = $sessionUserId";
    $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        $publisherCount = $row['publisher_count'];
        if ($publisherCount >= $userPublisherCount || $publisherCount % 100 == 0) {
            ?>
                <div class="modal fade" id="congratulationModal" tabindex="-1" aria-labelledby="congratulationModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="congratulationModalLabel">تهانينا!</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                لقد قمت بإضافة <?php echo $publisherCount; ?> دار نشر. 🎉 تهانينا على إنجازك!
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php

            // Display a JavaScript function to show the modal
            echo '<script>';
            echo 'jQuery(document).ready(function() {';
            echo '   jQuery("#congratulationModal").modal("show");';
            echo '});';
            echo '</script>';
        }
} unset($_SESSION['register_success_msg']); }  ?>

        <form role="form" action="" method="post" enctype="multipart/form-data">
        <h4 class="mb-3">إضافة دار نشر</h4>
            <div class="border rounded p-4 shadow">
                <h6 class="border-bottom pb-2 mb-3">معلومات دار النشر</h6>
                    <div class="d-flex">
                        <div class="input-group input-group-outline m-3">
                            <select class="form-control" id="country_id" name="country_id" required>
                                <option value="" disabled selected>-- اختر دولة دار النشر *  --</option>
                                <?php
                                // Fetch library publisher_specialties from the database
                                $sql_fetch_countries = "SELECT * FROM countries";
                                $result_countries = mysqli_query($conn, $sql_fetch_countries);
                                while ($row = mysqli_fetch_assoc($result_countries)) {
                                    echo '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['arabic_countries']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                    <div class="input-group input-group-outline my-3">
                    <select class="form-control" id="publisher_type_id" name="publisher_type_id" required>
                            <option value="" disabled selected>-- اختر نوع دار النشر *  --</option>
                            <?php
                            // Fetch publisher types from the database
                            $sql_fetch_publisher_types = "SELECT * FROM publisher_types";
                            $result_publisher_types = mysqli_query($conn, $sql_fetch_publisher_types);
                            while ($row = mysqli_fetch_assoc($result_publisher_types)) {
                                echo '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['publisher_type']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
            </div>

                <div class="d-flex">
                    <div class="input-group input-group-outline m-3">
                        <?php if (empty($publisher_name)): ?>
                            <label for="publisher_name" class="form-label">اسم دار النشر *  </label>
                        <?php endif; ?>
                        <input type="text" class="form-control <?php echo (!empty($publisher_name_err)) ? 'is-invalid' : ''; ?>"
                            id="publisher_name" name="publisher_name" value="<?php echo $publisher_name; ?>" required
                            <?php if (!empty($publisher_name)) echo 'placeholder="اسم دار النشر"'; ?> />
                        <span class="invalid-feedback"><?php echo $publisher_name_err; ?></span>
                    </div>
                    <div class="input-group input-group-outline my-3">
                        <?php if (empty($address)): ?>
                                <label for="address" class="form-label">العنوان</label>
                            <?php endif; ?>
                            <input type="text" class="form-control <?php echo (!empty($address_err)) ? 'is-invalid' : ''; ?>"
                                id="address" name="address" value="<?php echo $address; ?>"
                                <?php if (!empty($address)) echo 'placeholder="العنوان"'; ?> />
                            <span class="invalid-feedback"><?php echo $address_err; ?></span>
                    </div>
                </div>
            </div>
            <div class="border rounded p-4 my-4 shadow">
                <h6 class="border-bottom pb-2 mb-3">معلومات التواصل</h6>
            <div class="d-flex">
                <div class="input-group input-group-outline m-3">
                    <?php if (empty($phone)): ?>
                        <label for="phone" class="form-label">الهاتف *  </label>
                    <?php endif; ?>
                    <input type="text" class="form-control <?php echo (!empty($phone_err)) ? 'is-invalid' : ''; ?>"
                          id="phone" name="phone" value="<?php echo $phone; ?>" required
                          <?php if (!empty($phone)) echo 'placeholder="الهاتف"'; ?> />
                    <span class="invalid-feedback"><?php echo $phone_err; ?></span>
                </div>
                <div class="input-group input-group-outline my-3">
                    <?php if (empty($second_phone)): ?>
                        <label for="second_phone" class="form-label">الهاتف الثاني</label>
                    <?php endif; ?>
                    <input type="text" class="form-control <?php echo (!empty($second_phone_err)) ? 'is-invalid' : ''; ?>"
                          id="second_phone" name="second_phone" value="<?php echo $second_phone; ?>"
                          <?php if (!empty($second_phone)) echo 'placeholder="الهاتف الثاني"'; ?> />
                    <span class="invalid-feedback"><?php echo $second_phone_err; ?></span>
                </div>
            </div>
            <div class="d-flex">
                <div class="input-group input-group-outline m-3">
                <?php if (empty($websiteLink)): ?>
                    <label for="websiteLink" class="form-label">موقع الويب</label>
                  <?php endif; ?>
                  <input type="text" class="form-control" id="websiteLink" name="websiteLink" value="<?php echo $websiteLink; ?>"
                    <?php if (!empty($websiteLink)) echo 'placeholder="موقع الويب"'; ?> />
                </div>

                <div class="input-group input-group-outline my-3">
                    <?php if (empty($email)): ?>
                        <label for="email" class="form-label">الإيميل</label>
                    <?php endif; ?>
                    <input type="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>"
                          id="email" name="email" value="<?php echo $email; ?>"
                          <?php if (!empty($email)) echo 'placeholder="الإيميل"'; ?> />
                    <span class="invalid-feedback"><?php echo $email_err; ?></span>
                </div>
            </div>
                 
          </div>

        <!--  -->
        <div class="border rounded p-4 my-4 shadow">
            <h6 class="border-bottom pb-2 mb-3">معلومات صنف الكتب</h6>
                <div class="d-flex">
                    <div class="form-check col-md-6 me-3 mt-3">
                        <input class="form-check-input" type="checkbox" value="1" id="customCheck1" name="semi_school">
                        <label class="custom-control-label" for="customCheck1">كتب شبه مدرسية</label>
                    </div>

                    <div class="form-check col-md-6 mt-3">
                        <input class="form-check-input" type="checkbox" value="1" id="customCheck2" name="university">
                        <label class="custom-control-label" for="customCheck2">كتب جامعية</label>
                    </div>
                </div>

                <div class="d-flex">
                    <div class="form-check col-md-6 me-3 mt-3">
                        <input class="form-check-input" type="checkbox" value="1" id="customCheck3" name="children">
                        <label class="custom-control-label" for="customCheck3">كتب أطفال</label>
                    </div>

                    <div class="form-check col-md-6 mt-3">
                        <input class="form-check-input" type="checkbox" value="1" id="customCheck4" name="novel">
                        <label class="custom-control-label" for="customCheck4">روايات</label>
                    </div>
                </div>

           </div>
         <!--  -->
        <div class="border rounded p-4 my-4 shadow">
            <h6 class="border-bottom pb-2 mb-3">معلومات لغات الكتب</h6>
                <div class="d-flex">
                    <div class="form-check col-md-6 me-3 mt-3">
                        <input class="form-check-input" type="checkbox" value="1" id="customCheck5" name="arabic_lan">
                        <label class="custom-control-label" for="customCheck5">اللغة العربية</label>
                    </div>

                    <div class="form-check col-md-6 mt-3">
                        <input class="form-check-input" type="checkbox" value="1" id="customCheck6" name="english_lan">
                        <label class="custom-control-label" for="customCheck6">اللغة الإنجليزية</label>
                    </div>
                </div>

                <div class="d-flex">
                    <div class="form-check col-md-6 me-3 mt-3">
                        <input class="form-check-input" type="checkbox" value="1" id="customCheck7" name="french_lan">
                        <label class="custom-control-label" for="customCheck7">اللغة الفرنسية</label>
                    </div>
                    <div class="form-check col-md-6 mt-3">
                        <input class="form-check-input" type="checkbox" value="1" id="customCheck8" name="other_lan">
                        <label class="custom-control-label" for="customCheck8">لغة أخرى</label>
                    </div>

                </div>

           </div>

        <!-- Social Section-->
          <div class="border rounded p-4 my-4 shadow">
                <h6 class="border-bottom pb-2 mb-3">معلومات وسائل التواصل</h6>
                    <div class="d-flex">
                        <div class="input-group input-group-outline m-3">
                            <?php if (empty($fbLink)): ?>
                            <label for="fbLink" class="form-label">رابط الفيسبوك</label>
                            <?php endif; ?>
                            <input type="text" class="form-control" id="fbLink" name="fbLink" value="<?php echo $fbLink; ?>"
                            <?php if (!empty($fbLink)) echo 'placeholder="رابط الفيسبوك"'; ?> />
                        </div>
                        <div class="input-group input-group-outline my-3">
                            <?php if (empty($instaLink)): ?>
                            <label for="instaLink" class="form-label">رابط الإنستغرام</label>
                        <?php endif; ?>
                        <input type="text" class="form-control" id="instaLink" name="instaLink" value="<?php echo $instaLink; ?>"
                            <?php if (!empty($instaLink)) echo 'placeholder="رابط الإنستغرام"'; ?> />
                        </div>
                    </div>

                    <div class="d-flex">
                        <div class="input-group input-group-outline m-3">
                            <?php if (empty($mapAddress)): ?>
                            <label for="mapAddress" class="form-label">رابط خرائط قوقل</label>
                        <?php endif; ?>
                        <input type="text" class="form-control" id="mapAddress" name="mapAddress" value="<?php echo $mapAddress; ?>"
                            <?php if (!empty($mapAddress)) echo 'placeholder="رابط خرائط قوقل"'; ?> />
                        </div>
                        <div class="input-group input-group-outline my-3">
                            <?php if (empty($tiktokLink)): ?>
                            <label for="tiktokLink" class="form-label">رابط التيكتوك</label>
                            <?php endif; ?>
                            <input type="text" class="form-control" id="tiktokLink" name="tiktokLink" value="<?php echo $tiktokLink; ?>"
                            <?php if (!empty($tiktokLink)) echo 'placeholder="رابط التيكتوك"'; ?> />
                        </div>
                    </div> 

              </div>
            <!-- note Section-->
            <div class="border rounded p-4 shadow">
               <h6 class="border-bottom pb-2 mb-3">الملاحظات</h6>
                <div class="input-group input-group-outline m-3 ps-3">
                    <label for="notes" class="form-label">ملاحظات</label>
                    <textarea class="form-control" id="notes" name="notes" rows="4"><?php echo $notes; ?></textarea>
                </div>

            </div>   
            <div class="form-group mt-3">
                <button type="submit" name="but_submit" class="btn bg-gradient-primary" >إضـافة</button>
                </div> 
                <?php if (!empty($register_err)) { ?>
                <div class="alert alert-danger mt-3" role="alert">
                  <?php echo $register_err; ?>
                </div>
              <?php } ?>
        </form>
<?php
include('footer.php');
?>