<?php

// Favourite colour of the user
$favourite = "";
$decoded_file = "";

// Encrypted file with user credentials
$password_file = "password.txt";

if (file_exists($password_file) && is_readable($password_file)) {
  # Define variables and initialize with empty values
  $user_email_err = $user_password_err = $login_err = "";
  $user_email = $user_password = "";

  // Check if the login form has been submitted
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    if (empty(trim($_POST["user_email"]))) {
      $user_email_err = "Please enter your Email Address.";
    } else {
      $user_email = trim($_POST["user_email"]);
    }

    if (empty(trim($_POST["user_password"]))) {
      $user_password_err = "Please enter your password.";
    } else {
      $password = trim($_POST["user_password"]);
    }

    // Define the encryption key array
    $key = array(5, -14, 31, -9, 3);

    // bring the email and password into desired format
    $credentials = $user_email . "*" . $password;
    try {
      if ($decoded_file == "") {
        // read the contents of the file
        $file_contents = file_get_contents($password_file);

        // split the contents into lines
        $lines = explode("\n", $file_contents);

        // initialize the decoded file contents
        $decoded_file = "";

        // loop through each line and decode it
        foreach ($lines as $line) {
          // if the line is empty, skip it
          if (empty($line)) {
            continue;
          }

          // loop through each character in the line
          for ($i = 0; $i < strlen($line); $i++) {
            // get the ASCII code of the current character
            $char_code = ord($line[$i]);

            // subtract the corresponding key value from the ASCII code
            $key_value = $key[$i % count($key)];
            $decoded_char_code = ($char_code - $key_value + 256) % 256;

            // convert the decoded ASCII code to a character and add it to the decoded contents
            $decoded_char = chr($decoded_char_code);
            $decoded_file .= $decoded_char;
          }

          // add a newline character to the decoded contents
          $decoded_file .= "\n";
        }
      }

      if (strstr($decoded_file, $credentials)) {
        // Loop through the lines in the file
        $checker = true;
        // Connect to the database
        $servername = "sql211.epizy.com";
        $username = "epiz_33648223";
        $password = "FqM6VMjoMA5QaPZ";
        $dbname = "epiz_33648223_adatok";
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check the connection
        if ($conn->connect_error) {
          die("Connection failed: " . $conn->connect_error);
        }

        // Prepare and execute the query
        $stmt = $conn->prepare("SELECT Titkos FROM tabla WHERE Username = ?");
        $stmt->bind_param("s", $user_email);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if a row was returned
        if ($result->num_rows > 0) {
          // Get the favourite column value
          $row = $result->fetch_assoc();
          $favourite = $row["Titkos"];

          // Display the favourite column value
          switch ($favourite) {
            case "feher":
              $favourite = "white";
              break;
            case "piros":
              $favourite = "red";
              break;
            case "kek":
              $favourite = "blue";
              break;
            case "zold":
              $favourite = "green";
              break;
            case "sarga":
              $favourite = "yellow";
              break;
            case "fekete":
              $favourite = "black";
              break;
            default:
              // Display an error message
              $login_err = "No favourite colour found. Have some gray instead.";
              $favourite = "gray";
          }
        } else {
          // Display an error message
          echo "No favourite found.";
        }

        // Close the database connection
        $conn->close();
      }

      if (!$checker) {
        // Display an error message
        $login_err = "Invalid email or password.";
      }
    } catch (Exception $e) {
      echo $e->getMessage();
    }
  }
} else {
  echo 'Error: Password file not found or not readable';
}

?>
<!DOCTYPE html>
<html lang="en">
<html>

<head>
  <meta charset="UTF-8">
  <title>User login system</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
  <title>Login</title>

  <style>
    body {
      background-color: <?php echo $favourite ?>;
    }

    #container {
      background-color: #000;
      color: #fff;
      font-family: Arial, Helvetica, sans-serif;
      font-size: 12px;
      width: 30rem;
      margin-top: 20rem;
      margin-left: 45rem;
      align-items: center;
      justify-content: center;
      border: 0.2rem solid whitesmoke;
      border-radius: 3rem;
      padding: 3rem;
    }

    .email_field {
      margin-bottom: 1rem;
    }

    .password_field {
      margin-bottom: 3rem;
    }
  </style>
  <script>
    // Toggle password visibility
    function togglePassword() {
      var passwordField = document.getElementById("password");
      var checkbox = document.getElementById("togglePassword");
      if (checkbox.checked) {
        passwordField.type = "text";
      } else {
        passwordField.type = "password";
      }
    }
    var checkbox = document.getElementById("togglePassword");
    checkbox.addEventListener('change', togglePassword);
  </script>
</head>

<body>
  <div id="container">
    <h1>Log In</h1>
    <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" novalidate>
      <div class="email_field">
        <label for="user_email" class="form-label">Email</label>
        <input type="text" class="form-control" name="user_email" id="user_email" value="<?= $user_login; ?>" placeholder="example@provider.com">
        <small class="text-danger">
          <?= $user_email_err; ?>
        </small>
      </div>
      <div class="password_field">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" name="user_password" id="password" placeholder="random password">
        <small class="text-danger">
          <?= $user_password_err; ?>
        </small>
      </div>
      <div class="login_button">
        <input type="submit" class="btn btn-primary form-control" name="submit" value="Log In">
      </div>
      <?php
      if (!empty($login_err)) {
        echo "<div class='alert alert-danger'>" . $login_err . "</div>";
      } ?>
    </form>
  </div>
</body>

</html>