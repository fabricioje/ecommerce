<?php 

namespace Jsilva\Model;

use \Jsilva\DB\Sql;
use \Jsilva\Model;
use \Jsilva\Mailer;

class User extends Model{

	const SESSION = "User";

	//constante usado para a chave de criptografica
	const SECRET = "JsilvaPhp7Secret";

	public static function login($login, $password){


		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
				"LOGIN"=>$login
			));

		if (count($results) === 0) {
			
			throw new \Exception("Usuário inexistente ou senha inválida.");
		}

		$data = $results[0];

		if (password_verify($password, $data["despassword"]) === true) {
			
			$user = new User();

			$user->setData($data);

			$_SESSION[User::SESSION] = $user->getValues();

			return $user;

		}else{
			throw new \Exception("Usuário inexistente ou senha inválida.");
		}
	}

	public static function verifyLogin($inadmin = true){

		if (!isset($_SESSION[User::SESSION]) //verifica se a sessão esta setada
			||
			!$_SESSION[User::SESSION] //verifica se a sessão não é vazia
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0 //verifica se o id é maior que 0
			||
			(bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin //verificar se tem acesso a administração
			) {
				header("Location: /admin/login");
				exit;
		}
	}

	public static function logout(){

		$_SESSION[User::SESSION] = NULL;
	}

	public static function listAll(){

		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");
	}

	public function save(){

		$sql = new Sql();

		$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)",
			array(
				":desperson"=>$this->getdesperson(),
				":deslogin"=>$this->getdeslogin(),
				":despassword"=>$this->getdespassword(),
				":desemail"=>$this->getdesemail(),
				":nrphone"=>$this->getnrphone(),
				":inadmin"=>$this->getinadmin()
		));

		$this->setData($results[0]);
	}

	public function get($iduser){

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser",
			array(
				":iduser"=>$iduser
			));

		$this->setData($results[0]);
	}

	public function update(){

		$sql = new Sql();

		$results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)",
			array(
				"iduser"=>$this->getiduser(),
				":desperson"=>$this->getdesperson(),
				":deslogin"=>$this->getdeslogin(),
				":despassword"=>$this->getdespassword(),
				":desemail"=>$this->getdesemail(),
				":nrphone"=>$this->getnrphone(),
				":inadmin"=>$this->getinadmin()
		));

		$this->setData($results[0]);
	}

	public function delete(){

		$sql = new Sql();

		$sql->query("CALL sp_users_delete(:iduser)", array(
			":iduser"=>$this->getiduser()
		));
	}

	public static function getForgot($email){

		$sql = new Sql();

		$results = $sql->select(
			"SELECT *
			FROM tb_persons a
			INNER JOIN tb_users b USING(idperson)
			WHERE a.desemail = :email;",
			array(
			":email"=>$email
		));

		//verificar se encontrou o email informado no banco
		if (count($results) === 0) {
			
			throw new \Exception("Não foi possível recupear a senha");
			
		}else{

			//recupera o array com os dados que foram tragos do banco de dados
			$data = $results[0];

			//cria registro na tabela de recupesanção senha usando a procedures
			$results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
				":iduser"=>$data["iduser"],
				":desip"=>$_SERVER["REMOTE_ADDR"] //$_SERVER["REMOTE_ADDR"] retornar o ip do usuário que esta fazendo a solicitação
			));

			//verifica se o results2 foi criado com sucesso ou teve algum erro
			if (count($results2) === 0) {
				
				throw new \Exception("Não foi possível recuperar a senha");
				
			}else{

				$dataRecorevy = $results2[0];

				//gera um código encriptografádo usando base 64
				//1º parametro - qual é o tipo de criptografia
				//2º paramentro - é a chave de criptografia
				//3º paramentro - qual é o dado que vai ser criptografado
				//4º paramentro - qual tipo de criptografia que vai ser usado
				$code = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, User::SECRET, $dataRecorevy["idrecovery"], MCRYPT_MODE_ECB));

				//endereço que vai receber o $code
				$link = "http://www.ecommerce.com.br/admin/forgot/reset?code=$code";

				//cria um novo email para envia-lo
				$mailer = new Mailer($data["desemail"], $data["desperson"], "Redefinir a senha da Jsilva Store", "forgot",
					array(
						"name"=>$data["desperson"],
						"link"=>$link
					));

				//envia o email
				$mailer->send();

				return $data;
			}
		}
	}

	public static function validForgotDecrypt($code){

		//decodifica o código que foi enviado por email para o usuário
		$idrecovery = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, User::SECRET, base64_decode($code), MCRYPT_MODE_ECB);

		//verificar no banco se o código é valido
		$sql = new Sql();

		$results = $sql->select("
			SELECT *
			FROM tb_userspasswordsrecoveries a
			INNER JOIN tb_users b USING(iduser)
			INNER JOIN tb_persons c USING(idperson)
			WHERE
				a.idrecovery = :idrecovery
				AND
				a.dtrecovery IS NULL
				AND
				DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();
			", array(
				":idrecovery"=>$idrecovery
		));

		//verifica se trouxe dados do banco de dados
		if (count($results) === 0) {
			
			throw new \Exception("Não foi possível recuperar a senha");
			
		}else{

			return $results[0];
		}
	}

	public static function setForgotUsed($idrecovery){

		$sql = new Sql();

		$sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(
			":idrecovery"=>$idrecovery
		));
	}

	public function setPassword($password){

		$sql = new Sql();

		$sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
			":password"=>$password,
			":iduser"=>$this->getiduser()
		));
	}


}

?>