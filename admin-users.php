<?php 

use \Jsilva\PageAdmin;
use \Jsilva\Model\User;


$app->get('/admin/users', function(){

	User::verifyLogin(); // verificar se esta logado no sistema
        
        $search = (isset($_GET['search'])) ? $_GET['search'] : "";
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
        
	if ($search != '') {
            
		$pagination = User::getPageSearch($search, $page);
	} else {
            
		$pagination = User::getPage($page);
	}
        
	$pages = [];
        
	for ($x = 0; $x < $pagination['pages']; $x++)
	{
		array_push($pages, [
			'href'=>'/admin/users?'.http_build_query([
				'page'=>$x+1,
				'search'=>$search
			]),
			'text'=>$x+1
		]);
	}
        
	$page = new PageAdmin();
        
	$page->setTpl("users", array(
		"users"=>$pagination['data'],
		"search"=>$search,
		"pages"=>$pages
	));
});

$app->get('/admin/users/create', function(){

	User::verifyLogin(); // verificar se esta logado no sistema

	$page = new PageAdmin();

	$page->setTpl("users-create");
});

$app->get("/admin/users/:iduser/delete", function($iduser){

	User::verifyLogin(); // verificar se esta logado no sistema

	$user = new User();

	$user->get((int)$iduser);

	$user->delete();

	header("Location: /admin/users");
	exit;
});

$app->get("/admin/users/:iduser", function($iduser){

	User::verifyLogin(); // verificar se esta logado no sistema

	$user = new User();

	$user->get((int)$iduser);

	$page = new PageAdmin();

	$page->setTpl("users-update", array(
		"user"=>$user->getValues() //essa variável $users->getValues() que vai carregador os dados do usuário selecionado no HTML para edição
	));
});

$app->post("/admin/users/create", function(){

	User::verifyLogin(); // verificar se esta logado no sistema

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

	$user->setData($_POST);

	$user->save();

	header("Location: /admin/users");
	exit;
});

$app->post("/admin/users/:iduser", function($iduser){

	User::verifyLogin(); // verificar se esta logado no sistema

	$user = new User();

	$user->get((int)$iduser); //carrega os dados na tela colocando os valores nos values do HTML

	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

	$user->setData($_POST); //cria os gets e sets

	$user->update();

	header("Location: /admin/users");
	exit;
});

 ?>