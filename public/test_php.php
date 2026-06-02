<<'PHP'
<?php
echo 'PHP Version : ' . PHP_VERSION . '<br>';
echo 'intl : ';
var_dump(extension_loaded('intl'));
echo '<br>NumberFormatter : ';
var_dump(class_exists('NumberFormatter'));
echo '<br>gd : ';
var_dump(extension_loaded('gd'));
PHP