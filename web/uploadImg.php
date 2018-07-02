<?
include("init.php");
if (!$logged_in) {
  header("Location: login.php?ref=uploadImg.php");
  die();
}

if (isset($_POST["img-title"]) && isset($_POST["img-description"]) && file_exists($_FILES['img-file']['tmp_name'])) {
  
  $img_title = substr(mysqli_real_escape_string($mysqli, strip_tags($_POST["img-title"])), 0, 64);
  $img_description = substr(mysqli_real_escape_string($mysqli, strip_tags($_POST["img-description"])), 0, 1024);
  
  $file_array = $_FILES["img-file"];
  $type = explode('/', $file_array['type'])[1];
  if($type == 'jpeg') $type = 'jpg';

  if (in_array($type, array('png', 'gif', 'jpg', 'bmp'))) {
    
    $date = new DateTime();
    $sql = "INSERT INTO uploads (user, title, upload_time, description, category, duration, views) VALUES ('".$_SESSION["dp_username"]."', '$img_title', ".$date->getTimestamp().", '$img_description', ".intval($_POST["category"]).", 0, 0)";
    $result = $mysqli->query($sql);
    $id = $mysqli->insert_id; // id van upload-item
    
    $temp_path = $file_array["tmp_name"];
    mkdir("uploads/$id");
    $new_path = "uploads/$id/item";
    
    $rotate = intval($_POST["img-rotate"]);
    if (!in_array($rotate, array(0, 90, -90, 180))) $rotate = 0;
    
    // Kleine thumbnail maken (en eventueel draaien):
    resize_and_rotate_img(400*200, $temp_path, $type, "uploads/$id/thumbnail", $rotate * -1);
    
    // Afbeelding verkleinen als deze te groot is (en eventueel draaien):
    if ($type != "gif") resize_and_rotate_img(1280*1280, $temp_path, $type, $new_path, $rotate * -1);
    else {
      // Gif's worden niet gecomprimeerd, want anders beweegt ie niet meer.
      move_uploaded_file($temp_path, $new_path.".gif");
    }
    
    header("Location: view.php?id=$id");
    die();
    
  } else {
    $error_message = "Bestandstype <b>'$type'</b> wordt niet herkend als afbeelding.";
  }
  
}

$title = "Afbeelding uploaden";
$orange_main = true;
include("layout/header.php");
?>

<script type="text/javascript">
  
  $(document).ready(function() {
    $("#img-title").on("click focus change paste keyup blur input", check);
    $("#img-description").on("click focus change paste keyup blur input", check);
    <? if (isset($error_message)) print("$('#upload-error').modal('open');"); ?>
  });
  
  window.imageSet = false;
  
  function readURL(input) {
    if (input.files && input.files[0]) {
      var reader = new FileReader();

      loader();
      reader.onload = function (e) {
        $('#preview').attr('src', e.target.result);
        stopLoader();
      }

      reader.readAsDataURL(input.files[0]);
      window.imageSet = true;
      rotate(0, true);
      check();
      setTimeout(function () {
        var gif = $("#file-path").val().endsWith(".gif")
        $("#rotate-controls").css("opacity", gif ? .4 : 1).css("pointer-events", gif ? "none" : "all");
      }, 10);
    }
  }
  
  function rotate(deg, reset) {
    var input = $("#img-rotate");
    var totalDeg = reset ? deg : parseInt(input.val()) + deg;
    if (totalDeg == -180) totalDeg = 180;
    if (totalDeg == 270) totalDeg = -90;
    input.val(totalDeg);
    $("#preview").css("transform", "rotate(" + totalDeg + "deg)");
    previewMaxSize();
  }
  
  $(window).resize(previewMaxSize);
  
  function previewMaxSize() {
    var rotation = parseInt($("#img-rotate").val());
    var x = rotation == -90 || rotation == 90;
    $("#preview").css(
      "max-width", x ? "210px" : "100%"
    ).css(
      "max-height", x ? $("#preview").parent().width() : "210px"
    ).css(
      "margin-top", x ? (210 - $("#preview").height()) / 2 + "px" : "0px"
    );
  }
  
  function check() {
    if (window.imageSet && $("#img-title").val().length > 0 && $("#img-description").val().length > 0) $("#submit-img").removeClass("disabled");
    else $("#submit-img").addClass("disabled");
  }
  
  function loader() {
    $("#img-upload").css("opacity", ".3").css("pointer-events", "none");
    $("#upload-loader").css("display", "block");
  }
  
  function stopLoader() {
    $("#img-upload").css("opacity", "1").css("pointer-events", "all");
    $("#upload-loader").css("display", "none");
  }
  
</script>

<div id="upload-error" class="modal">
  <div class="modal-content">
    <h4>Fout bij uploaden</h4>
    <p><? print($error_message); ?></p>
  </div>
  <div class="modal-footer">
    <a class="modal-action modal-close waves-effect btn-flat">ok</a>
  </div>
</div>

<div class="row">
  <div class="col s10 m8 l6 offset-l3 offset-m2 offset-s1 card">
    <div class="row">
      <div class="col s12 l10 offset-l1">
        
        <div id="upload-loader" class="preloader-wrapper active" style="
            position: fixed;
            top: 50%;
            z-index: 5;
            left: calc(50% - 24px);
            display: none; 
          ">
          <div class="spinner-layer">
            <div class="circle-clipper left">
              <div class="circle"></div>
            </div><div class="gap-patch">
              <div class="circle"></div>
            </div><div class="circle-clipper right">
              <div class="circle"></div>
            </div>
          </div>
        </div>
        
        <br>
        <h5>
          Upload een afbeelding.
        </h5>
        <p>
          - <b>GIF</b>'s zijn ook van harte welkom.
          <br>- Ongepaste content wordt onmiddelijk door onze administrators verwijderd.
          <br>- Max. bestandsgrootte: <b>128MB</b>
          <br>- Grote afbeeldingen worden gecomprimeerd.
        </p>
        
        <form id="img-upload" enctype="multipart/form-data" method="post" action="uploadImg.php">
          <div class="row">
            <div class="col s12 m12 l6">
              
              <div style="width: 100%; height: 210px;"><center><img id="preview"></center></div>
              
            </div>
            <div class="col m12 l6">
              <p>
                Kies een bestand
              </p>
              <div class="file-field input-field">
                <div class="btn">
                  <span><i class="material-icons">file_upload</i></span>
                  <input name="img-file" onchange="readURL(this)" type="file" accept="image/*">
                </div>
                <div class="file-path-wrapper">
                  <input id="file-path" class="file-path" type="text">
                </div>
              </div>
              
              <div id="rotate-controls">
                <p>
                  Eventueel draaien
                </p>
                <a class="btn-floating btn-flat waves-effect" onclick="rotate(-90, false)">
                  <i class="material-icons black-text">rotate_left</i>
                </a>
                <a class="btn-floating btn-flat waves-effect" onclick="rotate(90, false)">
                  <i class="material-icons black-text">rotate_right</i>
                </a>
              </div>
            
            </div>
            
            <div class="col s12">
              <div class="input-field">
                <i class="material-icons prefix">title</i>
                <input id="img-title" type="text" name="img-title" data-length="64" maxlength="64">
                <label for="img-title">Titel</label>
              </div>
              
              <div class="input-field">
                <i class="material-icons prefix">mode_edit</i>
                <textarea form="img-upload" id="img-description" name="img-description" class="materialize-textarea" data-length="1024" maxlength="1024"></textarea>
                <label for="img-desctiption" onclick="$('#img-description').focus()">Descriptie</label>
              </div>
            </div>
          </div>
          
          <div class="input-field col s12">
            <select name="category">
              <option value="0">Puur vermaak</option>
              <option value="4">Meesterlijke kunst</option>
              <option value="3" disabled>Porno (niet toegestaan)</option>
              <option value="3">Vies plaatje (wel toegestaan)</option>
            </select>
            <label>Categorie</label>
          </div>
        
          <button id="submit-img" onclick="loader()" type="submit" class="btn disabled waves-effect">
            Uploaden
          </button>
          <a class="btn waves-effect btn-flat" onclick="window.history.back()">
            annuleren
          </a>
          
          <input value="0" id="img-rotate" name="img-rotate" type="hidden">
          
        </form>

      </div>
    </div>
  </div>
</div>

<?
include("layout/footer.php");
?>