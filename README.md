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
