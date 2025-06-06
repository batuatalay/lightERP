<?php 
require_once BASE . "/middleware/common.middleware.php";
require_once BASE . "/middleware/auth/AuthMiddleware.php";
require_once BASE . "/middleware/auth/AdminMiddleware.php";
require_once BASE . "/middleware/auth/LoginMiddleware.php";
require_once BASE . "/middleware/auth/TenantMiddleware.php";
require_once BASE . "/middleware/auth/ApiAuthMiddleware.php";
require_once BASE . "/model/organization.model.php";

class Organization extends SimpleController {

    /**
     * Get all organizations (Super Admin only)
     */
    #[LoginAttribute]
    #[AuthAttribute(role: 'superadmin')]
    public function index($params) {
        try {
            $organizations = OrganizationModel::all();
            
            // Include properties for each organization
            $organizationsWithProperties = [];
            foreach ($organizations as $org) {
                $orgModel = new OrganizationModel($org);
                $organizationsWithProperties[] = $orgModel->getWithProperties();
            }

            $this->jsonResponse([
                'success' => true,
                'data' => $organizationsWithProperties,
                'count' => count($organizationsWithProperties)
            ]);
        } catch (Exception $e) {
            $this->errorResponse('Error fetching organizations: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get current user's organization
     */
    #[LoginAttribute]
    #[TenantAttribute]
    public function show($params) {
        try {
            $orgId = $params['organization_id'] ?? SessionHelper::getCurrentOrganization();
            
            if (!$orgId) {
                $this->errorResponse('Organization ID required', 400);
                return;
            }

            $organization = OrganizationModel::find($orgId);
            
            if (!$organization) {
                $this->errorResponse('Organization not found', 404);
                return;
            }

            $orgModel = new OrganizationModel($organization->toArray());
            $orgData = $orgModel->getWithProperties();

            $this->jsonResponse([
                'success' => true,
                'data' => $orgData
            ]);
        } catch (Exception $e) {
            $this->errorResponse('Error fetching organization: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create new organization (Super Admin only)
     */
    #[LoginAttribute]
    #[AuthAttribute(role: 'superadmin')]
    public function create($params) {
        try {
            $input = $this->getJsonInput();
            
            // Validate required fields
            if (empty($input['organization_name'])) {
                $this->errorResponse('Organization name is required', 400);
                return;
            }

            // Create organization
            $organization = new OrganizationModel([
                'organization_name' => $input['organization_name'],
                'organization_slug' => $input['organization_slug'] ?? null,
                'status' => $input['status'] ?? 'trial'
            ]);

            if ($organization->save()) {
                // Set additional properties if provided
                if (isset($input['properties']) && is_array($input['properties'])) {
                    foreach ($input['properties'] as $key => $value) {
                        $type = $this->detectPropertyType($value);
                        $organization->setProperty($key, $value, $type);
                    }
                }

                $orgData = $organization->getWithProperties();

                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Organization created successfully',
                    'data' => $orgData
                ], 201);
            } else {
                $this->errorResponse('Failed to create organization', 500);
            }
        } catch (Exception $e) {
            $this->errorResponse('Error creating organization: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update organization
     */
    #[LoginAttribute]
    #[TenantAttribute]
    #[AuthAttribute(role: 'admin')]
    public function update($params) {
        try {
            $orgId = $params['organization_id'] ?? SessionHelper::getCurrentOrganization();
            $input = $this->getJsonInput();

            if (!$orgId) {
                $this->errorResponse('Organization ID required', 400);
                return;
            }

            $organization = OrganizationModel::find($orgId);
            
            if (!$organization) {
                $this->errorResponse('Organization not found', 404);
                return;
            }

            // Update basic fields
            if (isset($input['organization_name'])) {
                $organization->organization_name = $input['organization_name'];
            }
            if (isset($input['status'])) {
                $organization->status = $input['status'];
            }

            if ($organization->save()) {
                // Update properties if provided
                if (isset($input['properties']) && is_array($input['properties'])) {
                    foreach ($input['properties'] as $key => $value) {
                        $type = $this->detectPropertyType($value);
                        $organization->setProperty($key, $value, $type);
                    }
                }

                $orgData = $organization->getWithProperties();

                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Organization updated successfully',
                    'data' => $orgData
                ]);
            } else {
                $this->errorResponse('Failed to update organization', 500);
            }
        } catch (Exception $e) {
            $this->errorResponse('Error updating organization: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete organization (Super Admin only)
     */
    #[LoginAttribute]
    #[AuthAttribute(role: 'superadmin')]
    public function delete($params) {
        try {
            $orgId = $params['organization_id'] ?? null;

            if (!$orgId) {
                $this->errorResponse('Organization ID required', 400);
                return;
            }

            $organization = OrganizationModel::find($orgId);
            
            if (!$organization) {
                $this->errorResponse('Organization not found', 404);
                return;
            }

            // Soft delete
            $orgModel = new OrganizationModel($organization->toArray());
            if ($orgModel->softDelete()) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Organization deleted successfully'
                ]);
            } else {
                $this->errorResponse('Failed to delete organization', 500);
            }
        } catch (Exception $e) {
            $this->errorResponse('Error deleting organization: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Set organization property
     */
    #[LoginAttribute]
    #[TenantAttribute]
    #[AuthAttribute(role: 'admin')]
    public function setProperty($params) {
        try {
            $orgId = $params['organization_id'] ?? SessionHelper::getCurrentOrganization();
            $input = $this->getJsonInput();

            if (!$orgId) {
                $this->errorResponse('Organization ID required', 400);
                return;
            }

            if (empty($input['key'])) {
                $this->errorResponse('Property key is required', 400);
                return;
            }

            $organization = OrganizationModel::find($orgId);
            
            if (!$organization) {
                $this->errorResponse('Organization not found', 404);
                return;
            }

            $orgModel = new OrganizationModel($organization->toArray());
            $type = $input['type'] ?? $this->detectPropertyType($input['value']);
            
            if ($orgModel->setProperty($input['key'], $input['value'], $type)) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Property set successfully',
                    'data' => [
                        'key' => $input['key'],
                        'value' => $input['value'],
                        'type' => $type
                    ]
                ]);
            } else {
                $this->errorResponse('Failed to set property', 500);
            }
        } catch (Exception $e) {
            $this->errorResponse('Error setting property: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get organization property
     */
    #[LoginAttribute]
    #[TenantAttribute]
    public function getProperty($params) {
        try {
            $orgId = $params['organization_id'] ?? SessionHelper::getCurrentOrganization();
            $key = $params['key'] ?? null;

            if (!$orgId || !$key) {
                $this->errorResponse('Organization ID and property key are required', 400);
                return;
            }

            $organization = OrganizationModel::find($orgId);
            
            if (!$organization) {
                $this->errorResponse('Organization not found', 404);
                return;
            }

            $orgModel = new OrganizationModel($organization->toArray());
            $value = $orgModel->getProperty($key);

            if ($value !== null) {
                $this->jsonResponse([
                    'success' => true,
                    'data' => [
                        'key' => $key,
                        'value' => $value
                    ]
                ]);
            } else {
                $this->errorResponse('Property not found', 404);
            }
        } catch (Exception $e) {
            $this->errorResponse('Error getting property: ' . $e->getMessage(), 500);
        }
    }



    /**
     * Organization dashboard view
     */
    // [LoginAttribute]
    // [TenantAttribute]
    public function dashboard($params) {
        try {
            $orgId = SessionHelper::getCurrentOrganization();
            $orgId = "550e8400-e29b-41d4-a716-446655440001";
            $organization = OrganizationModel::find($orgId);
            var_dump($organization);
            if (!$organization) {
                throw new Exception('Organization not found');
            }

            $orgModel = new OrganizationModel($organization->toArray());
            $orgData = $orgModel->getWithProperties();

            // Include the view
            include BASE . '/view/organization/dashboard.php';
        } catch (Exception $e) {
            echo '<div style="color: red; padding: 20px;">
                Error: ' . htmlspecialchars($e->getMessage()) . '
            </div>';
        }
    }

    /**
     * Utility method to detect property type
     */
    private function detectPropertyType($value) {
        if (is_bool($value)) return 'boolean';
        if (is_int($value)) return 'integer';
        if (is_array($value)) return 'json';
        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) return 'date';
        return 'string';
    }

    /**
     * Get JSON input from request body
     */
    private function getJsonInput() {
        $input = json_decode(file_get_contents('php://input'), true);
        return $input ?: [];
    }

    /**
     * Send JSON response
     */
    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Send error response
     */
    private function errorResponse($message, $statusCode = 400) {
        $this->jsonResponse([
            'success' => false,
            'error' => $message
        ], $statusCode);
    }
}