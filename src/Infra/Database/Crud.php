<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Infra\Database;

// use SimplePhp\SimpleCrud\Core\Entity\QueryBuilder;
use Exception;
use PDO;
use PDOException;
use SimplePhp\SimpleCrud\Core\Entity\QueryBuilder;

class Crud
{
	private $query = "";
	private $terms = [];
	private static $error;

	private static ?PDO $pdo = null;
	private static ?QueryBuilder $queryBuilder = null;

	// private function __construct(string $query, array $terms = [])
	// {
	// 	echo "Construtor do CRUD.php";
	// }

	private static function init(): void
	{
		if (self::$pdo === null) {
			self::$pdo = Connection::getInstance();
		}
		if (self::$queryBuilder === null) {
			self::$queryBuilder = new QueryBuilder();
		}
	}

	/**
	 * @param $table
	 * @param $data
	 * @return Crud
	 */
	public static function insert(string $table, array $data): self
	{
		self::init();
		self::$queryBuilder->insert($table, $data);

		return new self;
	}

	/**
	 * Atualiza o registro especificado
	 * @param string $table nome da tabela que deseja atualizar
	 * @param string $columns colunas para atualizar. Ex: "nmlogin = ?, cdpass=? "
	 * @param array $data valores para substituirem os '?' devem ser colocados na mesma ordem
	 * @return Crud
	 */
	protected function update(string $table, string $columns, array $data): ?Crud
	{
		$this->init();
		self::$queryBuilder->update($table, $columns)->getQuery();

		$this->addTerms($data);
		// return new self;
		return $this;
	}

	/**
	 * @return Crud
	 */
	protected function delete(): ?Crud
	{
		// $this->query .= " DELETE";
		return $this;
	}


	/**
	 * @param $columns $columns = "id, nome, numero...etc";
	 * Monta a primeira parte de um clausula SELECT
	 */
	public static function select(string $columns = "*"): self
	{
		// $this->query .= " SELECT $columns";
		self::init();
		self::$queryBuilder->select($columns);

		// return $this;
		return new self;
	}


	/**
	 * @param string $table
	 * @return Crud
	 */
	public function from(string $table): ?Crud
	{
		self::$queryBuilder->from($table);
		return $this;
	}

	/**
	 * @param $conditions column1, column2 || LIKE, AND, OR
	 * @param $values
	 * @return Crud
	 */
	public function where(string $conditions, array $values = []): ?Crud
	{
		if (!empty($values)) {
			foreach ($values as $value) {
				array_push($this->terms, $value);
			}
		}

		self::$queryBuilder->where($conditions);
		return $this;
	}


	/**
	 * @param $query "SELECT * FROM foo WHERE foo_id = ?"
	 * @param $values "[1]"
	 * @return Crud
	 */
	protected function query(string $query, array $values = []): ?Crud
	{
		$this->query = $query;

		if (!empty($values)) {
			foreach ($values as $value) {
				array_push($this->terms, $value);
			}
		}
		return $this;
	}


	/**
	 * @param string $columns "column1, column2"
	 * @param string $order "ASC|DESC"
	 * @return Crud
	 */
	protected function order(string $columns, string $order = "ASC"): ?Crud
	{
		$this->query .= " ORDER BY $columns $order ";
		// $this->query .= " ORDER BY $columns ";
		return $this;
	}


	/**
	 * @param $start 0
	 * @param $end 10
	 * @return Crud
	 */
	protected function limit(int $start = 0, int $end = 10): ?Crud
	{
		$this->query .= " LIMIT $start, $end";
		return $this;
	}

	/**
	 * @param $name
	 * @param $params
	 * @return Crud
	 */
	protected function call(string $name, array $params = []): ?Crud
	{
		if (!empty($params)) {
			foreach ($params as $param) {
				array_push($this->terms, $param);
			}
		}
		$this->query .= " CALL $name";
		return $this;
	}

	/**
	 * @param $fetch fetch (retorna um objeto), fetchAll (retorna um array), rowCount (numero de linhas afetadas)
	 * @param $cleanQuery
	 * @return mixed
	 */
	protected function execute(string $fetch = "", bool $cleanQuery = true)
	{
		try {

			$conn = Connection::getInstance();

			$stmt = $conn->prepare($this->query);

			foreach ($this->terms as $key => $val) {
				$stmt->bindValue($key + 1, $val, $this->bindType($val));
			}

			if ($cleanQuery) {
				$this->query = "";
				$this->terms = [];
			}

			if ($stmt->execute()) {

				if ($fetch != "") {
					if ($fetch == "fetch") {
						$rs = $stmt->fetch(PDO::FETCH_OBJ);
					} else if ($fetch == "fetchAll") {
						$rs = $stmt->fetchAll(PDO::FETCH_ASSOC);
					} else if ($fetch == "rowCount") {
						$rs = $stmt->rowCount();
					} else if ($fetch == "lastId") {
						$rs = $conn->lastInsertId();
					}
					return $rs;
				}
				return true;
			} else {
				return false;
			}
		} catch (ConnectionException $con) {
			self::$error = $con->getError();
			throw new Exception("Falha ao conectar com o Banco de Dados: " . $con->getMessage());
		} catch (PDOException $e) {
			self::$error = $e->getMessage();
			throw new PDOException('Falha ao executar:' . self::$error);
		} catch (Exception $e) {
			self::$error = $e;
		}
	}

	/**
	 * @param $value
	 * @return PDO::PARAM_*
	 */
	private function bindType($value)
	{
		$var_type = null;

		switch (true) {
			case is_bool($value):
				$var_type = PDO::PARAM_BOOL;
				break;
			case is_int($value):
				$var_type = PDO::PARAM_INT;
				break;
			case is_null($value):
				$var_type = PDO::PARAM_NULL;
				break;
			default:
				$var_type = PDO::PARAM_STR;
		}

		return $var_type;
	}

	// protected function query(string $sql, array $data = []): ?Crud
	// {
	//     $this->query .= $sql;

	//     $this->addTerms($data);

	//     return $this;
	// }


	/**
	 * @return mixed
	 */
	protected function getQuery()
	{
		return $this->query;
	}

	/**
	 * @return mixed
	 */
	protected static function getError()
	{
		return self::$error;
	}

	private function addTerms(array $data)
	{
		foreach ($data as $value) {
			array_push($this->terms, $value);
		}
	}

	public function getSQL()
	{
		// return [
		// 	'SQL CRUD' => $this->query,
		// 	'SQL QB' => self::$queryBuilder->getQuery(),
		// ];

		return 'SQL QB:' . self::$queryBuilder->getQuery();
	}
}

