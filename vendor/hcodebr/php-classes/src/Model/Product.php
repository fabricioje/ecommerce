<?php 

namespace Jsilva\Model;

use \Jsilva\DB\Sql;
use \Jsilva\Model;
use \Jsilva\Mailer;

class Product extends Model{

	public static function listAll(){

		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");
	}

	public function save(){

		$sql = new Sql();

		$results = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)",
			array(
				":idproduct"=>$this->getidproduct(),
				":desproduct"=>$this->getdesproduct(),
				":vlprice"=>$this->getvlprice(),
				":vlwidth"=>$this->getvlwidth(),
				":vlheight"=>$this->getvlheight(),
				":vllength"=>$this->getvllength(),
				":vlweight"=>$this->getvlweight(),
				":desurl"=>$this->getdesurl()
		));

		var_dump($results);

		$this->setData($results[0]);

	}

	//busca a categoria pelo id do banco
	public function get($idproduct){

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct", [
			':idproduct'=>$idproduct
		]);

		$this->setData($results[0]);
	}

	public function delete(){

		$sql = new Sql();

		$results = $sql->select("DELETE FROM tb_products WHERE idproduct = :idproduct", [
			':idproduct'=>$this->getidproduct()
		]);

	}

	public function checkPhoto(){

		if (file_exists(
			$_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR .
			"res" . DIRECTORY_SEPARATOR .
			"site" . DIRECTORY_SEPARATOR .
			"img" . DIRECTORY_SEPARATOR .
			"products" . DIRECTORY_SEPARATOR .
			$this->getidproduct() . ".jpg"
			)) {

			$url = "/res/site/img/products/" . $this->getidproduct() . "jpg";
		}else{

			$url = "/res/site/img/product.jpg";
		}

		return $this->setdesphoto($url);
	}

	public function getValues(){

		$this->checkPhoto();

		$values = parent::getValues();

		return $values;
	}

	//verifica qual formato a imagem carregada pelo usuário esta e se não for jpg a converte para JPG
	public function setPhoto($file){

		$extension = explode('.', $file['name']);
		$extension = end($extension);

		switch ($extension) {
			case "jpg":
			case "jpeg":
				$image = imagecreatefromjpeg($file["tmp_name"]); //tmp_name é nome do arquivo temporário da imagem criado no servidor
				break;
			
			case "gif":
				$image = imagecreatefromgif($file["tmp_name"]); //tmp_name é nome do arquivo temporário da imagem criado no servidor
				break;

			case "png":
				$image = imagecreatefrompng($file["tmp_name"]); //tmp_name é nome do arquivo temporário da imagem criado no servidor
				break;
		}

		$dist = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR .
			"res" . DIRECTORY_SEPARATOR .
			"site" . DIRECTORY_SEPARATOR .
			"img" . DIRECTORY_SEPARATOR .
			"products" . DIRECTORY_SEPARATOR .
			$this->getidproduct() . ".jpg";

		imagejpeg($image, $dist); //pega a imagem e o caminho e a converte para JPG

		imagedestroy($image);

		$this->checkPhoto();

	}

}

?>