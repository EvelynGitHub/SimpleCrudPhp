# SimpleCrudPhp
Um sistema de CRUD simples, genérico e flexível em PHP  

### Autores: Evelyn Francisco Brandão e Rodrigo Yuri Veloso

## Breve Descrição:
Este pequeno sistema foi desenvolvido para ser usado em aplicações pequenas em PHP, sendo simples de entender e modificar.

## Técnologias Utilizadas:
- PHP

## Por onde começar:
- Clone este repositório;
- Mova os arquivos para um lugar que julgue apropriado dentro do seu projeto;
- Mude os valores de **Config.php** para os valores de conexão com seu Banco de Dados;
- Se achar necessário, mude o **namespace** do arquivo **Crud.php** para melhor se adquar ao seu projeto;
- Não esqueça de usar o **use namespace\escolhido** e **extends Crud** nas classe que pretende fazer alguma utilização do banco:
<pre>

  use Source\Crud\Crud;

  class Example extends Crud
  {}
  
</pre>

- Para se ter um exemplo mais prático, dê uma olhado no arquivo **Example.php**.

## Exemplos de como se utilizar o SimpleCrudPhp
<p>Não se esqueça, para se utilizar o SimpleCrudPhp, você precisa que a class php em questão extenda Crud.</p>
<p>Você consegue acessar as funções de Crud.php usando o <code>$this-></code> ou o <code>parent::</code></p>

<details>
  <summary><b>Insert</b></summary>
  <p>Para realizar um <code>insert</code> com o SimpleCrudPhp, você deve chamar a função <code>insert</code> passando os parametros na ordem:</p>
  <ul>
    <li>nome da tabela</li>
    <li> array com os valores que serão inseridos</li>
    <li>colunas em que os valores do array serão inseridos</li>
  </ul>
  <p>Por fim, devesse chamar a função <code>execute()</code>. Quando está função é chamada sem passar parametros, ela retorna um booleano verdaderio caso o insert tenha sido executado ou false em caso de falha</p>
  <h4>Exemplo:</h4>
  <pre>
  <?php

   use Source\Crud\Crud;

    class Example extends Crud
    {
        public function insertExample(array $data = null)
        {
            $query = $this->insert("example", $data, "qt_example, nm_example")->execute();

            if ($query) {
                return "example cadastrado";
            } else {
                return $this->getError();
            }
        }
    }
    
    
  </pre>
</details>

<details>
  <summary><b>Select</b></summary>
  
  <p>Para realizar um select com o SimpleCrudPhp, você deve chamar a função <code>select</code> passando as colunas que deseja trazer, caso não informe as colunas o valor padrão será o mesmo que <code>select * </code>. Depois de chamar função <code>select()</code> devesse chamar a função <code>from</code> em seguida.</p>
  <h4>Exemplo sem <code>WHERE</code>:</h4>
  <p>O <code>execute("fetchAll")</code> retorna um array (uma lista) de objetos.</p>
  <pre>    
    $query = $this->select("qt_example as qt, nm_example as nome")
            ->from("example")
            ->execute("fetchAll");

   return $query;
  </pre>
  <h4>Exemplo com <code>WHERE</code>:</h4>
  <p>O <code>execute("fetch")</code> retorna um objeto.</p>
  <pre>    
    $query = $this->select("qt_example as qt, nm_example as nome")
            ->from("example")
            ->where("cd_example = ?", [$id])
            ->execute("fetch");

    return $query;
  </pre>
  <p>O <code>where</code> recebe uma <code>string</code> com as colunas separadas por vírgula, mais o <code> ? </code> que será substituido pelo valor do array (segundo parametro). </p>
  <h5>Exemplo de <code>where</code> com mais de um parametro:</h5>
  <pre>
    $nome = "Fulano de Tal";
    $idade = 24;
    
    $query = $this->select("ds_perfil, nm_cidade")
            ->from("example")
            ->where("nm_example = ? AND idade = ?", [$nome, $idade])
            ->execute("fetch");
            
    // seria o mesmo que 
    
    $query = "select ds_perfil, nm_cidade from example 
              where nm_example = "Fulano de Tal" AND idade = 24";
    
  </pre>
  
  <h4>Exemplo com mais clausulas no <b>select</b>:</h4>
  <pre>
  
  $nome = "tal"
  
  $query = $this->select("qt_example as qt, nm_example as nome")
              ->from("example")
              ->where(""nm_example LIKE (?)", ["%{$nome}%"])
              ->order("dt_example", "DESC")
              ->limit(0, 10)
              ->execute("fetch");  
              
              
   </pre>

</details>

<details>
  <summary><b>Update</b></summary>
    <p>Para realizar um update com o SimpleCrudPhp, você deve chamar a função <code>update</code> passando os parametros na ordem:</p>
  <ul>
    <li>Nome da tabela</li>
    <li>Colunas separada por vírgula e o <code> ? </code> </li>
    <li>Array de valores que substituirão os <code> ? </code></li>
  </ul>
  <h4>Exemplo:</h4>
  <pre>    
        $crud = $this->update("example", "nm_example = ?, dt_example = ?", $data)
            ->where("cd_example = ?", [$id])->execute();

        if ($crud) {
            return "Atualizado Com Sucesso";
        } else {
            return $this->getError();
        }
  </pre>
</details>

<details>
  <summary><b>Delete</b></summary>
    <p>Para realizar um delete com o SimpleCrudPhp, você deve chamar a função <code>delete</code> seguida de função <code>from()</code></p>
  <h4>Exemplo:</h4>
  <pre>    
    $crud = $this->delete()->from("example")->where("cd_example = ?", [$id])->execute();

    return $crud;
  </pre>
</details>

