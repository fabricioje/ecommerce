<?php 
session_start();

require_once("vendor/autoload.php");

use \Slim\Slim;
use \Jsilva\Page;
use \Jsilva\PageAdmin;
use \Jsilva\Model\User;

$app = new Slim();

$app->config('debug', true);

$app->get('/', function() {
    
	$page = new Page();

	$page->setTpl("index");

});

$app->get('/admin', function() {
    
    User::verifyLogin(); //verifica se esta logado no sistema

	$page = new PageAdmin();

	$page->setTpl("index");

});

$app->get('/admin/login', function(){

	$page = new PageAdmin([ //esse comando é para desabilidatar o header e footer, já que na tela de login ambos são diferentes
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("login");

});

$app->post('/admin/login', function(){

	User::login($_POST["login"], $_POST["password"]);

	header("Location: /admin");
	exit();
});

$app->get('/admin/logout', function(){

	User::logout();

	header("Location: /admin/login");
	exit;
});

$app->get('/admin/users', function(){

	User::verifyLogin(); // verificar se esta logado no sistema

	$users = User::listAll(); //lista todos os usuários

	$page = new PageAdmin();

	$page->setTpl("users", array(
		"users"=>$users //essa variável $users que vai ser usada no HTML para fazer o loop para mostrar todos os usuários
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

$app->get("/admin/forgot", function(){

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot");
});

$app->post("/admin/forgot", function(){

	$user = User::getForgot($_POST['email']);

	header("Location: /admin/forgot/sent");
	exit;
});

$app->get("/admin/forgot/sent", function(){

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-sent");
});

$app->get("/admin/forgot/reset", function(){

	//tras os dados do usuário do banco de dados
	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));
});

$app->post("/admin/forgot/reset", function(){

		//tras os dados do usuário do banco de dados
	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();

	$user->get((int)$forgot["iduser"]);

	//criptografa a senha para envia para o banco
	$password = password_hash($_POST["password"], PASSWORD_DEFAULT, [
		"cost"=>12
	]);

	$user->setPassword($password);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-reset-success");
});



$app->run();

 ?>