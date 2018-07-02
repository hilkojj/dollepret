<!DOCTYPE HTML>
<html>

  <head>
    <script type="text/javascript" src="js/jquery-2.2.2.min.js"></script>
    <script type="text/javascript" src="js/croppie.min.js"></script>
    <link type="text/css" rel="stylesheet" href="css/croppie.css">
  </head>
  
  <body>
  
    <script type="text/javascript">
      $(document).ready(function() {
        window.c = $("#croppie").croppie({
          viewport: {
            width: 200,
            height: 200,
            type: "circle"
          },
          boundary: {
            height: 300
          },
          enableOrientation: true
        });
        
        var pics = ["https://encrypted-tbn3.gstatic.com/images?q=tbn:ANd9GcSlDhUSBw81XMyLS_W3qt4ed82Bf58a5RGfL0b4lu2knShC_efOEg",
                   "http://media.nu.nl/m/8c0xbyfapc4l_wd640.jpg/duitser-doet-alsof-hond-schaap-belasting-ontwijken.jpg"];

        window.c.croppie("bind", {
          url: pics[Math.floor(Math.random() * pics.length)],
          orientation: 1
        });

      });
    </script>
    
    <div id="croppie">
      
    </div>
  
  </body>
</html>
  