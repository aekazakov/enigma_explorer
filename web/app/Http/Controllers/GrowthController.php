<?php

namespace App\Http\Controllers;

//use App\Isolates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GrowthController extends Controller {
    // Construction function
    public function __construct() {
    }

    // get metadata by id
    public function metaById($id) {
        $plates = DB::connection('mysqlGrowth')->table('GrowthPlate')
            ->leftjoin('Instrument', 'GrowthPlate.instrumentId', '=', 'Instrument.instrumentId')
            ->select('growthPlateId', 'plateType', 'numberOfWells', 'dateCreated', 'dateScanned', 'instrumentName', 'anaerobic', 'measurement')
            ->where('growthPlateId', $id)
            ->get();
        // check invalid id
        if (count($plates) == 0) {
            return response()->json([ 'message' => 'Invalid plate id' ], 400);
        } else {
            return response()->json($plates[0]);
        }
    }

    public function metaBykeyword($keyword) {
        $wells = DB::connection('mysqlGrowth')->table('GrowthPlate')
            ->join('GrowthWell', 'GrowthPlate.growthPlateId', '=', 'GrowthWell.growthPlateId')
            ->join('StrainMutant', 'GrowthWell.strainMutantId', '=', 'StrainMutant.strainMutantId')
            ->join('Strain', 'StrainMutant.strainId', '=', 'Strain.strainId')
            ->select('GrowthPlate.growthPlateId', 'numberOfWells', 'dateCreated', 'Strain.label')
            ->where('Strain.label', 'LIKE', '%'.$keyword.'%')
            ->get();
        $json = [];
        foreach ($wells as $well) {
            if (!array_key_exists($well->growthPlateId, $json)) {
                $json[$well->growthPlateId] = [
                    'growthPlateId' => $well->growthPlateId,
                    'numberOfWells' => $well->numberOfWells,
                    'dateCreated' => $well->dateCreated,
                    'strain' => $well->label
                ];
            }
        }
        $json = array_values($json);
        if (count($json) == 0) {
            return response()->json([ 'message' => 'No plates found'], 400);
        } else {
            return response()->json($json);
        }
    }

    public function wellDataById($id) {
        // use innerjoin instead of leftjoin, because w/o well data is meaningless
        $wells = DB::connection('mysqlGrowth')->table('GrowthPlate')
            ->join('GrowthWell', 'GrowthPlate.growthPlateId', '=', 'GrowthWell.growthPlateId')
            ->join('WellData', 'GrowthWell.growthWellId', '=', 'WellData.growthWellId')
            ->join('TreatmentInfo', 'GrowthWell.growthWellId', '=', 'TreatmentInfo.growthWellId')
            ->leftjoin('StrainMutant', 'GrowthWell.strainMutantId', '=', 'StrainMutant.strainMutantId')
            ->leftjoin('Strain', 'StrainMutant.strainId', '=', 'Strain.strainId')
            ->select('GrowthWell.growthWellId', 'wellLocation', 'wellRow', 'wellCol', 'media', 'timepointSeconds', 'value', 'temperature', 'Strain.label', 'condition', 'concentration', 'units')
            ->where('GrowthPlate.growthPlateId', $id)
            ->get();
        // in summary, we got for each well
        // growthWellId, wellLocation (wellRow, wellCol), media, strain label, treatment (condition, concentration & unit)
        // we got for each timepoint
        // timepoint(in seconds), value, temperature

        // parse the query return
        // first indexed by plateid, then timepoint
        $json = [];
        foreach ($wells as $well) {
            if (!array_key_exists($well->growthWellId, $json)) {
                $json[$well->growthWellId] = [];
            }
            $wellJson = &$json[$well->growthWellId];
            if (!array_key_exists('wellLocation', $wellJson)) {
                // wellLocation, wellRow & wellCol coexists
                $wellJson['wellLocation'] = $well->wellLocation;
                $wellJson['wellRow'] = $well->wellRow;
                $wellJson['wellCol'] = $well->wellCol;
            }
            if (!array_key_exists('media', $wellJson))
                $wellJson['media'] = $well->media;
            if (!array_key_exists('label', $wellJson))
                $wellJson['strainLabel'] = $well->label;
            if (!array_key_exists('treatment', $wellJson)) {
                $wellJson['treatment'] = [];
                $treatJson = &$wellJson['treatment'];
                $treatJson['condition'] = [$well->condition];
                $treatJson['concentration'] = [$well->concentration];
                $treatJson['units'] = [$well->units];
            } else {    // Notice one well can have more than 1 treatment
                $treatJson = &$wellJson['treatment'];
                if (!in_array($well->condition, $treatJson['condition'])) { // is this a new condition?
                    $treatJson['condition'][] = $well->condition;
                    $treatJson['concentration'][] = $well->concentration;
                    $treatJson['units'][] = $well->units;
                }
            }
            if (!array_key_exists('data', $wellJson)) {
                $wellJson['data'] = [];
                $dataJson = &$wellJson['data'];
                $dataJson['timepoints'] = [ $well->timepointSeconds ];
                $dataJson['values'] = [ $well->value ];
                $dataJson['temperatures'] = [ $well->temperature ];
            } else {
                if (count($wellJson['treatment']['condition']) <= 1) {
                    $dataJson = &$wellJson['data'];
                    $dataJson['timepoints'][] = $well->timepointSeconds;
                    $dataJson['values'][] = $well->value;
                    $dataJson['temperatures'][] = $well->temperature;
                }
            }
        }

        // Tackle with multiple treatment: this is expedient
        foreach ($json as $wl => $wd) {
            $treatJson = &$json[$wl]['treatment'];
            $treatJson['condition'] = implode(' | ', $treatJson['condition']);
            $treatJson['concentration'] = $treatJson['concentration'][0];
            $treatJson['units'] = $treatJson['units'][0];
        }

        $json = array_values($json);
        // JSON structure:
        /* $wellId: {
                wellLocation: $var,
                wellRow: $var,
                wellCol: $var,
                media: $var,
                strainLabel: $var,
                data: {
                    timepoints: [],
                    values: [],
                    temperatures: []
                }
            } */
        if (count($json) == 0) {
            return response()->json([ 'message' => 'Invalid plate id for wells' ], 400);
        } else {
            return response()->json($json);
        }
    }
}
