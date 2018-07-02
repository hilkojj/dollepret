<?
include("init.php");

if ($_SESSION["dp_username"] == "Dhr. Boerema (technische dienst)" 
    || $_SESSION["dp_username"] == "C. van der Louw (schoonmaak afdeling)") {
  
  $mysqli->query()->fetch_assoc(); // huh hoe kan dat nou
  die();
  
}


if (!$_SESSION["dp_admin"]) {
  header("Location: index.php");
  die();
}

$id = mysqli_real_escape_string($mysqli, $_GET["id"]);


$sql = "SELECT comments.id FROM comments WHERE comments.upload_id = $id";
$result = $mysqli->query($sql);

$in = "(";
$first = true;
while ($row = $result->fetch_assoc()) {
  if (!$first) $in .= ", ";
  $in .= $row["id"];
  $first = false;
}
$in .= ")";

$sql = "DELETE FROM comment_votes WHERE comment_id IN $in";
$mysqli->query($sql);
$sql = "DELETE FROM comments WHERE id IN $in";
$mysqli->query($sql);
$sql = "DELETE FROM upload_votes WHERE upload_id = $id";
$mysqli->query($sql);
$sql = "DELETE FROM uploads WHERE id = $id";
$mysqli->query($sql);

$files = glob("uploads/$id/*");
foreach ($files as $file) {
  unlink($file);
}

header("Location: index.php");

?>