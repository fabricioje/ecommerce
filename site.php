<?php 

use \Jsilva\Page;
use \Jsilva\Model\Product;

$app->get('/', function() {

	$products = Product::listALL();
    
	$page = new Page();

	$page->setTpl("index",[
		'products'=>Product::checkList($products)
	]);

});



 ?>