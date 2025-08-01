<?php


class WarehouseModel extends BaseORM {
    protected static $table = 'inventory_warehouses';

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
    }

    public static function getWarehouses($organization_id) {
        try {
            if(empty($organization_id)) {
                throw new NotFoundException('Organization ID is required for this Warehouse');
            }
            $warehouses = self::select(['warehouse_id', 'name', 'code'])
                ->from(static::$table)
                ->where('organization_id', '=', $organization_id)
                ->where('is_active', '=', 1)
                ->get();
            if(empty($warehouses)) {
                throw new NotFoundException('No active Warehouses found for this Organization');
            }
            return $warehouses;
        } catch (PDOException $e) {
            ExceptionHandler::convertPDOException($e);
        } catch (Exception $e) {
            throw new DatabaseException('Failed to fetch warehouses: ' . $e->getMessage(), 'WAREHOUSE_FETCH_ERROR');
        }
    }

    public static function getWarehouse($warehouse_id) {
        try {
            if(empty($warehouse_id)) {
                throw new NotFoundException('Warehouse ID is required ');
            }
            $warehouse = self::select()
                ->from(static::$table)
                ->where('warehouse_id', '=', $warehouse_id)
                ->first();
            if(empty($warehouses)) {
                throw new NotFoundException('No Warehouse found');
            }
            return $warehouse;
        } catch (PDOException $e) {
            ExceptionHandler::convertPDOException($e);
        } catch (Exception $e) {
            throw new DatabaseException('Failed to fetch warehouses: ' . $e->getMessage(), 'WAREHOUSE_FETCH_ERROR');
        }
    }
}