<?php

use SimplePhp\SimpleCrud\Crud;

class Example extends Crud
{
  public function insertExample(array $data = null)
  {
    $query = $this->insert("example", $data)->execute();

    if ($query) {
      return "example cadastrado";
    } else {
      return $this->getError();
    }
  }

  public function showExample(int $id)
  {
    $query = $this->select("qt_example as qt, nm_example as nome")
      ->from("example")
      ->where("cd_example = ?", [$id])
      ->execute("fetchAll");

    return $query;
  }

  public function updateExample(int $id, array $data)
  {
    $crud = $this->update("example", "nm_example = ?, dt_example = ?", $data)
      ->where("cd_example = ?", [$id])->execute();

    if ($crud) {
      return "Atualizado Com Sucesso";
    } else {
      return $this->getError();
    }
  }

  public function deleteExample($id)
  {
    $crud = $this->delete()->from("example")->where("cd_example = ?", [$id])->execute();

    return $crud;
  }

  public function filterExample($data = array())
  {

    $queryFilter = $this->select("cd_example, DATE_FORMAT(dt_example, '%d/%m/%Y') as dt_example")
      ->from("example");

    $queryFilter = $queryFilter
      ->where("nm_example LIKE (?) OR nm_example2 LIKE (?)", ["%exam%", "%{$data["search"]}%"]);


    $totalRegisterInQuery = $queryFilter->execute("rowCount", false);

    $query = $queryFilter
      ->order("dt_example", "DESC")
      ->limit(0, 10)
      ->execute("fetchAll");

    $totalRegisterInTable = $this->select("COUNT(*) as count")
      ->from("example")
      ->execute("fetch")
      ->count;

    return [$totalRegisterInQuery, $query, $totalRegisterInTable];
  }
}
