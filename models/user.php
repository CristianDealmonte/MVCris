<?php
namespace Model;

use Infrastructure\ActiveRecord;

class User extends ActiveRecord {
    protected static string $table = 'usuarios';

    protected static array $columns = [
        'email',
        'password'
    ];
}


// Ahora tienes una base sólida. El camino lógico es:
// 1️⃣ QueryBuilder fluido (where, orderBy)
// 2️⃣ Relaciones (belongsTo, hasMany)
// 3️⃣ Soft deletes / timestamps
// 4️⃣ Validación integrada por capas