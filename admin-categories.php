<?php 

use \Jsilva\PageAdmin;
use \Jsilva\Model\User;
use \Jsilva\Model\Category;
use \Jsilva\Model\Product;

$app->get("/admin/categories", function(){

	User::verifyLogin(); //verifica se esta logado no sistema

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
        
	if ($search != '') {
            
		$pagination = Category::getPageSearch($search, $page);
	} else {
            
		$pagination = Category::getPage($page);
	}
        
	$pages = [];
        
	for ($x = 0; $x < $pagination['pages']; $x++)
	{
		array_push($pages, [
			'href'=>'/admin/categories?'.http_build_query([
				'page'=>$x+1,
				'search'=>$search
			]),
			'text'=>$x+1
		]);
	}
        
	$page = new PageAdmin();
        
	$page->setTpl("categories", [
		"categories"=>$pagination['data'],
		"search"=>$search,
		"pages"=>$pages
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