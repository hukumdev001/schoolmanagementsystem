<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Parents extends Model
{
    protected $table = 'parents';


    protected $fillable = [
    	'user_id',
    	'gender',
    	'phone',
    	'current_address',
    	'permanent_address'

    ];


    public function user() 
    {
    	return $this->belongdTo(User::class);
    }

    public function children()
    {
    	return $this->hasMany(Student::class, 'parent_id');
    }
}
