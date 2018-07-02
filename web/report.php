<?

if (!isset($_POST["id"]) || !isset($_POST["cat"]) || !isset($_POST["reason"])) {
  header("Location: index.php");
  die();
}

include("init.php");

$user = $logged_in ? $_SESSION["dp_username"] : "* niet ingelogde bezoeker";
$upload_id = mysqli_real_escape_string($mysqli, $_POST["id"]);
$category = mysqli_real_escape_string($mysqli, $_POST["cat"]);
$reason = mysqli_real_escape_string($mysqli, $_POST["reason"]);

$sql = "INSERT INTO reports (upload_id, user, category, reason) VALUES ($upload_id, '$user', '$category', '$reason')";
$result = $mysqli->query($sql);

$title = "Bedankt";
$orange_main = true;
include("layout/header.php");

?>
<div class="row">
  <div class="col s10 m6 l4 offset-l4 offset-m3 offset-s1 card">
    <h5>
      Hartelijk dank
    </h5>
    <br>
    Vielen Dank f&#252;r diesen Artikel berichten.
    <br>Unsere Administratoren werden so schnell wie m&#246;glich zu handeln.
    <br><br>
    <a class="btn btn-flat waves-effect" href="index.php">Naar homepagina</a>
    <a class="btn waves-effect" href="view.php?id=<? print($upload_id); ?>">Terug naar upload</a>
    <br><br>
  </div>
</div>
<?
include("layout/footer.php");
?>