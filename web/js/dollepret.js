$(document).ready(function() {

  if (navigator.appName == 'Microsoft Internet Explorer' ||  !!(navigator.userAgent.match(/Trident/) || navigator.userAgent.match(/rv 11/))) {
    $("body").html("<h6>Deze site werkt niet in Internet Explorer. Gebruik een browser zoals <a href='https://chrome.com/' target='_blank'>Chrome</a>, Firefox of Edge</h6>");
    return;
  }

  sendInfo();

  window.mobile = /Mobi/.test(navigator.userAgent);
  window.navbar = $("nav");
  window.scrollTop = 0;
  window.navbarPos = 0;
  window.searchVisible = false;

  if (window.mobile) {
    $("#scrollbar-css").remove();
    $(".pf").each(function() {
      var pf = $(this);
      pf.attr("src", pf.attr("src").replace("/small", ""));
    })
  }

  $('.button-collapse').sideNav({
    closeOnClick: true,
    draggable: false
  });
  $('ul.tabs').tabs({
//     swipeable: true
  });
  $('.modal').modal();
  $('.materialboxed').materialbox();
  $('select').material_select();
  $('.carousel.carousel-slider').carousel({fullWidth: true});
  $('.collapsible').collapsible({
    accordion : true
  });
  $('.dropdown-button').dropdown();

  $("[data-date]").each(function() {
    var element = $(this);
    element.html(convertTime(element.attr("data-date")));
  });
  $(".thumbnail p").each(function() {
    var element = $(this);
    element.html(secToTime(element.html()));
  });
  $("[data-hover-gif]").each(function() {
    $(this).hover(function() {
      var element = $(this);
      element.attr("data-original-bg", element.css("background-image")).css("background-image", "url(" + element.attr("data-hover-gif") + ")");
    }, function() {
      var element = $(this);
      element.css("background-image", element.attr("data-original-bg"));
    });
  });

  if (window.mobile) $(".tooltipped").each(function () {
    $(this).tooltip("remove");
  });

  window.currentPage = window.location.href.split("dollepret/")[1];
  if (window.currentPage == "") window.currentPage = "index.php";

  if (!window.currentPage.startsWith("login.php") && !window.currentPage.startsWith("register.php")) $("[href='login.php']").each(function () {
    $(this).attr("href", "login.php?ref=" + window.currentPage);
  });

  $(window).scroll(function() {
    var prevNavbarPos = window.navbarPos;
    var prevScrollTop = window.scrollTop;
    window.scrollTop = $(window).scrollTop();
    var diff = window.scrollTop - prevScrollTop;
    window.navbarPos += diff/50;
    window.navbarPos = window.navbarPos > 1 ? 1 : window.navbarPos < 0 ? 0 : window.navbarPos;

    if (!window.searchVisible && window.navbarPos == 1 && prevNavbarPos != 1) window.navbar.css("margin-top", "-100px");
    else if (window.navbarPos == 0 && prevNavbarPos != 0) window.navbar.css("margin-top", "0px");
  });

  window.searchVisible = false;

  $("#search").focusout(function() {
    $("#search-div").css("width", "0%").css("left", "50%");
    setTimeout(function() {
      $("#search-div").attr("style", "");
      window.searchVisible = false;
    }, 200);
  });

  $("#cursor").change(function() {
    $.ajax("ajax.php?setcursor=" + this.checked);
    Materialize.toast(this.checked ? "Genieten geblazen!" : "Jammer", 3000);
    setCursor();
  });
  setCursor();

});

function secToTime(sec) {
  sec = parseInt(sec);
  var h = parseInt(sec/3600);
  sec -= h * 3600;
  var min = parseInt(sec/60);
  sec -= min * 60;
  return (h > 0 ? h + ":" : "") + (min < 10 && h > 0 ? "0" + min : min) + ":" + (sec < 10 ? "0" + sec : sec);
}

function setCursor() {
  var style = $("#cursor-css");
  style.html($("#cursor").prop("checked") ? style.attr("data-content") : "");
}

function showSearch() {
  if (window.searchVisible) return;
  $("#search-div").css("display", "block");
  setTimeout(function() {
    $("#search-div").css("width", "100%").css("left", "0%");
    $("#search").focus();
  }, 1);
  window.searchVisible = true;
  $(window).scroll();
}

function sendInfo() {
  window.parent.postMessage({
    url: window.location.href,
    title: $("title").html(),
    themeColor: $("[name='theme-color']").attr("content")
  }, "*");
}
window.onhashchange = sendInfo;

function convertTime(timestamp) {
  var a = new Date(timestamp * 1000);
  var b = new Date();
  var months = ['januari', 'februari', 'maart', 'april', 'mei', 'juni', 'juli', 'augustus', 'september', 'oktober', 'november', 'december'];
  var year = a.getFullYear();
  var month = months[a.getMonth()];
  var date = a.getDate();
  var hour = a.getHours();
  var min = a.getMinutes();
  var string = '';
  if (year == b.getFullYear()) {
    if (date != b.getDate() || month != months[b.getMonth()]) {
      if (month == months[b.getMonth()] && b.getDate() - date == 1) {
        string += 'Gisteren ';
      } else {
        string += date + ' ' + month + ' ';
      }
    }
  } else {
    string += date + ' ' + month + ' ' + year + ' ';
  }
  if (min < 10) {
    min = '0' + min;
  }
  string += 'om ' + hour + ':' + min;
  return string;
}
