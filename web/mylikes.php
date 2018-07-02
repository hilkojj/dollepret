<?
include("init.php");
$title = "mijn reacties/likes";
include("layout/header.php");
?>
<div class="row">
  <div class="col s12 m10 l8 offset-s0 offset-m1 offset-l2 no-offset">
    <ul class="tabs">
      <li class="tab"><a class="waves-effect active" href="#likes"><i class="material-icons" style="vertical-align: middle;">thumb_up</i> Mijn gewaardeerde items</a></li>
      <li class="tab"><a class="waves-effect"href="#comments"><i class="material-icons" style="vertical-align: middle;">chat_bubble</i> Op gereageerd</a></li>
    </ul>
    <div id="likes" class="col s12 no-offset">
      <? 
      $sql = "SELECT uploads.id, title, duration, description, views, upload_time, (SELECT COUNT(*) FROM upload_votes WHERE upload_votes.upload_id = uploads.id AND upload_votes.up = 1)
      - (SELECT COUNT(*) FROM upload_votes WHERE upload_votes.upload_id = uploads.id AND upload_votes.up = 0) AS score,
      (SELECT COUNT(*) FROM comments WHERE comments.upload_id = uploads.id) AS comments
      FROM uploads, upload_votes WHERE uploads.id = upload_votes.upload_id AND upload_votes.user = '".$_SESSION["dp_username"]."' ORDER BY upload_votes.time DESC";
      $result = $mysqli->query($sql);
      $likes = get_result_array($result); 
      print_thumbnails_and_info($likes, true);?>
    </div>
    <div id="comments" class="col s12 no-offset">
      <?
      $sql = "SELECT uploads.id, title, duration, description, views, upload_time, (SELECT COUNT(*) FROM upload_votes WHERE upload_votes.upload_id = uploads.id AND upload_votes.up = 1)
      - (SELECT COUNT(*) FROM upload_votes WHERE upload_votes.upload_id = uploads.id AND upload_votes.up = 0) AS score,
      (SELECT COUNT(*) FROM comments WHERE comments.upload_id = uploads.id) AS comments
      FROM uploads, comments WHERE uploads.id = comments.upload_id AND comments.user = '".$_SESSION["dp_username"]."' ORDER BY comments.time DESC";
      $result = $mysqli->query($sql);
      $reacties = get_result_array($result);
      print_thumbnails_and_info($reacties, true);
      ?>
    </div>
    </div>
  </div>
</div>



<?
include("layout/footer.php");
?>
