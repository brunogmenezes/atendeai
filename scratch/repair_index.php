<?php
$file = 'c:/wamp64/www/atendeai/index.php';
$content = file($file);

// Fix line 221 (index 220)
$content[220] = "                                             <a href=\"index.php?page=ListarContas\">\n";

// Remove lines 246-255 (indices 245-254)
for ($i = 245; $i <= 254; $i++) {
    unset($content[$i]);
}

file_put_contents($file, implode("", $content));
echo "Reparado com sucesso!";
?>
