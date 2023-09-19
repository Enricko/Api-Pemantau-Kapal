<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CoorRes;
use App\Http\Resources\LatLangRes;
use App\Models\Coordinate as ModelsCoordinate;
use App\Models\Coordinate_gga as ModelsCoordinate_gga;
use Illuminate\Support\Carbon;
use App\Models\Coordinate_hdt;
use App\Models\Kapal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

use function PHPUnit\Framework\isEmpty;

class Coordinate extends Controller
{

    public function getLatLangCoor(){
        $call_sign = request()->call_sign;
        $message = "Data Coor Kapal Ditemukan";
        $coor = ModelsCoordinate::select(["*","coordinates.call_sign as call_sign"])->join('coordinate_ggas','coordinate_ggas.id_coor_gga','=','coordinates.id_coor_gga')
        ->leftJoin('coordinate_hdts','coordinate_hdts.id_coor_hdt','=','coordinates.id_coor_hdt');
        if(!empty($call_sign)){
            $coor = $coor->where('coordinates.call_sign',$call_sign);
        }
        $coor = $coor->orderByDesc('coordinates.series_id')->get();
        // $rules = [
        //     'call_sign' => ['required','max:255'],
        // ];
        return Coordinate::displayDataLatLang($coor,$message);
    }

    public function getKapalAllCoor(){
        $call_sign = request()->call_sign;
        $message = "Data Coor Kapal Ditemukan";
        $coor = ModelsCoordinate::join('coordinate_ggas','coordinate_ggas.id_coor_gga','=','coordinates.id_coor_gga')
        // ->join('coordinate_hdts','coordinate_hdts.id_coor_hdt','=','coordinates.id_coor_hdt')
        ->where('coordinates.call_sign',$call_sign)
        ->orderByDesc('coordinates.series_id')->get();
        $rules = [
            'call_sign' => ['required','max:255'],
        ];
        return Coordinate::displayData($coor,$message,$rules);
    }
    public function getKapalLatestCoor(){
        $call_sign = request()->call_sign;
        $message = "Data Coor Kapal Ditemukan";
        $coor = ModelsCoordinate::join('coordinate_ggas','coordinate_ggas.id_coor_gga','=','coordinates.id_coor_gga')
        ->join('coordinate_hdts','coordinate_hdts.id_coor_hdt','=','coordinates.id_coor_hdt');
        if(!empty($call_sign)){
            $coor = $coor->where('coordinates.call_sign',$call_sign);
        }

        $coor = $coor->orderByDesc('coordinates.series_id')->get()->unique('call_sign');
        return Coordinate::displayData($coor,$message);
    }

    public function displayData($coor,$message,$rules = []){
        $total = $coor->count();
        // Pages parameter
        $page = request()->page == null ? 1 : request()->page;
        $perpage = request()->perpage == null ? 10 : request()->perpage;
        $coor = $coor->skip(($page - 1) * $perpage)->take($perpage);

        // Validasi
        $validator = Validator::make(request()->all(),$rules);
        if($validator->fails()){
            return response()->json([
                'message' => "Validator Fails",
                'error' => $validator->errors()
            ],400);
        }

        // Response
        $CoorCol = CoorRes::collection($coor);
        return response()->json([
            'message' => $message,
            'status' => 200,
            'perpage' => intval($perpage),
            'page' => intval($page),
            'total' => $total,
            'data' => $CoorCol,
        ]);
    }

    public function displayDataLatLang($coor,$message,$rules = []){
        $total = $coor->count();
        // Pages parameter
        $page = request()->page == null ? 1 : request()->page;
        $perpage = request()->perpage == null ? 10 : request()->perpage;
        $coor = $coor->skip(($page - 1) * $perpage)->take($perpage);

        // Validasi
        $validator = Validator::make(request()->all(),$rules);
        if($validator->fails()){
            return response()->json([
                'message' => "Validator Fails",
                'error' => $validator->errors()
            ],400);
        }

        // Response
        $CoorCol = LatLangRes::collection($coor);
        return response()->json([
            'message' => $message,
            'status' => 200,
            'perpage' => intval($perpage),
            'page' => intval($page),
            'total' => $total,
            'data' => $CoorCol,
        ]);

    }

    static public function degree2decimal($deg_coord,$direction,$precision=10){
        $degree = (int)($deg_coord/100);
        $minutes = $deg_coord-($degree*100);
        $dotdegree = $minutes/60;
        $decimal = $degree + $dotdegree;
        if (($direction=="S") || ($direction=="W"))
        {
            $decimal=$decimal*(-1);
        }
        $decimal=number_format($decimal,$precision,'.','');
        return $decimal;
    }

    // static public function insertCoorGGA($call_sign_param = "",$coor_gga_param = ""){
    //     $message = "";
    //     $call_sign = $call_sign_param = "" ? request()->call_sign : $call_sign_param;
    //     $coor_gga = $coor_gga_param = "" ? request()->coor_gga : $coor_gga_param;
    //     // $coor_hdt = request()->coor_hdt;
    //     $gps_quality = [
    //         'Fix not valid','GPS fix',
    //         'Differential GPS fix (DGNSS), SBAS, OmniSTAR VBS, Beacon, RTX in GVBS mode',
    //         'Not applicable',
    //         'RTK Fixed, xFill',
    //         'RTK Float, OmniSTAR XP/HP, Location RTK, RTX',
    //         'INS Dead reckoning'
    //     ];
    //     $gga_split = explode(',',$coor_gga);

    //     $latitude = Coordinate::degree2decimal($gga_split[2],$gga_split[3]);
    //     $longitude = Coordinate::degree2decimal($gga_split[4],$gga_split[5]);
    //     // $validator = Validator::make(request()->all(),[
    //     //     'call_sign' => ['required','max:255'],
    //     //     'coor_gga' => ['required','max:255'],
    //     // ]);
    //     // if($validator->fails()){
    //     //     return response()->json([
    //     //         'message' => "Validator Fails",
    //     //         'error' => $validator->errors()
    //     //     ],400);
    //     // }
    //     if(count($gga_split) != 15){
    //         return response()->json([
    //             'message' => "Kordinat NMEA-183 GGA kurang lengkap",
    //             'status' => 403
    //         ],403);
    //     }
    //     Log::info(Carbon::now());

    //     $latest = ModelsCoordinate_gga::where("call_sign",$call_sign)->latest("id_coor_gga")->first();
    //     if($latest != null){
    //         if (Carbon::now()->lessThan(Carbon::parse($latest->created_at)->addMinutes(3))) {
    //             return response()->json([
    //                 'message' => "Wait For a moment and Try Again later!!",
    //                 'status' => 403
    //             ],403);
    //         }
    //     }
    //     try {
    //         $id_gga = ModelsCoordinate_gga::create([
    //             'call_sign' => $call_sign,
    //             'message_id' => $gga_split[0],
    //             'utc_position' => $gga_split[1],
    //             'latitude' => $latitude,
    //             'direction_latitude' => $gga_split[3],
    //             'longitude' => $longitude,
    //             'direction_longitude' => $gga_split[5],
    //             'gps_quality_indicator' => $gps_quality[$gga_split[6]],
    //             'number_sv' => $gga_split[7],
    //             'hdop' => $gga_split[8],
    //             'orthometric_height' => $gga_split[9],
    //             'unit_measure' => $gga_split[10],
    //             'geoid_seperation' => $gga_split[11],
    //             'geoid_measure' => $gga_split[12],
    //         ])->id;
    //         ModelsCoordinate::create([
    //             'call_sign' => $call_sign,
    //             'series_id' => ModelsCoordinate::where("call_sign",$call_sign)->get()->count() + 1,
    //             'id_coor_gga' => $id_gga,
    //         ]);

    //     } catch (\Throwable $th) {
    //         $message = $th;

    //         echo $message;
    //         return response()->json([
    //             'message' => $message,
    //             'status' => 400
    //         ],400);
    //     }

    //     return response()->json([
    //         'message' => $message == "" ? "Data berhasil masuk database" : $message,
    //         'status' => 200
    //     ]);

    // }
    // public function insertCoor(){
    //     $message = "";
    //     $call_sign = request()->call_sign;
    //     $coor_gga = request()->coor_gga;
    //     $coor_hdt = request()->coor_hdt;
    //     $gps_quality = [
    //         'Fix not valid','GPS fix',
    //         'Differential GPS fix (DGNSS), SBAS, OmniSTAR VBS, Beacon, RTX in GVBS mode',
    //         'Not applicable',
    //         'RTK Fixed, xFill',
    //         'RTK Float, OmniSTAR XP/HP, Location RTK, RTX',
    //         'INS Dead reckoning'
    //     ];
    //     $gga_split = explode(',',$coor_gga);
    //     $hdt_split = explode(',',$coor_hdt);
    //     $latitude = Coordinate::degree2decimal($gga_split[2],$gga_split[3]);
    //     $longitude = Coordinate::degree2decimal($gga_split[4],$gga_split[5]);
    //     $validator = Validator::make(request()->all(),[
    //         'call_sign' => ['required','max:255'],
    //         'coor_gga' => ['required','max:255'],
    //         'coor_hdt' => ['required','max:255'],
    //     ]);
    //     if($validator->fails()){
    //         return response()->json([
    //             'message' => "Validator Fails",
    //             'error' => $validator->errors()
    //         ],400);
    //     }
    //     if(count($gga_split) != 15){
    //         return response()->json([
    //             'message' => "Kordinat NMEA-183 GGA kurang lengkap",
    //             'status' => 403
    //         ],403);
    //     }
    //     if(count($hdt_split) != 3){
    //         return response()->json([
    //             'message' => "Kordinat NMEA-183 GGA kurang lengkap",
    //             'status' => 403
    //         ],403);
    //     }
    //     try {
    //         $id_hdt = Coordinate_hdt::create([
    //             'call_sign' => $call_sign,
    //             'message_id' => $hdt_split[0],
    //             'heading_degree' => $hdt_split[1],
    //             'checksum' => $hdt_split[2],
    //         ])->id;
    //         $id_gga = ModelsCoordinate_gga::create([
    //             'call_sign' => $call_sign,
    //             'message_id' => $gga_split[0],
    //             'utc_position' => $gga_split[1],
    //             'latitude' => $latitude,
    //             'direction_latitude' => $gga_split[3],
    //             'longitude' => $longitude,
    //             'direction_longitude' => $gga_split[5],
    //             'gps_quality_indicator' => $gps_quality[$gga_split[6]],
    //             'number_sv' => $gga_split[7],
    //             'hdop' => $gga_split[8],
    //             'orthometric_height' => $gga_split[9],
    //             'unit_measure' => $gga_split[10],
    //             'geoid_seperation' => $gga_split[11],
    //             'geoid_measure' => $gga_split[12],
    //         ])->id;
    //         ModelsCoordinate::create([
    //             'call_sign' => $call_sign,
    //             'series_id' => ModelsCoordinate::where("call_sign",$call_sign)->get()->count() + 1,
    //             'id_coor_gga' => $id_gga,
    //             'id_coor_hdt' => $id_hdt,
    //         ]);
    //     } catch (\Throwable $th) {
    //         $message = $th;
    //         return response()->json([
    //             'message' => $message,
    //             'status' => 400
    //         ],400);
    //     }


    //     return response()->json([
    //         'message' => $message == "" ? "Data berhasil masuk database" : $message,
    //         'status' => 200
    //     ]);

    // }

    // public function insertCoorHDT($call_sign_param = "",$coor_hdt_param = ""){
    //     $message = "";
    //     $call_sign = $call_sign_param = "" ? request()->call_sign : $call_sign_param;
    //     $coor_hdt = $coor_hdt_param = "" ? request()->coor_hdt : $coor_hdt_param;
    //     $hdt_split = explode(',',$coor_hdt);
    //     // $validator = Validator::make(request()->all(),[
    //     //     'call_sign' => ['required','max:255'],
    //     //     'coor' => ['required','max:255'],
    //     // ]);
    //     // if($validator->fails()){
    //     //     return response()->json([
    //     //         'message' => "Validator Fails",
    //     //         'error' => $validator->errors()
    //     //     ],400);
    //     // }
    //     if(count($hdt_split) != 3){
    //         return response()->json([
    //             'message' => "Kordinat NMEA-183 GGA kurang lengkap",
    //             'status' => 403
    //         ],403);
    //     }
    //     try {
    //         $id_hdt = Coordinate_hdt::create([
    //             'call_sign' => $call_sign,
    //             'message_id' => $hdt_split[0],
    //             'heading_degree' => $hdt_split[1],
    //             'checksum' => $hdt_split[2],
    //         ])->id;
    //         if(ModelsCoordinate::where("call_sign",$call_sign)->get()->count() <= 0){
    //             ModelsCoordinate::create([
    //                 'call_sign' => $call_sign,
    //                 'series_id' => ModelsCoordinate::where("call_sign",$call_sign)->get()->count() + 1,
    //                 'id_coor_hdt' => $id_hdt,
    //             ]);
    //         }
    //         $findCoor = ModelsCoordinate::where("call_sign",$call_sign)->whereNull('id_coor_hdt')->orderBy('series_id',"ASC")->first();
    //         if($findCoor){
    //             ModelsCoordinate::where("call_sign",$call_sign)->where('id_coor',$findCoor->id_coor)->update([
    //                 'id_coor_hdt' => $id_hdt,
    //             ]);
    //         }else{
    //             ModelsCoordinate::create([
    //                 'call_sign' => $call_sign,
    //                 'series_id' => ModelsCoordinate::where("call_sign",$call_sign)->get()->count() + 1,
    //                 'id_coor_hdt' => $id_hdt,
    //             ]);
    //         }
    //     } catch (\Throwable $th) {
    //         $message = $th;
    //         return response()->json([
    //             'message' => $message,
    //             'status' => 400
    //         ],400);
    //     }


    //     return response()->json([
    //         'message' => $message == "" ? "Data berhasil masuk database" : $message,
    //         'status' => 200
    //     ]);

    // }
}
