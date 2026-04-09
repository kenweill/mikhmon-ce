<?php
echo 'PHP version: ' . phpversion();
?>
<br />
<hr />
<?php 
foreach (get_loaded_extensions() as $i => $ext)
{
   echo $ext .' => '. phpversion($ext). '<br/>';
}
?>