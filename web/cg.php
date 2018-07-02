<?


for ($i = 0; $i < 100; $i++) {
  if (rand(0, 1)) {
    $e = array("Ik ga", "Ik hou van", "Zullen we samen", "Wil jij", "Roelof gaat", "Pizzaman houd van", "Kabouter plop gaat");
    $t = array("deze upload rapporteren", "deze upload disliken", "poepen op de maan", "naar de albert hjeien", "dansen");
    $v = array("dat is verplicht", "dat is leuk", "dat is gezellig", "dat is kei kei fantastisch", "Putin is mijn vriend", "roze koeken zijn top", "zwart is geen kleur", "500 - 490 = het aantal vingers dat hitler had", "het is al laat", "dat moet gewoon");
    $vvv = array("!!!!", "!", ", nog een vijne dag vandaag.", ", tot morgen.", " #obamaiseenneger", " #yolo", ", roelof is trouwens een scheitkind, doei.", ".", ", is het trouwens kikker of kinker?");
    print($e[array_rand($e)] . " " . $t[array_rand($t)] . " want " . $v[array_rand($v)] . $vvv[array_rand($vvv)]);
  } else {
    $e = array("Ik vind dit een", "Wat een leuk", "Super origineel", "Lelijk", "Artisiek", "Bijzonder", "Wonderbaarlijk");
    $t = array("scheit", "grapig", "saai", "ingewikkeld", "kleurrijk", "aanstootgevend");
    print($e[array_rand($e)] . " " . $t[array_rand($t)] . " filmpje.");
  }
  
  print("<br><br>");

}


?>