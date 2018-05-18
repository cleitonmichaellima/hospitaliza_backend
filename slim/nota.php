<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';

$app = new \Slim\App(array(
    'templates.path' => 'templates'
));
function Conexao(){
   return  new \PDO('mysql:host=jlg7sfncbhyvga14.cbetxkdyhwsb.us-east-1.rds.amazonaws.com;dbname=h9w81k8dpa80jk2o', 'werqx8ism2m6wgx2', 'u9alw9axiniilu2q');
}

$app->get('/nota/', function (Request $request, Response $response) {// pega todas notas
   	$banco = Conexao();//Conexão
    lista($banco);

});

$app->get('/nota/{id}', function (Request $request, Response $response) { // pega nota de avalicao

   $banco = Conexao();
   $id = $request->getAttribute('id');
   if($id){
    listaUnico($banco,$id);
   }
   else{
       echo "Codigo de usuário não especificado";
   }
   // return $response;
});

//nova nota
$app->post('/nota/', function (Request $request, Response $response) {  // insere nova nota
    $dados = json_decode($request->getBody());
	  novo($dados);
});

//avaliacao por instituicao
$app->get('/nota/', function (Request $request, Response $response) {
  $banco = Conexao();
  $id = $request->getAttribute('id_instituicao');
  if($id){
    listaporInstituicao($banco,$id);
  }
  else{
    echo "Instituicao não especificado";
  }
});

//avaliacao por contribuicao
$app->get('/nota/', function (Request $request, Response $response) {
   $banco = Conexao();
   $id = $request->getAttribute('id_contribuicao');
   if($id){
    listaporContribuicao($banco,$id);
   }
   else{
    echo "Codigo de contribuicao não especificado";
   }
});

//notas do usuário
$app->get('/nota/{id_usuario}', function (Request $request, Response $response) {
  $banco = Conexao();
  $id = $request->getAttribute('id_usuario');
  if($id){
    listaporUsuario($banco,$id);
  }
  else{
    echo "Codigo de usuário não especificado";
  }
});

function lista($banco){
  global $app;
  $sth=$banco->prepare("SELECT * FROM avaliacao");
  $sth->execute();
  $result = $sth->fetchAll(\PDO::FETCH_ASSOC);
  echo json_encode($result);

}

function listaNotaAvaliacao($banco,$id){ // busca a nota da avaliacao
	global $app;
  $sth=$banco->prepare("SELECT * FROM notas WHERE id_avaliacao=:id_avaliacao");
  $sth->bindValue(':id_avaliacao',$id);
	$sth->execute();
	$result = $sth->fetch(\PDO::FETCH_ASSOC);
  echo json_encode($result);
}

function novo( $dados){ // nova nota
  global $app;
  $banco = Conexao();
  $dados = (sizeof($dados)==0)? $_POST : $dados;
  $keys = array_keys($dados); //Paga as chaves do array
  $sth = $banco->prepare("INSERT INTO avaliacao (".implode(',', $keys).") VALUES (:".implode(",:", $keys).")");
  foreach ($dados as $key => $value) {
    $sth ->bindValue(':'.$key,$value);
  }
  $sth->execute();
  //Retorna o id inserido
  echo json_encode( $banco->lastInsertId());

}

function listaporAvaliacao($banco,$id){
  global $app;
  $sth=$banco->prepare("SELECT * FROM notas WHERE id_avaliacao=:id");
  $sth->bindValue(':id',$id);
  $sth->execute();
  $result = $sth->fetch(\PDO::FETCH_ASSOC);
  echo json_encode($result);

}

function listaporUsuario($banco,$id){
  global $app;
  $sth=$banco->prepare("SELECT * FROM avaliacao WHERE id_usuario=:id");
  $sth->bindValue(':id',$id);
  $sth->execute();
  $result = $sth->fetch(\PDO::FETCH_ASSOC);
  echo json_encode($result);
}

$app->run();
