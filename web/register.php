<?
include("init.php");

if ($logged_in) {
  header("Location: ".(isset($_GET["ref"]) ? $_GET["ref"] : "index.php"));
  die();
}

$allowed_chars = "abcdefghijklmnopqrstuvwxyz01234567890-_=+)(@!.,:;[]{} ";

if (isset($_POST["register-username"])) {

  $username = substr(mysqli_real_escape_string($mysqli, $_POST["register-username"]), 0, 64);
  $splitted = str_split($username);
  $valid_username = true;
  foreach ($splitted as $char) {
    if (!(strpos($allowed_chars, strtolower($char)) !== false)) {
      $valid_username = false; // teken gevonden die niet toegestaan is
      break;
    }
  }

  $password = substr(mysqli_real_escape_string($mysqli, $_POST["register-password"]), 0, 64);
  $password_repeat = mysqli_real_escape_string($mysqli, $_POST["register-password-repeat"]);

  if ($valid_username && !empty($password) && $password == $password_repeat) {
    // controleren of username al in gebruik is
    $sql = "SELECT name FROM users WHERE name = '".$username."'";
    $result = $mysqli->query($sql);
    $count = mysqli_num_rows($result);

    if ($count == 0) { // username bestaat nog niet
      // toevoegen aan database:
      $sql = "INSERT INTO users (name, password) VALUES ('$username', '$password')";
      $result = $mysqli->query($sql);

      if (!empty($_POST["register-img"]) && !empty($_POST["register-img-small"])) {
        // profielfoto opslaan:
        // grote versie:
        $username_lower = strtolower($username);
        file_put_contents("profilepics/".$username_lower.".png", base64_decode(explode(",", $_POST["register-img"])[1]));
        // kleine versie:
        file_put_contents("profilepics/small/".$username_lower.".png", base64_decode(explode(",", $_POST["register-img-small"])[1]));
      }

      header("Location: login.php?ref=".(isset($_GET["ref"]) ? $_GET["ref"] : "index.php")); // Registratie voltooid -> inlogpagina
      die();
    }
  }
}

$title = "Registreren";
$orange_main = true;
include("layout/header.php");
include("jsallusernames.php");
?>

<script type="text/javascript">

  window.allowedChars = "<? print($allowed_chars); ?>";

  $(document).ready(function() {
    $("#username").on("click focus change paste keyup blur input", function() { // Check of naam beschrikbaar is
      var input = $(this);
      var value = input.val();
      var newValue = value;
      for (i = 0; i < value.length; i++) { // checken op verboden tekens (dit wordt ook nog een keer in PHP gedaan want dit kan omzijlt worden)
        var char = value.charAt(i);
        if (window.allowedChars.indexOf(char.toLowerCase()) == -1) {
          Materialize.toast(char + " is niet toegestaan.", 1500);
          newValue = newValue.slice(0, i) + newValue.slice(i + 1); // teken er tussenuit halen
        }
      }
      if (newValue != value) input.val(newValue);

      if ($.inArray(newValue.toLowerCase(), window.allUsernames) > -1) input.addClass("invalid"); // Naam al in gebruik -> foutmelding
      else input.removeClass("invalid"); // Naam is beschrikbaar

      if (newValue.length > 0) window.onbeforeunload = function() { return "Wilt u dit registratieproces afbreken?"; };
    }).click();

    $("#password-repeat").on("click focus change paste keyup blur input", function() { // Check of wachtwoorden overeen komen
      var pas1 = $("#password").val();
      var input = $(this);
      var pas2 = input.val();
      if (pas1 != pas2) input.addClass("invalid");
      else input.removeClass("invalid");
    }).click();

    $("#accept").change(function() {
      if (this.checked) $("#submit").removeClass("disabled");
      else $("#submit").addClass("disabled");
    });
  });

  function readURL(input) { // Afbeelding gekozen -> laden
    if (input.files && input.files[0]) {
      var reader = new FileReader();
      reader.onload = function (e) {
        $("#croppie-frame").get(0).contentWindow.c.croppie("bind", {
          url: e.target.result,
          orientation: 1
        });
      }
      reader.readAsDataURL(input.files[0]);
    }
  }

  function rotateCroppie(left) {
    $("#croppie-frame").get(0).contentWindow.c.croppie("rotate", left ? -90 : 90);
  }

  function submit() { // Alles checken -> Bijgesneden afbeelding in 2 formaten (klein en groot) krijgen -> toevoegen aan form als base64 -> form submitten

    if ($("#username").val().length == 0 || $("#password").val().length == 0 || $("#password-repeat").val().length == 0) {
      Materialize.toast('Vul alles in a.u.b.', 6000);
      return;
    }

    window.onbeforeunload = null;
    window.croppieFinished = 0;

    var c = $("#croppie-frame").get(0).contentWindow.c;

    c.croppie("result", { // groot
      type: "base64",
      size: {
        width: 180,
        height: 180
      },
      circle: false
    }).then(function(imgData) {
      var form = $("#register");
      form.append($("<input type='hidden' name='register-img' value='" + imgData + "'>"));
      ++window.croppieFinished;
      if (window.croppieFinished == 2) form.submit();
    });

    c.croppie("result", { // klein
      type: "base64",
      size: {
        width: 48,
        height: 48
      },
      circle: true
    }).then(function(imgData) {
      var form = $("#register");
      form.append($("<input type='hidden' name='register-img-small' value='" + imgData + "'>"));
      ++window.croppieFinished;
      if (window.croppieFinished == 2) form.submit();
    });

  }

</script>

<div class="row">
  <div class="col s10 m8 l6 offset-l3 offset-m2 offset-s1 card">
    <div class="row">

      <div class="col s12 l10 offset-l1">
        <br><h5>
          Registreren
        </h5>
        <h6 class="red-text">
          Waarschuwing: vul absoluut geen wachtwoorden in die u bij andere sites gebruikt/gaat gebruiken.
        </h6>
        <form id="register" method="post" action="register.php<? print(isset($_GET["ref"]) ? "?ref=".$_GET["ref"] : ""); ?>">

          <div class="input-field">
            <i class="material-icons prefix">account_circle</i>
            <input value="<? print($username); ?>" id="username" type="text" name="register-username" class="validate" data-length="64" maxlength="64">
            <label for="username" data-error="Naam is al in gebruik.">Gebruikersnaam</label>
          </div>

          <div class="input-field">
            <i class="material-icons prefix">lock</i>
            <input value="<? print($password); ?>" id="password" type="password" name="register-password" class="validate" data-length="64" maxlength="64">
            <label for="password">Wachtwoord</label>
          </div>

          <div class="input-field">
            <i class="material-icons prefix">lock</i>
            <input value="<? print($password_repeat); ?>" id="password-repeat" type="password" name="register-password-repeat" class="validate" data-length="64" maxlength="64">
            <label for="password-repeat" data-error="Wachtwoord komt niet overeen">Herhaal wachtwoord</label>
          </div>

          <br>
          <h5>
            Profielfoto
          </h5>
          <p>
            Omdat wij van mening zijn dat onze gebruikers een beeld moeten kunnen vormen van elkaar bent u verplicht om een profielfoto te uploaden.
            <br>Of gebruik de voorbeeld-afbeelding als deze overeenkomt met uw karakter.
          </p>
          <div class="file-field input-field">
            <div class="btn">
              <span>Afbeelding uploaden</span>
              <input onchange="readURL(this)" type="file" accept="image/*"> <!-- Wordt niet verstuurd, wordt als input gebruikt voor bijsnijden gedoe -->
            </div>
            <div class="file-path-wrapper">
              <input class="file-path" type="text">
            </div>
          </div>
          <br>
          <h5 style="display: inline;">
            Bijsnijden
          </h5>
          <a onclick="rotateCroppie(false)" class="btn-floating btn-flat waves-effect tooltipped" data-position="bottom" data-delay="50" data-tooltip="Rechtsom roteren" style="float: right;">
            <i class="material-icons black-text">rotate_right</i>
          </a>
          <a onclick="rotateCroppie(true)" class="btn-floating btn-flat waves-effect tooltipped" data-position="bottom" data-delay="50" data-tooltip="Linksom roteren" style="float: right;">
            <i class="material-icons black-text">rotate_left</i>
          </a>

          <iframe id="croppie-frame" src="croppie.php"></iframe>

          <a href="#registermodal" class="btn waves-effect btn-flat" type="submit" name="login-submit" style="float: right;">
            Verder
            <i class="material-icons right">arrow_forward</i>
          </a>

          <div id="registermodal" class="modal">
            <div class="modal-content">
              <h4>Algemene voorwaarden</h4>
              <p>
                <b>Privacy</b><br>
                Wij vinden je privacy erg belangrijk. We hebben een gegevensbeleid opgesteld dat belangrijke bepalingen bevat over hoe je dollepret kunt gebruiken om items te delen met anderen en hoe we jouw inhoud en informatie verzamelen en kunnen gebruiken. We raden je aan het gegevensbeleid te lezen en op basis hiervan beslissingen te nemen.
                <br><br>
                <b>Inhoud en informatie delen</b><br>
                Wij zijn nu eigenaar van alle inhoud en informatie die je op Dollepret plaatst. Verder gelden de volgende bepalingen:<br>
                1. Voor inhoud waarvoor intellectuele-eigendomsrechten gelden, zoals foto's en video's (IE-inhoud), geef je ons specifiek de volgende toestemming met inachtneming van je privacy- en toepassingsinstellingen: je verleent ons een niet-exclusieve, overdraagbare, sublicentieerbare, royaltyvrije en wereldwijde licentie voor het gebruik van IE-inhoud die je op of in verband met Dollepret plaatst (IE-licentie). Deze IE-licentie eindigt zefls niet wanneer je jouw IE-inhoud of je account verwijdert.
                <br>
                2. Je kan je IE-inhoud niet verwijderen, dat komt omdat jouw plaatjes en video's nu van ons zijn. Het kijkvoer wordt zelfs opgeslagen in back-ups.
                <br>
                3. Indien er afbeeldingen of video's van jou op deze site verspreidt zijn, kan jij er niks tegen doen. Dit is namelijk onze site en de plaatjes zijn nu ook van ons.
                <br><br>
                <b>Eigen risico</b>
                <br>
                1. Als de door u geplaatste content op een onrechtvaardige manier is verkregen dan zijn de boetes voor u, het beeldmateriaal voor ons. Ongeacht of wij het beeldmateriaal ons zelf hebben toege&#235;igend.
                <br>
                2. Op deze site wordt verwacht dat jij je aan onze regels houdt, gebeurd dit niet dan misbruiken we je emailadres en andere gebruikersgegevens.
                <br>
                3. We stellen het altijd in meer of mindere mate op prijs als je feedback of andere suggesties over Dollepret geeft, maar houd er wel rekening mee dat we deze informatie kunnen gebruiken zonder enige verplichting om je hiervoor te belonen (net zoals jij geen verplichting hebt om deze aan te bieden) stel dat we er naar luisteren.
              </p>
              <img src="https://www.afas.nl/menus/203DCBAA480899C5145DD0A92960B629/algemene-voorwaarden-algemene%20voorwaarden.jpg" style="width: 100%">
              <p>
                <input type="checkbox" id="accept">
                <label for="accept">Ik ga akkoord met de algemene voorwaarden</label>
              </p>
              <a id="submit" href="#" onclick="submit()" class="btn waves-effect disabled">
                Cre&#235;er account
              </a>
              <a href="#" class="btn btn-flat waves-effect modal-close">
                annuleren
              </a>
            </div>
          </div>

        </form>
      </div>

    </div>
  </div>
</div>


</main>
</body>
</html>
