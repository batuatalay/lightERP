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
	private function getInsertFiles() {
		$insertFiles = [];
		$insertsDir = __DIR__ . '/../migrations/insert';
		
		// Klasör yoksa oluştur
		if (!is_dir($insertsDir)) {
			return $insertFiles;
		}
		
		$files = scandir($insertsDir);
		
		foreach ($files as $file) {
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
	
	public function runAllMigrations() {
		try {
			$messages = [];
			
			$this->pdo->exec("SET FOREIGN_KEY_CHECKS=0");
			$messages[] = "Foreign key checks disabled";
			
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