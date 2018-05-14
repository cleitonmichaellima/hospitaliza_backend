<?php
class Sql{

   public $dados;
   public $conexao;
   public $result;
   public $sql;
   public $msg;
   public $error;
   
    public function insert(){
        if($this->sql){
            if($this->result=mysqli_query($this->conexao,$this->sql)){

            }
            else{
                $this->msg="Erro ao inserir dados";
                $this->error=mysqli_error($this->conexao);
            }
        }
        else{
          $this->msg="SQL EM BRANCO";
          $this->error=mysqli_error($this->conexao);
            
        }

    }   

    public function selectOneLine(){
        
        $this->result=mysqli_query($this->conexao,$this->sql);
        $this->fetch(); 

    }

    public function selectAllLines(){
        
        $this->result=mysqli_query($this->conexao,$this->sql);
        $this->fetchAllLines(); 

    }

    public function fetch(){
        
        $this->dados=mysqli_fetch_assoc($this->result);            
        
    }

    public function fetchAllLines(){
        
        while($this->dados[]=mysqli_fetch_assoc($this->result)){ // pega todos os resultados 

        }
    }
    public function retorno(){
        return   "mensagem:".$this->msg."<br>erro".$this->error;
    }
  
    public function clean(){
      unset($this->dados);
      unset($this->result);
      unset($this->sql);
      unset($this->error);
      unset($this->msg);
        
    }
}

?>