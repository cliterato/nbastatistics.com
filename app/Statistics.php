<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Statistics extends Model
{
    protected $connection = "mysql";

    public function getPlayerStats($search){   
        try {
            
            $reference = DB::connection($this->connection)
                        ->table('player_totals')
                        ->select(
                            'roster.name', 
                            'player_totals.*'
                            )
                        ->join('roster', function($join){
                            $join->on('roster.id', '=','player_totals.player_id');
                        })
                        ->where($search)
                        ->get()
                        ->toArray();
            // calculate totals
            $data = [];
            foreach ($reference as $key => $value) {
                $row = json_decode(json_encode($value), true);
                unset($row['player_id']);
                $row['total_points'] = ($row['3pt'] * 3) + ($row['2pt'] * 2) + $row['free_throws'];
                $row['field_goals_pct'] = $row['field_goals_attempted'] ? (round($row['field_goals'] / $row['field_goals_attempted'], 2) * 100) . '%' : 0;
                $row['3pt_pct'] = $row['3pt_attempted'] ? (round($row['3pt'] / $row['3pt_attempted'], 2) * 100) . '%' : 0;
                $row['2pt_pct'] = $row['2pt_attempted'] ? (round($row['2pt'] / $row['2pt_attempted'], 2) * 100) . '%' : 0;
                $row['free_throws_pct'] = $row['free_throws_attempted'] ? (round($row['free_throws'] / $row['free_throws_attempted'], 2) * 100) . '%' : 0;
                $row['total_rebounds'] = $row['offensive_rebounds'] + $row['defensive_rebounds'];
                $data[] = $row;
            }

            return collect($data);
        } catch (\Throwable $th) {
            header('Content-Type: application/json');
            $err = [
                "Status" => false,
                "message_1" => $th->getMessage(),
                "message_2" => "",
                "data" => []
            ];
            echo json_encode($err);
            die;
        }       
    }

    public function getPlayers($search){   
        try {
            
            $reference = DB::connection($this->connection)
                        ->table('roster')
                        ->select(
                            'roster.*'
                            )
                        ->where($search)
                        ->get()
                        ->toArray();

            foreach ($reference as $key => $value) {
                unset($reference[$key]->id);
            }
            return collect($reference);
        } catch (\Throwable $th) {
            header('Content-Type: application/json');
            $err = [
                "Status" => false,
                "message_1" => $th->getMessage(),
                "message_2" => "",
                "data" => []
            ];
            echo json_encode($err);
            die;
        }       
    }
}
