<?php 

use \Jsilva\Page;

$app->get('/', function() {
    
	$page = new Page();

	$page->setTpl("index");

});



 ?>