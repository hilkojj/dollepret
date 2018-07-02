<?
$notifications = array();
$not_seen_notifications = 0;
if ($logged_in) {
  
  // notificaties ophalen..
  // likes
  $sql = "SELECT uploads.id, uploads.title, upload_votes.user, upload_votes.up, upload_votes.time, users.checked_notifications_time > upload_votes.time AS seen FROM upload_votes, uploads, users 
  WHERE upload_votes.user != '".$_SESSION["dp_username"]."' AND upload_votes.upload_id = uploads.id AND uploads.user = '".$_SESSION["dp_username"]."' 
  AND users.name = uploads.user";
  
  $result = $mysqli->query($sql);
  while ($row = $result->fetch_assoc()) {
    $key = $row["time"];
    while(isset($notifications[$key])) $key += .0001;
    $notifications[$key] = $row;
    if (!$row["seen"]) $not_seen_notifications++;
  }
  
  // reacties
  $sql = "SELECT uploads.id, uploads.title, comments.user, comments.time, users.checked_notifications_time > comments.time AS seen FROM comments, uploads, users 
  WHERE comments.user != '".$_SESSION["dp_username"]."' AND comments.upload_id = uploads.id AND uploads.user = '".$_SESSION["dp_username"]."' AND users.name = uploads.user";
  
  $result = $mysqli->query($sql);
  while ($row = $result->fetch_assoc()) {
    $key = $row["time"];
    while(isset($notifications[$key])) $key += .0001;
    $notifications[$key] = $row;
    if (!$row["seen"]) $not_seen_notifications++;
  }
  
  //mentions
  $sql = "SELECT comments.user, comments.time, comments.upload_id AS id, users.checked_notifications_time > comments.time AS seen FROM comments, users 
  WHERE comments.user != '".$_SESSION["dp_username"]."' AND comments.text LIKE '%@".$_SESSION["dp_username"]."%' AND users.name = '".$_SESSION["dp_username"]."'";
  
  $result = $mysqli->query($sql);
  while ($row = $result->fetch_assoc()) {
    $key = $row["time"];
    $row["mention"] = 1;
    while(isset($notifications[$key])) $key += .0001;
    $notifications[$key] = $row;
    if (!$row["seen"]) $not_seen_notifications++;
  }
  
  krsort($notifications);
}

?>

<!DOCTYPE HTML>
<html>

<head>
  
  <title><? print(isset($title) ? $title . " | Dolle pret" : "Dolle pret!"); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <meta name="theme-color" content="<? print($red_theme_color ? "#F44336" : "#FF9800"); ?>">
  <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link type="text/css" rel="stylesheet" href="css/materialize.css?hoi_als_je_dit_leest_wil_ik_je_zeggen_dat_je_echt_een_super_goeie_dolle_pret_fanaat_bent" media="screen,projection">
  <link type="text/css" rel="stylesheet" href="css/style.css?v=<? print(filemtime(__DIR__."/../css/style.css")); ?>">
  
  <style id="cursor-css" type="text/css" data-content="body {cursor: url('data/cursor.cur'), progress !important;}"></style>
  
  <style id="scrollbar-css" type="text/css">
    ::-webkit-scrollbar {
      width: 4px;
      height: 4px;
      background: white;
    }
    ::-webkit-scrollbar-thumb {
      background-color: grey;
    }
  </style>
  
  <link rel="apple-touch-icon" sizes="57x57" href="icons/apple-icon-57x57.png">
  <link rel="apple-touch-icon" sizes="60x60" href="icons/apple-icon-60x60.png">
  <link rel="apple-touch-icon" sizes="72x72" href="icons/apple-icon-72x72.png">
  <link rel="apple-touch-icon" sizes="76x76" href="icons/apple-icon-76x76.png">
  <link rel="apple-touch-icon" sizes="114x114" href="icons/apple-icon-114x114.png">
  <link rel="apple-touch-icon" sizes="120x120" href="icons/apple-icon-120x120.png">
  <link rel="apple-touch-icon" sizes="144x144" href="icons/apple-icon-144x144.png">
  <link rel="apple-touch-icon" sizes="152x152" href="icons/apple-icon-152x152.png">
  <link rel="apple-touch-icon" sizes="180x180" href="icons/apple-icon-180x180.png">
  <link rel="icon" type="image/png" sizes="192x192"  href="icons/android-icon-192x192.png">
  <link rel="icon" type="image/png" sizes="32x32" href="icons/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="96x96" href="icons/favicon-96x96.png">
  <link rel="icon" type="image/png" sizes="16x16" href="icons/favicon-16x16.png">
  <link rel="manifest" href="icons/manifest.json">
  <meta name="msapplication-TileColor" content="#FF9800">
  <meta name="msapplication-TileImage" content="icons/ms-icon-144x144.png">

<body<? if($orange_main) print(" class='orange'"); ?>>
  
  <script type="text/javascript" src="js/jquery-2.2.2.min.js"></script>
  <script type="text/javascript" src="js/materialize.min.js"></script>
  <script type="text/javascript" src="js/dollepret.js?v=<? print(filemtime(__DIR__."/../js/dollepret.js")); ?>"></script>
  
  <header>
    <div class="navbar-fixed">
      <nav class="<? if($orange_main) print("z-depth-0 "); if ($red_theme_color) print("red"); ?>" style="transition: .5s;">
        <div class="nav-wrapper">

          <a class="button-collapse" data-activates="slide-out">
            <i class="material-icons">menu</i>
            <? if ($not_seen_notifications > 0): ?>
            <span class="badge white-text red" style="position: fixed;margin-left: 15px;margin-top: -30px;border-radius: 4px;border: solid 2px #ff9800;min-width: 0;line-height: 19px;"><? print($not_seen_notifications); ?></span>
            <? endif; ?>
          </a>
          <div class="hide-on-med-and-down" style="width: 1rem; height: 1px; float: left;"></div>
          <a href="index.php" class="brand-logo waves-effect waves-orange"><img style="max-height: 50px; max-width: 100%; vertical-align: middle;" src="data/logo<? if (rand(0, 100) > 50) print("pert"); ?>.png"></a>

          <ul class="right hide-on-med-and-down">
            
            <? if ($logged_in): ?>
            <li>
              <a class="btn-flat btn-floating" href="#notifications" onclick="$.ajax('ajax.php?updatenotificationstime=true')">
                <i class="material-icons right tooltipped" data-position="bottom" data-delay="50" data-tooltip="Notificaties">notifications</i>
                <? if ($not_seen_notifications > 0): ?>
                <span class="badge white-text red" style="position: fixed;margin-left: 21px;border-radius: 4px;border: solid 2px #ff9800;min-width: 0;line-height: 19px;"><? print($not_seen_notifications); ?></span>
                <? endif; ?>
              </a>
            </li>
            <? endif; ?>
            
            <li><a href="#upload-modal" class="waves-effect btn-flat white-text"><i class="material-icons right">file_upload</i>uploaden</a></li>
            
            <? if ($logged_in): ?>
            <li>
              <a class="dropdown-button btn-flat waves-effect white-text me-dropdown-btn" data-activates="me-dropdown">
                <img class="navbar-pf" src="<? profile_pic(true, $_SESSION["dp_username"]); ?>">
                <? print($_SESSION["dp_username"]); ?>
              <? if ($_SESSION["dp_admin"]) print("<i class='material-icons right'>verified_user</i>"); ?>
              </a>
            </li>
            
            <ul id="me-dropdown" class="dropdown-content">
              <center><img class="circle" style="margin-top: 1rem;" src="<? profile_pic(false, $_SESSION["dp_username"]); ?>"></center>
              <? if ($_SESSION["dp_admin"]): ?>
              <li><a class="waves-effect red-text" href="reports.php"><i class="material-icons left">flag</i>Rapporteringen</a></li>
              <? endif; ?>
              <li><a class="waves-effect" href="myuploads.php"><i class="material-icons left">view_list</i>Mijn uploads</a></li>
              <li><a class="waves-effect" href="mylikes.php"><i class="material-icons left">list</i>Mijn reacties/likes</a></li>
              <li><a class="waves-effect" href="changepf.php"><i class="material-icons left">portrait</i>Profielfoto wijzigen</a></li>
              <li><a class="waves-effect" href="#!" onclick="$('#cursor').click()">
                <input <? if ($_SESSION["alternative_cursor"]) print("checked='checked'"); ?> type="checkbox" id="cursor" style="pointer-events: none;">
                <label class="indigo-text" style="margin-left: 3px; font-size: 16px; pointer-events: none;" for="cursor">Handige cursor utiliseren</label>
                </a></li>
              
              <li class="divider"></li>
              <li><a class="waves-effect" href="logout.php"><i class="material-icons left">exit_to_app</i>Uitloggen</a></li>
            </ul>
            <? else: ?>
            <li><a href="login.php" class="waves-effect btn-flat white-text"><i class="material-icons right">account_circle</i>aanmelden</a></li>
            <? endif; ?>
            
          </ul>
          <ul class="right">
            <li><a class="btn-flat waves-effect btn-floating" onclick="showSearch()"><i class="material-icons right tooltipped" data-position="bottom" data-delay="50" data-tooltip="Zoek naar amusement.">search</i></a></li>
          </ul>

        </div>

        <div id="search-div" class="nav-wrapper z-depth-1">
          <form action="search.php" method="get">
            <div class="input-field">
              <input name="q" id="search" type="search" <? if (isset($q)) print("value='".$q."'"); ?>>
              <label class="label-icon" for="search"><i class="material-icons">search</i></label>
              <i class="material-icons">close</i>
            </div>
          </form>
        </div>
        
        
      </nav>
    </div>
    
    <ul class="side-nav" id="slide-out">
      <li><a class="waves-effect" style="margin-top: 4px;"><i class="material-icons">arrow_back</i></a></li>
      
      <? if ($logged_in): ?>
      
      <center>
        <img class="circle" src="<? profile_pic(false, $_SESSION["dp_username"]); ?>">
        <br><? 
        print($_SESSION["dp_username"]); 
        if ($_SESSION["dp_admin"]) print("<i class='material-icons' style='vertical-align: bottom;'>verified_user</i>");
        ?>
      </center>
      
      <? if ($_SESSION["dp_admin"]): ?>
      <li><a class="waves-effect red-text" href="reports.php"><i class="material-icons red-text">flag</i>Rapporteringen</a></li>
      <? endif; ?>
      
      <li>
        <a href="#notifications" class="waves-effect" onclick="$.ajax('ajax.php?updatenotificationstime=true')"><i class="material-icons">notifications</i>Notificaties
          <? if ($not_seen_notifications > 0): ?>
          <span class="badge white-text red" style="border-radius: 4px; min-width: 0; margin-right: .75rem;"><? print($not_seen_notifications); ?></span>
          <? endif; ?>
        </a>
      </li>
      <li><a href="#upload-modal" class="waves-effect"><i class="material-icons">file_upload</i>Uploaden</a></li>
      <li><a class="waves-effect" href="myuploads.php"><i class="material-icons">view_list</i>Mijn uploads</a></li>
      <li><a class="waves-effect" href="mylikes.php"><i class="material-icons">list</i>Mijn reacties/likes</a></li>
      <li><a class="waves-effect" href="changepf.php"><i class="material-icons">portrait</i>Profielfoto wijzigen</a></li>
      <li class="divider">
      <li><a href="logout.php" class="waves-effect"><i class="material-icons">exit_to_app</i>Uitloggen</a></li>
      
      <? else: ?>
      
      <li><a href="login.php" class="waves-effect"><i class="material-icons">account_circle</i>Aanmelden</a></li>
      <li><a href="#upload-modal" class="waves-effect"><i class="material-icons">file_upload</i>Uploaden</a></li>
      
      <? endif; ?>

    </ul>
    
  </header>
  
  <main>
    <div id="notifications" class="modal bottom-sheet" style="height: 100%; max-height: none !important; max-width: 600px;">
      <div class="modal-content">
        <a class="btn-floating btn-flat waves-effect modal-close"><i class="material-icons left black-text">arrow_back</i></a>
        <?
        print($not_seen_notifications > 0 ? $not_seen_notifications." nieuwe notificatie".($not_seen_notifications > 1 ? "s" : "") : "Geen nieuwe notificaties"); 
        ?>
        
        <div class="collection">
          <?
          foreach ($notifications as $not) {
          ?>
          <a href="view.php?id=<? print($not["id"]); ?>" class="collection-item waves-effect<? if (!$not["seen"]) print(" blue lighten-4"); ?>">
            <img src="<? profile_pic(true, $not["user"]) ?>">
            <?
            if ($not["up"] == 1) {
              print("<b>".$not["user"]."</b> waardeert je upload <b>'".$not["title"]."'</b>");
            } elseif (isset($not["up"])) {
              print("<b>".$not["user"]."</b> vind je upload <b>'".$not["title"]."'</b> <span class='red-text'>niet</span> leuk");
            } elseif ($not["mention"]) {
              print("<b>".$not["user"]."</b> heeft je genoemd in een reactie!");
            } else {
              print("<b>".$not["user"]."</b> heeft gereageerd op je upload <b>'".$not["title"]."'</b>");
            }
            print('<br>');
            if ($not["time"] == 1) {
              print('<span class="grey-text">Datum en tijd onbekend</span>');
            } else {
              print('<span class="grey-text" data-date="'.$not["time"].'"></span>');
            }
            ?>
          </a>
          <?
          }
          ?>
        </div>
      </div>
    </div>
    
    <div id="upload-modal" class="modal bottom-sheet">
      <div class="modal-content">
        <? if (!$logged_in): ?>
        U moet eerst <a href="login.php">inloggen</a> voordat u deze site van amusement kunt voorzien.
        <? else: ?>
        <ul class="collection">
          <a href="uploadVid.php">
            <li class="collection-item avatar waves-effect">
              <i class="material-icons circle indigo">movie</i>
              <span class="title">Video</span>
              <p>Upload bewegend beeld.</p>
            </li>
          </a>
          <a href="uploadImg.php">
            <li class="collection-item avatar waves-effect">
              <i class="material-icons circle green">image</i>
              <span class="title">Afbeelding</span>
              <p>Upload een afbeelding. Hetzij een foto, hetzij een GIF.</p>
            </li>
          </a>
        </ul>
        
        <? endif; ?>
      </div>
    </div>
    
    