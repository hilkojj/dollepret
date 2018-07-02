<?
include("init.php");

if (!$logged_in && isset($_POST["login-submit"])) {
  $username = mysqli_real_escape_string($mysqli, $_POST["login-username"]);
  $password = mysqli_real_escape_string($mysqli, $_POST["login-password"]);
  
  $username_not_set = empty($username);
  $password_not_set = empty($password);
  
  if (!$username_not_set && !$password_not_set) {
    
    $invalid_username = true;
    $invalid_password = true;
    
    $sql = "SELECT * FROM users WHERE name = '".$username."'";
    $result = $mysqli->query($sql);
    $count = mysqli_num_rows($result);
    
    if ($count == 1) {
      $invalid_username = false;
      
      $row = $result->fetch_assoc();
      if ($row["password"] == $password) {
        $logged_in = true;
        session_destroy();
        session_start();
        $_SESSION["dp_username"] = $row["name"];
        $_SESSION["dp_admin"] = $row["is_admin"] == 1;
      }
    }
  }
}

if ($logged_in) {
  if ($_GET["ref"] == "logout.php") $_GET["ref"] = "index.php";
  header("Location: ".(isset($_GET["ref"]) ? $_GET["ref"] : "index.php"));
  die();
}

$title = "Aanmelden";
$orange_main = true;
$red_theme_color = $username_not_set || $password_not_set || $invalid_username || $invalid_password; // URL balk wordt rood op chrome-android als iets verkeers is ingevuld
include("layout/header.php");
$profile_pic_paths = true;
include("jsallusernames.php");
?>
<script type="text/javascript">
  
  $(document).ready(function() {
    $("#username").on("click focus change paste keyup blur input", function() {
      var path = window.allProfilePicPaths[$(this).val().toLowerCase()];
      if (path === undefined) $("#ppic").fadeOut(200);
      else $("#ppic").attr("src", path).fadeIn(200);
    }).click();
  });
  
</script>

<div class="row">
  <div class="col s10 m6 l4 offset-l4 offset-m3 offset-s1 card">
    <div class="row">
      
      <div class="col s12 l10 offset-l1">
        <br><center><div id="ppic-parent" style="width: 10rem; height: 10rem; background-image: url('data/250.png'); background-size: contain;"><img id="ppic" class="circle" style="width: 10rem; height: 10rem; display: none;"></div></center>
        <form method="post" action="login.php<? print(isset($_GET["ref"]) ? "?ref=".$_GET["ref"] : ""); ?>">
          <div class="input-field">
            <i class="material-icons prefix">account_circle</i>
            <input value="<? print($username); ?>" id="username" type="text" name="login-username" class="validate <? if ($username_not_set || $invalid_username) print("invalid"); ?>">
            <label for="username" data-error="<? print($username_not_set ? "Vul uw gebruikersnaam in a.u.b." : "Gebruikersnaam is onjuist"); ?>">Gebruikersnaam</label>
          </div>
          <div class="input-field">
            <i class="material-icons prefix">lock</i>
            <input value="<? print($password); ?>" id="password" type="password" name="login-password" class="validate <? if ($password_not_set || $invalid_password) print("invalid"); ?>">
            <label for="password" data-error="<? print($password_not_set ? "Vul uw wachtwoord in a.u.b." : "Wachtwoord is onjuist"); ?>">Wachtwoord</label>
          </div>
          <br>
          <button class="btn waves-effect btn-flat" type="submit" name="login-submit" style="float: right;">
            Log in
            <i class="material-icons right">arrow_forward</i>
          </button>
        </form>
      </div>
              
      <div class="col s12 l10 offset-l1">
        <h6>
          Beschrikt u nog niet over een account?
        </h6>
        <a href="register.php<? print(isset($_GET["ref"]) ? "?ref=".$_GET["ref"] : ""); ?>">Registreer gratis voor &#8364;0,-</a>
      </div>
      
    </div>
  </div>
</div>

<? include("layout/footer.php"); ?>