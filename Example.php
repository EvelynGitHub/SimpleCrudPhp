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

    public function showExample()
    {
        try {
            $query = $this->select()
                ->from("teste")
                ->execute("fetchAll");

            return $query;
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
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

        $queryFilter = parent::select("cd_example, DATE_FORMAT(dt_example, '%d/%m/%Y') as dt_example")
            ->from("example");

        $queryFilter = $queryFilter
            ->where("nm_example LIKE (?) OR nm_example2 LIKE (?)", ["%exam%", "%{$data["search"]}%"]);


        $totalRegisterInQuery = $queryFilter->execute("rowCount", false);

        $query = $queryFilter
            ->order("dt_example", "DESC")
            ->limit(0, 10)
            ->execute("fetchAll");

        $totalRegisterInTable = parent::select("COUNT(*) as count")
            ->from("example")
            ->execute("fetch")
            ->count;

        return [$totalRegisterInQuery, $query, $totalRegisterInTable];
    }
}
