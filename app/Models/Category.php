<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = ['name'];

    // Vlastná metóda na vyhľadávanie podľa názvu kategórie
    public static function searchByCategoryName($keyword)
    {
        return self::where('name', $keyword)->get();
    }

    public function notes()
    {
        return $this->belongsToMany(Note::class, 'note_category');
    }

}
