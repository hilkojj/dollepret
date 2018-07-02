<script type="text/javascript">
  window.allUsernames = [<?
  
  $names = array();
    
  $sql = "SELECT name FROM users";
  $result = $mysqli->query($sql);
  $first = true;
  while ($row = $result->fetch_assoc()) {
    if (!$first) print(", ");
    $first = false;
    $names[] = $row["name"];
    print("'".strtolower($row["name"])."'");
  }

  ?>];
  
  <? 
  if ($profile_pic_paths) {
    print("window.allProfilePicPaths = {");
    $first = true;
    foreach ($names as $name) {
      if (!$first) print(", ");
      $first = false;
      print("'".strtolower($name)."': '");
      profile_pic(false, $name);
      print("'");
    }
    print("};");
  }
  ?>
</script>