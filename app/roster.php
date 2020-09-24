<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class roster extends Model
{
    protected $connection = "NBA";
    protected $table = "roster";
}
