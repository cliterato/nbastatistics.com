<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class player_totals extends Model
{
    protected $connection = "NBA";
    protected $table = "player_totals";
}
