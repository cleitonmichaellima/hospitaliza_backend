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

});

//nova contribuicao
$app->post('/avaliacao/', function (Request $request, Response $response) {
    $dados = json_decode($request->getBody());   
    novo($dados);
});

//contribuicao por instituicao
$app->get('/avaliacao/{id_instituicao}', function (Request $request, Response $response) {
    $banco = Conexao();
    $id = $request->getAttribute('id_instituicao');
    
    if($id){
        listaporInstituicao($banco,$id);   
    }
    else{
        echo "Codigo de instituicao não especificado";
    }
    
});

//contribuicao por instituicao
$app->get('/avaliacao/{id_usuario}', function (Request $request, Response $response) {
    $banco = Conexao();
    $id = $request->getAttribute('id_usuario');
    
    if($id){
        listaporInstituicao($banco,$id);   
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

function listaUnico($banco,$id){
    global $app;
    $sth=$banco->prepare("SELECT * FROM avaliacao WHERE id_avaliacao=:id");
    $sth->bindValue(':id',$id);
    $sth->execute();
    $result = $sth->fetch(\PDO::FETCH_ASSOC);			
    echo json_encode($result);

}

function listaporUsuario($banco,$id){
    global $app;						
    $sth=$banco->prepare("SELECT * FROM avaliacao WHERE id_instituicao=:id");
    $sth->bindValue(':id',$id);
    $sth->execute();
    $result = $sth->fetch(\PDO::FETCH_ASSOC);			
	echo json_encode($result);
    
}

function listaporInstituicao($banco,$id){
    global $app;
    $sth=$banco->prepare("SELECT * FROM avaliacao WHERE id_usuario=:id");
    $sth->bindValue(':id',$id);
    $sth->execute();
    $result = $sth->fetch(\PDO::FETCH_ASSOC);			
    echo json_encode($result);
}

function novo( $dados){
    global $app;
    $banco = Conexao();												
    $dados = (sizeof($dados)==0)? $_POST : $dados;
    $keys = array_keys($dados);
    $sth = $banco->prepare("INSERT INTO avaliacao (".implode(',', $keys).") VALUES (:".implode(",:", $keys).")");
    
    foreach ($dados as $key => $value) {
        $sth ->bindValue(':'.$key,$value);
    }
    
    $sth->execute();
    echo json_encode($banco->lastInsertId());

}


$app->run();
