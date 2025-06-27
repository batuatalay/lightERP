<?php 
require_once BASE . "/middleware/common.middleware.php";
require_once BASE . "/model/user.model.php";
require_once BASE . "/middleware/organization/Organization.middleware.php";

class Main extends SimpleController {

    #[OrganizationAdminAttribute]
    public static function testFunction() {
        var_dump('test');exit;
    }


    public static function getMainPage() {
        try {
            //$companies = Company::getAll();
            $users = UserModel::getAllUsers();
            echo '<!DOCTYPE html>
            <html>
            <head>
                <title>LightERP - Ana Sayfa</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        margin: 20px;
                        background-color: #f5f5f5;
                    }
                    .container {
                        max-width: 800px;
                        margin: 0 auto;
                        background-color: white;
                        padding: 20px;
                        border-radius: 8px;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    }
                    h1 {
                        color: #333;
                        margin-bottom: 20px;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-top: 20px;
                    }
                    th, td {
                        padding: 12px;
                        text-align: left;
                        border-bottom: 1px solid #ddd;
                    }
                    th {
                        background-color: #f8f9fa;
                        font-weight: bold;
                    }
                    tr:hover {
                        background-color: #f5f5f5;
                    }
                    .role-admin {
                        color: #dc3545;
                        font-weight: bold;
                    }
                    .role-user {
                        color: #28a745;
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <h1>Kullanıcı Listesi</h1>
                    <table>
                        <thead>
                            <tr>
                                <th>Kullanıcı Adı</th>
                                <th>E-posta</th>
                                <th>Organizasyon</th>
                                <th>Rol</th>
                            </tr>
                        </thead>
                        <tbody>';
            
            foreach ($users as $user) {
                $permissions = str_replace(";", '<br>', $user['all_permissions']);
                echo '<tr>
                    <td>' . htmlspecialchars($user['username']) . '</td>
                    <td>' . htmlspecialchars($user['email']) . '</td>
                    <td>' . htmlspecialchars($user['organization']) . '</td>
                    <td class="' . $user['type'] . '">' . $permissions . '</td>
                </tr>';
            }
            
            echo '</tbody>
                    </table>
                </div>
            </body>
            </html>';
            
        } catch (Exception $e) {
            echo '<div style="color: red; padding: 20px;">
                Hata: ' . htmlspecialchars($e->getMessage()) . '
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