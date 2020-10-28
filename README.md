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
  
  <p>Para realizar um select com o SimpleCrudPhp, você deve chamar a função <code>select</code> passando os parametros na ordem:</p>
  <h4>Exemplo:</h4>
  <pre>    
    
  </pre>

</details>

<details>
  <summary><b>Update</b></summary>
    <p>Para realizar um update com o SimpleCrudPhp, você deve chamar a função <code>update</code> passando os parametros na ordem:</p>
  <h4>Exemplo:</h4>
  <pre>    
    
  </pre>
</details>

<details>
  <summary><b>Delete</b></summary>
    <p>Para realizar um delete com o SimpleCrudPhp, você deve chamar a função <code>delete</code> passando os parametros na ordem:</p>
  <h4>Exemplo:</h4>
  <pre>    
    
  </pre>
</details>

