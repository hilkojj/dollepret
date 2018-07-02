<?
include("init.php");

if ($_SESSION["dp_username"] == "Dhr. Boerema (technische dienst)" 
    || $_SESSION["dp_username"] == "C. van der Louw (schoonmaak afdeling)") {
  
  $mysqli->query()->fetch_assoc(); // huh hoe kan dat nou
  die();
  
}

$comment_id = mysqli_real_escape_string($mysqli, $_GET["id"]);

if (!$_SESSION["dp_admin"]) {
  $sql = "SELECT * FROM comments WHERE id = $comment_id AND user = '".$_SESSION["dp_username"]."'";
  $result = $mysqli->query($sql);
  if (mysqli_num_rows($result) == 0) {
    header("Location: view.php?id=".$_GET["ref"]);
    die();
  }
}
$sql = "DELETE FROM comment_votes WHERE comment_id = $comment_id";
$mysqli->query($sql);
$sql = "DELETE FROM comments WHERE id=$comment_id";
$mysqli->query($sql);

header("Location: view.php?id=".$_GET["ref"]);
?>