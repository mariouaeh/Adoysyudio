<?php
if (!defined('_PS_VERSION_')) {
  exit;
}
/*
*clase principal
*/
class Adoystudio extends Module
{
  public function __construct(){
    $this->name = 'Adoystudio'; //nombre del módulo el mismo que la carpeta y la clase.
    $this->tab = 'front_office_features'; // pestaña en la que se encuentra en el backoffice.
    $this->version = '1.0.0'; //versión del módulo
    $this->author ='Adoystudio'; // autor del módulo
    $this->need_instance = 0; //si no necesita cargar la clase en la página módulos,1 si fuese necesario.
    $this->ps_versions_compliancy = array('min' => '1.7.x.x', 'max' => _PS_VERSION_); //las versiones con las que el módulo es compatible.
    $this->bootstrap = true; //si usa bootstrap plantilla responsive.

    parent::__construct(); //llamada al constructor padre.

    $this->displayName = $this->l('Adoystudio'); // Nombre del módulo
    $this->description = $this->l('Crea impresionantes imágenes en 360° de todos tus productos.'); //Descripción del módulo
    $this->confirmUninstall = $this->l('¿Estás seguro de que quieres desinstalar el módulo?'); //mensaje de alerta al desinstalar el módulo.
  }
  public function install(){
    if (Shop::isFeatureActive()) {
      Shop::setContext(Shop::CONTEXT_ALL);
    }

    if( !parent::install() ||
    // instalar los hook
    !$this->registerHook('header') ||
    !$this->registerHook('home') ||
    !$this->registerHook('displayAdminProductsExtra') ||
    !$this->registerHook('displayFooterProduct') ||
    !$this->instaldb()){
      return false;
    }
    return true;
  }
  public function instaldb(){
    //crear la DB
        $sql1 = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."adoy_img`(
        `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
        `url` VARCHAR(500) NOT NULL )";
        $sql2  = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."adoy_ps`(
        `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
        `id_product` INT(11) NOT NULL ,
        `id_img` INT(11) NOT NULL)";
        if(!Db::getInstance()->Execute($sql1))
        return false;
        if(!Db::getInstance()->Execute($sql2))
        return false;
    return true;
      }
  public function uninstall(){
        if (!parent::uninstall() ||
          !Configuration::deleteByName('Adoystudio') ||
          !$this->uninstalldb()
          ) {
            return false;
            }
            return true;
  }
  public function uninstalldb(){
      $sql1 = "DROP TABLE IF EXISTS `"._DB_PREFIX_."adoy_img`";
      $sql2 = "DROP TABLE IF EXISTS `"._DB_PREFIX_."adoy_ps`";

      if(!Db::getInstance()->Execute($sql1))
      return false;
      if(!Db::getInstance()->Execute($sql2))
      return false;
      return true;
    }
  //funciones hook
  public function hookDisplayHeader(){
        $this->context->controller->addCSS($this->_path.'/views/css/Adoystudio.css', 'all');
      }
  public function getContent(){
      $this->displayForm();
      //return $output.$this->displayForm();
      return $this->_html;
    }
  public function displayForm(){
      ?>
      <script>
      //function add y delete in js
      function borrar_adoy(id){
        let dataVar = JSON.stringify({"id_img" : id});
        let url = './../modules/Adoystudio/library/ws_original.php';
        const configVar = {
          method: 'POST',
          body: dataVar,
          headers:{
            'Content-Type': 'application/json'
          }
        };

        fetch(url, configVar).then(response => response.json())
        .then(responseJson => {
          //console.log(responseJson);
          location.reload();
        })
        .catch(err => {
          console.log(err)

        });

      }

      function submitForm() {
        var fileInput = document.getElementById('add_img');
        var filePath = fileInput.value;
        var allowedExtensions = /(.jpg|.jpeg)$/i;
        if ($('#add_img').val().length == 0) {
          alert('Por favor seleccione algunas imágenes');
        }
        else if(!allowedExtensions.exec(filePath)){
             alert('Por favor solo seleccione imágen JPG');
             //fileInput.value = '';
           }
        else {
            let oData = new FormData(document.forms.namedItem("fileinfo"));
            let oReq = new XMLHttpRequest();
            oReq.open("POST", "../modules/Adoystudio/library/ws_original.php", true);
            oReq.onload = function(oEvent) {
              console.log(oEvent);
            };
            oReq.send(oData);
            location.reload();
        }
      }
      </script>
      <?php
      // Get default language
      $defaultLang = (int)Configuration::get('PS_LANG_DEFAULT');
      // se crea la configuracion del modulo
      $this->_html .='
            <center>
            <h2>Sube tus imágenes </h2>
            <div>
            <form id="fileinfo" name="fileinfo"  method="POST" enctype="multipart/form-data">
            <input type="file" name="file[]" id="add_img" class="form-control" style= "width: 50%;" multiple >
            <br>
            </form>
            <p id="demo"></p>
            <button  class="btn btn-success btn-lg" id="agregar" type="button" onclick="submitForm()">Agregar</button>
            <br><br>
            </div>
            <h2>imágenes 360° actuales</h2>
            </center>
            <br><br>
      ';
      $sql = 'SELECT * FROM '._DB_PREFIX_.'adoy_img';
      if ($results = Db::getInstance()->ExecuteS($sql)){
        foreach ($results as $row){
          $dirImgs = scandir(dirname(__FILE__).'/spritesadoystudio/'.$row['url']);
          $b = [];
          foreach ($dirImgs as &$v2) {
            $a2 = explode(".", $v2);
            if ($a2[1] === 'jpg' || $a2[1] === 'JPG') {
              array_push($b, $v2 );
            }
          }
          $filename = _MODULE_DIR_.'Adoystudio/spritesadoystudio/'.$row['url'].'/'.$b[0];
          $this->_html .=
          '<div style="float: left; margin: 15px; padding: 5px; border-style: solid; border-width: 1px;" class="d-flex justify-content-center">
          <img width="150px" height="120px" src="'.$filename.'" " />
          <p>Nombre de la carpeta: </p>
          <p>'.$row['url'].'</p>
           <br>
          <center>
          <button data-id="'.$row['id'].'" class="btn btn-danger borrar" id="'.$row['id'].'"onclick="borrar_adoy('.$row['id'].')">Borrar</button>
          </center>
          </div>';
        }
      }
    }
  public function hookhome($params){
      $this->context->controller->addJS($this->_path.'/views/js/OriginalAdoySprites.js', 'all');
      $this->context->controller->addJS($this->_path.'/views/js/Adoystudio.js', 'all');
      return $this->display(__FILE__, 'views/templates/hook/Adoystudio.tpl');
    }
  public function hookProductFooter($params){
      ?>
        <script src="<?php echo $this->_path ?>/views/js/OriginalAdoySprites.js" ></script>
        <script>
          //CREAMOS EL DIV PARA MONTAR LOS div360
          content  = document.getElementById("content");
          let adoySprites = document.createElement("div");
          adoySprites.setAttribute("id", "adoySprites");
          content.appendChild(adoySprites);

          function fn(img) {
            let dataVar = {'especificFolder' : img};
            const configVar = {
              method: 'POST',
              body: JSON.stringify(dataVar),
              headers:{
                'Content-Type': 'application/json'
              }
            };
            fetch("../../modules/Adoystudio/library/ws_original.php",configVar)
            .then(response => response.json())
            .then(responseJson => {
              readFolders(responseJson);
            })
            .catch(err => {
              console.log(err)
            });
          }

        </script>
      <?php
      //CONSULTA PARA OBTENER LOS FOLDER REGISTRADOS
      $pid = Tools::getValue('id_product');
      $sql = 'SELECT DISTINCT a.id_img, i.url FROM '._DB_PREFIX_.'adoy_ps a INNER JOIN '._DB_PREFIX_.'adoy_img i ON i.id = a.id_img WHERE a.id_product = '.$pid;
      $results = Db::getInstance()->ExecuteS($sql);
      ?>
      <script type="text/javascript">
        function init (){
          <?php
          for ($i=0; $i < count($results) ; $i++) {
          ?>
            url = '<?php echo $results[$i]['url'];?>';
            fn(url);
            <?php
            }
            ?>
        }
        init();
      </script>
      <?php
    }
  public function hookDisplayAdminProductsExtra($params){
      //$pid = Tools::getValue('id_product');
      $pid = $params['id_product'];
      $html = '<center><h1>Selecciona tu imágen 360° </h1> <br></center>';
        //"SELECT NULL AS id, i.url, 'no' AS link FROM ps_adoy_img i WHERE i.id NOT IN (SELECT id_img FROM ps_adoy_ps WHERE id_product = 2) UNION SELECT a.id, g.url, 'si' AS link FROM ps_adoy_ps a INNER JOIN ps_adoy_img g ON g.id=a.id_img WHERE id_product = 2"
      //$sql = "SELECT  a.id, i.url, 'si' AS link FROM "._DB_PREFIX_."adoy_ps a LEFT JOIN "._DB_PREFIX_."adoy_img i  ON i.id = a.id_img WHERE a.id_product = ".$pid." AND url IS NOT NULL" ;
      $sql = "SELECT NULL AS id_r,i.id , i.url, 'no' AS link FROM "._DB_PREFIX_."adoy_img i WHERE i.id NOT IN (SELECT id_img FROM "._DB_PREFIX_."adoy_ps WHERE id_product = ".$pid.")
       UNION SELECT a.id,g.id, g.url, 'si' AS link FROM "._DB_PREFIX_."adoy_ps a INNER JOIN "._DB_PREFIX_."adoy_img g ON g.id=a.id_img WHERE id_product = ".$pid."";
      if ($results = Db::getInstance()->ExecuteS($sql)){
        foreach ($results as $row){
          $dirImgs = scandir(dirname(__FILE__).'/spritesadoystudio/'.$row['url'] );
          $b = [];
          foreach ($dirImgs as &$v2) {
            $a2 = explode(".", $v2);
            if ($a2[1] === 'jpg' || $a2[1] === 'JPG') {
              array_push($b, $v2 );
            }
          }
          $filename = _MODULE_DIR_.'Adoystudio/spritesadoystudio/'.$row['url'].'/'.$b[0];

          $html .='<div id="'.$row['id'].'divAdoy" style="float: left; margin: 15px; padding: 5px; border-style: solid; border-width: 1px;" class="">
                    <img width="180px" height="120px" src="'.$filename.'" " />
                    <p>Nombre de la carpeta: </p>
                    <p>'.$row['url'].'</p>
                    <br />
                    <center>';

          if ($row['link'] == "si") {
            $html .= '<button class="btn btn-danger " id="'.$row['id'].'imgAdoy" onclick="eliminar_link('.$row['id'].','.$row['id_r'].')">
                        Desenlazar
                      </button>';
          }
          else {
            $html .= '<button class="btn btn-success " id="'.$row['id'].'imgAdoy" onclick="agregar('.$row['id'].','.$pid.')">Enlazar</button>';
          }

          $html .= '</center></div>';
        }
      }
      $html .='
      <script>
      function agregar(id_img, id_product){
        let dataVar = JSON.stringify({"id_img_r" : id_img, "id_product_r" : id_product});
        let url = "./../../../../../modules/Adoystudio/library/ws_original.php";
        const configVar = {
          method: "POST",
          body: dataVar,
          headers:{
            "Content-Type": "application/json"
          }
        };
        fetch(url, configVar).then(response => response.json())
        .then(responseJson => {
          $("#"+id_img+"imgAdoy").remove();
          $("#"+id_img+"divAdoy").append(responseJson);
        })
        .catch(err => {
          console.log(err)
        });
      }

      function eliminar_link(id_img, id_relacion){
          let dataVar = JSON.stringify({"id_img_link" : id_relacion, "id_img_l":id_img});
          let url = "./../../../../../modules/Adoystudio/library/ws_original.php";
          const configVar = {
            method: "POST",
            body: dataVar,
            headers:{
              "Content-Type": "application/json"
            }
          };
          fetch(url, configVar).then(response => response.json())
          .then(responseJson => {
            $("#"+id_img+"imgAdoy").remove();
            $("#"+id_img+"divAdoy").append(responseJson);
          })
          .catch(err => {
            console.log(err)
          });
      }
      </script>
      ';
      return $html;
    }

  }

  ?>
