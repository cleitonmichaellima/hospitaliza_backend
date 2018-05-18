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

$app->get('/instituicao/', function (Request $request, Response $response) {
   	$banco = Conexao();//Conexão
    lista($banco);

});

$app->get('/instituicao/{id}', function (Request $request, Response $response) {

   $banco = Conexao();
   $id = $request->getAttribute('id');
   if($id){
    listaUnico($banco,$id);
   }
   else{
       echo "Codigo de instituicao não especificado";
   }
   // return $response;
});

$app->get('/buscaTermoInstituicao/{termo}', function (Request $request, Response $response) { // utilizado no campo buscar de instituicao

   $banco = Conexao();
   $termo = $request->getAttribute('termo');
   if($termo){
    pesquisaPorTermoDeBusca($banco,$termo);
   }
   else{
       echo "Termo de utilizado invalido";
   }
   // return $response;
});

//nova instituicao
$app->post('/instituicao/', function (Request $request, Response $response) {
  $dados = json_decode($request->getBody());
	novo($dados);
});


function novo( $dados){
  global $app;
  $banco = Conexao();
  $dados = (sizeof($dados)==0)? $_POST : $dados;
  $keys = array_keys($dados); //Paga as chaves do array
  $sth = $banco->prepare("INSERT INTO instituicao (".implode(',', $keys).") VALUES (:nome".implode(",:nome", $keys).")");

  foreach ($dados as $key => $value) {
    $sth ->bindValue(':nome'.$key,$value);
  }

  $sth->execute();
  //Retorna o id inserido
  echo json_encode( $banco->lastInsertId());

}

function lista($banco){
  global $app;
  $sth=$banco->prepare("SELECT * FROM instituicao");
  $sth->execute();
  $result = $sth->fetchAll(\PDO::FETCH_ASSOC);
  echo json_encode($result);
}

function listaUnico($banco,$id){
  global $app;
  $sth=$banco->prepare("SELECT * FROM instituicao WHERE id_instituicao=:id");
  $sth->bindValue(':id',$id);
  $sth->execute();
  $result = $sth->fetch(\PDO::FETCH_ASSOC);
  echo json_encode($result);
}

function pesquisaPorTermoDeBusca($banco,$termo){
  global $app;
  $sth=$banco->prepare("SELECT * FROM instituicao WHERE nome LIKE '%:termo%'");
  $sth->bindValue(':termo',$termo);
  $sth->execute();
  $result = $sth->fetchAll(\PDO::FETCH_ASSOC);
  echo json_encode($result);
}


 /*
function alterar($dados){
			global $app;
            var_dump($id);
            $banco = Conexao();
			$dados = (sizeof($dados)==0)? $_POST : $dados;
			$sets = [];
			foreach ($dados as $key => $VALUES) {
				$sets[] = $key." = :".$key;
			}

			$sth = $banco->prepare("UPDATE usuario SET ".implode(',', $sets)." WHERE id_usuario = :id");
			$sth ->bindValue(':id',$id);
			foreach ($dados as $key => $value) {
				$sth ->bindValue(':'.$key,$value);
			}
			//Retorna status da edi��o

           echo json_encode(["data"=>['status'=>$sth->execute()==1]],200);
		}

*/

$app->run();