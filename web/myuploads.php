<?
include("init.php");
$title = "mijn uploads";
include("layout/header.php");
$sql = "SELECT *, (SELECT COUNT(*) FROM upload_votes WHERE upload_votes.upload_id = uploads.id AND upload_votes.up = 1)
- (SELECT COUNT(*) FROM upload_votes WHERE upload_votes.upload_id = uploads.id AND upload_votes.up = 0) AS score,
(SELECT COUNT(*) FROM comments WHERE comments.upload_id = uploads.id) AS comments
fROM `uploads`
where user = '".$_SESSION["dp_username"]."'";
$result = $mysqli->query($sql);
$rows = get_result_array($result);

?>
<div class="row">
 <div class="col s12 m10 l8 offset-s0 offset-m1 offset-l2 no-offset">
    <? print_thumbnails_and_info($rows, true); ?>
  </div> 
</div>


<?
include("layout/footer.php");
?>
