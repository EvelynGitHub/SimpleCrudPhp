<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Infra\Database;

use Exception;
use PDO;
use PDOException;
use SimplePhp\SimpleCrud\Core\Entity\QueryBuilder;

class Crud2
{
	// private $query = "";
	// private $terms = [];
	private static ?PDO $pdo = null;
	private static ?QueryBuilder $queryBuilder = null;


	private ?QueryBuilder $builder = null;



	/**
	 * Impedindo new Crud() fora da própria classe
	 */
	private function __construct()
	{
		echo "Construtor do CRUD2.php <br>";
	}

	private static function init(): void
	{
		if (self::$pdo === null) {
			self::$pdo = Connection::getInstance();
		}
		if (self::$queryBuilder === null) {
			self::$queryBuilder = QueryBuilder::newInstance();
		}
		// self::$queryBuilder = QueryBuilder::newInstance();

	}


	public function __call($name, $arguments)
	{
		if (method_exists(self::$queryBuilder, $name)) {
			echo "<p> EXISTE O método: " . $name . " Parâmetro: " . $arguments[0] . "</p>";

			call_user_func_array([self::$queryBuilder, $name], $arguments);

			// return new self();
			return $this;
		} else {

			echo "<p> NÃO EXISTE O método: " . $name . " Parâmetro: " . $arguments[0] . "</p>";
		}

		// Observação: valor de $name é sensível a maiúsculas/minúsculas.
		echo "Chamando método '$name' do <b>objeto</b> "
			. implode(', ', $arguments) . "\n";
	}

	public static function __callStatic($name, $arguments): ?self
	{
		self::init();

		if (method_exists(self::$queryBuilder, $name)) {

			echo "<p> EXISTE O método: " . $name . " Parâmetro: " . $arguments[0] . "</p>";

			call_user_func_array([self::$queryBuilder, $name], $arguments);

			return new self();
		} else {

			echo "<p> NÃO EXISTE O método: " . $name . " Parâmetro: " . $arguments[0] . "</p>";
		}


		// // Observação: valor de $name é sensível a maiúsculas/minúsculas.
		// echo "<br>";
		// echo "Chamando método '$name' <b>estático</b> "
		// 	. implode(', ', $arguments) . "\n";
		// echo "<br>";

	}


	/**
	 * @param $fetch fetch (retorna um objeto), fetchAll (retorna um array), rowCount (numero de linhas afetadas)
	 * @param $cleanQuery
	 * @return mixed
	 */
	public function execute(string $fetch = "", bool $cleanQuery = true)
	{
		try {

			$conn = Connection::getInstance();

			$query = self::$queryBuilder->getQuery();

			$stmt = $conn->prepare($query);

			foreach (self::$queryBuilder->getParams() as $key => $val) {
				// $stmt->bindValue($key + 1, $val, $this->bindType($val));
				$stmt->bindValue(":$key", $val, $this->bindType($val));
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
			throw new PDOException('Falha ao executar:' . $e->getMessage());
		} catch (Exception $e) {
			die('Exe.. Geral: ' . $e->getMessage());
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
		// return $this->query;
		return self::$queryBuilder->getQuery();
	}


	private function addTerms(array $data)
	{
		foreach ($data as $value) {
			array_push($this->terms, $value);
		}
	}
}

