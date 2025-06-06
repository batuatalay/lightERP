<?php

class OrganizationModel extends BaseORM {
    protected static $table = 'organizations';
    protected static $primaryKey = 'organization_id';
    protected static $fillable = [
        'organization_name',
        'organization_slug', 
        'status'
    ];

    /**
     * Get organization with all its properties
     */
    public function getWithProperties() {
        $organization = $this->toArray();
        $properties = $this->getProperties();
        
        // Merge properties into organization array
        foreach ($properties as $property) {
            $organization[$property['property_key']] = $this->castPropertyValue(
                $property['property_value'], 
                $property['property_type']
            );
        }
        
        return $organization;
    }

    /**
     * Get all properties for this organization
     */
    public function getProperties() {
        if (!$this->organization_id) {
            return [];
        }

        $sql = "SELECT property_key, property_value, property_type 
                FROM organization_properties 
                WHERE organization_id = ?";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$this->organization_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error fetching organization properties: " . $e->getMessage());
        }
    }

    /**
     * Set a property for this organization
     */
    public function setProperty($key, $value, $type = 'string') {
        if (!$this->organization_id) {
            throw new Exception("Organization must be saved before setting properties");
        }

        $sql = "INSERT INTO organization_properties 
                (organization_id, property_key, property_value, property_type) 
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                property_value = VALUES(property_value),
                property_type = VALUES(property_type),
                updated_at = CURRENT_TIMESTAMP";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                $this->organization_id,
                $key,
                $this->formatPropertyValue($value, $type),
                $type
            ]);
        } catch (PDOException $e) {
            throw new Exception("Error setting organization property: " . $e->getMessage());
        }
    }

    /**
     * Get a specific property value
     */
    public function getProperty($key, $default = null) {
        if (!$this->organization_id) {
            return $default;
        }

        $sql = "SELECT property_value, property_type 
                FROM organization_properties 
                WHERE organization_id = ? AND property_key = ?";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$this->organization_id, $key]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                return $default;
            }

            return $this->castPropertyValue($result['property_value'], $result['property_type']);
        } catch (PDOException $e) {
            throw new Exception("Error getting organization property: " . $e->getMessage());
        }
    }

    /**
     * Delete a property
     */
    public function deleteProperty($key) {
        if (!$this->organization_id) {
            return false;
        }

        $sql = "DELETE FROM organization_properties 
                WHERE organization_id = ? AND property_key = ?";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$this->organization_id, $key]);
        } catch (PDOException $e) {
            throw new Exception("Error deleting organization property: " . $e->getMessage());
        }
    }

    /**
     * Generate UUID for new organizations
     */
    protected function create() {
        // Generate UUID if not provided
        if (!isset($this->attributes['organization_id'])) {
            $this->attributes['organization_id'] = $this->generateUUID();
        }

        // Generate slug if not provided
        if (!isset($this->attributes['organization_slug']) && isset($this->attributes['organization_name'])) {
            $this->attributes['organization_slug'] = $this->generateSlug($this->attributes['organization_name']);
        }

        return parent::create();
    }

    /**
     * Generate a unique slug for organization
     */
    private function generateSlug($name) {
        $base_slug = $this->seflink($name);
        $slug = $base_slug;
        $counter = 1;

        // Check for slug uniqueness
        while ($this->slugExists($slug)) {
            $slug = $base_slug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if slug already exists
     */
    private function slugExists($slug) {
        $sql = "SELECT COUNT(*) FROM " . static::$table . " WHERE organization_slug = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$slug]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Generate UUID v4
     */
    private function generateUUID() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Format property value for storage
     */
    private function formatPropertyValue($value, $type) {
        switch ($type) {
            case 'json':
                return json_encode($value);
            case 'boolean':
                return $value ? '1' : '0';
            case 'date':
                return is_string($value) ? $value : date('Y-m-d H:i:s', $value);
            default:
                return (string) $value;
        }
    }

    /**
     * Cast property value from storage
     */
    private function castPropertyValue($value, $type) {
        switch ($type) {
            case 'json':
                return json_decode($value, true);
            case 'boolean':
                return (bool) $value;
            case 'integer':
                return (int) $value;
            case 'date':
                return $value;
            default:
                return $value;
        }
    }

    /**
     * Generate SEF link (from parent Mysql class)
     */
    public function seflink($text) {
        $find = array("/Ğ/","/Ü/","/Ş/","/İ/","/Ö/","/Ç/","/ğ/","/ü/","/ş/","/ı/","/ö/","/ç/");
        $degis = array("G","U","S","I","O","C","g","u","s","i","o","c");
        $text = preg_replace("/[^0-9a-zA-ZÄzÜŞİÖÇğüşıöç]/"," ",$text);
        $text = preg_replace($find,$degis,$text);
        $text = preg_replace("/ +/"," ",$text);
        $text = preg_replace("/ /","-",$text);
        $text = preg_replace("/\s/","",$text);
        $text = strtolower($text);
        $text = preg_replace("/^-/","",$text);
        $text = preg_replace("/-$/","",$text);
        return $text;
    }

    /**
     * Get all active organizations
     */
    public static function getAllActive() {
        return static::where('status', 'active');
    }

    /**
     * Find organization by slug
     */
    public static function findBySlug($slug) {
        $results = static::where('organization_slug', $slug);
        return !empty($results) ? $results[0] : null;
    }

    /**
     * Soft delete organization
     */
    public function softDelete() {
        $sql = "UPDATE " . static::$table . " 
                SET deleted_at = CURRENT_TIMESTAMP 
                WHERE " . static::$primaryKey . " = ?";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$this->attributes[static::$primaryKey]]);
        } catch (PDOException $e) {
            throw new Exception("Error soft deleting organization: " . $e->getMessage());
        }
    }
}