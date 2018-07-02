<?
include("init.php");
if (!$logged_in) {
  header("Location: index.php");
  die();
}
if (isset($_POST["pf-img"]) && isset($_POST["pf-img-small"])) {
  // profielfoto opslaan:
  // grote versie:
  $username_lower = strtolower($_SESSION["dp_username"]);
  file_put_contents("profilepics/".$username_lower.".png", base64_decode(explode(",", $_POST["pf-img"])[1]));
  // kleine versie:
  file_put_contents("profilepics/small/".$username_lower.".png", base64_decode(explode(",", $_POST["pf-img-small"])[1]));
  header("Location: index.php");
  die();
}

$title = "Profielfoto wijzigen";
$orange_main = true;
include("layout/header.php");
?>

<script type="text/javascript">
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
  
  function submit() {
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
      var form = $("#changepf");
      form.append($("<input type='hidden' name='pf-img' value='" + imgData + "'>"));
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
      var form = $("#changepf");
      form.append($("<input type='hidden' name='pf-img-small' value='" + imgData + "'>"));
      ++window.croppieFinished;
      if (window.croppieFinished == 2) form.submit();
    });
  }
</script>

<div class="row">
  <div class="col s10 m8 l6 offset-l3 offset-m2 offset-s1 card">
    <div class="row">
      
      <div class="col s12 l10 offset-l1">
        <br><br>
        <div class="row">
          <div class="col s12 m12 l6">
            <center><img src="<? profile_pic(false, $_SESSION["dp_username"]); ?>" class="circle"><br>Uw huidige profielfoto<br><br></center>
          </div>
          <div class="col m12 l6">
            <h5>
              Wijzig uw profielfoto.
            </h5>
            <p>
              Bent u drastisch van uiterlijk veranderd?<br>Voel u vrij om uw profielfoto hier te wijzigen.
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
          </div>
        </div>
        
        <h5 style="display: inline;">
          Bijsnijden
        </h5>
        <a onclick="rotateCroppie(false)" class="btn-floating btn-flat waves-effect tooltipped" data-position="bottom" data-delay="50" data-tooltip="Rechtsom roteren" style="float: right;">
          <i class="material-icons black-text">rotate_right</i>
        </a>
        <a onclick="rotateCroppie(true)" class="btn-floating btn-flat waves-effect tooltipped" data-position="bottom" data-delay="50" data-tooltip="Linksom roteren" style="float: right;">
          <i class="material-icons black-text">rotate_left</i>
        </a>

        <iframe id="croppie-frame" src="croppie.php">
          
        </iframe>
        
        <form id="changepf" method="post" action="changepf.php">
          <a class="btn waves-effect" onclick="submit()">
            <i class="material-icons left">done</i>opslaan
          </a>
          <a class="btn waves-effect btn-flat" onclick="window.history.back()">
            annuleren
          </a>
        </form>
      
      </div>
      
    </div>
  </div>
</div>

<?
include("layout/footer.php");
?>