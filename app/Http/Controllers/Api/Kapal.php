<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\KapalRes;
use App\Models\Kapal as ModelsKapal;
use Illuminate\Http\Request;
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

        $rules = [
            'call_sign' => ['required','max:255','unique:kapals'],
            'flag' => ['required','max:255'],
            'class' => ['required','max:255'],
            'builder' => ['required','max:255'],
            'year_built' => ['required','max:255'],
            'size' => ['required','max:255'],
            'ip' => ['required','max:255'],
            'port' => ['required','max:255'],
        ];
        $validator = Validator::make(request()->all(),$rules);
        if($validator->fails()){
            return response()->json([
                'message' => "Validator Fails",
                'error' => $validator->errors()
            ],400);
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
        ];
        $validator = Validator::make(request()->all(),$rules);
        if($validator->fails()){
            return response()->json([
                'message' => "Validator Fails",
                'error' => $validator->errors()
            ],400);
        }
        if(ModelsKapal::where(['call_sign'=>$old_call_sign])->get()->count() <= 0){
            return response()->json([
                'message' => "old_call_sign Not Found"
            ],404);
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
        ModelsKapal::where('call_sign',$call_sign)->delete();
        return response()->json([
            'message' => "Data berhasil di hapus database",
            'status' => 200
        ]);
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
}
