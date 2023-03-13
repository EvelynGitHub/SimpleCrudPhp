<?php

use SimplePhp\SimpleCrud\Crud;

class Example
{
    private Crud $crud;

    public function __construct(Crud $crud)
    {
        $this->crud = $crud;
    }

    public function insertExample(array $data = null)
    {
        $query = $this->crud->insert("example", $data, "qt_example, nm_example")->execute();

        if ($query) {
            return "example cadastrado";
        } else {
            return $this->crud->getError();
        }
    }

    public function showExample(int $id)
    {
        $query = $this->crud->select("qt_example as qt, nm_example as nome")
            ->from("example")
            ->where("cd_example = ?", [$id])
            ->execute("fetchAll");

        return $query;
    }

    public function updateExample(int $id, array $data)
    {
        $crud = $this->crud->update("example", "nm_example = ?, dt_example = ?", $data)
            ->where("cd_example = ?", [$id])->execute();

        if ($crud) {
            return "Atualizado Com Sucesso";
        } else {
            return $this->crud->getError();
        }
    }

    public function deleteExample($id)
    {
        $crud = $this->crud->delete()->from("example")->where("cd_example = ?", [$id])->execute();

        return $crud;
    }

    public function filterExample($data = array())
    {

        $queryFilter = $this->crud->select("cd_example, DATE_FORMAT(dt_example, '%d/%m/%Y') as dt_example")
            ->from("example");

        $queryFilter = $queryFilter
            ->where("nm_example LIKE (?) OR nm_example2 LIKE (?)", ["%exam%", "%{$data["search"]}%"]);


        $totalRegisterInQuery = $queryFilter->execute("rowCount", false);

        $query = $queryFilter
            ->order("dt_example", "DESC")
            ->limit(0, 10)
            ->execute("fetchAll");

        $totalRegisterInTable = $this->crud->select("COUNT(*) as count")
            ->from("example")
            ->execute("fetch")
            ->count;

        return [$totalRegisterInQuery, $query, $totalRegisterInTable];
    }
}
