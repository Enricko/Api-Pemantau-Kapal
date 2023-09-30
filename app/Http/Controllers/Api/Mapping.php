<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MappingRes;
use App\Models\Mapping as ModelsMapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Mapping extends Controller
{
    public function mapping(){
        $map_name = request()->map_name;
        $message = "Data Mapping Ditemukan";
        $data = ModelsMapping::orderBy('id_mapping','DESC');
        if(!empty($map_name)){
            $data = $data->where('mapping.id_mapping',$map_name);
        }
        $data = $data->get();
        return Mapping::displayData($data,$message);
    }

    public function insertMap(){
        $name = request()->name;
        $file = request()->file("file");
        // return $file->getMimeType();
        $switch = request()->switch;

        if ($file->getClientOriginalExtension() == 'zip') {
            return response()->json([
                'message' => "Zip files are not allowed"
            ],400);
        }

        $rules = [
            'name' => ['required','max:255'],
            'file' => ['required','file','mimes:kml,kmz,vnd.google-earth.kmz,zip','max:8192'],
            'switch' => ['required','boolean'],
        ];
        $validator = Validator::make(request()->all(),$rules);
        if($validator->fails()){
            return response()->json([
                'message' => $validator->errors()->first()
            ],400);
        }

        if(request()->hasFile('file')){
            $file_name = now()->format('Y_m_d_H_i_s').'_'.request()->name."_".$file->getClientOriginalName();
            $path_upload = 'public/mapping';

            $file->storeAs($path_upload,$file_name);
        }

        try {
            ModelsMapping::create([
                "name" => $name,
                "file" => $file_name,
                "switch" => $switch
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

    public function updateMap(){
        $id_mapping = request()->id_mapping;
        $name = request()->name;
        $file = request()->file("file");
        // return $file->getMimeType();
        $switch = request()->switch;

        $rules = [
            'name' => ['max:255'],
            'file' => ['file','mimes:kml,kmz,vnd.google-earth.kmz,zip','max:8192'],
            'switch' => ['boolean'],
        ];
        $validator = Validator::make(request()->all(),$rules);
        if($validator->fails()){
            return response()->json([
                'message' => $validator->errors()->first()
            ],400);
        }

        $mapping = ModelsMapping::where(['id_mapping'=>$id_mapping])->first();
        if(request()->hasFile('file')){
            if ($file->getClientOriginalExtension() == 'zip') {
                return response()->json([
                    'message' => "Zip files are not allowed"
                ],400);
            }
            $file_name = now()->format('Y_m_d_H_i_s').'_'.request()->name."_".$file->getClientOriginalName();
            $path_upload = 'public/mapping';

            $file->storeAs($path_upload,$file_name);

            $tmp = storage_path("app/{$path_upload}/{$mapping->file}");

            if (file_exists($tmp)) {
                unlink($tmp);
            }
            ModelsMapping::where('id_mapping',$id_mapping)->update([
                "file" => $file_name,
            ]);
        }

        try {
            ModelsMapping::where('id_mapping',$id_mapping)->update(request()->all());
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

    public function deleteMap($id_mapping){
        $mapping = ModelsMapping::where('id_mapping',$id_mapping)->first();
        $path_upload = 'public/mapping';
        if($mapping){
            $tmp = storage_path("app/{$path_upload}/{$mapping->file}");

            if (file_exists($tmp)) {
                unlink($tmp);
            }
            ModelsMapping::where('id_mapping',$id_mapping)->delete();
            return response()->json([
                'message' => $tmp,
                'status' => 200
            ]);
        }
        return response()->json([
            'message' => "id_mapping tidak ditemukan",
            'status' => 404
        ],404);

    }

    public function displayData($data,$message,$rules = []){
        $total = $data->count();
        // Pages parameter
        $page = request()->page == null ? 1 : request()->page;
        $perpage = request()->perpage == null ? 10 : request()->perpage;
        $data = $data->skip(($page - 1) * $perpage)->take($perpage);

        // Validasi
        $validator = Validator::make(request()->all(),$rules);
        if($validator->fails()){
            return response()->json([
                'message' => $validator->errors()->first()
            ],400);
        }

        // Response
        $MapCol = MappingRes::collection($data);
        return response()->json([
            'message' => $message,
            'status' => 200,
            'perpage' => intval($perpage),
            'page' => intval($page),
            'total' => $total,
            'data' => $MapCol,
        ]);
    }
}
