<?
$id = $_GET["id"];
if (empty($id)) {
  header("Location: index.php");
  die();
}

include("init.php");

$id = intval(mysqli_real_escape_string($mysqli, $id));

$sql = "SELECT uploads.*, users.is_admin FROM uploads, users WHERE users.name = uploads.user AND uploads.id = $id";
$result = $mysqli->query($sql);
if (mysqli_num_rows($result) !== 1) {
  header("Location: index.php");
  die();
}

$props = $result->fetch_assoc();
$title = $props["title"];
$orange_main = true;
include("layout/header.php");

$profile_pic_paths = true;
include("jsallusernames.php");

$time = new DateTime();
$time = $time->getTimestamp();

// REACTIE ACHTERLATEN
if (isset($_POST["comment"]) && $logged_in) {
  $comment = substr(mysqli_real_escape_string($mysqli, strip_tags($_POST["comment"])), 0, 512);
  $gif_url = substr(mysqli_real_escape_string($mysqli, $_POST["gif-url"]), 0, 2083);

  if ($_SESSION["prev_comments"][$id] != $comment.$gif_url) {
    $_SESSION["prev_comments"][$id] = $comment.$gif_url;

    $gif_url = explode("?", $gif_url)[0];

    $valid_gif = filter_var($gif_url, FILTER_VALIDATE_URL) && strrpos($gif_url, ".gif") == strlen($gif_url) - 4 && strpos($gif_url, "giphy.com/") !== false;
    if (!$valid_gif) $gif_url = "";

    if ($comment != "" || $gif_url != "") {
      $sql = "INSERT INTO comments (upload_id, user, time, text, gif_url) VALUES ($id, '".$_SESSION["dp_username"]."', $time, '$comment', '$gif_url')";
      $result = $mysqli->query($sql);
    }
  }
}

$file = glob("uploads/$id/item.*")[0];
$ext = explode(".", $file)[1];
$type = $ext == "png" ? "img" : ($ext == "gif" ? "gif" : "vid");

// WAARDERING
$sql = "SELECT count(*) AS ups FROM `upload_votes` WHERE up = 1 AND upload_id = $id";
$result = $mysqli->query($sql);
$row = $result->fetch_assoc();
$ups = intval($row["ups"]);

$sql = "SELECT count(*) AS downs FROM `upload_votes` WHERE up = 0 AND upload_id = $id";
$result = $mysqli->query($sql);
$row = $result->fetch_assoc();
$downs = intval($row["downs"]);

$waardering = $ups - $downs;

$sql = "SELECT up FROM `upload_votes` WHERE user = '".$_SESSION["dp_username"]."' AND upload_id = $id";
$result = $mysqli->query($sql);

$voted = false;
if (mysqli_num_rows($result) == 1) {
  $row = $result->fetch_assoc();
  $myVote = $row["up"];
  $voted = true;
}

// VIEW
$last_viewed = $_SESSION["viewed"][$id];
if (!isset($last_viewed) || $time - $last_viewed > 15) { // er zit minstens 15 sec tussen refresh
  $sql = "UPDATE uploads SET views = views + 1 WHERE id = $id";
  $result = $mysqli->query($sql);
  $props["views"] += 1;
}
$_SESSION["viewed"][$id] = $time;

if ((!isset($last_viewed) || $time - $last_viewed > 120) && rand(0, 100) > 60) {

  $com = "";
  if (rand(0, 10) > 1) {

    $e = array("Ik ga", "Ik hou van", "Zullen we samen", "Wil jij", "Roelof gaat", "Pizzaman houd van", "Kabouter plop gaat", "hilko gaat graag", "wil jij misschien met mij", "me ketting is van me fiets geknapt kan jij ff", "haha wil je niet", "arrays zijne echt leuk", "3");
    $t = array("deze upload NIET rapporteren", "deze upload disliken", "poepen op de maan", "naar de albert hjeien", "een stukje gaan swemmen", "hilko een tik verkopen?");
    $v = array("dat is verplicht", "dat is leuk", "dat is gezellig", "dat is niet zo fantastisch", "Putin is mijn vriend", "roze koeken zijn top", "zwart is een kleur", "hoi", "het is al laat", "dat moet gewoon");
    $vvv = array(" ****!!", " goor rattenjong!", "!!!!", "!", ", nog een vijne dag vandaag.", ", tot morgen.", ", ik stem PVV!", " #yolo", ", roelof is trouwens een echte toffe jongen, doei.", ".", ", is het trouwens nikker of kinker?", " en nog eentje voor piet saman! Een blondje bestelt een pizza. Wanneer de ober vraagt of hij de pizza in zes of in twaalf stukken moet snijden, antwoordt het blondje. In zes stukken alstublieft. Twaalf stukken kan ik nooit op.", " heeft iemand me sleutel gezien?", " auw! me condoom knapte", ", je meoder heeft echt grote voeten", ", net stukje gefietst was echt leuk", ", tjillen en tjetten zijn mijn 2 bijnamen", ", heb ik al gezecht dat ik lezer ogen heb?", " vissen hebben maar 1 hartkamer");

    $com = $e[array_rand($e)] . " " . $t[array_rand($t)] . " want " . $v[array_rand($v)] . $vvv[array_rand($vvv)];
  } else {

    $e = array("Ik vind dit een", "Wat een leuk", "leuk geprobeerd maar een", "Super origineel", "Lelijk", "Artisiek", "Bijzonder", "Wonderbaarlijk", "doet pijn in m'n aars van lelijkheid", "kan je het echt niet beter doen?", "sexueel opwindend");
    $t = array("scheit", "grapig", "saai", "ingewikkeld", "kleurrijk", "aanstootgevend", "opwindend", "interesant", "pijnlijk", "afhankelijk", "politiek", "prachtig", "eerlijk", "fel", "heilig", "druk", "flink", "triest", "gratis", "handig", "depresief", "complex", "puur", "rubbertje", "stevig");

    $com = $e[array_rand($e)] . " " . $t[array_rand($t)] . " " . ($type == "img" ? "beeldmatriaal" : ($type == "vid" ? "filmpje" : "gifje"));
  }

  $gifs = json_decode(file_get_contents("http://api.giphy.com/v1/gifs/trending?api_key=dc6zaTOxFJmzC"), true)["data"];
  $gif = $gifs[array_rand($gifs)]["images"]["downsized"]["url"];

  $sql = "INSERT INTO comments (upload_id, user, time, text, gif_url) VALUES ($id, 'Jos Tibant (ROBOT)', $time, '$com', '$gif')";
  $result = $mysqli->query($sql);

}

// REACTIES

$sql = "SELECT comments.*, users.is_admin, (SELECT COUNT(*) FROM comment_votes WHERE comment_votes.comment_id = comments.id AND comment_votes.up = 1)
- (SELECT COUNT(*) FROM comment_votes WHERE comment_votes.comment_id = comments.id AND comment_votes.up = 0) AS score
FROM comments, users WHERE users.name = comments.user AND comments.upload_id = $id";

$result = $mysqli->query($sql);
$comments = get_result_array($result);

$sql = "SELECT comment_id, up FROM comment_votes WHERE user = '".$_SESSION["dp_username"]."'";
$result = $mysqli->query($sql);
$my_comment_votes = array();
while($row = $result->fetch_assoc()) $my_comment_votes[$row["comment_id"]] = $row["up"];


// SHUFFLE EN VOLGENDE ITEMS
$ids = array();
$sql = "SELECT uploads.id FROM uploads";
$result = $mysqli->query($sql);
while ($row = $result->fetch_assoc()) {
  $ids[] = $row["id"];
}
$shuffle_id = $ids[array_rand($ids)];

$tried = array();
$next_array = array();
$next = "(";
$i = 0;
while ($i < 6) {
  $n = $ids[array_rand($ids)];
  if (!in_array($n, $next_array) && $n != $id && !isset($_SESSION["viewed"][$n])) {
    $next_array[] = $n;
    $next .= ($i == 0 ? "" : ", ").$n;
    $i++;
  } else {
    if (!in_array($n, $tried)) {
      $tried[] = $n;
      if (count($tried) + count($next_array) == count($ids)) // deze man heeft bijna alle uploads al bekeken.
        break;
    }
  }
}
$next .= ")";
if (count($next_array) > 0) {
  $sql = "SELECT id, title, duration, description, views, upload_time, (SELECT COUNT(*) FROM upload_votes WHERE upload_votes.upload_id = uploads.id AND upload_votes.up = 1)
  - (SELECT COUNT(*) FROM upload_votes WHERE upload_votes.upload_id = uploads.id AND upload_votes.up = 0) AS score,
  (SELECT COUNT(*) FROM comments WHERE comments.upload_id = uploads.id) AS comments
  FROM uploads WHERE uploads.id IN ".$next;
  $result = $mysqli->query($sql);
  $next_items = get_result_array($result);
} else $next_items = array();


?>

<script src='https://code.responsivevoice.org/responsivevoice.js'></script>
<script type="text/javascript">

  window.itemCardHeight = 0;
  window.loggedIn = <? print($logged_in ? "true" : "false"); ?>;
  window.itemType = "<? print($type); ?>";
  window.zoomed = false;
  window.lastVolume = <? print(isset($_SESSION["volume"]) ? $_SESSION["volume"] : 1); ?>;
  window.myVote = <? print($voted ? -1 + $myVote * 2 : 0); ?>;
  window.ups = <? print($ups); ?>;
  window.downs = <? print($downs); ?>;
  window.id = <? print($id); ?>;
  window.gifCat = true;

  $(document).ready(function() {
    updateVoteInfo();

    $(".comment").each(function() {
      var comment = $(this);
      var text = comment.find("#ct").html();
      if (text !== undefined) {
        for (var i = 0; i < window.allUsernames.length; i++) {
          var username = window.allUsernames[i];
          var ppp = window.allProfilePicPaths[username].replace("profilepics/", "profilepics/small/");

          var indexOf = text.toLowerCase().indexOf("@" + username);
          if (indexOf > -1) {
            text = text.substr(0, indexOf) + "<div class='comment-chip'><img src='" + ppp + "'>@" + username + "</div>" + text.substr(indexOf + username.length + 1, text.length);
          }
        }
        comment.find("#ct").html(text);
      }
    });

    var winHeight = $(window).height();
    if (window.itemType == "vid" || !window.mobile) $("#item").css("max-height", (winHeight - 150 > 560 ? 560 : winHeight - 150) + "px");
    if (window.mobile) {

      $("#description").css("max-height", "3000px");
      $("#item-card").css("margin-top", "0");

    } else {

      setInterval(function() {
        var prevItemCardHeight = window.itemCardHeight;
        window.itemCardHeight = $("#item-card").height();
        if (prevItemCardHeight != window.itemCardHeight) resizeDN();
      });
      $(window).resize(resizeDN);

    }
    resizeDN();

    <? if ($type == "vid"): ?>
    $("video")[0].volume = <? print(isset($_SESSION["volume"]) ? $_SESSION["volume"] : 1); ?>;

    setInterval(function() {
      var newVolume = $("video")[0].volume;
      if (window.lastVolume != newVolume) {
        $.ajax("ajax.php?setvolume=" + newVolume);
      }
      window.lastVolume = newVolume;
    }, 2000);
    <? endif; ?>

  });

  $(window).bind("hashchange", function() {
    if (window.zoomed && window.location.hash !== "#zoomed") {
      $("#zoomed-item-container").fadeOut(250);
      window.zoomed = false;
      $("body").css("overflow", "auto");
    }
  });

  function updateVoteInfo() {
    var totalVotes = window.ups + window.downs;
    $(".votes-bar.green").css("width", 100 * window.ups / totalVotes + "%").find("p").html(window.ups);
    $(".votes-bar.red").css("width", 100 * window.downs / totalVotes + "%").find("p").html((window.downs == 0 ? "" : "-") + window.downs);
    if (window.myVote == 1) {
      $("#up").addClass("green").find("i").removeClass("green-text");
      $("#down").removeClass("red").find("i").addClass("red-text");
    } else if (window.myVote == -1) {
      $("#down").addClass("red").find("i").removeClass("red-text");
      $("#up").removeClass("green").find("i").addClass("green-text");
    }
  }

  function vote(up) {
    window.nextVote = up ? 1 : -1;
    if (window.nextVote == window.myVote) return;
    $.ajax({
      url: "ajax.php",
      type: "post",
      data: {
        "id": window.id,
        "up": up
      },
      dataType: 'text',
      success: function(response) {
        if (response == "SUCCESS") {
          if (window.myVote == -1) window.downs--;
          else if (window.myVote == 1) window.ups--;
          window.myVote = window.nextVote;
          if (window.myVote == 1) window.ups++;
          else window.downs++;
          updateVoteInfo();
        } else if (response == "nietminnen") {

          if (window.myVote == -1) window.downs--;
          else if (window.myVote != 1) window.ups++;
          window.myVote = 1;
          updateVoteInfo();

          $("body").append($("<img style='position: fixed;z-index: 9999999999999;height: 100%;margin-top: -110%;' src='data/nietminnen.png'>").animate({
            "margin-top": "200%"
          }, {
            duration: 10000,
            complete: function() {
              $("[src='data/nietminnen.png']").remove();
            }
          }));

        } else {

          if (window.mobile) {
            Materialize.toast(response, 10000);
          } else {
            var modal = $('#error-modal');
            modal.find("div").find("p").html(response.replace("<a", "<br><a")).find("a").addClass("btn white indigo-text waves-effect");
            modal.modal('open');
          }

        }
      }
    });
  }

  function voteComment(id, up) {

    var parent = $("#comment-score-" + id).parent();
    if (up) {
      parent.find("#c-up").addClass("green").find("i").removeClass("green-text");
      parent.find("#c-down").removeClass("red").find("i").addClass("red-text");
    } else {
      parent.find("#c-down").addClass("red").find("i").removeClass("red-text");
      parent.find("#c-up").removeClass("green").find("i").addClass("green-text");
    }

    $.ajax({
      url: "ajax.php",
      type: "post",
      data: {
        "comment-id": id,
        "c-up": up,
        "id": window.id
      },
      dataType: 'text',
      success: function(response) {
        if (response.startsWith("SUCCESS")) {

          response = response.split(" ");
          $("#comment-score-" + response[2]).html(response[1]);

        } else {

          if (window.mobile) {
            Materialize.toast(response, 10000);
          } else {
            var modal = $('#error-modal');
            modal.find("div").find("p").html(response.replace("<a", "<br><a")).find("a").addClass("btn white indigo-text waves-effect");
            modal.modal('open');
          }

        }
      }
    });
  }

  function zoom() {
    if (window.mobile) {
      window.parent.location = "http://infgc.nl/h16hilko/dollepret/" + $("#item").attr("src");
      return;
    }
    window.location.hash = "zoomed";
    $("body").css("overflow", "hidden");
    $("#zoomed-item-container").fadeIn(250);
    window.zoomed = true;
  }

  function zoomOut() {
    if (window.zoomed) window.history.back();
  }

  function shareToolbar() {
    $("#share-toolbar").find("li").each(function(){
      $(this).css("display", "none");
    });
    setTimeout(function() {
        $("#share-toolbar").find("li").each(function(){
          $(this).fadeIn(400);
        });
      }, 300);
  }

  function resizeDN() {
    console.log("pssst hoi!");
    var medSmall = $("body").width() < 989;

    if (medSmall) $("#comments").removeClass("no-offset");
    else $("#comments").addClass("no-offset");

    var desc = $("#description");
    var itemCardHeight = $("#item-card").height();
    desc.css("max-height", (medSmall ? 3000 : itemCardHeight - 139) + "px");
    var mt = medSmall ? 0 : $("#info-col").height() - itemCardHeight - 33;
    $("#next-items").css("margin-top", mt + "px");
  }

  function gifSearch(q) {
    if (window.gifCat) {
      window.gifCatHTML = $(".gif-results").parent().html();
      window.gifCat = false;
    }

    $.ajax({
      url: "http://api.giphy.com/v1/gifs/search?api_key=dc6zaTOxFJmzC&q=" + q,
      dataType: "json",
      success: function(json) {
        console.log("Giphy result:");
        console.log(json);

        var col0 = $("#gif-results-0").html("");
        var col1 = $("#gif-results-1").html("");
        var col0Height = 0;
        var col1Height = 0;

        for (i = 0; i < json.data.length; ++i) {
          var still = json.data[i].images.downsized_still;
          var imgHeight = (parseInt(still.height) / parseInt(still.width));
          var div = $("<div><img class='still' src='" + still.url + "'></div>").click(selectGif).append($("<img class='full-gif' style='display: none' src='" + json.data[i].images.downsized.url + "'>").load(function() {
            $(this).css("display", "inline-block").parent().find(".still").remove();
          }));
          if (col0Height > col1Height) {
            col1.append(div);
            col1Height += imgHeight;
          } else {
            col0.append(div);
            col0Height += imgHeight;
          }
        }

      }
    });

  }

  function gifSearchBack() {
    if (window.gifCat) {
      $("#giphy").modal("close");
      return;
    }
    $(".gif-results").parent().html(window.gifCatHTML);
    window.gifCat = true;
    playGifCat();
  }

  function playGifCat() {
    $('#giphy video').each(function() {
      this.play()
    });
  }

  function selectGif() {
    var src = $(this).find(".full-gif").attr("src");
    $("[name='gif-url']").val(src);
    $("#gif-preview").css("height", "200px").css("margin-bottom", "12px").find("img").attr("src", src);
    $("#giphy").modal("close");
    checkCommentValid();
  }

  function checkCommentValid() {
    if (!window.loggedIn) {
      var modal = $('#error-modal');
      modal.find("div").find("p").html(
        "U moet eerst inloggen om te kunnen reageren.<br>Excuus voor het ongemak.<br><a href='login.php?ref=view.php?id=" + window.id + "' class='btn white indigo-text waves-effect'>Inloggen</a>"
      );
      modal.modal('open');
      return false;
    }
    var valid = $("[name='gif-url']").val().length > 0 || $("#comment").val().length > 0;
    if (valid) $("#comment-submit").removeClass("disabled");
    else $("#comment-submit").addClass("disabled");
    return valid;
  }

  function showLoader() {
    $("#comment-form").append($('<div class="progress"><div class="indeterminate"></div></div>'));
  }

  function toggleGif(gif) {
    gif = $(gif);
    var gifIcon = gif.find("div");
    var still = gif.find(".still");
    var full = gif.find(".full-gif");
    var play = !(still.css("display") == "none");
    gifIcon.css("display", play ? "none" : "block");
    if (play) {
      if (full.length == 0) {
        still.css("opacity", .3);
        gif.append($("<img class='full-gif' style='display: none;' src='" + still.attr("src").replace("_s.gif", ".gif") + "'>").load(function() {
          $(this).css("display", "block").parent().find(".still").css("display", "none");
        }));
      } else {
        full.css("display", "block");
        still.css("display", "none");
      }
    } else {
      still.css("display", "block").css("opacity", 1);
      full.css("display", "none");
    }
  }

  function reply(user) {
    var commentInput = $("#comment");
    window.scrollTo(0, commentInput.offset().top - 200);
    var newVal = "@" + user + " ";
    commentInput.val(newVal);
    commentInput[0].selectionStart = commentInput[0].selectionEnd = newVal.length;
    commentInput.focus();
  }

</script>

<div id="error-modal" class="modal">
  <div class="modal-content">
    <h5>
      <i class="material-icons" style="vertical-align: top;">error_outline</i> Fout
    </h5>
    <p></p>
  </div>
</div>

<div class="row">
  <div id="item-card" class="col s12 m10 l6 offset-l1 offset-m1 offset-s0 card">
    <? if ($type == "vid"): ?>
    <video loop preload="auto" id="item" controls autoplay src="<? print($file); ?>">
      Uw browser ondersteunt geen video player. Gebruik een browser zoals Chrome, Firefox of Edge
    </video>
    <? else: ?>
    <center onclick="zoom()" style="cursor: -webkit-zoom-in !important;"><img id="item" src="<? print($file); ?>"></center>
    <div id="zoomed-item-container" onclick="zoomOut()">
      <img id="zoomed-item" src="<? print($file); ?>">
    </div>
    <? endif; ?>

    <h5 style="word-break: break-word;">
      <? print($props["title"]); ?>
    </h5>


    <div>

      UW WAARDERING:
      <a id="up" onclick="vote(1)" class="btn-floating btn-flat waves-effect tooltipped" data-position="bottom" data-delay="50" data-tooltip="Dit waardeer ik">
        <i class="material-icons green-text">exposure_plus_1</i>
      </a>
      <a id="down" onclick="vote(0)" class="btn-floating btn-flat waves-effect tooltipped" data-position="bottom" data-delay="50" data-tooltip="Dit minacht ik">
        <i class="material-icons red-text">exposure_neg_1</i>
      </a>

      <div style="width: 210px; height: 3px; margin-top: 24px;">
        <div class="green votes-bar" style="float: left;"><p><? print($ups); ?></p></div>
        <div class="red votes-bar" style="float: right;"><p><? print($downs); ?></p></div>
      </div>

    </div>


    <p class="grey-text">
      <i class="material-icons" style="vertical-align: bottom;">remove_red_eye</i>
      <? print($props["views"]); ?> WEERGAVEN
      <br>
      <i class="material-icons" style="vertical-align: bottom;">event</i>
      GE&Uuml;PLOAD <span style="text-transform: uppercase;" data-date="<? print($props["upload_time"]); ?>"></span>
    </p>

    <div class="divider"></div>

    <div style="margin: .75rem 0;">
      <? if ($_SESSION["dp_admin"]): ?>
      <a class="btn-flat waves-effect" href="deleteitem.php?id=<? print($id); ?>" onclick="return confirm('Geachte administrator, weet u zeker dat u dit item definitief wilt verwijderen?')">
        <i class="material-icons left">delete_forever</i>
        verwijderen
      </a>
      <? endif; ?>
      <a href="#report-modal" class="red-text btn-flat waves-effect tooltipped" data-position="bottom" data-delay="50" data-tooltip="Ongepaste content melden">
        <i class="material-icons left">flag</i>Rapporteren
      </a>
      <a href="view.php?id=<? print($shuffle_id); ?>" class="btn-flat waves-effect tooltipped" data-position="bottom" data-delay="50" data-tooltip="Willekeurige post bekijken">
        <i class="material-icons left">shuffle</i>shuffle
      </a>
    </div>

    <div id="report-modal" class="modal">
        <div class="modal-content">
          <h5>Rapporteren</h5>
          <form id="report-form" action="report.php" method="post" class="row">
            <input type="hidden" name="id" value="<? print($id); ?>">

            <p>
              <input name="cat" type="radio" id="aanstootgevend" value="aanstootgevend" checked onclick="$('#reason-label').html('Waarom is dit aanstootgevend?')"/>
              <label for="aanstootgevend">Dit is aanstootgevend</label>
            </p>
            <p>
              <input name="cat" type="radio" id="nepnieuws" value="nepnieuws" onclick="$('#reason-label').html('Waarom is dit nepnieuws? (kijk op NOS.nl voor voorbeelden van nepnieuws)')"/>
              <label for="nepnieuws">Dit is nepnieuws</label>
            </p>
            <p>
              <input name="cat" type="radio" id="spam" value="spam" onclick="$('#reason-label').html('Waarom is dit spam?')"/>
              <label for="spam">Dit is spam</label>
            </p>

            <p id="reason-label">Waarom is dit aanstootgevend?</p>
            <textarea form="report-form" id="reason" name="reason" class="materialize-textarea" maxlength="1024"></textarea>

            <button class="modal-action waves-effect waves-red btn-flat red-text">
              <i class="material-icons left">flag</i>
              Rapporteren
            </button>

          </form>
        </div>
      </div>

  </div>

  <div id="info-col" class="col s12 m10 l4 offset-l0 offset-m1 offset-s0">
    <div class="col s12 m12 l12 card" style="margin-bottom: 6px;">
      <img class="pf" src="<? profile_pic(true, $props["user"]); ?>" style="margin: 6px 10px 6px 0px; vertical-align: middle; float: left;">
      <p style="margin: 6px;">
        <span style="color: grey;">GE&Uuml;PLOAD DOOR</span><br>
        <span class="indigo-text" style="font-weight: 500;"><? print($props["user"].($props["is_admin"] == 1 ? " <i class='material-icons indigo-text tooltipped' data-position='bottom' data-delay='50' data-tooltip='Erkend hoogwaardige administrator' style='vertical-align: bottom;'>verified_user</i>" : "")); ?></span>
      </p>
    </div>
    <div class="col s12 m12 l12 card" style="margin-bottom: 6px; overflow: auto; max-height: 52px;" id="description">
      <h5>
        Descriptie
      </h5>

      <p>
        <? print(nl2br(replace_emoticons(create_links($props["description"])))); ?>
      </p>

      <div class="divider"></div>

      <div style="margin: .75rem 0;">
        <button class="indigo-text btn-flat waves-effect" onclick="responsiveVoice.speak($('#description').find('p').html().replace(/<\/?[^>]+(>|$)/g, ''), 'Dutch Female')">
          <i class="material-icons left">volume_up</i>
          Voorlezen &#10279;&#10261;&#10261;&#10263;&#10247;&#10257;&#10293;&#10257;&#10269;
        </button>
      </div>

    </div>

    <div class="col s12 m12 l12 card share-upload hide-on-med-and-down" style="margin-bottom: 6px; padding: 6px .75rem;">
      <a class="btn-floating btn-flat waves-effect tooltipped" data-position="bottom" data-delay="50" data-tooltip="Delen op Facebook"
         onclick="window.open('https://www.facebook.com/sharer/sharer.php?u=http://dollepret.pe.hu/view.php?id=<? print($id); ?>', 'newwindow', 'width=400, height=350')" href="#">
        <img src="data/socmedicons/fb.png">
      </a>
      <a class="btn-floating btn-flat waves-effect tooltipped" data-position="bottom" data-delay="50" data-tooltip="Delen op Google+"
         onclick="window.open('https://plus.google.com/share?url=http://dollepret.pe.hu/view.php?id=<? print($id); ?>', 'newwindow', 'width=400, height=450')" href="#">
        <img style="height: 68.3%;" src="data/socmedicons/gp.png">
      </a>
      <a class="btn-floating btn-flat waves-effect tooltipped" data-position="bottom" data-delay="50" data-tooltip="Delen op Twitter"
         onclick="window.open('https://twitter.com/intent/tweet?url=http://dollepret.pe.hu/view.php?id=<? print($id); ?>&text=Een site boordevol amusement!', 'newwindow', 'width=500, height=280')" href="#">
        <img src="data/socmedicons/tw.png">
      </a>
    </div>

    <div class="fixed-action-btn toolbar hide-on-large-only">
      <a class="btn-floating btn-large" onclick="shareToolbar()">
        <i class="large material-icons">share</i>
      </a>
      <ul id="share-toolbar">
        <li class="waves-effect waves-light">
          <a onclick="window.open('https://www.facebook.com/sharer/sharer.php?u=http://dollepret.pe.hu/view.php?id=<? print($id); ?>', 'newwindow', 'width=400, height=350')" href="#">
            <img src="data/socmedicons/fbw.png">
          </a>
        </li>

        <li class="waves-effect waves-light">
          <a style="text-align: center;" onclick="window.open('https://plus.google.com/share?url=http://dollepret.pe.hu/view.php?id=<? print($id); ?>', 'newwindow', 'width=400, height=450')" href="#">
            <img style="height: 51.2%;" src="data/socmedicons/gp.png">
          </a>
        </li>
        <li class="waves-effect waves-light">
          <a onclick="window.open('https://twitter.com/intent/tweet?url=http://dollepret.pe.hu/view.php?id=<? print($id); ?>&text=Een site boordevol amusement!', 'newwindow', 'width=500, height=280')" href="#">
            <img src="data/socmedicons/tww.png">
          </a>
        </li>
        <li class="waves-effect waves-light">
          <a href="whatsapp://send?text=http://dollepret.pe.hu/view.php?id=<? print($id); ?>" data-action="share/whatsapp/share">
            <img src="data/socmedicons/wa.png">
          </a>
        </li>
      </ul>
    </div>
  </div>

</div>

<div class="row">
  <div id="comments" class="col s12 m10 l6 offset-l1 offset-m1 offset-s0 no-offset" style="margin-top: -20px;">
    <div class="col s12 card" >
      <h5>
        Reacties (<? print(count($comments)); ?>)
      </h5>

      <form id="comment-form" method="post">
        <div class="input-field">
          <img class="pf" style="margin-left: 6px;" src="<? profile_pic(true, $logged_in ? $_SESSION["dp_username"] : "55555"); ?>">
          <textarea form="comment-form" id="comment" name="comment" onkeydown="setTimeout(checkCommentValid, 1)" class="materialize-textarea" maxlength="512" style="
            margin-left: 64px;
            margin-top: -48px;
            width: calc(100% - 64px);
            height: 20.6px;
          "></textarea>
          <label for="comment" style="margin-left: 64px;">Schrijf een reactie</label>
          <div id="gif-preview" style="transition: .3s; height: 0; width: calc(100% - 64px); margin-left: 64px;">
            <img style="max-width: 100%; height: 100%;">
          </div>
        </div>
        <input type="hidden" name="gif-url">
        <div style="text-align: right;">
          <a class="btn-flat waves-effect" href="#giphy" onclick="playGifCat()"><i class="material-icons left">collections</i>.GIF</a>
          <button id="comment-submit" type="submit" onclick="showLoader()" class="btn white indigo-text waves-effect disabled" ><i class="material-icons left">send</i>Plaatsen</button>
        </div>
      </form>

      <ul class="collection">
        <?
        foreach ($comments as $comment) {
          ?>
          <li class="collection-item comment" id="comment-<? print($comment["id"]); ?>">
            <img class="pf" src="<? profile_pic(true, $comment["user"]); ?>">
            <? if ($comment["text"] != ""): ?>
            <p id="ct">
              <? print(nl2br(replace_emoticons(create_links($comment["text"])))); ?>
            </p>
            <?
            endif;
            if ($comment["gif_url"] != ""):
            ?>
            <div class="gif" onclick="toggleGif(this)">
              <img class="still" src="<? print(str_replace(".gif", "_s.gif", $comment["gif_url"]));?>">
              <div>
                <i class="material-icons white-text">gif</i>
              </div>
            </div>
            <? endif; ?>
            <p class="grey-text">
              <? print($comment["user"]
                       .($comment["is_admin"] == 1 ? " <i class='material-icons indigo-text tooltipped' data-position='bottom' data-delay='50' data-tooltip='Erkend hoogwaardige administrator' style='vertical-align: bottom;'>verified_user</i>" : "")
                       .($comment["user"] == "Jos Tibant (ROBOT)" ? " <i class='material-icons teal-text tooltipped' data-position='bottom' data-delay='50' data-tooltip='Officiele robot van Dolle Pret' style='vertical-align: bottom;'>build</i>" : "")
                       ." &#xb7; <span data-date='".$comment["time"]."'></span>"); ?>
            </p>
            <p class="comment-votes grey-text">
              <a id="c-up" class="<? if ($my_comment_votes[$comment["id"]] == "1") print("green"); ?> btn-floating btn-flat waves-effect" onclick="voteComment(<? print($comment["id"]); ?>, 1)">
                <i class="<? if ($my_comment_votes[$comment["id"]] != "1") print("green-text"); ?> material-icons">exposure_plus_1</i>
              </a>
              <a id="c-down" class="<? if ($my_comment_votes[$comment["id"]] == "0") print("red"); ?> btn-floating btn-flat waves-effect" onclick="voteComment(<? print($comment["id"]); ?>, 0)">
                <i class="<? if ($my_comment_votes[$comment["id"]] != "0") print("red-text"); ?> material-icons">exposure_neg_1</i>
              </a>

              <span id="comment-score-<? print($comment["id"]); ?>"><? print($comment["score"] >= 0 ? "+".$comment["score"] : $comment["score"]); ?></span>

              <a class="btn-floating btn-flat waves-effect tooltipped" data-position="bottom" data-delay="50" data-tooltip="Reageer op <? print($comment["user"]); ?>"
                 onclick="reply('<? print($comment["user"]); ?>')">
                  <i class="indigo-text material-icons">reply</i>
              </a>

              <? if ($_SESSION["dp_username"] == $comment["user"] || $_SESSION["dp_admin"]): ?>
              <a class="btn-floating btn-flat waves-effect" onclick="return confirm('Wilt u deze reactie verwijderen?')" href="deletecomment.php?id=<? print($comment["id"]."&ref=".$id); ?>">
                <i class="red-text material-icons">delete</i>
              </a>
              <? endif; ?>

            </p>
          </li>
          <?
        }
        ?>
      </ul>

    </div>

  </div>

  <div id="next-items" class="col s12 m10 l4 offset-l0 offset-m1 offset-s0">
    <p class="white-text" style="margin-left: 10px;">
      <i class="material-icons" style="vertical-align: bottom;">playlist_play</i>DIT HEEFT U NOG NIET GEZIEN:
    </p>
    <?
    print_thumbnails_and_info($next_items, false);

    if (count($next_items) == 0) {
      print("<img src='https://media.giphy.com/media/Hwq45iwTIUBGw/giphy.gif' style='max-width: 100%;'><br><p class='white-text'>U heeft alle bestaande uploads al bekeken.<br>We zien u graag een andere keer weer terug wanneer er weer nieuwe uploads zijn</p>");
    }

    ?>
  </div>

</div>

<div id="giphy" class="modal bottom-sheet" style="height: 100%; max-height: none !important; max-width: 600px;">
  <div class="modal-content">
    <a class="btn-floating btn-flat waves-effect" onclick="gifSearchBack()"><i class="material-icons left black-text">arrow_back</i></a>
    <div class="input-field" style="display: inline-block; width: calc(100% - 100px);">
      <input id="search-gif" type="text" onkeydown="if (event.keyCode == 13) gifSearch($(this).val())">
      <label for="search-gif">Zoeken</label>
    </div>
    <a class="btn-floating btn-flat waves-effect" onclick="gifSearch($('#search-gif').val())"><i class="material-icons left black-text">search</i></a>

    <div class="row" style="height: 100%; overflow-y: auto;">
      <div id="gif-results-0" class="gif-results col s6">

        <div onclick="gifSearch('evil kermit')">
          <img src="http://media2.giphy.com/media/3oriNSf2iLjMVO7dao/giphy.gif">
          <h6>
            Kermit
          </h6>
        </div>
        <div onclick="gifSearch('agree')">
          <img src="http://media2.giphy.com/media/k48soGtCrLqZq/giphy-downsized.gif">
          <h6>
            Mee eens
          </h6>
        </div>
        <div onclick="gifSearch('irritated')">
          <img src="http://media2.giphy.com/media/ql4LidslabKpi/giphy-downsized.gif">
          <h6>
            Ge&#207;rriteerd
          </h6>
        </div>
        <div onclick="gifSearch('weird')">
          <img src="http://media2.giphy.com/media/6Eyrpl2cuOCvm/giphy-downsized.gif">
          <h6>
            Apart
          </h6>
        </div>
        <div onclick="gifSearch('bert ernie')">
          <img src="http://media2.giphy.com/media/lKGT2KiIV50DC/giphy-downsized.gif">
          <h6>
            Bert en ernie
          </h6>
        </div>

      </div>
      <div id="gif-results-1" class="gif-results col s6">

        <div onclick="gifSearch('pleased excited')">
          <img src="http://media2.giphy.com/media/WfEwBPHoHVp6/giphy.gif">
          <h6>
            Verheugd
          </h6>
        </div>
        <div onclick="gifSearch('animal')">
          <img src="http://media2.giphy.com/media/E6AKG0k1r2vx6/giphy-downsized.gif">
          <h6>
            Dierlijk
          </h6>
        </div>
        <div onclick="gifSearch('shocked')">
          <img src="http://media2.giphy.com/media/3o7TKshA1b3tp0Y20U/giphy-downsized.gif">
          <h6>
            Geschokt
          </h6>
        </div>
        <div onclick="gifSearch('applause')">
          <img src="http://media2.giphy.com/media/l0K4m0mzkJDAIdhHW/giphy.gif">
          <h6>
            Applaus
          </h6>
        </div>

      </div>
    </div>

  </div>
</div>

<?
$rvCredit = true;
include("layout/footer.php");
?>
