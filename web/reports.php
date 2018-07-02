<?
include("init.php");

if (!$_SESSION["dp_admin"]) {
  header("Location: index.php");
  die();
}

$title = "Rapporteringen";
include("layout/header.php");

$sql = "SELECT *, (SELECT COUNT(*) FROM uploads WHERE uploads.id = reports.upload_id) AS not_deleted FROM reports ORDER BY id DESC";
$result = $mysqli->query($sql);

?>

<div class="row">
  <div class="col s12 m10 l8 offset-s0 offset-m1 offset-l2">
    <h5>Rapporteringen</h5>
    
    
    <table>
      <thead>
        <tr>
          <th>Upload</th>
          <th>Gerapporteerd door</th>
          <th>Categorie</th>
          <th>Reden</th>
        </tr>
      </thead>

      <tbody>
        <?
        while ($row = $result->fetch_assoc()) {
          print("<tr>");
          if ($row["not_deleted"]) print(" <td><a class='btn waves-effect' href='view.php?id=".$row["upload_id"]."'>".$row["upload_id"]."</a></td>");
          else print("<td><a class='red btn waves-effect' href='#deleted-modal'>".$row["upload_id"]."</a></td>");
          print(" <td>".$row["user"]."</td>");
          print(" <td>".$row["category"]."</td>");
          print(" <td>".$row["reason"]."</td>");
          print("</tr>");
        }
        ?>

      </tbody>
    </table>
    
  </div>
</div>

<div id="deleted-modal" class="modal">
  <div class="modal-content">
    Dit item is reeds verwijderd door een administrator.<br>
    <a class="waves-effect btn-flat modal-close">ok</a>
  </div>
</div>

</main></body>