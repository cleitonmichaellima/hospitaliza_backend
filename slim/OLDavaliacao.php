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
$app->get('/avaliacao/', function (Request $request, Response $response) {  
   	$banco = Conexao();//Conexão
    lista($banco);
 
});

$app->get('/avaliacao/{id}', function (Request $request, Response $response) {
      
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

//nova avaliacao
$app->post('/avaliacao/', function (Request $request, Response $response) {
    $dados = json_decode($request->getBody());   
	novo($dados);
});

//avaliacao por instituicao
$app->get('/avaliacao/instituicao/{id_instituicao}', function (Request $request, Response $response) {
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
$app->get('/avaliacao/contribuicao/{id_contribuicao}', function (Request $request, Response $response) {
    $banco = Conexao();
   $id = $request->getAttribute('id_contribuicao');
   if($id){
    listaporContribuicao($banco,$id);   
   }
   else{
       echo "Codigo de contribuicao não especificado";
   }
});

//avaliacao por usuario
$app->get('/avaliacao/usuario/{id_usuario}', function (Request $request, Response $response) {
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
            
		//	$sth = $banco->query("SELECT * FROM usuario");		
            $sth=$banco->prepare("SELECT * FROM avaliacao");
			$sth->execute();
			$result = $sth->fetchAll(\PDO::FETCH_ASSOC);			
            echo json_encode($result);

            //$app->render('default.php',["data"=>$result],200); 
}

function listaUnico($banco,$id){
			global $app;
            
		//	$sth = $banco->query("SELECT * FROM usuario");		
            $sth=$banco->prepare("SELECT * FROM avaliacao WHERE id_avaliacao=:id");
            $sth->bindValue(':id',$id);
			$sth->execute();
			$result = $sth->fetch(\PDO::FETCH_ASSOC);			
            echo json_encode($result);

            //$app->render('default.php',["data"=>$result],200); 
}

function novo( $dados){
			global $app;
            $banco = Conexao();	           
            
            
			$dados = (sizeof($dados)==0)? $_POST : $dados;
			$keys = array_keys($dados); //Paga as chaves do array			
		//	O uso de prepare e bindValue � importante para se evitar SQL Injection
			
			$sth = $banco->prepare("INSERT INTO avaliacao (".implode(',', $keys).") VALUES (:".implode(",:", $keys).")");
			foreach ($dados as $key => $value) {
				$sth ->bindValue(':'.$key,$value);
			}
			$sth->execute();
			//Retorna o id inserido
            echo json_encode( $banco->lastInsertId());
			//$app->render('default.php',["data"=>['id'=>$this->PDO->lastInsertId()]],200); 
}

function listaporInstituicao($banco,$id){
			global $app;
            
		//	$sth = $banco->query("SELECT * FROM usuario");		
            $sth=$banco->prepare("SELECT * FROM contribuicao WHERE id_instituicao=:id");
            $sth->bindValue(':id',$id);
			$sth->execute();
			$result = $sth->fetch(\PDO::FETCH_ASSOC);			
            echo json_encode($result);

            //$app->render('default.php',["data"=>$result],200); 
}

function listaporContribuicao($banco,$id){
			global $app;
            
		//	$sth = $banco->query("SELECT * FROM usuario");		
            $sth=$banco->prepare("SELECT * FROM avaliacao WHERE id_contribuicao=:id");
            $sth->bindValue(':id',$id);
			$sth->execute();
			$result = $sth->fetch(\PDO::FETCH_ASSOC);			
            echo json_encode($result);

            //$app->render('default.php',["data"=>$result],200); 
}

function listaporUsuario($banco,$id){
			global $app;
            
		//	$sth = $banco->query("SELECT * FROM usuario");		
            $sth=$banco->prepare("SELECT * FROM avaliacao WHERE id_usuario=:id");
            $sth->bindValue(':id',$id);
			$sth->execute();
			$result = $sth->fetch(\PDO::FETCH_ASSOC);			
            echo json_encode($result);

            //$app->render('default.php',["data"=>$result],200); 
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
