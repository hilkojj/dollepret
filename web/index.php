<?
include("init.php");
include("layout/header.php");
$sql = "SELECT id, title, duration, description, views, upload_time, (SELECT COUNT(*) FROM upload_votes WHERE upload_votes.upload_id = uploads.id AND upload_votes.up = 1)
- (SELECT COUNT(*) FROM upload_votes WHERE upload_votes.upload_id = uploads.id AND upload_votes.up = 0) AS score,
(SELECT COUNT(*) FROM comments WHERE comments.upload_id = uploads.id) AS comments
FROM uploads ORDER BY score DESC LIMIT 12";
$result = $mysqli->query($sql);
$toppie_rows = get_result_array($result);

$sql = "SELECT id, title, duration, description, views, upload_time, (SELECT COUNT(*) FROM upload_votes WHERE upload_votes.upload_id = uploads.id AND upload_votes.up = 1)
- (SELECT COUNT(*) FROM upload_votes WHERE upload_votes.upload_id = uploads.id AND upload_votes.up = 0) AS score,
(SELECT COUNT(*) FROM comments WHERE comments.upload_id = uploads.id) AS comments
FROM uploads ORDER BY views DESC LIMIT 12";
$result = $mysqli->query($sql);
$most_views_rows = get_result_array($result);

$sql = "SELECT id, title, duration, description, views, upload_time, (SELECT COUNT(*) FROM upload_votes WHERE upload_votes.upload_id = uploads.id AND upload_votes.up = 1)
- (SELECT COUNT(*) FROM upload_votes WHERE upload_votes.upload_id = uploads.id AND upload_votes.up = 0) AS score,
(SELECT COUNT(*) FROM comments WHERE comments.upload_id = uploads.id) AS comments
FROM uploads WHERE user='-Dolle Pret Nieuws Redacteur-' ORDER BY upload_time DESC LIMIT 12";
$result = $mysqli->query($sql);
$news_rows = get_result_array($result);

$sql = "SELECT id, title, duration, description, views, upload_time, (SELECT COUNT(*) FROM upload_votes WHERE upload_votes.upload_id = uploads.id AND upload_votes.up = 1)
- (SELECT COUNT(*) FROM upload_votes WHERE upload_votes.upload_id = uploads.id AND upload_votes.up = 0) AS score,
(SELECT COUNT(*) FROM comments WHERE comments.upload_id = uploads.id) AS comments
FROM uploads WHERE category=1 ORDER BY upload_time DESC";
$result = $mysqli->query($sql);
$gameplay_rows = get_result_array($result);

$sql = "SELECT id, title, duration, description, views, upload_time, (SELECT COUNT(*) FROM upload_votes WHERE upload_votes.upload_id = uploads.id AND upload_votes.up = 1)
- (SELECT COUNT(*) FROM upload_votes WHERE upload_votes.upload_id = uploads.id AND upload_votes.up = 0) AS score,
(SELECT COUNT(*) FROM comments WHERE comments.upload_id = uploads.id) AS comments
FROM uploads WHERE category=2 ORDER BY upload_time DESC";
$result = $mysqli->query($sql);
$unboxing_rows = get_result_array($result);

$sql = "SELECT id, title, duration, description, views, upload_time, (SELECT COUNT(*) FROM upload_votes WHERE upload_votes.upload_id = uploads.id AND upload_votes.up = 1)
- (SELECT COUNT(*) FROM upload_votes WHERE upload_votes.upload_id = uploads.id AND upload_votes.up = 0) AS score,
(SELECT COUNT(*) FROM comments WHERE comments.upload_id = uploads.id) AS comments
FROM uploads WHERE category=4 ORDER BY upload_time DESC";
$result = $mysqli->query($sql);
$kunst_rows = get_result_array($result);

$sql = "SELECT id, title, duration, description, views, upload_time, (SELECT COUNT(*) FROM upload_votes WHERE upload_votes.upload_id = uploads.id AND upload_votes.up = 1)
- (SELECT COUNT(*) FROM upload_votes WHERE upload_votes.upload_id = uploads.id AND upload_votes.up = 0) AS score,
(SELECT COUNT(*) FROM comments WHERE comments.upload_id = uploads.id) AS comments
FROM uploads WHERE category=3 ORDER BY upload_time DESC";
$result = $mysqli->query($sql);
$vies_rows = get_result_array($result);

$sql = "SELECT id, title, duration, description, views, upload_time, (SELECT COUNT(*) FROM upload_votes WHERE upload_votes.upload_id = uploads.id AND upload_votes.up = 1)
- (SELECT COUNT(*) FROM upload_votes WHERE upload_votes.upload_id = uploads.id AND upload_votes.up = 0) AS score,
(SELECT COUNT(*) FROM comments WHERE comments.upload_id = uploads.id) AS comments
FROM uploads ORDER BY upload_time DESC LIMIT 12";
$result = $mysqli->query($sql);
$fresh_rows = get_result_array($result);

$sql = "SELECT id, title, duration, description, views, upload_time, (SELECT COUNT(*) FROM upload_votes WHERE upload_votes.upload_id = uploads.id AND upload_votes.up = 1)
- (SELECT COUNT(*) FROM upload_votes WHERE upload_votes.upload_id = uploads.id AND upload_votes.up = 0) AS score,
(SELECT COUNT(*) FROM comments WHERE comments.upload_id = uploads.id) AS comments
FROM uploads ORDER BY score LIMIT 12";
$result = $mysqli->query($sql);
$shit_rows = get_result_array($result);

$time = new DateTime();
$time = $time->getTimestamp();

$sql = "SELECT id FROM comments WHERE comments.user = 'Jos Tibant (ROBOT)' AND comments.time < ".($time - 60*60);
$result = $mysqli->query($sql);
$in = "(";
while ($row = $result->fetch_assoc()) {
  if ($in != "(") $in .= ", ";
  $in .= $row["id"];
}
$in .= ")";
$sql = "DELETE FROM comment_votes WHERE comment_votes.comment_id IN $in";
$result = $mysqli->query($sql);
$sql = "DELETE FROM comments WHERE comments.id IN $in";
$result = $mysqli->query($sql);

?>

<script type="text/javascript">
  
  window.carouselMouseDown = false;
  window.carouselWait = 0;
  
  $(document).ready(function() {
<?
    if (isset($_SESSION["lasttab"])) {
      ?>
    $("[href='#<? print($_SESSION["lasttab"]); ?>']").click();
      <?
    }
?>
    
    checkTabsArrows();
    
    setInterval(function() {
      if (!window.carouselMouseDown && window.carouselWait == 0) $('#toppie-uploads').carousel('next');
      if (window.carouselWait > 0) window.carouselWait--;
    }, 3000);
    $("#toppie-uploads").bind('mousedown touchstart', function() {
      window.carouselMouseDown = true;
    }).bind('mouseup touchend', function() {
      window.carouselMouseDown = false;
      window.carouselWait = 2;
    });
    
    $(".tab").click(function() {
      $.ajax("ajax.php?lasttab=" + $(this).find("a").attr("href").substring(1, 3));
    });
  });
  
  function tabLeft() {
    var tabs = $(".tabs");
    tabs.animate( { scrollLeft: '+=200' }, 400);
    checkTabsArrows();
  }
  
  function tabRight() {
    var tabs = $(".tabs");
    tabs.animate( { scrollLeft: '-=200' }, 400);
    checkTabsArrows();
  }
  
  $(window).resize(checkTabsArrows);
  
  function checkTabsArrows() {
    var tabs = $(".tabs");
    var scrollLeft = tabs.scrollLeft();
    var maxScrollLeft = tabs[0].scrollWidth - tabs[0].clientWidth;
    $("#leftArrowTabs").css("display", scrollLeft == maxScrollLeft ? "none" : "block");
    $("#rightArrowTabs").css("display", scrollLeft == 0 ? "none" : "block");
  }
  
</script>
<div class="black" style="margin-top: -70px; height: 70px; width: 100%;"></div>

<div id="toppie-uploads" class="carousel carousel-slider center" data-indicators="true">
  
  <?
  // Carousel met de 6 meest gewaardeerde uploads
  for ($i = 0; $i < 6; $i++) {
    $title = $toppie_rows[$i]["title"];
    $description = $toppie_rows[$i]["description"];
    $id = $toppie_rows[$i]["id"];
    $views = $toppie_rows[$i]["views"];
    $score = $toppie_rows[$i]["score"];
    
    $file = glob("uploads/".$id."/item.*")[0];
    $ext = explode(".", $file)[1];
    
    if (strlen($description) > 120) $description = substr($description, 0, 120)."...";
    ?>
    <a class="carousel-item white-text black" href="view.php?id=<? print($id); ?>">
      
      <center>
        <div class="carousel-thumbnail z-depth-2" style="background-image: url(uploads/<? print($id."/".($ext == "gif" ? "item.gif" : "thumbnail.png")); ?>);">
          <p>
            <i class="material-icons" style="vertical-align: bottom;">thumb_up</i> <? print($score); ?>
            <i class="material-icons" style="vertical-align: bottom;">remove_red_eye</i> <? print($views); ?>
          </p>
        </div>
      </center>
      <h2><? print($title); ?></h2>
      <p class="white-text"><? print($description); ?></p>
      
      <div class="carousel-background" style="background-image: url(uploads/<? print($id); ?>/thumbnail.png);"></div>
      
    </a>
    <?
    
  }
  
  ?>
</div>

<div class="row">
  <? if (!$logged_in): ?>
  <div class="col s12 indigo hide-on-small-only">
    <p class="white-text" style="text-align: center;">
      <i class="material-icons" style="vertical-align: middle;">info_outline</i> Om te kunnen waarderen, reageren en uploaden moet u eerst inloggen<br><br>
      <a class="btn white indigo-text waves-effect" href="login.php"><i class="material-icons right">account_circle</i>aanmelden</a>
    </p>
  </div>
  <? endif; ?>
  <div class="col s12 m7 l7 offset-s0 offset-m0 offset-l1 no-offset">
    
    <ul class="tabs" onscroll="checkTabsArrows()">
      <li class="tab"><a class="waves-effect active" href="#mw"><i class="material-icons" style="vertical-align: middle;">trending_up</i> Meest gewaardeerd</a></li>
      <li class="tab"><a class="waves-effect"href="#pb"><i class="material-icons" style="vertical-align: middle;">new_releases</i> nieuw</a></li>
      <li class="tab"><a class="waves-effect" href="#mb"><i class="material-icons" style="vertical-align: middle;">remove_red_eye</i> Meest bekeken</a></li>
      <li class="tab"><a class="waves-effect" href="#nb"><i class="material-icons" style="vertical-align: middle;">receipt</i> Nieuwsberichten</a></li>
      <li class="tab"><a class="waves-effect" href="#gp"><i class="material-icons" style="vertical-align: middle;">videogame_asset</i> Gameplay</a></li>
      <li class="tab"><a class="waves-effect" href="#ku"><i class="material-icons" style="vertical-align: middle;">photo</i> kunst</a></li>
      <li class="tab"><a class="waves-effect" href="#uv"><i class="material-icons" style="vertical-align: middle;">widgets</i> Unboxing video's</a></li>
      <li class="tab"><a class="waves-effect" href="#v"><i class="material-icons" style="vertical-align: middle;">perm_media</i> Vieze plaatjes</a></li>
      <li class="tab"><a class="waves-effect" href="#mg"><i class="material-icons" style="vertical-align: middle;">trending_down</i> Pretbedervers</a></li>
    </ul>
    
    <a id="leftArrowTabs" onclick="tabLeft()" class="btn-flat waves-effect" style="margin-top: -44px;float: right;background: linear-gradient(to right, transparent, white);"><i class="material-icons black-text">keyboard_arrow_right</i></a>
    <a id="rightArrowTabs" onclick="tabRight()" class="btn-flat waves-effect" style="margin-top: -44px;float: left;background: linear-gradient(to left, transparent, white);"><i class="material-icons black-text">keyboard_arrow_left</i></a>
    
    <div id="mw" class="col s12 no-offset"><? print_thumbnails_and_info($toppie_rows, true); ?>
      <a class="btn white indigo-text waves-effect" style="width: calc(100% - 1.5rem); margin: 0.75rem"
         href="search.php?q=&order-by=score">
        <i class="material-icons right">keyboard_arrow_right</i>alles weergeven
      </a>
    </div>
    <div id="mb" class="col s12 no-offset"><? print_thumbnails_and_info($most_views_rows, true); ?>
      <a class="btn white indigo-text waves-effect" style="width: calc(100% - 1.5rem); margin: 0.75rem"
         href="search.php?q=&order-by=views"><i class="material-icons right">keyboard_arrow_right</i>alles weergeven</a>
    </div>
    <div id="nb" class="col s12 no-offset"><? print_thumbnails_and_info($news_rows, true); ?>
      <a class="btn white indigo-text waves-effect" style="width: calc(100% - 1.5rem); margin: 0.75rem"
         href="search.php?q=&type=news"><i class="material-icons right">keyboard_arrow_right</i>alle nieuwsberichten</a>
    </div>
    <div id="gp" class="col s12 no-offset">
      <span class="col s12">
        Elke dag om 7 uur* een nieuwe gameplay video.
      </span><br>
      <? print_thumbnails_and_info($gameplay_rows, true); ?>
      <br>
      <span class="col s12">
        *niet altijd.
      </span>
    </div>
    <div id="uv" class="col s12 no-offset">
      <? print_thumbnails_and_info($unboxing_rows, true); ?>
    </div>
    <div id="ku" class="col s12 no-offset">
      <? print_thumbnails_and_info($kunst_rows, true); ?>
    </div>
    <div id="v" class="col s12 no-offset">
      <? print_thumbnails_and_info($vies_rows, true); ?>
    </div>
    <div id="pb" class="col s12 no-offset"><? print_thumbnails_and_info($fresh_rows, true); ?>
      <a class="btn white indigo-text waves-effect" style="width: calc(100% - 1.5rem); margin: 0.75rem"
         href="search.php?q=&order-by=upd"><i class="material-icons right">keyboard_arrow_right</i>alles weergeven</a>
    </div>
    <div id="mg" class="col s12 no-offset"><? print_thumbnails_and_info($shit_rows, true); ?>
      <a class="btn white indigo-text waves-effect" style="width: calc(100% - 1.5rem); margin: 0.75rem"
         href="search.php?q&order-by=score&desc=false"><i class="material-icons right">keyboard_arrow_right</i>alle pretbedervers</a>
    </div>
  </div>
  
  <div class="col s12 m5 l3">
    <p class="indigo-text">
      <i class="material-icons" style="vertical-align: middle;">chat_bubble</i>
      LAATSTE REACTIES:
    </p>
    
    <div class="card">
    
    <?
    $sql = "SELECT * FROM comments WHERE user <> 'jos tibant (robot)' ORDER BY time DESC LIMIT 6";
    $result = $mysqli->query($sql);
    $latest_comments = get_result_array($result);

    foreach ($latest_comments as $comment) {
      ?>

    <a href="view.php?id=<? print($comment["upload_id"]."#comment-".$comment["id"]); ?>" class="waves-effect black-text" style="padding: .75rem; width: 100%;">
      <img class="pf" src="<? profile_pic(true, $comment["user"]) ?>" style="margin-right: .75rem; float: left;">
      <i class="material-icons grey-text" style="margin-top: 15px; float: right;">keyboard_arrow_right</i>
      <p style="margin-left: 60px; margin-top: 0;">
        <span class="grey-text"><? print($comment["user"]." <span style='text-transform: lowercase;' data-date='".$comment["time"]."'></span>:"); ?></span>
        <? print(replace_emoticons($comment["text"])); ?>
        <? if ($comment["gif_url"] != "") print(" (<b>GIF</b>)"); ?>
      </p>
    </a>
      
    <div class="divider"></div>

      <?
    }
    ?>
    </div>
    
    <div class="card" style="background: linear-gradient(270deg, #9999ff, #7CE8B1); padding: .75rem;">
      <h5 class="white-text" style="font-weight: 300;">
        <i class="material-icons" style="vertical-align: middle;">chat</i>
        Zin in tjetten en tjillen?
      </h5>
      <p class="white-text" style="font-weight: 400;">
        KOM NAAR DE SUPERKOELE TJILKLUP!
      </p>
      <a class="btn white waves-effect black-text" href="http://www.desuperkoeletjilklub.hol.es" target="_blank"><i class="material-icons left">child_care</i>INLOGGUH</a>
    </div>
  </div>
  
</div>

<?
include("layout/footer.php");
?>