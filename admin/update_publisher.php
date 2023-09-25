<?php
session_start();
ob_start(); // Start output buffering
include('secure.php');
include('header.php');
include('../connect.php');
// Initialize variables
$publisher_name = $publisher_type_id = $address = $phone = $second_phone = $email = $fbLink = $instaLink = $mapAddress = $tiktokLink = $websiteLink = $notes = "";
$publisher_name_err = $phone_err = $second_phone_err = $email_err = $register_err = "";

$sql_countries = "SELECT id, arabic_countries FROM countries";
$result_countries = mysqli_query($conn, $sql_countries);
$publisherCountries = mysqli_fetch_all($result_countries, MYSQLI_ASSOC);

// Fetch library percentages from the database
$sql_publisher_type = "SELECT id, publisher_type FROM publisher_types";
$result_publisher_types = mysqli_query($conn, $sql_publisher_type);
$publisherTypes = mysqli_fetch_all($result_publisher_types, MYSQLI_ASSOC);

?>
<div class="container-fluid py-4">
    <?php
    if (isset($_SESSION['create_update_success']) && $_SESSION['create_update_success'] === true) {
        // Unset the session variable to avoid displaying the message on page refresh
        unset($_SESSION['create_update_success']);
        // Redirect to the display_publishers page with a success message
        header("Location: display_publishers.php?create_update_success=1");
        exit;
    }

    if (isset($_SESSION['item_not_found']) && $_SESSION['item_not_found'] === true) {
        // Unset the session variable to avoid displaying the message on page refresh
        unset($_SESSION['item_not_found']);
        // Redirect to the display_publishers page with a success message
        header("Location: display_publishers.php?item_not_found=1");
        exit;
    }

    // Database connection configuration
    include('../connect.php');

    $id = isset($_GET['id']) ? $_GET['id'] : '';

    if (!empty($id)) {
        $stmt = mysqli_prepare($conn, "SELECT * FROM publishers WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $item = mysqli_fetch_assoc($result);

            $publisher_name = htmlspecialchars($item["publisher_name"]);          
            $address = htmlspecialchars($item["address"]);
            $phone = htmlspecialchars($item["phone"]);
            $second_phone = htmlspecialchars($item["second_phone"]);
            $email = htmlspecialchars($item["email"]);
            $notes = htmlspecialchars($item["notes"]);
          
            $fbLink = htmlspecialchars($item["fbLink"]);
            $instaLink = htmlspecialchars($item["instaLink"]);
            $mapAddress = htmlspecialchars($item["mapAddress"]);
            $tiktokLink = htmlspecialchars($item["tiktokLink"]);
            $websiteLink = htmlspecialchars($item["websiteLink"]);

            $publisher_type_id = htmlspecialchars($item["publisher_type_id"]);
            $country_id =  htmlspecialchars($item["country_id"]);

            $semi_school =  htmlspecialchars($item["semi_school"]);
            $university =  htmlspecialchars($item["university"]);
            $children =  htmlspecialchars($item["children"]);
            $novel =  htmlspecialchars($item["novel"]);
            $arabic_lan =  htmlspecialchars($item["arabic_lan"]);
            $english_lan =  htmlspecialchars($item["english_lan"]);
            $french_lan =  htmlspecialchars($item["french_lan"]);
            $other_lan =  htmlspecialchars($item["other_lan"]);
        } else {
            $_SESSION['item_not_found'] = true;
            // Close the statement result
            mysqli_stmt_close($stmt);
            // Redirect to the display_publishers page after item not found
            header("Location: display_publishers.php");
            exit;
        }

        // Close the statement result
        mysqli_stmt_close($stmt);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['updateData'])) {
           // Get the form data
           $publisher_name = trim($_POST["publisher_name"]);
           $address = trim($_POST["address"]);
           $phone = trim($_POST["phone"]);
           $second_phone = trim($_POST["second_phone"]);
           $email = trim($_POST["email"]);
           $notes = trim($_POST["notes"]);
         
           $fbLink = trim($_POST["fbLink"]);
           $instaLink = trim($_POST["instaLink"]);
           $mapAddress = trim($_POST["mapAddress"]);
           $tiktokLink = trim($_POST["tiktokLink"]);
           $websiteLink = trim($_POST["websiteLink"]);

           $publisher_type_id = trim($_POST["publisher_type_id"]);
           $country_id = trim($_POST["country_id"]);

           $semi_school = isset($_POST["semi_school"]) ? 1 : 0;
           $university = isset($_POST["university"]) ? 1 : 0;
           $children = isset($_POST["children"]) ? 1 : 0;
           $novel = isset($_POST["novel"]) ? 1 : 0;
           $arabic_lan = isset($_POST["arabic_lan"]) ? 1 : 0;
           $english_lan = isset($_POST["english_lan"]) ? 1 : 0;
           $french_lan = isset($_POST["french_lan"]) ? 1 : 0;
           $other_lan = isset($_POST["other_lan"]) ? 1 : 0;
           
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
                $existingPhoneQuery = "SELECT id, publisher_name FROM publishers WHERE (phone = ? OR second_phone = ?) AND id != ?";
                $stmt_existingPhone = mysqli_prepare($conn, $existingPhoneQuery);
                mysqli_stmt_bind_param($stmt_existingPhone, "ssi", $phone, $phone, $id);
                mysqli_stmt_execute($stmt_existingPhone);
                mysqli_stmt_store_result($stmt_existingPhone);
                if (mysqli_stmt_num_rows($stmt_existingPhone) > 0) {
                    mysqli_stmt_bind_result($stmt_existingPhone, $existingAuthorId, $existingPublisherName);
                    mysqli_stmt_fetch($stmt_existingPhone);
                    $phone_err = "رقم الهاتف مستخدم بالفعل مع دار نشر: $existingPublisherName (معرف دار النشر: $existingAuthorId)";
                }
                mysqli_stmt_close($stmt_existingPhone);
            }
            
            // Validate secondary phone
            if (!empty($second_phone) && !preg_match($phonePattern, $second_phone)) {
                $second_phone_err = "رقم هاتف ثانوي غير صالح.";
            } else {
                // Check if secondary phone number already exists in the database (in phone or second_phone column)
                if (!empty($second_phone)) {
                    $existingSecondPhoneQuery = "SELECT id, publisher_name FROM publishers WHERE (phone = ? OR second_phone = ?) AND id != ?";
                    $stmt_existingSecondPhone = mysqli_prepare($conn, $existingSecondPhoneQuery);
                    mysqli_stmt_bind_param($stmt_existingSecondPhone, "ssi", $second_phone, $second_phone, $id);
                    mysqli_stmt_execute($stmt_existingSecondPhone);
                    mysqli_stmt_store_result($stmt_existingSecondPhone);
                    if (mysqli_stmt_num_rows($stmt_existingSecondPhone) > 0) {
                        mysqli_stmt_bind_result($stmt_existingSecondPhone, $existingAuthorId, $existingPublisherName);
                        mysqli_stmt_fetch($stmt_existingSecondPhone);
                        $second_phone_err = "رقم الهاتف الثانوي مستخدم بالفعل مع دار نشر: $existingPublisherName (معرف دار النشر: $existingAuthorId)";
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

                // If no new file is uploaded, don't update userfile and filetype
                $sql_update_publisher = "UPDATE publishers SET publisher_name = ?, address = ?, phone = ?, second_phone = ?, email = ?, fbLink = ?, instaLink = ?, mapAddress = ?, tiktokLink = ?, websiteLink = ?, notes = ?, semi_school = ?, university = ?, children = ?, novel = ?, arabic_lan = ?, english_lan = ?, french_lan = ?, other_lan = ?, publisher_type_id = ?, country_id = ? WHERE id = ?";
                $stmt_update_publisher = mysqli_prepare($conn, $sql_update_publisher);
                mysqli_stmt_bind_param($stmt_update_publisher, "sssssssssssiiiiiiiiiii", $publisher_name, $address, $phone, $second_phone, $email, $fbLink, $instaLink, $mapAddress, $tiktokLink, $websiteLink, $notes, $semi_school, $university, $children, $novel, $arabic_lan, $english_lan, $french_lan, $other_lan, $publisher_type_id, $country_id, $id);
                mysqli_stmt_execute($stmt_update_publisher);
                 // Redirect to the display_publishers page after successful update
                 $_SESSION['create_update_success'] = true;
                 header("Location: display_publishers.php");
                 exit;
        }
        }
            }
    ?>
             <form role="form" action="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $id; ?>" method="post">
              <h4 class="mb-3">تحديث دار نشر</h4>
              <div class="border rounded p-4 shadow">
                 <h6 class="border-bottom pb-2 mb-3">تحديث معلومات دار النشر</h6>
                    <div class="row mb-3">
                        <div class="col-md-6 mt-4">
                            <div class="input-group input-group-outline mt-2">
                                    <select name="country_id" id="country_id" class="form-control" required>
                                        <option value="" disabled>-- اختر دولة دار النشر *  --</option>
                                        <?php
                                        foreach ($publisherCountries as $countries) {
                                            $selected = ($countries['id'] == $country_id) ? 'selected' : ''; // Check if this option is selected
                                            echo '<option value="' . $countries['id'] . '" ' . $selected . '>' . $countries['arabic_countries'] . '</option>';
                                        }
                                        ?>
                                    </select>
                            </div>
                        </div>
                        <div class="col-md-6 mt-4">
                            <div class="input-group input-group-outline mt-2">
                                     <select name="publisher_type_id" id="publisher_type_id" class="form-control" required>
                                        <option value="" disabled> -- اختر نوع دار النشر *  --</option>
                                        <?php
                                        foreach ($publisherTypes as $type) {
                                            $selected = ($type['id'] == $publisher_type_id) ? 'selected' : ''; // Check if this option is selected
                                            echo '<option value="' . $type['id'] . '" ' . $selected . '>' . $type['publisher_type'] . '</option>';
                                        }
                                        ?>
                                    </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label class="form-label">إسم دار النشر *  :</label>
                            <input type="text" name="publisher_name" class="form-control border pe-2 mb-3 <?php echo (!empty($publisher_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($publisher_name); ?>" required>
                            <span class="invalid-feedback"><?php echo $publisher_name_err; ?></span>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label">العنوان :</label>
                            <input type="text" name="address" class="form-control border pe-2 mb-3" value="<?php echo htmlspecialchars($address); ?>">
                        </div>
                    </div>
            </div>
                <div class="border rounded p-4 shadow mt-4">
                 <h6 class="border-bottom pb-2 mb-3">تحديث معلومات التواصل</h6>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label class="form-label">الهاتف *  :</label>
                        <input type="text" name="phone" class="form-control border pe-2 mb-3 <?php echo (!empty($phone_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($phone); ?>" required>
                        <span class="invalid-feedback"><?php echo $phone_err; ?></span>
                    </div>
                    <div class="form-group col-md-6">
                        <label class="form-label">الهاتف الثاني :</label>
                        <input type="text" name="second_phone" class="form-control border pe-2 mb-3 <?php echo (!empty($second_phone_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($second_phone); ?>">
                        <span class="invalid-feedback"><?php echo $second_phone_err; ?></span>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-6">
                                <label class="form-label">رابط موقع الويب :</label>
                                <input type="text" name="websiteLink" class="form-control border pe-2 mb-3" value="<?php echo htmlspecialchars($websiteLink); ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label class="form-label">الإيميل :</label>
                        <input type="email" name="email" class="form-control border pe-2 mb-3 <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($email); ?>">
                        <span class="invalid-feedback"><?php echo $email_err; ?></span>
                    </div>
                </div>
            </div>
                <!-- Update Types Section-->
                <div class="border rounded p-4 shadow mt-4">
                 <h6 class="border-bottom pb-2 mb-3">تحديث معلومات صنف الكتب</h6>
                    <div class="d-flex">
                            <div class="form-check col-md-6 me-3 mt-3">
                                <input class="form-check-input" type="checkbox" value="1" id="fcustomCheck1" name="semi_school" <?php if ($semi_school == '1') echo 'checked'; ?>>
                                <label class="custom-control-label" for="customCheck1">كتب شبه مدرسية</label>
                            </div>
        
                            <div class="form-check col-md-6 mt-3">
                                <input class="form-check-input" type="checkbox" value="1" id="fcustomCheck2" name="university" <?php if ($university == '1') echo 'checked'; ?>>
                                <label class="custom-control-label" for="customCheck2">كتب جامعية</label>
                            </div>
                    </div>

                    <div class="d-flex">
                            <div class="form-check col-md-6 me-3 mt-3">
                                <input class="form-check-input" type="checkbox" value="1" id="fcustomCheck3" name="children" <?php if ($children == '1') echo 'checked'; ?>>
                                <label class="custom-control-label" for="customCheck3">كتب أطفال</label>
                            </div>

                            <div class="form-check col-md-6 mt-3">
                                <input class="form-check-input" type="checkbox" value="1" id="fcustomCheck4" name="novel" <?php if ($novel == '1') echo 'checked'; ?>>
                                <label class="custom-control-label" for="customCheck4">روايات</label>
                            </div>
                    </div>
                </div>
                <!-- Update Language Section-->
                <div class="border rounded p-4 shadow mt-4">
                 <h6 class="border-bottom pb-2 mb-3">تحديث معلومات لغات الكتب</h6>
                    <div class="d-flex">
                                <div class="form-check col-md-6 me-3 mt-3">
                                    <input class="form-check-input" type="checkbox" value="1" id="fcustomCheck5" name="arabic_lan" <?php if ($arabic_lan == '1') echo 'checked'; ?>>
                                    <label class="custom-control-label" for="fcustomCheck5">اللغة العربية</label>
                                </div>
            
                                <div class="form-check col-md-6 mt-3">
                                    <input class="form-check-input" type="checkbox" value="1" id="fcustomCheck6" name="english_lan" <?php if ($english_lan == '1') echo 'checked'; ?>>
                                    <label class="custom-control-label" for="fcustomCheck6">اللغة الإنجليزية</label>
                                </div>
                        </div>

                        <div class="d-flex">
                                <div class="form-check col-md-6 me-3 mt-3">
                                    <input class="form-check-input" type="checkbox" value="1" id="fcustomCheck7" name="french_lan" <?php if ($french_lan == '1') echo 'checked'; ?>>
                                    <label class="custom-control-label" for="fcustomCheck7">اللغة الفرنسية</label>
                                </div>

                                <div class="form-check col-md-6 mt-3">
                                    <input class="form-check-input" type="checkbox" value="1" id="fcustomCheck8" name="other_lan" <?php if ($other_lan == '1') echo 'checked'; ?>>
                                    <label class="custom-control-label" for="fcustomCheck8">لغة أخرى</label>
                                </div>
                        </div>
                 </div>
                <!-- Update Social Section-->
                <div class="border rounded p-4 shadow mt-4">
                    <h6 class="border-bottom pb-2 mb-3">تحديث معلومات وسائل التواصل</h6>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label class="form-label">رابط الفيسبوك :</label>
                                <input type="text" name="fbLink" class="form-control border pe-2 mb-3" value="<?php echo htmlspecialchars($fbLink); ?>">
                            </div>
                            <div class="form-group col-md-6">
                                <label class="form-label">رابط الإنستغرام :</label>
                                <input type="text" name="instaLink" class="form-control border pe-2 mb-3" value="<?php echo htmlspecialchars($instaLink); ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label class="form-label">رابط خرائط قوقل :</label>
                                <input type="text" name="mapAddress" class="form-control border pe-2 mb-3" value="<?php echo htmlspecialchars($mapAddress); ?>">
                            </div>
                            <div class="form-group col-md-6">
                                <label class="form-label">رابط التيكتوك :</label>
                                <input type="text" name="tiktokLink" class="form-control border pe-2 mb-3" value="<?php echo htmlspecialchars($tiktokLink); ?>">
                            </div>
                        </div>
                </div>

                        <!-- Updete Notes Section-->
                    <div class="border rounded p-4 shadow mt-4">
                        <h6 class="border-bottom pb-2 mb-3">تحديث الملاحظات</h6>
                                <div class="row">
                                    <div class="form-group col-md-12 my-3">
                                        <label for="notes" class="form-label">تحديث الملاحظات:</label>                   
                                        <textarea class="form-control border pe-2 mb-3" id="notes" name="notes" rows="4"><?php echo htmlspecialchars($notes); ?></textarea>
                                    </div>
                                </div>
                    </div>

                           <div class="form-group mt-3">
                                <button type="submit" name="updateData" class="btn btn-primary">تحديث</button>
                            </div>
          </form>          
    <hr>
    <a href="display_publishers.php" class="btn btn-secondary">العودة إلى قائمة المؤلفين</a>
</div>
<?php
// Close the database connection
mysqli_close($conn);
include('footer.php');
ob_end_flush();
?>