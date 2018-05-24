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

// rotas
$app->get('/usuario/', function (Request $request, Response $response) {
   	$banco = Conexao();//Conexão
    lista($banco);

});

$app->get('/usuario/{id}', function (Request $request, Response $response) {

   $banco = Conexao();
   $id = $request->getAttribute('id');
   if($id){
    listaUnico($banco,$id);
   }
   else{
       echo "Usuário não encontrado";
   }
   // return $response;
});

$app->get('/login/', function (Request $request, Response $response) {

   $banco = Conexao();
   $dados = json_decode($request->getAttribute('login'));
   print_r($dados);
   authUser($dados);
});


//nova pessoa
$app->post('/usuario/', function (Request $request, Response $response) {    
    $dados = json_decode($request->getBody());   
	novo($dados);
});

function lista($banco){ // lista todos os usuarios
	global $app;
    $sth=$banco->prepare("SELECT * FROM usuario");
	$sth->execute();
	$result = $sth->fetchAll(\PDO::FETCH_ASSOC);
    echo json_encode($result);
}

function listaUnico($banco,$id){ // lista usuário por id
    global $app;
    $sth=$banco->prepare("SELECT * FROM usuario WHERE id_usuario=:id");
    $sth->bindValue(':id',$id);
    $sth->execute();
    $result = $sth->fetch(\PDO::FETCH_ASSOC);
    echo json_encode($result);
}

function novo($dados){ // isercao de novo usuario
	global $app;
    $banco = Conexao();
    $dados = get_object_vars($dados);
	$dados = (sizeof($dados)==0)? $_POST : $dados;
	$keys = array_keys($dados); //Paga as chaves do array
	$sth = $banco->prepare("INSERT INTO usuario (".implode(',', $keys).") VALUES (:".implode(",:", $keys).")");
    
	foreach ($dados as $key => $value) {
		$sth ->bindValue(':'.$key,$value);
	}
    
	$sth->execute();
	//Retorna o id inserido
    echo json_encode( $banco->lastInsertId());

}

function authUser($dados){
    global $app;   
	$dados = (sizeof($dados)==0)? $_POST : $dados;	
    $banco = Conexao();    
    $sth=$banco->prepare("SELECT * FROM usuario WHERE email=:email AND senha=:senha");
    $sth->bindValue(':id',$dados['email']);
    $sth->bindValue(':senha',$dados['senha']);
    $result = $sth->fetch(\PDO::FETCH_ASSOC);
    echo json_encode($result);
    $sth->execute();
    
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
