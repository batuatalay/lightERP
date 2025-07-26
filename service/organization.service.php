<?php
class OrganizationService extends BaseService {

    public function createOrganization($params) {
        return self::executeInTransaction(function() use ($params) {
            // Validate required fields
            $this->validateOrganizationData($params);
            
            // Set current timestamp
            DateHelper::now();
            
            // Create organization
            $organizationID = OrganizationModel::create($params);
            
            // Create organization user relationship
            $userID = SessionHelper::getUserData('id');
            OrganizationModel::createOrganizationUser($userID, $organizationID, 'admin');
            
            // Create organization properties
            if (isset($params['properties'])) {
                OrganizationModel::createProperties($organizationID, $params['properties']);
            }
            
            return $organizationID;
        });
    }

    public function deleteOrganization($params) {
        // Validate required fields for deletion
        $this->validateDeleteParams($params);
        
        $result = OrganizationModel::deleteByUUID($params);
        
        if (!$result) {
            throw new DatabaseException("Organization delete operation failed", "DELETE_FAILED");
        }
        
        return $result;
    }

    private function validateOrganizationData($params) {
        $requiredFields = ['organization_name'];
        self::validateRequired($requiredFields, $params);
        
        // Organization name validation
        if (isset($params['organization_name']) && strlen(trim($params['organization_name'])) < 2) {
            throw new ValidationException("Organization name must be at least 2 characters long", "INVALID_NAME_LENGTH");
        }
        
        // Status validation
        if (isset($params['status'])) {
            $validStatuses = ['active', 'inactive', 'pending', 'suspended'];
            if (!in_array($params['status'], $validStatuses)) {
                throw new ValidationException("Invalid status. Must be one of: " . implode(', ', $validStatuses), "INVALID_STATUS");
            }
        }
        
        // Properties validation
        if (isset($params['properties'])) {
            $this->validatePropertiesArray($params['properties']);
        }
        
        // Email validation (if provided)
        if (isset($params['email']) && !empty($params['email']) && !filter_var($params['email'], FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException("Invalid email format", "INVALID_EMAIL_FORMAT");
        }
    }

    private function validateDeleteParams($params) {
        $requiredFields = ['uuid'];
        self::validateRequired($requiredFields, $params);
        
        if (isset($params['uuid']) && !$this->isValidUUID($params['uuid'])) {
            throw new ValidationException("Invalid UUID format", "INVALID_UUID_FORMAT");
        }
    }

    private function isValidUUID($uuid) {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid);
    }

    public function getOrganizationById($organizationId) {
        if (!$this->isValidUUID($organizationId)) {
            throw new ValidationException("Invalid organization ID format", "INVALID_UUID_FORMAT");
        }
        
        return OrganizationModel::getById($organizationId);
    }

    public function updateOrganization($organizationId, $params) {
        return self::executeInTransaction(function() use ($organizationId, $params) {
            // Validate organization ID
            if (!$this->isValidUUID($organizationId)) {
                throw new ValidationException("Invalid organization ID format", "INVALID_UUID_FORMAT");
            }
            
            // Validate update data
            $this->validateUpdateData($params);
            
            // Update organization
            return OrganizationModel::update($organizationId, $params);
        });
    }

    private function validateUpdateData($params) {
        if (isset($params['organization_name']) && strlen(trim($params['organization_name'])) < 2) {
            throw new ValidationException("Organization name must be at least 2 characters long", "INVALID_NAME_LENGTH");
        }
        
        if (isset($params['status'])) {
            $validStatuses = ['active', 'inactive', 'pending', 'suspended'];
            if (!in_array($params['status'], $validStatuses)) {
                throw new ValidationException("Invalid status. Must be one of: " . implode(', ', $validStatuses), "INVALID_STATUS");
            }
        }
        
        if (isset($params['properties'])) {
            $this->validatePropertiesArray($params['properties']);
        }
        
        if (isset($params['email']) && !empty($params['email']) && !filter_var($params['email'], FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException("Invalid email format", "INVALID_EMAIL_FORMAT");
        }
    }

    private function validatePropertiesArray($properties) {
        if (!is_array($properties)) {
            throw new ValidationException("Properties must be an array", "INVALID_PROPERTIES_FORMAT");
        }
        
        foreach ($properties as $index => $property) {
            if (!is_array($property)) {
                throw new ValidationException("Property at index $index must be an object", "INVALID_PROPERTY_FORMAT");
            }
            
            // Required fields for each property
            $requiredPropertyFields = ['key', 'value', 'type'];
            foreach ($requiredPropertyFields as $field) {
                if (!isset($property[$field]) || empty($property[$field])) {
                    throw new ValidationException("Property at index $index is missing required field: $field", "MISSING_PROPERTY_FIELD");
                }
            }
            
            // Validate property type
            $validTypes = ['string', 'integer', 'boolean', 'decimal', 'date', 'json'];
            if (!in_array($property['type'], $validTypes)) {
                throw new ValidationException("Invalid property type '{$property['type']}' at index $index. Must be one of: " . implode(', ', $validTypes), "INVALID_PROPERTY_TYPE");
            }
            
            // Validate value based on type
            $this->validatePropertyValue($property, $index);
        }
    }

    private function validatePropertyValue($property, $index) {
        $key = $property['key'];
        $value = $property['value'];
        $type = $property['type'];
        
        switch ($type) {
            case 'integer':
                if (!is_numeric($value) || !ctype_digit((string)$value)) {
                    throw new ValidationException("Property '$key' at index $index must be a valid integer", "INVALID_INTEGER_VALUE");
                }
                break;
                
            case 'boolean':
                $validBooleans = ['true', 'false', '1', '0', 'yes', 'no'];
                if (!in_array(strtolower($value), $validBooleans)) {
                    throw new ValidationException("Property '$key' at index $index must be a valid boolean (true/false, 1/0, yes/no)", "INVALID_BOOLEAN_VALUE");
                }
                break;
                
            case 'decimal':
                if (!is_numeric($value)) {
                    throw new ValidationException("Property '$key' at index $index must be a valid decimal number", "INVALID_DECIMAL_VALUE");
                }
                break;
                
            case 'date':
                if (!strtotime($value)) {
                    throw new ValidationException("Property '$key' at index $index must be a valid date format", "INVALID_DATE_VALUE");
                }
                break;
                
            case 'json':
                json_decode($value);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new ValidationException("Property '$key' at index $index must be valid JSON", "INVALID_JSON_VALUE");
                }
                break;
                
            case 'string':
            default:
                // String validation - just check it's not empty
                if (empty(trim($value))) {
                    throw new ValidationException("Property '$key' at index $index cannot be empty", "EMPTY_STRING_VALUE");
                }
                break;
        }
    }
}