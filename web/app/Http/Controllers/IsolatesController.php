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
        $isoList = Isolates::where('isolate_id', 'LIKE', '%'.$keyword.'%')
            ->orWhere('order', 'LIKE', '%'.$keyword.'%')
            ->orWhere('closest_relative', 'LIKE', '%'.$keyword.'%')->get();
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
}
