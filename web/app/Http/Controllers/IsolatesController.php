<?php

namespace App\Http\Controllers;

use App\Isolates;
use Illuminate\Http\Request;

class IsolatesController extends Controller {
    public function __construct() {
        //
    }

    # select by isolate id
    public function selectByIsoid($isoid) {
        $iso = Isolates::where('isolate_id', $isoid)->get();
        # isolate id should be unique. doublecheck
        if (count($iso) > 1) {
            return response()->json(['message' => 'Unexpected data encountered'], 404);
        } else if (count($iso) == 0) {
            # empty inquery, return nothing
            return response()->json();
        }else {
            # success inquery
            return response()->json($iso[0]);
        }
    }

    # select by id
    public function selectById($id) {
        # the query should be integer
        if (!is_numeric($id)) {
            return response()->json(['message' => 'Bad inquery'], 400);
        }
        $iso = Isolates::where('id', $id)->first();
        return response()->json($iso);
    }

    private function fuzzySelect($keyword) {
        # filter out too short query
        if (strlen($keyword) < 3) {
            return -1;
        }
        # search by isolate id, phylogenic order, or closest relative
        $keyword = urldecode($keyword);
        $isoList = Isolates::where('isolate_id', 'LIKE', '%'.$keyword.'%')
            ->orWhere('order', 'LIKE', '%'.$keyword.'%')
            ->orWhere('closest_relative', 'LIKE', '%'.$keyword.'%')
            ->select('id','isolate_id','condition','order','closest_relative',
                'similarity','date_sampled','sample_id','lab','campaign')->get();
        # return all, in sequence
        return $isoList->toArray();
    }

    public function selectByKeyword($keyword) {
        $isoList = $this->fuzzySelect($keyword);
        if ($isoList == -1) {
            return response()->json(['message' => 'Too short query keyword'], 400);
        } else {
            return response()->json($isoList);
        }
    }

    public function countByKeyword($keyword) {
        $isoList = $this->fuzzySelect($keyword);
        if ($isoList == -1) {
            return response()->json(['message' => 'Too short query keyword'], 400);
        } else {
            return response()->json(['count' => count($isoList)]);
        }
    }

    public function rrnaById($id) {
       $iso = Isolates::where('id', $id)->select('isolate_id', 'rrna')->first();
       $response = response()->make('> '.$iso->isolate_id."\n".$iso->rrna, 200);
       $response->header('Content-Type', 'text/plain')
           ->header('Content-Disposition', 'attachment;filename='.$iso->isolate_id.'.fa');
       return $response;
    }

    public function selectByMultiKeywords(Request $request) {
        $input = $request->all();
        $eqSet = array(); $likeSet = array();
        foreach ($input['isEqual'] as $key => $val) {
            // ignore all empty forms
            // not necessary. empty forms will not be posted
            if (!array_key_exists($key, $input) || $input[$key] == '') {
                continue;
            }
            // map real db columns:
            $dbMap = [
                'isoid' => 'isolate_id',
                'order' => 'order',
                'relative' => 'closest_relative',
                'lab' => 'lab'
            ];
            if ($val == 'true') {
                $eqSet[$dbMap[$key]] = $input[$key];
            } else {
                $likeSet[$dbMap[$key]] = $input[$key];
            }
        }
        $query = Isolates::where($eqSet);
        foreach ($likeSet as $key => $val) {
            $query->where($key, 'LIKE', '%'.$val.'%')
                ->select('id','isolate_id','condition','order','closest_relative',
                'similarity','date_sampled','sample_id','lab','campaign');
        }
        return response()->json($query->get());
    }

    public function genomeList($id) {
        $iso = Isolates::where('id', $id)->select('closest_relative')->first();
        $relList = explode(' ', $iso->closest_relative);
        $species = implode(' ', array_slice($relList, 0, 2));
        $strain = implode(' ', array_slice($relList, 2));
        $cmd = implode(' ', [base_path("scripts/fetchGenome.py"), "-s", "\"$species\"", "\"$strain\""]);
        $genomeList = shell_exec($cmd);
        if (is_null($genomeList)) {
            return response()->json(["message" => "Unexpected internal error"], 400);
        } else {
            return response()->json(json_decode($genomeList));
        }
    }

    public function genomeByNcbiId($id) {
        $cmd = implode(' ', [base_path("scripts/fetchGenome.py"), "-i", $id]);
        $genome = shell_exec($cmd);
        $response = response()->make($genome, 200);
        $response->header('Content-Type', 'text/plain')
           ->header('Content-Disposition', 'attachment;filename='.$id.'.fa');
        return $response;
    }

    public function blastRidById($id) {
        // fasta string of 16s
        $iso = Isolates::where('id', $id)->select('isolate_id', 'rrna')->first();
        $queryString = '>'.$iso->isolate_id."\n".$iso->rrna;
        // NCBI BLAST url
        $ncbiUrl = 'https://blast.ncbi.nlm.nih.gov/Blast.cgi';
        $postData = [
            'CMD' => 'Put',
            'PROGRAM' => 'blastn',
            'MEGABLAST' => 'on',
            'DATABASE' => 'nr',
            'QUERY' => $queryString
        ];
        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($postData)
            ]
        ];
        $context = stream_context_create($options);
        $result = file_get_contents($ncbiUrl, false, $context);

        // parse html & get RID
        preg_match('/<input.+name="RID".+value="(\w+)".+id="rid".+>/', $result, $matches);
        try {
            $rid = $matches[1];
        } catch (Exception $err) {
            return response()->json(['message', $err->getMessage()]);
        }
        // return form data necessary for ncbi blast
        $retData = [
            'CMD' => 'Get',
            'FORMAT_TYPE' => 'HTML',
            'RID' => $rid,
            'SHOW_OVERVIEW' => 'on'
        ];
        return response()->json($retData);
    }
}
