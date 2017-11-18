<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class Tag extends Model
{
    protected $hidden = [
         'created_at', 'updated_at', 'id', 'user_id', 'pivot'
    ];

    public function transactions()
    {
        return $this->belongsToMany('App\Transaction', 'transactions__tags');
    }

    public function toArray()
    {
        return $this->name;
    }

    public static function store($tag)
    {
        // Clean tag
        foreach(['#', ' '] as $ch) {
            // Trim leading #s/spaces
            $tag = ltrim($tag, $ch);
            // If that causes the string to be empty, return false
            if(strlen($tag) < 1) {
                return false;
            }
            // Explode the tags by #s/spaces that may be left behind
            $tag = explode($ch, $tag);
            // Take the first result, leave the rest behind
            $tag = $tag[0];
            // If that also causes the string to be empty, return false
            if(strlen($tag) < 1) {
                return false;
            }
        }
        $tag = trim($tag, ",");

        // Look for existing tag with the same name:
        // if there is one, just return that tag's ID,
        // but if not, actually insert the tag and return the inserted ID.
        $existing = self::where('name', $tag)->first();
        if($existing) {
            return $existing;
        } else {
            $new = new self();
            $new->name = $tag;
            $save = $new->save();
            if($save) {
                return $new;
            } else {
                return false;
            }
        }
    }
}
