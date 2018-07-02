<?
include("init.php");
if (!$logged_in) {
  header("Location: login.php?ref=uploadVid.php");
  die();
}

if (isset($_POST["vid-title"]) && isset($_POST["vid-description"]) && isset($_POST["vid-duration"]) && isset($_POST["vid-thumbnail"]) && file_exists($_FILES['vid-file']['tmp_name'])) {
  $vid_title = substr(mysqli_real_escape_string($mysqli, strip_tags($_POST["vid-title"])), 0, 64);
  $vid_description = substr(mysqli_real_escape_string($mysqli, strip_tags($_POST["vid-description"])), 0, 1024);
  $vid_duration = intval(mysqli_real_escape_string($mysqli, $_POST["vid-duration"]));
  if ($vid_duration == 0) $vid_duration = 1;
  $vid_thumbnail = $_POST["vid-thumbnail"];
  
  $file_array = $_FILES["vid-file"];
  $temp_path = $file_array["tmp_name"];
  $type = explode('/', $file_array['type'])[1];
  
  if (strstr(mime_content_type($temp_path), "video/")) {
    
    $date = new DateTime();
    $sql = "INSERT INTO uploads (user, title, upload_time, description, category, duration, views)
    VALUES ('".$_SESSION["dp_username"]."', '$vid_title', ".$date->getTimestamp().", '$vid_description', ".intval($_POST["category"]).", $vid_duration, 0)";
    $result = $mysqli->query($sql);
    $id = $mysqli->insert_id; // id van upload-item
    
    mkdir("uploads/$id");
    $new_path = "uploads/$id/item.$type";
    move_uploaded_file($temp_path, $new_path); // video opslaan
    
    // tumbnail opslaan
    resize_and_rotate_img(400*200, $vid_thumbnail, "base64", "uploads/$id/thumbnail", 0);
    
    header("Location: view.php?id=$id");
    die();
    
  } else {
    
    $error_message = "Bestandstype <b>'$type'</b> wordt niet herkend als video.";
    
  }
}


$title = "Video uploaden";
$orange_main = true;
include("layout/header.php");
?>

<script type="text/javascript">
  
  $(document).ready(function() {
    $("#vid-title").on("click focus change paste keyup blur input", check);
    $("#vid-description").on("click focus change paste keyup blur input", check);
    
    $("#preview").on("loadeddata", function () {
      $("#preview-thumbnail").find("p").html(secToTime(this.duration));
      setThumbnail();
    });
    
    $("#preview").on("pause timeupdate", function () {
      if (!this.paused) return;
      window.thumbnailTime = this.currentTime;
    });
    
    <? if (isset($error_message)) print("$('#upload-error').modal('open');"); ?>
    if (window.mobile) $("#preview").attr("style", "margin: 0 -2rem; width: calc(100% + 4rem);");
  });
  
  window.prevThumbnailTime = 0;
  window.thumbnailTime = 0;
  window.vidSet = false;
  window.thumbnail = null;
  
  setInterval(function() {
    if (window.prevThumbnailTime == window.thumbnailTime) return;
    
    window.prevThumbnailTime = window.thumbnailTime;
    setThumbnail();
    
  }, 750);
  
  function setThumbnail() {
    var canvas = document.createElement("canvas");
    var video = $("#preview")[0];
    var width =  video.videoWidth;
    var height = video.videoHeight;
    canvas.width = width;
    canvas.height = height;
    canvas.getContext('2d').drawImage(video, 0, 0, width, height);
    window.thumbnail = canvas.toDataURL();
    $("#preview-thumbnail").css("background-image", "url(" + window.thumbnail + ")");
  }
  
  function readURL(input) {
    if (input.files[0].size > 128000000) {
      Materialize.toast("Bestand te groot.<br><br>" + parseInt(input.files[0].size/1000000) + "MB van de 128MB", 6000);
      return;
    }
    window.vidSet = true;
    check();
    var URL = window.URL || window.webkitURL;
    $("#preview").attr("src", URL.createObjectURL(input.files[0]));
  }
  
  function check() {
    if (window.vidSet && window.thumbnail != null && $("#vid-title").val().length > 0 && $("#vid-description").val().length > 0) $("#submit-vid").removeClass("disabled");
    else $("#submit-vid").addClass("disabled");
  }
  
  function submit() {
    var form = $("#vid-upload");
    form.append($("<input type='hidden' name='vid-duration' value='" + parseInt($("#preview")[0].duration) + "'>"));
    form.append($("<input type='hidden' name='vid-thumbnail' value='" + window.thumbnail + "'>"));
    loader();
    form.submit();
  }
  
  function loader() {
    $("#vid-upload").css("opacity", ".3").css("pointer-events", "none");
    $("#upload-loader").css("display", "block");
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
          Upload een video.
        </h5>
        <p>
          - Ongepaste content wordt onmiddelijk door onze administrators verwijderd.
          <br>- Max. bestandsgrootte: <b>128MB</b>
          <br>- Probeer <b>.MP4</b> bestanden te gebruiken
        </p>
        
        <form id="vid-upload" enctype="multipart/form-data" method="post" action="uploadVid.php">
          
          <div class="file-field input-field">
            <div class="btn">
              <span><i class="material-icons">file_upload</i></span>
              <input name="vid-file" onchange="readURL(this)" type="file" accept="video/*">
            </div>
            <div class="file-path-wrapper">
              <input id="file-path" class="file-path" type="text">
            </div>
          </div>
          
          <div class="row">
            <div class="col s12 m12 l7">
              
              <video id="preview" width="100%;" height="260" controls>
                Uw browser ondersteunt geen video player. Gebruik een browser zoals Chrome, Firefox of Edge
              </video>

            </div>
            <div class="col m12 l5">
              
              <h5>
                <i class="material-icons" style="vertical-align: top;">video_label</i> Miniatuur
              </h5>
              <p>
                Pauzeer de video op een tijdstip waarvan u de miniatuur wilt.
              </p>
              <center>
                <div id="preview-thumbnail" class="thumbnail waves-effect waves-light">
                  <img src="data/vid.png">
                  <p>
                    0:00
                  </p>
                </div>
              </center>
            </div>
            
            <div class="col s12">
              <div class="input-field">
                <i class="material-icons prefix">title</i>
                <input id="vid-title" type="text" name="vid-title" data-length="64" maxlength="64">
                <label for="vid-title">Titel</label>
              </div>
              
              <div class="input-field">
                <i class="material-icons prefix">mode_edit</i>
                <textarea form="vid-upload" id="vid-description" name="vid-description" class="materialize-textarea" data-length="1024" maxlength="1024"></textarea>
                <label for="vid-desctiption" onclick="$('#vid-description').focus()">Descriptie</label>
              </div>
            </div>
          </div>
          
          <div class="input-field col s12">
            <select name="category">
              <option value="0">Puur vermaak</option>
              <option value="4">Meesterlijke kunst</option>
              <option value="1">Gameplay video</option>
              <option value="2">Unboxing video</option>
              <option value="3" disabled>Porno (niet toegestaan)</option>
            </select>
            <label>Categorie</label>
          </div>
        
          <a id="submit-vid" onclick="submit()" type="submit" class="btn disabled waves-effect">
            Uploaden
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