<?php 

use \Jsilva\PageAdmin;
use \Jsilva\Model\User;
use \Jsilva\Model\Category;
use \Jsilva\Model\Product;

$app->get("/admin/categories", function(){

	User::verifyLogin(); //verifica se esta logado no sistema

	$categories = Category::listAll();

	$page = new PageAdmin();

	$page->setTpl("categories", [
		'categories'=>$categories
	]);
});

$app->get("/admin/categories/create", function(){

	User::verifyLogin(); //verifica se esta logado no sistema

	$page = new PageAdmin();

	$page->setTpl("categories-create");
});

$app->post("/admin/categories/create", function(){

	User::verifyLogin(); //verifica se esta logado no sistema

	$category = new Category();

	$category->setData($_POST);

	$category->save();

	header('Location: /admin/categories');
	exit;
});

$app->get("/admin/categories/:idcategory/delete", function($idcategory){

	User::verifyLogin(); //verifica se esta logado no sistema

	$category = new Category();

	//carrega a categoria do banco de dados para verificar se ela ainda existe no banco
	$category->get((int)$idcategory);

	$category->delete();

	header('Location: /admin/categories');
	exit;
});

$app->get("/admin/categories/:idcategory", function($idcategory){

	User::verifyLogin(); //verifica se esta logado no sistema

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new PageAdmin();

	$page->setTpl("categories-update", [
		'category'=>$category->getValues()
	]);
});

$app->post("/admin/categories/:idcategory", function($idcategory){

	User::verifyLogin(); //verifica se esta logado no sistema

	$category = new Category();

	$category->get((int)$idcategory);

	$category->setData($_POST);

	$category->save();

	header('Location: /admin/categories');
	exit;

});



$app->get("/admin/categories/:idcategory/products", function($idcategory){

	User::verifyLogin(); //verifica se esta logado no sistema

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new PageAdmin();

	$page->setTpl("categories-products",[
		'category'=>$category->getValues(),
		'productsRelated'=>$category->getProducts(),
		'productsNotRelated'=>$category->getProducts(false)
	]);
});

//adiciona produro na lista de categorias relacionadas
$app->get("/admin/categories/:idcategory/products/:idproduct/add", function($idcategory, $idproduct){

	User::verifyLogin(); //verifica se esta logado no sistema

	$category = new Category();

	$category->get((int)$idcategory);

	$product = new Product();

	$product->get((int)$idproduct);

	$category->addProduct($product);

	header("Location: /admin/categories/".$idcategory."/products");
	exit;
});

//remove produto na lista de categorias relacionadas
$app->get("/admin/categories/:idcategory/products/:idproduct/remove", function($idcategory, $idproduct){

	User::verifyLogin(); //verifica se esta logado no sistema

	$category = new Category();

	$category->get((int)$idcategory);

	$product = new Product();

	$product->get((int)$idproduct);

	$category->removeProduct($product);

	header("Location: /admin/categories/".$idcategory."/products");
	exit;
});



 ?>