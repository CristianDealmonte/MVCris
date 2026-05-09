<?php
namespace Model;

use Infrastructure\ActiveRecord;

class User extends ActiveRecord
{
    protected static string $table = 'users';

    protected static array $columns = [
        'id',
        'email',
        'name'
    ];

    public ?int $id;
}


// Ahora tienes una base sólida. El camino lógico es:
// 1️⃣ QueryBuilder fluido (where, orderBy)
// 2️⃣ Relaciones (belongsTo, hasMany)
// 3️⃣ Soft deletes / timestamps
// 4️⃣ Validación integrada por capas