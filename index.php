<?php

date_default_timezone_set("Asia/Jakarta");

require_once('Medoo/Medoo.php');
require 'Slim/Slim.php';
use Medoo\Medoo;

\Slim\Slim::registerAutoloader();
$md   = new Moduledb();
$app  = new \Slim\Slim();


/*==========================================================================
 Information Basic    
===========================================================================*/

$app->get('/', array($md, 'getIndex'));
$app->get('/info', array($md, 'info'));
$app->get('/error_page', array($md, 'error_page'))->name("error");

/*==========================================================================
 Employee Function    
===========================================================================*/

$app->get('/employees', array($md, 'employees'));
$app->get('/getEmployee/:empid', array($md, 'getemployee'));
$app->post('/saveEmployee', array($md, 'saveEmployee'));

$app->run();

/*==========================================================================
 Database and result function    
===========================================================================*/

class Moduledb {

  private $db;
  public function __construct(){

    $this->db = new Medoo(
      [ 
        'database_type' => 'mysql',
        'server'        => 'localhost',
        'database_name' => 'hris',
        'username'      => 'root',
        'password'      => '',
      ]
    );
    $this->db->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  }

  private function db_execute($sql){
    return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
  }

  private function renderJSON($hasil){
    $app = \Slim\Slim::getInstance();
    $app->contentType("application/json");
    $app->response->header('Access-Control-Allow-Origin', '*');
    echo json_encode($hasil);
  }


//===============================================================================================================================
//basic function
//=============================================================================================================================== 

  public function info(){
   phpinfo();
 }

 public function getIndex(){
  $r = array(
    array(
      "Welcome" => "This is a Slim index page",
    ),
  );

  $this->renderJSON($r); 
}

public function error_page(){
  $r = array(
    "kind" => "error",
    "error_code" => "404",
    "error_msgs" => "Page Not Found"
  );

  $this->renderJSON($r); 
}


//===============================================================================================================================
//Trans function
//===============================================================================================================================

public function employees(){

  //token ini harusnya dari database
  $token = "00043eb6617434cc5f357bbf692e53be";
  $app = \Slim\Slim::getInstance();
  $header = $app->request->headers;

  $is_token = "";

  foreach ($header as $key => $value) {
    if($key == 'x-token' || $key == 'X-Token'){
      $is_token = $value;
    }
  }

  //check token in database
  if($is_token != $token){
    $app->response->redirect($app->urlFor('error'), 303);
  }else{    

    $table  = "employee";
    $fields = "*";
    $query = $this->db->select($table, $fields);
    if($query){
      $hasil = array(
        "kind" => "success",
        "data" => $query
      );
      $this->renderJSON($hasil);
    }else{
      $result = array(
        "kind" => "error",
        "error_code" => substr($this->db->lasterror, 0,15),
        "error_msgs"  => substr($this->db->lasterror,17,400),
        "data" => $query
      );
      return $result;
    }
  }
}


public function getEmployee($empid){

  //token ini harusnya dari database
  $token = "00043eb6617434cc5f357bbf692e53be";
  $app = \Slim\Slim::getInstance();
  $header = $app->request->headers;

  $is_token = "";

  foreach ($header as $key => $value) {
    if($key == 'x-token' || $key == 'X-Token'){
      $is_token = $value;
    }
  }

  //check token in database
  if($is_token != $token){
    $app->response->redirect($app->urlFor('error'), 303);
  }else{    

    $table  = "employee";
    $fields = "*";
    $where  = [
      "empid" => "$empid"
    ];
    $query = $this->db->select($table, $fields, $where);
    if($query){
      $hasil = array(
        "kind" => "success",
        "data" => $query
      );
      $this->renderJSON($hasil);
    }else{
      $result = array(
        "kind" => "error",
        "error_code" => substr($this->db->lasterror, 0,15),
        "error_msgs"  => substr($this->db->lasterror,17,400),
        "data" => $query
      );
      $this->renderJSON($result);
    }
  }
}

public function saveEmployee(){
  $app = \Slim\Slim::getInstance();  
  $rawhasilpost = $app->request->getBody();
  $j = json_decode($rawhasilpost, true);

  if($this->db->insert('employee', $j )) {
    $result = array(
      "kind" => "success",
      "msg"  => "Data updated",
      "data" => array($j),
    );
    $this->renderJSON($result);
  }else{
    $result = array(
      "kind" => "error",
      "error_code" => substr($this->db->lasterror, 0,15),
      "error_msgs"  => substr($this->db->lasterror,17,400),
      "data" => array(),
    );
    $this->renderJSON($result);     
  }             
}


}
?>
