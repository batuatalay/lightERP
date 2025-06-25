<?php

class InitializeModel extends Mysql {
	private $tableName = "migrations";
	
	public function __construct($arr = []) {
		$this->pdo = $this->connect();
		foreach ($arr as $key => $value) {
			$this->$key = $value;
		}
	}

	public function createUser($userData) {
		try {
			$sql = "INSERT INTO users (username, name, email, password) VALUES (:username, :name, :email, :password)";
			$stmt = $this->pdo->prepare($sql);
			$stmt->execute([
				':username' => $userData['username'],
				':name' => $userData['name'],
				':email' => $userData['email'],
				':password' => $userData['password']
			]);
			return true;
		} catch (PDOException $e) {
			throw new Exception("Error creating user: " . $e->getMessage());
		}
	}

	private function getRequiredTables() {
		$tables = [];
		$migrationsDir = __DIR__ . '/../migrations';
		
		// Read all files in directory
		$files = scandir($migrationsDir);
		
		foreach ($files as $file) {
			// Skip . and .. directories
			if ($file === '.' || $file === '..') {
				continue;
			}
			
			if (strpos($file, 'create') !== false && strpos($file, 'table') !== false) {
				$tableName = str_replace(['create_', '_table.sql'], '', $file);
				$tableName = preg_replace('/^\d+_/', '', $tableName);
				$tables[] = $tableName;
			}
		}
		
		return $tables;
	}

	private function getInsertFiles() {
		$insertFiles = [];
		$insertsDir = __DIR__ . '/../migrations/insert';
		
		// Klasör yoksa oluştur
		if (!is_dir($insertsDir)) {
			return $insertFiles;
		}
		
		$files = scandir($insertsDir);
		
		foreach ($files as $file) {
			// Skip . and .. directories
			if ($file === '.' || $file === '..') {
				continue;
			}
			
			if (strpos($file, 'insert') !== false && pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
				$insertFiles[] = $insertsDir . '/' . $file;
			}
		}
		
		// Dosyaları sırala (001_, 002_ vs.)
		sort($insertFiles);
		
		return $insertFiles;
	}

	private function hasDataInTables() {
		try {
			// Temel tabloları kontrol et - eğer data varsa insert yapma
			$tables = ['organizations', 'users', 'permissions'];
			
			foreach ($tables as $table) {
				$stmt = $this->pdo->query("SELECT COUNT(*) FROM {$table}");
				$count = $stmt->fetchColumn();
				
				if ($count > 0) {
					return true; // Herhangi bir tabloda data varsa true döner
				}
			}
			
			return false; // Hiç data yoksa false
		} catch (Exception $e) {
			// Tablo yoksa false döner
			return false;
		}
	}

	public function checkIfTablesExist() {
		try {
			$tables = $this->getRequiredTables();
			
			// Check all tables
			foreach ($tables as $table) {
				$stmt = $this->pdo->query("SHOW TABLES LIKE '{$table}'");
				if ($stmt->rowCount() == 0) {
					// Return false if any table is missing
					return false;
				}
			}
			// Return true if all tables exist
			return true;
		} catch (Exception $e) {
			throw new Exception("Error checking tables: " . $e->getMessage());
		}
	}
	
	public function runAllMigrations() {
		try {
			// First check if tables exist and have data
			if ($this->checkIfTablesExist() && $this->hasDataInTables()) {
				// If all tables exist and have data, redirect to main page
				header("Location: /main");
				exit;
			}

			$messages = [];
			
			// Disable foreign key checks
			$this->pdo->exec("SET FOREIGN_KEY_CHECKS=0");
			$messages[] = "Foreign key checks disabled";
			
			// First drop all existing tables
			$tables = $this->getRequiredTables();
			foreach ($tables as $table) {
				$this->pdo->exec("DROP TABLE IF EXISTS {$table}");
				$messages[] = "{$table} table dropped (if existed)";
			}
			
			// STEP 1: Run table creation migrations
			$migrations = glob(BASE . '/migrations/*.sql');
			sort($migrations);
			
			foreach ($migrations as $migration) {
				$sql = file_get_contents($migration);
				$filename = basename($migration);
				
				$messages[] = "Starting table migration: {$filename}";
				
				// Split SQL file into separate commands
				$statements = array_filter(
					array_map('trim', explode(';', $sql)),
					function($sql) { return !empty($sql); }
				);
				
				// Execute each command
				foreach ($statements as $statement) {
					if (!empty(trim($statement))) {
						$this->pdo->exec($statement);
					}
				}
				
				$messages[] = "Table migration completed: {$filename}";
			}
			
			$insertFiles = $this->getInsertFiles();
			if (!empty($insertFiles)) {
				$messages[] = "Starting data insertions...";
				
				foreach ($insertFiles as $insertFile) {
					$sql = file_get_contents($insertFile);
					$filename = basename($insertFile);
					
					$messages[] = "Starting data insert: {$filename}";
					
					// Split SQL file into separate commands
					$statements = array_filter(
						array_map('trim', explode(';', $sql)),
						function($sql) { return !empty($sql); }
					);
					
					// Execute each command
					foreach ($statements as $statement) {
						if (!empty(trim($statement))) {
							$this->pdo->exec($statement);
						}
					}
					
					$messages[] = "Data insert completed: {$filename}";
				}
				
				$messages[] = "All data insertions completed successfully!";
			} else {
				$messages[] = "No insert files found in migrations/inserts/ directory";
			}
			
			// Re-enable foreign key checks
			$this->pdo->exec("SET FOREIGN_KEY_CHECKS=1");
			$messages[] = "Foreign key checks re-enabled";
			$messages[] = "Database setup completed successfully!";

			return $messages;
			
		} catch (Exception $e) {
			// Re-enable foreign key checks in case of error
			$this->pdo->exec("SET FOREIGN_KEY_CHECKS=1");
			throw new Exception("Migration error: " . $e->getMessage());
		}
	}
}