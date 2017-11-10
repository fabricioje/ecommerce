<?php 

namespace Jsilva\Model;

use \Jsilva\DB\Sql;
use \Jsilva\Model;
use \Jsilva\Mailer;

class Category extends Model{

	

	public static function listAll(){

		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_categories ORDER BY descategory");
	}

	public function save(){

		$sql = new Sql();

		$results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)",
			array(
				":idcategory"=>$this->getidcategory(),
				":descategory"=>$this->getdescategory()
		));

		$this->setData($results[0]);
	}

	//busca a categoria pelo id do banco
	public function get($idcategory){

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory", [
			':idcategory'=>$idcategory
		]);

		$this->setData($results[0]);
	}

	public function delete(){

		$sql = new Sql();

		$results = $sql->select("DELETE FROM tb_categories WHERE idcategory = :idcategory", [
			':idcategory'=>$this->getidcategory()
		]);
	}

}

?>