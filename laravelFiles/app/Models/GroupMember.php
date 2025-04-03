<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GroupMember extends Model
{
    
    /**
     * Get the user associated with the GroupMember
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function group(): HasOne
    {
        return $this->hasOne(Group::class, 'id', 'group_id');
    }
    
}
