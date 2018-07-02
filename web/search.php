<?
include("init.php");
$q = $_GET["q"];
if (!isset($q)) {
  header("Location: index.php");
  die();
}

$q = mysqli_real_escape_string($mysqli, strip_tags($q));
$type = isset($_GET["type"]) ? $_GET["type"] : "all";
$order_by = $_GET["order-by"];
$upload_date = isset($_GET["date"]) ? $_GET["date"] : "alw";
$desc = !($_GET["desc"] == "false");
$p = isset($_GET["p"]) ? intval($_GET["p"]) : 1;
$offset = ($p - 1) * 16;

$sql = "SELECT id, title, duration, description, views, upload_time, user, (SELECT COUNT(*) FROM upload_votes WHERE upload_votes.upload_id = uploads.id AND upload_votes.up = 1)
- (SELECT COUNT(*) FROM upload_votes WHERE upload_votes.upload_id = uploads.id AND upload_votes.up = 0) AS score,
(SELECT COUNT(*) FROM comments WHERE comments.upload_id = uploads.id) AS comments
FROM uploads 
WHERE (";
if ($q == "") {
  
  $sql .= "true";
    
} else {
  
  $words = explode(" ", $q);
  $first = true;
  foreach ($words as $word) {
    if (!$first) $sql .= "OR ";
    $first = false;
    $sql .= "title LIKE '%".$word."%' OR description LIKE '%".$word."%' OR user LIKE '%".$word."%' ";
  }  
  
}

$sql .= ")";

if ($type != "all") {
  if ($type == "news") $sql .= " AND user = '-Dolle Pret Nieuws Redacteur-'";
  else $sql .= " AND duration ".($type == "vids" ? ">" : "=")." 0";
}

if (isset($upload_date) && $upload_date != "alw") {
  $date = new DateTime();
  $day = 60*60*24;
  $minimal_time = $date->getTimestamp();
  if ($upload_date == "tod") $minimal_time -= $day;
  else if ($upload_date == "wee") $minimal_time -= $day * 7;
  else if ($upload_date == "mon") $minimal_time -= $day * 30;
  $sql .= " AND upload_time > ".$minimal_time;
}

if (isset($order_by))
  $sql .= " ORDER BY ".($order_by == "score" ? "score" : ($order_by == "views" ? "views" : ($order_by == "comments" ? "comments" : "upload_time"))).($desc ? " DESC" : "");

$sql .= " LIMIT 17";
if ($offset > 0) $sql .= " OFFSET ".$offset;

$result = $mysqli->query($sql);
$rows = get_result_array($result);

$count = count($rows);
if ($count == 17) unset($rows[16]);

$result_title = $q == "" ? "Uploads" : "Resultaten voor '".$q."'";
$title = $q == "" ? "Uploads" : $q." - zoeken";
include("layout/header.php");
?>

<div id="filter-modal" class="modal bottom-sheet">
  <div class="modal-content">
    
    <form id="filter-form" action="search.php" method="get" class="row">
      <input type="hidden" name="q" value="<? print($q); ?>">
      <div class="col s12 m4 l3">
        <h6>
          Zoeken naar:
        </h6>
        <p>
          <input name="type" type="radio" id="all" value="all" <? if ($type == "all") print("checked"); ?>/>
          <label for="all">Alles</label>
        </p>
        <p>
          <input name="type" type="radio" id="vids" value="vids" <? if ($type == "vids") print("checked"); ?>/>
          <label for="vids">Video's</label>
        </p>
        <p>
          <input name="type" type="radio" id="imgs" value="imgs" <? if ($type == "imgs") print("checked"); ?>/>
          <label for="imgs">GIF's/Afbeeldingen</label>
        </p>
        <p>
          <input name="type" type="radio" id="news" value="news" <? if ($type == "news") print("checked"); ?>/>
          <label for="news">Nieuwsberichten</label>
        </p>
      </div>
      <div class="col s12 m4 l3">
        <h6>
          Sorteren op:
        </h6>
        <p>
          <input name="order-by" type="radio" id="upd" value="upd" <? if ($order_by == "upd") print("checked"); ?>/>
          <label for="upd">Uploaddatum</label>
        </p>
        <p>
          <input name="order-by" type="radio" id="score" value="score" <? if ($order_by == "score") print("checked"); ?>/>
          <label for="score">Waardering</label>
        </p>
        <p>
          <input name="order-by" type="radio" id="views" value="views" <? if ($order_by == "views") print("checked"); ?>/>
          <label for="views">Aantal weergaven</label>
        </p>
        <p>
          <input name="order-by" type="radio" id="comments" value="comments" <? if ($order_by == "comments") print("checked"); ?>/>
          <label for="comments">Aantal reacties</label>
        </p>
        
      </div>
      <div class="col s12 m4 l3">
        <h6>
          Volgorde:
        </h6>
        <p>
          <input name="desc" type="radio" id="dt" value="true" <? if ($desc) print("checked"); ?>/>
          <label for="dt">Aflopend</label>
        </p>
        <p>
          <input name="desc" type="radio" id="df" value="false" <? if (!$desc) print("checked"); ?>/>
          <label for="df">Oplopend</label>
        </p>
        
      </div>
      <div class="col s12 m4 l3">
        <h6>
          Uploaddatum:
        </h6>
        <p>
          <input name="date" type="radio" id="tod" value="tod" <? if ($upload_date == "tod") print("checked"); ?>/>
          <label for="tod">Afgelopen 24 uur</label>
        </p>
        <p>
          <input name="date" type="radio" id="wee" value="wee" <? if ($upload_date == "wee") print("checked"); ?>/>
          <label for="wee">Afgelopen 7 dagen</label>
        </p>
        <p>
          <input name="date" type="radio" id="mon" value="mon" <? if ($upload_date == "mon") print("checked"); ?>/>
          <label for="mon">Afgelopen 30 dagen</label>
        </p>
        <p>
          <input name="date" type="radio" id="alw" value="alw" <? if ($upload_date == "alw") print("checked"); ?>/>
          <label for="alw">Altijd</label>
        </p>
      </div>
    </form>
    
  </div>
</div>


<div class="row">
  <div class="col s12 m10 l8 offset-s0 offset-m1 offset-l2">
    <h5>
      <? print($result_title); ?>
    </h5>
    <p>
      <? if ($p > 1) print("Pagina ".$p."<br>"); ?>
      <? print($count == 17 ? "Meer dan 1 pagina " : ($count == 0 ? "Geen" : $count).($count == 1 ? " resultaat" : " resultaten")); ?> gevonden.
    </p>
    <a onclick="$('#apply-filters').css('display', 'block');" class="btn white indigo-text waves-effect" href="#filter-modal"><i class="material-icons left">filter_list</i>filters</a>
    <br><br>
    <a onclick="$('#filter-form').submit();" id="apply-filters" class="btn waves-effect" style="display: none;"><i class="material-icons left">check</i>filters toepassen</a>
  </div>
  <div class="col s12 m10 l8 offset-s0 offset-m1 offset-l2 no-offset">
    <? print_thumbnails_and_info($rows, true); ?>
  </div>
  <div class="col s12 m10 l8 offset-s0 offset-m1 offset-l2">
    <br>
    <? 
    
    $path = "search.php?".$_SERVER['QUERY_STRING'];
    $prev_path = str_replace("p=".$p, "p=".($p - 1), $path);
    if (isset($_GET["p"])) $next_path = str_replace("p=".$p, "p=".($p + 1), $path);
    else $next_path = $path."&p=".($p + 1);
    
    if($p > 1): 
    ?>
    <a class="btn waves-effect white indigo-text" href="<? print($prev_path); ?>"><i class="material-icons left">keyboard_arrow_left</i>Pagina <? print($p + -1); ?></a>
    <? endif;?>
    <? if ($count == 17): ?>
    <a class="btn waves-effect" href="<? print($next_path); ?>"><i class="material-icons right">keyboard_arrow_right</i>Pagina <? print($p + 1); ?></a>
    <? endif; ?>
  </div>
</div>

<?
include("layout/footer.php");
?>