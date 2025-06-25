<?php 
require_once BASE . "/model/initialize.model.php";

class Initialize extends SimpleController {
    private static function createInitialUsers($model) {
        $users = [
            [
                'username' => 'admin',
                'name' => 'admin',
                'email' => 'admin@example.com',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
            ],
            [
                'username' => 'john_doe',
                'name' => 'john_doe',
                'email' => 'john@example.com',
                'password' => password_hash('user123', PASSWORD_DEFAULT),
            ],
            [
                'username' => 'jane_smith',
                'name' => 'jane_smith',
                'email' => 'jane@example.com',
                'password' => password_hash('user123', PASSWORD_DEFAULT),
            ],
            [
                'username' => 'mike_wilson',
                'name' => 'mike_wilson',
                'email' => 'mike@example.com',
                'password' => password_hash('user123', PASSWORD_DEFAULT),
            ],
            [
                'username' => 'sarah_brown',
                'name' => 'sarah_brown',
                'email' => 'sarah@example.com',
                'password' => password_hash('user123', PASSWORD_DEFAULT),
            ]
        ];

        foreach ($users as $user) {
            $model->createUser($user);
        }

        return count($users);
    }

    public static function getMainPage() {
        try {
            $model = new InitializeModel();
            
            // First check if tables exist
            if ($model->checkIfTablesExist()) {
                header("Location: /main");
                exit;
            }
            
            $messages = $model->runAllMigrations();
            
            // Create initial users
            echo "<h1>Database Migration</h1>";
            echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; border-radius: 4px;'>";
            echo "<h3>✅ Database tables created successfully!</h3>";
            echo "<ul>";
            foreach ($messages as $message) {
                echo "<li>" . htmlspecialchars($message) . "</li>";
            }
            echo "</ul>";
            echo "<p>You will be redirected to main page in 3 seconds...</p>";
            echo "</div>";
            
            // Redirect to main page after 3 seconds
            echo "<script>setTimeout(function() { window.location.href = '/main'; }, 3000);</script>";
            
        } catch (Exception $e) {
            echo '<div style="color: red; padding: 20px;">
                Error: ' . htmlspecialchars($e->getMessage()) . '
            </div>';
        }
    }

    // This method will perform both Login and Auth checks
    #[LoginAttribute]
    #[AdminAttribute]
    public static function getDashboard() {
        echo 'Dashboard Page<br>';
        exit;
    }
}