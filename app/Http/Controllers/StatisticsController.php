<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use LSS\Array2Xml;
use App\Statistics;

class StatisticsController extends Controller
{
    public function index(Request $request) {
        $data = [];
        $args = $request->all();
        $exporter = new Statistics();
        switch ($request->type) {
            case 'playerstats':
                $searchArgs = ['player', 'playerId', 'team', 'position', 'country'];
                
                $where = [];
                $count = 0;
                foreach ($args as $key => $value) {
                    if ( !in_array($key, $searchArgs) ) {
                        $err['status'] = false;
                        $err['message'] = "invalid parameter " . $key;
                        return response()->json($err);
                    }else{
                        if ( $key == 'playerId' ) {
                            $where[] =  [
                                "roster.id",
                                "=",
                                $value
                            ];
                        }
                        if ( $key == 'player' ) {
                            $where[] =  [
                                "roster.name",
                                "=",
                                $value
                            ];
                        }
                        if ( $key == 'team' ) {
                            $where[] =  [
                                "roster.team_code",
                                "=",
                                $value
                            ];
                        }
                        if ( $key == 'position' ) {
                            $where[] =  [
                                "roster.pos",
                                "=",
                                $value
                            ];
                        }
                        if ( $key == 'country' ) {
                            $where[] =  [
                                "roster.nationality",
                                "=",
                                $value
                            ];
                        }
                    }
                }   
                $data = $exporter->getPlayerStats($where);
                break;
            case 'players':
                $searchArgs = ['player', 'playerId', 'team', 'position', 'country'];
                $where = [];
                foreach ($args as $key => $value) {
                    if ( !in_array($key, $searchArgs) ) {
                        $err['status'] = false;
                        $err['message'] = "invalid parameter " . $key;
                        return response()->json($err);
                    }else{
                        if ( $key == 'playerId' ) {
                            $where[] =  [
                                "roster.id",
                                "=",
                                $value
                            ];
                        }
                        if ( $key == 'player' ) {
                            $where[] =  [
                                "roster.name",
                                "=",
                                $value
                            ];
                        }
                        if ( $key == 'team' ) {
                            $where[] =  [
                                "roster.team_code",
                                "=",
                                $value
                            ];
                        }
                        if ( $key == 'position' ) {
                            $where[] =  [
                                "roster.pos",
                                "=",
                                $value
                            ];
                        }
                        if ( $key == 'country' ) {
                            $where[] =  [
                                "roster.nationality",
                                "=",
                                $value
                            ];
                        }
                    }
                }   
                $data = $exporter->getPlayers($where);
                break;
        }

        switch($request->format) {
            case 'xml':
                header('Content-type: text/xml');
                
                // fix any keys starting with numbers
                $keyMap = ['zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'];
                $xmlData = [];
                foreach ($data->all() as $row) {
                    $xmlRow = [];
                    foreach ($row as $key => $value) {
                        $key = preg_replace_callback('(\d)', function($matches) use ($keyMap) {
                            return $keyMap[$matches[0]] . '_';
                        }, $key);
                        $xmlRow[$key] = $value;
                    }
                    $xmlData[] = $xmlRow;
                }
                $xml = Array2XML::createXML('data', [
                    'entry' => $xmlData
                ]);
                return $xml->saveXML();
                break;
            case 'json':
                return response()->json($data);
                break;
            case 'csv':
                header('Content-type: text/csv');
                header('Content-Disposition: attachment; filename="export.csv";');
                if (!$data->count()) {
                    return;
                }
                $csv = [];
                
                // extract headings
                // replace underscores with space & ucfirst each word for a decent headings
                $headings = collect($data->get(0))->keys();
                $headings = $headings->map(function($item, $key) {
                    return collect(explode('_', $item))
                        ->map(function($item, $key) {
                            return ucfirst($item);
                        })
                        ->join(' ');
                });
                $csv[] = $headings->join(',');

                // format data
                foreach ($data as $dataRow) {
                    $csv[] = implode(',', array_values($dataRow));
                }
                return implode("\n", $csv);
                break;
            default: // html
                if (!$data->count()) {
                    return $this->htmlTemplate('Sorry, no matching data was found');
                }
                
                // extract headings
                // replace underscores with space & ucfirst each word for a decent heading
                $headings = collect($data->get(0))->keys();

                $headings = $headings->map(function($item, $key) {
                    return collect(explode('_', $item))
                        ->map(function($item, $key) {
                            return ucfirst($item);
                        })
                        ->join(' ');
                });
                $headings = '<tr><th>' . $headings->join('</th><th>') . '</th></tr>';

                // output data
                $rows = [];
                foreach ($data as $dataRow) {
                    $row = '<tr>';
                    foreach ($dataRow as $key => $value) {
                        $row .= '<td>' . $value . '</td>';
                    }
                    $row .= '</tr>';
                    $rows[] = $row;
                }
                $rows = implode('', $rows);
                return $this->htmlTemplate('<table>' . $headings . $rows . '</table>');
                break;
        }


        if (!$data) {
            exit("Error: No data found!");
        }
    }

    // wrap html in a standard template
    public function htmlTemplate($html) {
        return '
        <html>
        <head>
        <style type="text/css">
            body {
                font: 16px Roboto, Arial, Helvetica, Sans-serif;
            }
            td, th {
                padding: 4px 8px;
            }
            th {
                background: #eee;
                font-weight: 500;
            }
            tr:nth-child(odd) {
                background: #f4f4f4;
            }
        </style>
        </head>
        <body>
            ' . $html . '
        </body>
        </html>';
    }
}
