<?

$mysqli = new mysqli("bla bla bla");
session_start();
$logged_in = isset($_SESSION["dp_username"]);

function profile_pic($small, $username) {
  $username = strtolower($username);
  print(($small ? "profilepics/small/" : "profilepics/").$username.".png?timestamp=".filemtime("profilepics/".$username.".png"));
}

function resize_and_rotate_img($max_size, $original_path, $type, $new_path, $rotate) {
  switch($type){
    case 'bmp':
      $img = imagecreatefromwbmp($original_path);
      break;
    case 'gif':
      $img = imagecreatefromgif($original_path);
      break;
    case 'jpg':
      $img = imagecreatefromjpeg($original_path);
      break;
    case 'png':
      $img = imagecreatefrompng($original_path);
      break;
    case 'base64':
      $img = imagecreatefromstring(base64_decode(explode(",", $original_path)[1]));
      break;
  }

  list($original_width, $original_height) = getimagesize($original_path);

  $new_width = $original_width;
  $new_height = $original_height;

  while ($new_width * $new_height > $max_size) { // als plaatje te groot is (wat vaak het geval is bij fotos) -> verkleinen
    $new_width *= .95;
    $new_height *= .95;
  }

  $new_img = imagecreatetruecolor($new_width, $new_height);
  imagecopyresampled($new_img, $img, 0, 0, 0, 0, $new_width, $new_height, $original_width, $original_height);

  if ($rotate != 0) $new_img = imagerotate($new_img, $rotate, 0); // plaatje draaien

  imagepng($new_img, $new_path.".png"); // opslaan
}

function create_links($string) {
  $url = '@(http)?(s)?(://)?(([a-zA-Z])([-\w]+\.)+([^\s\.]+[^\s]*)+[^,.\s])@';
  $string = preg_replace($url, '<a href="http$2://$4" target="_blank">$0</a>', $string);
  return $string;
}

function print_thumbnails_and_info($rows, $small) {
  $date = new DateTime();
  $time = $date->getTimestamp();

  foreach ($rows as $row) {

    $file = glob("uploads/".$row["id"]."/item.*")[0];
    $ext = explode(".", $file)[1];
    $type = $ext == "png" ? "img" : ($ext == "gif" ? "gif" : "vid");

    $viewed = isset($_SESSION["viewed"][$row["id"]]);
    $sec = $time - $_SESSION["viewed"][$row["id"]];

    ?>

    <a class="col s12<? if ($small) print(" l6"); ?>" href="view.php?id=<? print($row["id"]); ?>">
      <div class="col s12 card hoverable thumbnail-container no-offset waves-effect">
        <div <? if ($type == "gif") print('data-hover-gif="uploads/'.$row["id"].'/item.gif"'); ?>
             class="col s5 thumbnail waves-effect waves-light" style="background-image: url(uploads/<? print($row["id"]); ?>/thumbnail.png);">

          <img src="data/<? print($type); ?>.png">
          <? if ($type == "vid") print ("<p>".$row["duration"]."</p>");?>

        </div>
        <p class="col s7">

          <b><? print($row["title"]); ?></b><br>
          <span class="grey-text" data-date="<? print($row["upload_time"]); ?>"></span>
          <span class="grey-text stats">
            <? if ($viewed): ?>
            <i class="material-icons blue-text tooltipped"
               data-position="bottom" data-delay="50" data-tooltip="<? print($sec > 60 ? intval($sec/60)." min." : $sec." sec."); ?> geleden bekeken" style="vertical-align: bottom;">check</i>
            <? endif; ?>
            <i class="material-icons" style="vertical-align: middle;">thumb_<? print($row["score"] < 0 ? "down" : "up"); ?></i> <? print($row["score"]); ?>
            <i class="material-icons" style="vertical-align: middle;">chat_bubble<? print((intval($row["comments"]) == 0 ? "_outline" : "")."</i>".$row["comments"]); ?>
            <i class="material-icons" style="vertical-align: middle;">remove_red_eye</i> <? print($row["views"]); ?>
          </span>

        </p>
      </div>
    </a>

   <?
  }
}

function get_result_array($result) {
  $array = array();
  while ($row = $result->fetch_assoc()) {
    $array[] = $row;
  }
  return $array;
}

function replace_emoticons($string) {
  $emoticons = array(":)", ";)", ":D", ";D", "(:", "(;", ":o", ":O", ";o", ";O", ":-)", ";-)", "&#128512;", "&#128513;", "&#128514;", "&#128515;", "&#128516;", "&#128517;", "&#128518;", "&#128521;", "&#128522;", "&#128523;", "&#128526;", "&#128525;", "&#128536;", "&#128535;", "&#128537;", "&#128538;", "&#9786;", "&#128578;", "&#129303;", "&#128519;", "&#129299;", "&#129300;", "&#128528;", "&#128529;", "&#128566;", "&#128580;", "&#128527;", "&#128547;", "&#128549;", "&#128558;", "&#129296;", "&#128559;", "&#128554;", "&#128555;", "&#128564;", "&#128524;", "&#128539;", "&#128540;", "&#128541;", "&#128530;", "&#128531;", "&#128532;", "&#128533;", "&#128579;", "&#129297;", "&#128562;", "&#128567;", "&#129298;", "&#129301;", "&#128577;", "&#128534;", "&#128542;", "&#128543;", "&#128548;", "&#128546;", "&#128557;", "&#128550;");
  return str_replace($emoticons, "<img class='genotsjong' src='data/genotsjong.png'>", $string);
}

?>
