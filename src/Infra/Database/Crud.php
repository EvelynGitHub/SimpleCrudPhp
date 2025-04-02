<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Infra\Database;

use Exception;
use PDO;
use PDOException;
use SimplePhp\SimpleCrud\Core\Entity\QueryBuilder;

/**
 * @method static QueryBuilder|Crud select(...$columns)
 * @method static QueryBuilder insert(string $table, array|Crud $data, ?string $columns = null)
 * @method static QueryBuilder update(string $table, array $data)
 */
class Crud
{
	private static ?PDO $pdo = null;
	private ?QueryBuilder $queryBuilder = null;

	/**
	 * Impedindo new Crud() fora da própria classe
	 */
	private function __construct(QueryBuilder $queryBuilder)
	{
		self::init();
		$this->queryBuilder = $queryBuilder;

		echo "<p> ### Chamando construtor do Crud. ### </p>" . PHP_EOL;
	}

	private static function init(): void
	{
		if (self::$pdo === null) {
			self::$pdo = Connection::getInstance();
		}
	}

	public function getQueryBuilder(): QueryBuilder
	{
		return $this->queryBuilder;
	}

	// public static function customQuery(string $name, array $params = []): self
	// {
	// 	self::init();

	// 	try {
	// 		self::$queryBuilder->customQuery($name, $params);
	// 		// QueryBuilder::getInstance()->customQuery($name, $params);
	// 	} catch (Exception $e) {
	// 		throw new Exception("Erro ao executar consulta personalizada: " . $e->getMessage());
	// 	}

	// 	return new self;
	// }

	/**
	 * @param $fetch fetch (retorna um objeto), fetchAll (retorna um array), rowCount (numero de linhas afetadas)
	 * @param $cleanQuery
	 * @return mixed
	 */
	public function execute(string $fetch = "", bool $cleanQuery = true)
	{
		try {

			$conn = Connection::getInstance();

			$query = $this->queryBuilder->getQuery();

			$stmt = $conn->prepare($query);

			foreach ($this->queryBuilder->getParams() as $key => $val) {
				if (is_string($key)) {
					$stmt->bindValue(":$key", $val, $this->bindType($val));
				} else {
					$stmt->bindValue($key + 1, $val, $this->bindType($val));
				}
			}

			if ($cleanQuery) {
				// $this->query = "";
				// $this->terms = [];

				// Destruir $this->queryBuilder ou só limpar
				$this->queryBuilder->reset();
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
			throw new Exception("Falha ao conectar com o Banco de Dados: " . $con->getMessage());
		} catch (PDOException $e) {
			throw new PDOException('Falha ao executar:' . $e->getMessage() . "  Linha: " . $e->getLine());
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


	public function getQuery()
	{
		return $this->queryBuilder->getQuery();
	}

	/**
	 * Captura chamadas estáticas e retorna um Crud em vez de um QueryBuilder
	 * @param string $name nome do método
	 * @param array $arguments parâmetros que serão passados para o método
	 * @return QueryBuilder
	 * @throws \BadMethodCallException
	 */
	public static function __callStatic($name, $arguments): self
	{

		echo "<p> ## Chamada Static do Crud. $name ## </p>" . PHP_EOL;

		$queryBuilder = QueryBuilder::newInstance();

		if (method_exists($queryBuilder, $name)) {
			call_user_func_array([$queryBuilder, $name], $arguments);
			return new self($queryBuilder); // 🔥 Retorna Crud, não QueryBuilder
		}

		throw new \BadMethodCallException("Método '$name' não encontrado no QueryBuilder.");
	}

	/**
	 * Encaminha chamadas para QueryBuilder e mantém encadeamento
	 * @param string $name nome do método
	 * @param array $arguments parâmetros que serão passados para o método
	 * @return QueryBuilder
	 * @throws \BadMethodCallException
	 */
	public function __call($name, $arguments): self
	{
		echo "<p> ## Chamada Obj do Crud. $name ## </p>" . PHP_EOL;

		if (method_exists($this->queryBuilder, $name)) {
			call_user_func_array([$this->queryBuilder, $name], $arguments);
			return $this;
		}

		throw new \BadMethodCallException("Método '$name' não encontrado no QueryBuilder.");
	}

	public function __toString()
	{
		return $this->queryBuilder->getQuery();
	}
}

