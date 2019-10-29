<?php
$studioAPI = new AdoyStudio();
$studioAPI->API();
require_once(dirname(__FILE__).'/../../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../../init.php');


class AdoyStudio {
  public function API(){
    header('Content-Type: application/JSON');

    if (isset($_FILES['file']) && !empty($_FILES['file'])) {
      $nameFolder = 'group_'.date("YmdGis");
      $target_dir = './../spritesadoystudio/'.$nameFolder.'/';
      if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
      }
      require_once(dirname(__FILE__).'/../../../config/config.inc.php');
      require_once(dirname(__FILE__).'/../../../init.php');
      $sql = "INSERT INTO `"._DB_PREFIX_."adoy_img`(`id`, `url`) VALUES (NULL, '".$nameFolder."')";
      $cons = Db::getInstance()->Execute($sql);
        //echo $sql;
      $response = [];
      foreach ($_FILES['file']['name'] as $clave => $valor ) {
        $arr = array('name' => $valor, 'size' =>
        $_FILES['file']['size'][$clave], 'tmp_name' =>
        $_FILES['file']['tmp_name'][$clave], 'type' =>
        $_FILES['file']['type'][$clave] );

        $msj = upload($arr, $_POST, $target_dir);

        array_push($response,$msj);
      }
    }

    if ( isset($_POST['getSprites']) && !empty($_POST['getSprites']) ){
      $response = getSprites();
    }

    if ( isset($_SERVER["CONTENT_TYPE"]) ) {
      $contentType = isset($_SERVER["CONTENT_TYPE"]) ?
      trim($_SERVER["CONTENT_TYPE"]) : '';

      if ($contentType === "application/json") {
        //Receive the RAW post data.
        $content = trim(file_get_contents("php://input"));
        $decoded = json_decode($content, true);

        if (isset($decoded['id_img']) && !empty($decoded['id_img'])) {
          $response = delate($decoded['id_img']);
        }
        if (isset($decoded['id_img_r']) && !empty($decoded['id_img_r'])
            &&isset($decoded['id_product_r']) && !empty($decoded['id_product_r']) ) {
          $response = relacion_imagen_produc($decoded['id_img_r'],$decoded['id_product_r'] );
        }
        if ( isset($decoded['especificFolder']) && !empty($decoded['especificFolder']) ) {
          $response = especificFolder( $decoded['especificFolder'] );
        }
        if ( isset($decoded['id_img_link']) && !empty($decoded['id_img_link']) ) {
          $response = eliminar_link( $decoded['id_img_link'], $decoded['id_img_l'] );
        }
      }
    }


    else{
      $response = 'No hay sprite' ;
    }

    echo json_encode($response);
  }
}

function upload($file, $post, $target_dir){

  $target_file = $target_dir . basename($file['name']);

  $uploadOk = 1;
  $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);

  if(isset($post['submit'])) {
    $check = getimagesize($file['tmp_name']);
    if($check !== false) {
      $uploadOk = 1;
      $response = $file["name"].' archivo es una imagen - ' .
      $check['mime'] . '.' ;
    } else {
      $uploadOk = 0;
      $response = $file["name"].' archivo no es una imagen.' ;
    }
  }


  if (file_exists($target_file)) {
    $uploadOk = 0;
    $response = $file["name"].' ya existe.' ;
  }


  if ($file['size'] > 500000000) {
    $uploadOk = 0;
    $response = $file["name"].' es muy grande.' ;
  }


  if( $imageFileType !== 'jpg' && $imageFileType !== 'JPG' ) {
    $uploadOk = 0;
    $response = $file["name"].' archivo invalido, sólo *.jpg' ;
  }

  if ($uploadOk !== 0 ) {
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
      $response = 'ok';
    }
    else{
      $response = $file["name"].' Error de red, intente más tarde o verifique su conexión';
    }
  }

  return $response;
}

function url(){
  if(isset($_SERVER['HTTPS'])){
      $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
  }
  else{
      $protocol = 'http';
  }
  return $protocol . "://" . $_SERVER['HTTP_HOST']. $_SERVER['REQUEST_URI'];
}


function getSprites(){
  //$url = $_SERVER['DOCUMENT_ROOT'].'/webadoystudio/spritesadoystudio';
  $DIR = url();
  $url = './../spritesadoystudio/';
  $f1  = scandir( $url );
  //$fic = [];
  foreach ($f1 as &$v) {
    $a = explode("_", $v);
    if ($a[0] == 'group') {
      $b = [];

      $f2  = scandir( $url.'/'.$v );
      foreach ($f2 as &$v2) {
        $a2 = explode(".", $v2);
        if ($a2[1] === 'jpg') {
          array_push($b,$DIR.'/../../spritesadoystudio/'.$v.'/'.$v2 );
          /*$path = dirname(__FILE__).'/../spritesadoystudio/'.$v.'/'.$v2;
          $type = pathinfo($path, PATHINFO_EXTENSION);
          $data = file_get_contents($path);
          $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
          array_push($b,$base64);*/
        }
      }
      sort($b);
      $fic-> folders[$v] = $b;
      //array_push($fic, $b);
    }
  }
  return $fic;
}

function delate($id_img){
  require_once(dirname(__FILE__).'/../../../config/config.inc.php');
  require_once(dirname(__FILE__).'/../../../init.php');
  $sql = 'DELETE FROM '._DB_PREFIX_.'adoy_img  WHERE id='.$id_img;
  $results = Db::getInstance()->ExecuteS($sql);
  $sql = 'DELETE FROM '._DB_PREFIX_.'adoy_ps  WHERE id_img ='.$id_img;
  $results = Db::getInstance()->ExecuteS($sql);
  return("exito");
}

function relacion_imagen_produc($id_img, $id_produc){
  require_once(dirname(__FILE__).'/../../../config/config.inc.php');
  require_once(dirname(__FILE__).'/../../../init.php');
  $sql1 ="SELECT * FROM `"._DB_PREFIX_."adoy_ps` WHERE id_product='".$id_produc."' AND id_img='".$id_img."'";
  $execute = Db::getInstance()->ExecuteS($sql1);
    if(!$execute){
      $sql = "INSERT INTO `"._DB_PREFIX_."adoy_ps`(`id`, `id_product`,`id_img`) VALUES (NULL, '".$id_produc."','".$id_img."' )";
      $results = Db::getInstance()->ExecuteS($sql);
      $endId = Db::getInstance()->Insert_ID();
      $btnDel = '<center><button class="btn btn-danger " id="'.$id_img.'imgAdoy" onclick="eliminar_link( '.$id_img.', '.$endId.' )" style="display: block;">Desenlazar</button></center>';
      return($btnDel);
    }
}

function especificFolder($folder){
  $url = './../spritesadoystudio/';
  //$fic = [];
  $b = [];
  $_SERVER= url();
  $f2  = scandir( $url.'/'.$folder );
  foreach ($f2 as &$v2) {
    $a2 = explode(".", $v2);
    if ($a2[1] === 'jpg' || $a2[1] === 'JPG') {
      //$b[$v2] -> $folder.'/'.$v2;
      array_push($b,$_SERVER.'/../../spritesadoystudio/'.$folder.'/'.$v2);
      /*$path = dirname(__FILE__).'/../spritesadoystudio/'.$folder.'/'.$v2;
      $type = pathinfo($path, PATHINFO_EXTENSION);
      $data = file_get_contents($path);
      $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
      array_push($b, $path );*/
    }
  }
  sort($b);
  $fic-> folders[$folder] = $b;
  //array_push($fic, $b);

  return $fic;
}

function eliminar_link($id_relacion, $id_img){
  require_once(dirname(__FILE__).'/../../../config/config.inc.php');
  require_once(dirname(__FILE__).'/../../../init.php');
  $sql = 'DELETE FROM '._DB_PREFIX_.'adoy_ps WHERE id='.$id_relacion;
  $results = Db::getInstance()->ExecuteS($sql);
  $btnDel = '<center><button class="btn btn-success" id="'.$id_img.'imgAdoy" onclick="agregar('.$id_img.','.$id_relacion.')">Enlazar</button></center>';
  return($btnDel);
}
?>
