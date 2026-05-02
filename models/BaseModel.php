<?php
namespace Model;

use Infrastructure\ActiveRecord;


class MainTableView extends ActiveRecord {
    // =============== DATABASE ===============
    protected static $db = 'ente';
    protected static $table = 'main_table_view';
    protected static $colsDB = [
        'ID',
        'ID_MPd',
        'Prioridad',
        'Pendientes',
        'Riesgo',
        'DSM',
        'Promesa',
        'OT',
        'Descripcion',
        'CURD',
        'Estado',
        'Tecnica',
        'PlantaMini', 
        'sin_jerarquia',
        'momento'
    ];

    // =============== CONSTRUCTOR ===============
    public function __construct(array $args = []) {
        foreach (self::$colsDB as $col) {
            $this->$col = $args[$col] ?? null;
        }
    }
}