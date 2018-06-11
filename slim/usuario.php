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

$app->get('/usuario/{id_usuario}', function (Request $request, Response $response) {

   $banco = Conexao();
   $id = $request->getAttribute('id_usuario');
   if($id){
    listaUnico($banco,$id);
   }
   else{
       echo "Usuário não encontrado";
   }
   // return $response;
});


$app->get('/avaliacaoUsuario/{id_usuario}', function (Request $request, Response $response) {

   $banco = Conexao();
   $id = $request->getAttribute('id_usuario');
   if($id){
    listaAvaliacoesUsuario($banco,$id);
   }
   else{
       echo "Usuário não encontrado";
   }
   // return $response;
});


$app->get('/usuarioVerificaEmail/{email}', function (Request $request, Response $response) {

   $banco = Conexao();
   $email = $request->getAttribute('email');
   if($email){
    pesquisaSeEmailExiste($banco,$email);
   }
   else{
       echo "Usuário não encontrado";
   }
   // return $response;
});

$app->post('/login/', function (Request $request, Response $response) {

   $banco = Conexao();
   $dados = json_decode($request->getBody());   
   authUser($dados);
});


//nova pessoa
$app->post('/usuario/', function (Request $request, Response $response) {    
    $dados = json_decode($request->getBody());
   
    if($dados->id_usuario!=''){
       atualizaUser($dados);
    }
    else{
        novo($dados);
    }
	
});

//novo contato
$app->post('/contato/', function (Request $request, Response $response) {    
    $dados = json_decode($request->getBody());
    novoContato($dados);
	
});

//desativar contato
$app->post('/desativarUsuario/', function (Request $request, Response $response) {    
    $dados = json_decode($request->getBody());
    desativarUsuario($dados);
	
});

//desativar contato
$app->post('/lembrarSenha/', function (Request $request, Response $response) {    
    $dados = json_decode($request->getBody());
    lembrarSenha($dados);
	
});


//desativar contato
$app->post('/alterarSenhaDoLembrar/', function (Request $request, Response $response) {    
    $dados = json_decode($request->getBody());
    alterarSenhaDoLembrar($dados);
	
});



function alterarSenhaDoLembrar($dados){ //  alterar a senha de fora do portal 
    global $app;    
	$dados = (sizeof($dados)==0)? $_POST : $dados;	
    $banco = Conexao(); 
    
    $sth=$banco->prepare("SELECT id_usuario FROM usuario_lembrar_senha WHERE chave_unica=:chave_unica");
    $sth->bindValue(':chave_unica',$dados->chave_unica);
    $sth->execute();
    $result = $sth->fetch(\PDO::FETCH_ASSOC);     
    
    
    if($sth->rowCount()>0){ // sucesso, encontrou usuario
        $sth=$banco->prepare("UPDATE usuario SET senha =:senha  WHERE id_usuario=:id_usuario");
        $sth->bindValue(':id_usuario',$result->id_usuario);  
        $sth->bindValue(':senha',$dados->senha); 
        $sth->execute();
        $response['status'] = 1;
    }
    else{
        $response['status'] = 0;
    }
    echo json_encode($response);
}

function alterarSenha($dados){ // alterar a senha dentro do portal 
    global $app;    
	$dados = (sizeof($dados)==0)? $_POST : $dados;	
    $banco = Conexao();     
    $sth=$banco->prepare("UPDATE usuario SET senha =:senha  WHERE id_usuario=:id_usuario");
    $sth->bindValue(':id_usuario',$dados->id_usuario);  
    $sth->bindValue(':senha',$dados->senha); 
    $sth->execute();
       
   if($sth->rowCount()>0){      
         $response['status'] = 1;
    }
    else{        
        $response['status'] =0;
    }
    echo json_encode($response);
}

function lembrarSenha($dados){
    global $app;    
	$dados = (sizeof($dados)==0)? $_POST : $dados;	
    $banco = Conexao(); 
    
    $sth=$banco->prepare("SELECT id_usuario FROM usuario WHERE email=:email");
    $sth->bindValue(':email',$dados->email);
    $sth->execute();
    $result = $sth->fetch(\PDO::FETCH_ASSOC);     
    
    
    if($sth->rowCount()>0){ // sucesso, encontrou usuario
        $dataVencimento =  new DateTime();
        $dataVencimento =  $dataVencimento->add(new DateInterval('P1D'));// adiciona o limite de 24hs para resetar a senha
        $dataEUA =  $dataVencimento->format('Y-m-d H:m:s');        
        $chaveUnica = md5(uniqid()); // gera chave unica de acesso
        $sth = $banco->prepare("INSERT INTO usuario_lembrar_senha (id_usuario,chave_unica,data_vencimento) VALUES (:id_usuario,:chave_unica,:data_vencimento)");
        $sth->bindValue(':id_usuario',$result['id_usuario']);
        $sth->bindValue(':chave_unica',$chaveUnica);
        $sth->bindValue(':data_vencimento',$dataEUA);
        if($sth->execute()){
            
            $response['status'] = 1;
        }
        else{
            $sth->debugDumpParams();
             $response['status'] = 0;
        }
        
    }
    else{
        $response['status'] = 0;
    }
    echo json_encode($response);
}


function desativarUsuario($dados){
    global $app;   
	$dados = (sizeof($dados)==0)? $_POST : $dados;	
    $banco = Conexao();    
    $sth=$banco->prepare("UPDATE usuario SET inativado =:inativado  WHERE id_usuario=:id_usuario");
    $sth->bindValue(':inativado',1);  
    $sth->bindValue(':id_usuario',$dados->id_usuario); 
    $sth->execute();
    if($sth->rowCount()>0){      
         $response['status'] = 1;
    }
    else{        
        $response['status'] =0;
    }
    echo json_encode($response);
}

function listaAvaliacoesUsuario($banco,$id){
  global $app;
  $sth=$banco->prepare(
                            "SELECT a.id_avaliacao,a.titulo,a.descricao,n.nota,a.`data`,i.nome,i.id_instituicao 
                            FROM avaliacao a
                            INNER JOIN instituicao i ON i.id_instituicao = a.id_instituicao
                            INNER JOIN nota n ON n.id_avaliacao = a.id_avaliacao
                            WHERE id_usuario=:id"
                      );
  $sth->bindValue(':id',$id);
  $sth->execute();
  $result = $sth->fetchAll(\PDO::FETCH_ASSOC);
  echo json_encode($result);
}



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

function pesquisaSeEmailExiste($banco,$email){ // lista usuário por id   
    global $app;
    $sth=$banco->prepare("SELECT email FROM usuario WHERE email=:email");
    $sth->bindValue(':email',$email);
    $sth->execute();
    $result['email'] = $sth->fetch(\PDO::FETCH_ASSOC);
    
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
    
	//Retorna o id inserido
    if($sth->execute()){
         $response['id_usuario'] = $banco->lastInsertId();
         $response['status'] = 1;
    }
    else{
        
        $response['status'] =0;
    }
   
    
    echo json_encode($response );

}

function novoContato($dados){ // isercao de novo usuario
	global $app;
    $banco = Conexao();
    $dados = get_object_vars($dados);
	$dados = (sizeof($dados)==0)? $_POST : $dados;
	$keys = array_keys($dados); //Paga as chaves do array
	$sth = $banco->prepare("INSERT INTO contato (".implode(',', $keys).") VALUES (:".implode(",:", $keys).")");
    
	foreach ($dados as $key => $value) {
		$sth ->bindValue(':'.$key,$value);
	}
    
	//Retorna o id inserido
    if($sth->execute()){        
         $response['status'] = 1;
    }
    else{
        
        $response['status'] =0;
    }
   
    
    echo json_encode($response );

}

function authUser($dados){
    global $app;    
	$dados = (sizeof($dados)==0)? $_POST : $dados;	
    $banco = Conexao(); 
    
    $sth=$banco->prepare("SELECT * FROM usuario WHERE email=:email AND senha=:senha");
    $sth->bindValue(':email',$dados->email);
    $sth->bindValue(':senha',$dados->senha);
    $sth->execute();
    $result = $sth->fetch(\PDO::FETCH_ASSOC);     
    
    
    if($sth->rowCount()>0){ // sucesso, encontrou usuario
        
        $response['status'] = 1;
        
        if($_SESSION['pass_user']){
          $response['msg'] = "usuário já logado";
         
        }
        else{    
            require "session.php";
            $response['msg'] = "login efetuado com sucesso";
            $response['id_usuario'] = $result['id_usuario'];  
            $response['nomeUsuario'] = $result['nome']; 
            $response['pass_user'] = $_SESSION['pass_user']; 
           
        }
        
    }
    else{
        $response['status'] = 0;
    }
    echo json_encode($response);
}

function atualizaUser($dados){
    global $app;   
	$dados = (sizeof($dados)==0)? $_POST : $dados;	
    $banco = Conexao();    
    $sth=$banco->prepare("UPDATE usuario SET nome=:nome,data_nascimento=:data_nascimento,sexo=:sexo,telefone=:telefone,dddtelefone=:dddtelefone WHERE id_usuario=:id_usuario");
    $sth->bindValue(':nome',$dados->nome);
    $sth->bindValue(':sexo',$dados->sexo);
    $sth->bindValue(':data_nascimento',$dados->data_nascimento);
    $sth->bindValue(':telefone',$dados->telefone);
    $sth->bindValue(':dddtelefone',$dados->dddtelefone);  
    $sth->bindValue(':id_usuario',$dados->id_usuario); 
    $sth->execute();
    if($sth->rowCount()>0){      
         $response['status'] = 1;
    }
    else{        
        $response['status'] =0;
    }
    echo json_encode($response);
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
