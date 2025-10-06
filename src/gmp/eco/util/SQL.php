<?php
declare(strict_types=1);

namespace gmp\eco\util;

class SQL {
	private $pdo;
	private $type;
	private $table = 'data';
	private $buffer = [];
	private $initialized = false;

	public function __construct(array $config, string $table_name, string $filedir = "") {
		$this->table = $table_name;

		$this->type = $config['type'];

		if ($this->type === 'sqlite') {
			$file = $filedir.($config['sqlite']['file'] ?? $filedir.'data.sqlite');
			$dsn = "sqlite:" . $file;
			$this->pdo = new \PDO($dsn);
		} elseif ($this->type === 'mysql') {
			$mysql = $config['mysql'];
			$dsn = "mysql:host={$mysql['host']};dbname=minecraft;charset=utf8";
			$this->pdo = new \PDO($dsn, $mysql['username'], $mysql['password']);
		} else {
			throw new \Exception("Unsupported database type: " . $this->type);
		}

		if ($this->pdo === null) {
			throw new \RuntimeException('Failed to initialize PDO connection');
		}

		$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	}

	private function initialize() {
		if ($this->initialized) return;
	
		$sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (
	`key` VARCHAR(191) PRIMARY KEY,
	`value` TEXT
)";
		$this->pdo->exec($sql);
		$this->initialized = true;
	}

	public function get(string $key, $default = null) {
		$this->initialize();
		$stmt = $this->pdo->prepare("SELECT `value` FROM `{$this->table}` WHERE `key` = :key LIMIT 1");
		$stmt->execute(['key' => $key]);
		$result = $stmt->fetchColumn();

		if ($result === false) {
			return $default;
		}

		$unserialized = unserialize($result);
		return $unserialized !== false ? $unserialized : $default;
	}

	public function set(string $key, $value): void {
		$this->initialize();
		$this->buffer[$key] = $value;
		$this->save();
	}

	public function setDefaults(array $defaults): void {
		$this->initialize();
		foreach ($defaults as $key => $value) {
			if (!array_key_exists($key, $this->buffer)) {
				$existing = $this->get($key);
				if ($existing === null) {
					$this->buffer[$key] = $value;
				}
			}
		}
		$this->save();
	}

	public function save(): void {
		$this->initialize();
		
		// Prepare the appropriate statement based on database type
		if ($this->type === 'sqlite') {
			$stmt = $this->pdo->prepare("
				INSERT OR REPLACE INTO `{$this->table}` (`key`, `value`) 
				VALUES (:key, :value)
			");
		} else {
			$stmt = $this->pdo->prepare("
				INSERT INTO `{$this->table}` (`key`, `value`) 
				VALUES (:key, :value)
				ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)
			");
		}
	
		foreach ($this->buffer as $key => $value) {
			$stmt->execute([
				'key' => $key,
				'value' => serialize($value)
			]);
		}
	
		$this->buffer = [];
	}

	public function close(): void {
		$this->pdo = null;
	}
}
