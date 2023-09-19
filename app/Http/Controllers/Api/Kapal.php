<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\KapalAndCoorRes;
use App\Http\Resources\KapalRes;
use App\Models\Coordinate;
use App\Models\Kapal as ModelsKapal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class Kapal extends Controller
{
    public function index()
    {
        $kapal = ModelsKapal::select();
        $message = "Data Kapal Ditemukan";
        return Kapal::displayData($kapal,$message);
    }

    public function getKapal(){
        $call_sign = request()->call_sign;

        $message = "Data Kapal Ditemukan";
        $kapal = ModelsKapal::where("call_sign",$call_sign);
        if(!$kapal->exists()){
            return response()->json([
                'message' => "Data Kapal Tidak Ditemukan",
            ],400);
        }
        $rules = [
            'call_sign' => ['required','max:255'],
        ];
        return Kapal::displayData($kapal,$message,$rules);
    }

    public function insertKapal(){
        $call_sign = request()->call_sign;
        $flag = request()->flag;
        $class = request()->class;
        $builder = request()->builder;
        $year_built = request()->year_built;
        $size = strtolower(request()->size);
        $ip = request()->ip;
        $port = request()->port;
        $xml = request()->file("xml_file");

        $rules = [
            'call_sign' => ['required','max:255','unique:kapals'],
            'flag' => ['required','max:255'],
            'class' => ['required','max:255'],
            'builder' => ['required','max:255'],
            'year_built' => ['required','max:255'],
            'size' => ['required','max:255'],
            'ip' => ['required','max:255'],
            'port' => ['required','max:255'],
            'xml_file' => ['required','file','mimes:xml','max:8192'],
        ];
        $validator = Validator::make(request()->all(),$rules);
        if($validator->fails()){
            return response()->json([
                'message' => "Validator Fails",
                'error' => $validator->errors()
            ],400);
        }

        if(request()->hasFile('xml_file')){
            $xml_file_name = now().'_'.request()->call_sign.'.xml';
            $xml_path_upload = 'xml';

            $xml->move($xml_path_upload,$xml_file_name);
        }

        try {
            ModelsKapal::create([
                'call_sign'=>$call_sign,
                'flag'=>$flag,
                'class'=>$class,
                'builder'=>$builder,
                'year_built'=>$year_built,
                'size'=>$size,
                'ip'=>$ip,
                'port'=>$port,
                'xml_file'=>$xml_file_name,
            ]);
        } catch (\Throwable $th) {
            $message = $th;
            return response()->json([
                'message' => $message,
                'status' => 400
            ],400);
        }

        return response()->json([
            'message' => "Data berhasil masuk database",
            'status' => 200
        ]);

    }

    public function updateKapal(){
        $old_call_sign = request()->old_call_sign;
        $call_sign = request()->call_sign;
        $flag = request()->flag;
        $class = request()->class;
        $builder = request()->builder;
        $year_built = request()->year_built;
        $size = strtolower(request()->size);
        $ip = request()->ip;
        $port = request()->port;
        $xml = request()->file("xml_file");

        $rules = [
            'old_call_sign' => ['required','max:255'],
            'call_sign' => ['required','max:255','unique:kapals'],
            'flag' => ['required','max:255'],
            'class' => ['required','max:255'],
            'builder' => ['required','max:255'],
            'year_built' => ['required','max:255'],
            'size' => ['required','max:255'],
            'ip' => ['required','max:255'],
            'port' => ['required','max:255'],
            'xml_file' => ['file','mimes:xml','max:8192'],
        ];
        $validator = Validator::make(request()->all(),$rules);
        if($validator->fails()){
            if($validator->errors()->first() != "The call sign has already been taken."){
                return response()->json([
                    'message' => $validator->errors()->first(),
                ],400);
            }
        }

        if(ModelsKapal::where(['call_sign'=>$old_call_sign])->get()->count() <= 0){
            return response()->json([
                'message' => "old_call_sign Not Found"
            ],404);
        }

        $kapal = ModelsKapal::where(['call_sign'=>$old_call_sign])->first();
        if(request()->hasFile('xml_file')){
            $xml_file_name = now().'_'.request()->call_sign.'.xml';
            $xml_path_upload = 'xml';

            $xml->move($xml_path_upload,$xml_file_name);

            $tmp = "/home/binavavt/api.binav-avts.id/{$xml_path_upload}/{$kapal->xml_file}";

            if (file_exists($tmp)) {
                unlink($tmp);
            }
            ModelsKapal::where(['call_sign'=>$old_call_sign])->update([
                "xml_file"=>$xml_file_name
            ]);
        }

        try {
            ModelsKapal::where(['call_sign'=>$old_call_sign])->update([
                'call_sign'=>$call_sign,
                'flag'=>$flag,
                'class'=>$class,
                'builder'=>$builder,
                'year_built'=>$year_built,
                'size'=>$size,
                'ip'=>$ip,
                'port'=>$port,
            ]);
        } catch (\Throwable $th) {
            $message = $th;
            return response()->json([
                'message' => $message,
                'status' => 400
            ],400);
        }

        return response()->json([
            'message' => "Data berhasil di ubah database",
            'status' => 200
        ]);
    }

    public function deleteKapal($call_sign){
        $kapal = ModelsKapal::where('call_sign',$call_sign)->first();
        $xml_path_upload = 'xml';
        if($kapal){
            $tmp = "/home/binavavt/api.binav-avts.id/{$xml_path_upload}/{$kapal->xml_file}";

            if (file_exists($tmp)) {
                unlink($tmp);
            }
            ModelsKapal::where('call_sign',$call_sign)->delete();
            return response()->json([
                'message' => "Data berhasil di hapus database",
                'status' => 200
            ]);
        }
        return response()->json([
            'message' => "Call Sign tidak ditemukan",
            'status' => 404
        ],404);

    }

    public function displayData($kapal,$message,$rules = []){
        $total = $kapal->count();

        // Pages parameter
        $page = request()->page == null ? 1 : request()->page;
        $perpage = request()->perpage == null ? 10 : request()->perpage;
        $kapal = $kapal->skip(($page - 1) * $perpage)->take($perpage)->get();

        // Validasi
        $validator = Validator::make(request()->all(),$rules);
        if($validator->fails()){
            return response()->json([
                'message' => "Validator Fails",
                'error' => $validator->errors()
            ],400);
        }

        // Response
        $kapalCol = KapalRes::collection($kapal);
        return response()->json([
            'message' => $message,
            'status' => 200,
            'perpage' => intval($perpage),
            'page' => intval($page),
            'total' => $total,
            'data' => $kapalCol,
        ]);

    }

    public function getKapalAndLatestCoor(){
        $call_sign = request()->call_sign;
        $kapal = ModelsKapal::orderBy("kapals.call_sign","ASC");
        if(!empty($call_sign)){
            $kapal = $kapal->where("kapals.call_sign",$call_sign);
        }
        $data = collect();
        foreach($kapal->get() as $row){
            $coor = Coordinate::join('coordinate_ggas','coordinates.id_coor_gga','=','coordinate_ggas.id_coor_gga')
            ->leftJoin('coordinate_hdts','coordinates.id_coor_hdt','=','coordinate_hdts.id_coor_hdt')->latest("coordinates.series_id")->where('coordinates.call_sign',$row->call_sign)->take(1);
            foreach($coor->get() as $rowCoor){
                $data->push([
                    "kapal" => [
                        "call_sign"=> $row->call_sign,
                        "flag"=> $row->flag,
                        "kelas"=> $row->class,
                        "builder"=> $row->builder,
                        "size"=> $row->size,
                        "ip"=> $row->ip,
                        "port"=> $row->port,
                        "year_built"=> $row->year_built,
                        "created_at"=> $row->created_at,
                        "updated_at"=> $row->updated_at
                    ],
                    'coor'=>[
                        'id_coor'=> (int)$rowCoor->id_coor,
                        'call_sign'=> $rowCoor->call_sign,
                        'series_id'=> (int)$rowCoor->series_id,
                        'default_heading' => $rowCoor->default_heading == null ? null : (float)$rowCoor->default_heading,
                        'coor_hdt' => [
                            'id_coor_hdt'=> $rowCoor->id_coor_hdt == null ? null : (int)$rowCoor->id_coor_hdt,
                            'heading_degree'=> $rowCoor->heading_degree == null ? null :(float)$rowCoor->heading_degree,
                            'checksum'=>$rowCoor->checksum
                        ],
                        'coor_gga' => [
                            'id_coor_gga'=> (int)$rowCoor->id_coor_gga,
                            'utc_position'=> (float)$rowCoor->utc_position,
                            'latitude'=> (float)$rowCoor->latitude,
                            'direction_latitude'=>$rowCoor->direction_latitude,
                            'longitude'=> (float)$rowCoor->longitude,
                            'direction_longitude'=>$rowCoor->direction_longitude,
                            'gps_quality_indicator'=>$rowCoor->gps_quality_indicator,
                            'number_sv'=> (int)$rowCoor->number_sv,
                            'hdop'=> (float)$rowCoor->hdop,
                            'orthometric_height'=> (float)$rowCoor->orthometric_height,
                            'unit_measure'=>$rowCoor->unit_measure,
                            'geoid_seperation'=> (float)$rowCoor->geoid_seperation,
                            'geoid_measure'=>$rowCoor->geoid_measure,
                        ],
                        'created_at'=>$rowCoor->created_at,
                        'updated_at'=>$rowCoor->updated_at,
                    ]
                ]);
            }
        }
        // return $data->count();
        return Kapal::displayDataKapalCoor($data,"Data Kapal Ditemukan");
    }

    public function displayDataKapalCoor($kapal,$message,$rules = []){
        $total = $kapal->count();

        // Pages parameter
        $page = request()->page == null ? 1 : request()->page;
        $perpage = request()->perpage == null ? 10 : request()->perpage;
        $kapal = $kapal->skip(($page - 1) * $perpage)->take($perpage);

        // Validasi
        $validator = Validator::make(request()->all(),$rules);
        if($validator->fails()){
            return response()->json([
                'message' => "Validator Fails",
                'error' => $validator->errors()
            ],400);
        }

        // Response
        // $kapalCol = KapalAndCoorRes::collection($kapal);
        return response()->json([
            'message' => $message,
            'status' => 200,
            'perpage' => intval($perpage),
            'page' => intval($page),
            'total' => $total,
            'data' => $kapal,
        ]);

    }

}
