<?php 
require_once BASE . "/model/initialize.model.php";

class Initialize extends SimpleController {
    public static function getMainPage() {
        try {
            $model = new InitializeModel();
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