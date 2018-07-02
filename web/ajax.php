<?
include("init.php");

if (isset($_GET["setcursor"])) $_SESSION["alternative_cursor"] = $_GET["setcursor"] == "true";

if (isset($_GET["setvolume"])) $_SESSION["volume"] = $_GET["setvolume"];

if (isset($_GET["upldinfo"])) {

  $sql = "SELECT title, description FROM uploads WHERE id = ".mysqli_real_escape_string($mysqli, $_GET["upldinfo"]);
  $result = $mysqli->query($sql);
  $props = $result->fetch_assoc();
  header("Access-Control-Allow-Origin: http://dollepret.pe.hu");

  ?>
  <meta property="og:title" content="<? print($props["title"]); ?>"/>
  <meta property="og:image" content="http://infgc.nl/h16hilko/dollepret/uploads/<? print($_GET["upldinfo"]); ?>/thumbnail.png"/>
  <meta property="og:description" content="<? print($props["description"]); ?>"/>
  <?

}

if (isset($_GET["updatenotificationstime"]) && $logged_in) {
  $time = new DateTime();
  $time = $time->getTimestamp();
  $sql = "UPDATE users SET checked_notifications_time = $time WHERE users.name = '".$_SESSION["dp_username"]."'";
  $mysqli->query($sql);
  print("SUCCESS");
  die();
}

if (isset($_GET["lasttab"])) {
  $_SESSION["lasttab"] = $_GET["lasttab"];
  print("hoi als je dit leest ben je stom.");
  die();
}

if (isset($_POST["up"]) && isset($_POST["id"])) {
  $id = mysqli_real_escape_string($mysqli, $_POST["id"]);
  if (!$logged_in) {
    print("U bent niet ingelogd <a href='login.php?ref=view.php?id=".$_POST["id"]."'><b>INLOGGEN</b></a>");
    die();
  }
  $up = intval($_POST["up"]);
  $nietminnen = ($casper || $_SESSION["dp_username"] == "Piet Saman") && $up == 0;
  if ($up != 0 && $up != 1) {
    print("Extreem super ernstige fatale fout opgetreden.");
    die();
  }

  // Waardering achterlaten.
  // Eventuele oude verwijderen
  $sql = "DELETE FROM upload_votes WHERE user = '".$_SESSION["dp_username"]."' AND upload_id = $id;";
  // nieuwe toevoegen
  $time = new DateTime();
  $time = $time->getTimestamp();
  $sql .= " INSERT INTO upload_votes (upload_id, user, up, time) VALUES ($id, '".$_SESSION["dp_username"]."', ".($nietminnen ? "1" : $up).", $time);";
  $result = $mysqli->multi_query($sql);
  print($nietminnen ? "nietminnen" : "SUCCESS");
  die();
}

if (isset($_POST["c-up"]) && isset($_POST["comment-id"]) && isset($_POST["id"])) {
  $id = mysqli_real_escape_string($mysqli, $_POST["id"]);
  $comment_id = mysqli_real_escape_string($mysqli, $_POST["comment-id"]);
  if (!$logged_in) {
    print("U bent niet ingelogd <a href='login.php?ref=view.php?id=".$_POST["id"]."'><b>INLOGGEN</b></a>");
    die();
  }
  $up = intval($_POST["c-up"]);
  if ($up != 0 && $up != 1) {
    print("Extreem ernstige fout opgetreden.");
    die();
  }
  // Waardering achterlaten.
  // Eventuele oude verwijderen
  $sql = "DELETE FROM comment_votes WHERE user = '".$_SESSION["dp_username"]."' AND comment_id = ".$comment_id.";";
  $mysqli->query($sql);
  // nieuwe toevoegen
  $sql = " INSERT INTO comment_votes (comment_id, user, up) VALUES ($comment_id, '".$_SESSION["dp_username"]."', $up);";
  $mysqli->query($sql);

  $sql = "SELECT (SELECT COUNT(*) FROM comment_votes WHERE comment_votes.comment_id = $comment_id AND comment_votes.up = 1) - (SELECT COUNT(*) FROM comment_votes WHERE comment_votes.comment_id = $comment_id AND comment_votes.up = 0) AS score";
  $result = $mysqli->query($sql);
  $score = $result->fetch_assoc()["score"];
  if ($score >= 0) $score = "+".$score;
  print("SUCCESS $score $comment_id");
  die();
}

?>
