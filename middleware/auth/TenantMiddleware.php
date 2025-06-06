<?php
require_once BASE . "/helper/session.helper.php";

#[Attribute]
class TenantAttribute {
    public function __construct(
        public readonly bool $strict = true
    ) {}

    public function handle($next, $params) {
        // Check if user is logged in first
        if (!SessionHelper::isLoggedIn()) {
            header("Location: /login");
            exit;
        }

        // Get user's organization
        $userOrgId = SessionHelper::getCurrentOrganization();
        
        if (!$userOrgId) {
            http_response_code(403);
            echo json_encode([
                'error' => 'No organization context found',
                'code' => 'TENANT_MISSING'
            ]);
            exit;
        }

        // Get organization from URL parameter or request
        $requestedOrgId = $this->getRequestedOrganization($params);
        
        // If no specific organization requested, inject user's organization
        if (!$requestedOrgId) {
            $params['organization_id'] = $userOrgId;
        } 
        // If organization requested, validate access
        else if ($this->strict && $requestedOrgId !== $userOrgId) {
            // Super admin can access any organization
            if (!SessionHelper::isSuperAdmin()) {
                http_response_code(403);
                echo json_encode([
                    'error' => 'Access denied to organization',
                    'code' => 'TENANT_ACCESS_DENIED',
                    'requested_org' => $requestedOrgId,
                    'user_org' => $userOrgId
                ]);
                exit;
            }
        }

        // Set organization context for this request
        $this->setOrganizationContext($userOrgId);
        
        echo "TenantMiddleware: Organization context set to {$userOrgId}<br>";
        return $next($params);
    }

    /**
     * Get organization ID from request parameters
     */
    private function getRequestedOrganization($params): ?string {
        // From URL parameter
        if (isset($params['organization_id'])) {
            return $params['organization_id'];
        }
        
        // From POST data
        if (isset($_POST['organization_id'])) {
            return $_POST['organization_id'];
        }
        
        // From JSON body
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input && isset($input['organization_id'])) {
            return $input['organization_id'];
        }
        
        // From subdomain (if using tenant.domain.com structure)
        $subdomain = $this->getSubdomain();
        if ($subdomain) {
            return $this->getOrganizationBySlug($subdomain);
        }
        
        return null;
    }

    /**
     * Get subdomain from request
     */
    private function getSubdomain(): ?string {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $parts = explode('.', $host);
        
        // If more than 2 parts, first part is subdomain
        if (count($parts) > 2) {
            return $parts[0];
        }
        
        return null;
    }

    /**
     * Get organization ID by slug
     */
    private function getOrganizationBySlug(string $slug): ?string {
        // This would typically query the database
        // For now, return null - will be implemented with Organization model
        return null;
    }

    /**
     * Set organization context for BaseORM queries
     */
    private function setOrganizationContext(string $organizationId): void {
        // Set global organization context that BaseORM can use
        define('CURRENT_ORGANIZATION_ID', $organizationId);
        
        // Alternative: Set in a registry or static variable
        $_SESSION['current_organization_context'] = $organizationId;
    }
}