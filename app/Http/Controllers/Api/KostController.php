<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class KostController extends Controller
{
    public function listKost(Request $request)
    {
        $keyword = $request->keyword;
        $data = Kost::orderby('created_at', 'desc');

        if ($request != null) {
            $data->where(function ($query) use ($keyword) {
                $query->orwhere('kosts.nama_kost', 'LIKE', '%' .  $keyword . '%');
            });
        }

        $data = $data->get();
        return response()->json([
            'message' => 'List data kost',
            'data' => $data
        ], Response::HTTP_OK);
    }

    public function addKost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_kost' => 'required|string',
            'deskripsi' => 'required|string',
            'harga' => 'required|numeric',
            'longlat' => 'required|string',
            'gambar.*' => 'required|image|mimes:png,jpg,jpeg'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try {

            DB::beginTransaction();
            $dataGambar = [];
            $files = $request->file('gambar');
            foreach ($files as $key => $file) {
                $filename = 'kost_' . uniqid() .
                    strtolower(Str::random(10)) . '.' . $request->gambar[$key]->extension();
                $file->move('storage/gambar-kost/', $filename);
                $dataGambar[$key] = env('APP_URL') . '/storage/gambar-kost/' . $filename;
            }

            $data = Kost::create([
                'nama_kost' => $request->nama_kost,
                'deskripsi' => $request->deskripsi,
                'harga' => $request->harga,
                'gambar' => json_encode($dataGambar),
                'longlat' => $request->longlat,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Data kost berhasil ditambahkan',
                'data' => $data
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Data kost gagal ditambahkan',
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function updateKost(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'nama_kost' => 'required|string',
            'deskripsi' => 'required|string',
            'harga' => 'required|numeric',
            'longlat' => 'required|string',
            'gambar.*' => 'required|image|mimes:png,jpg,jpeg'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try {
            $dataGambar = [];
            if (isset($request->gambar)) {
                $files = $request->file('gambar');
                foreach ($files as $key => $file) {
                    $filename = 'kost_' . uniqid() .
                        strtolower(Str::random(10)) . '.' . $request->gambar[$key]->extension();
                    $file->move('storage/gambar-kost/', $filename);
                    $dataGambar[$key] = env('APP_URL') . '/storage/gambar-kost/' . $filename;
                }
            }

            $data = Kost::find($id);
            if (!empty($data)) {
                $data->nama_kost = $request->nama_kost;
                $data->deskripsi = $request->deskripsi;
                $data->harga = $request->nama_kost;
                $data->longlat = $request->longlat;
                if ($dataGambar != []) {
                    $data->gambar = json_encode($dataGambar);
                }
                $data->save();
            }
            return response()->json([
                'message' => 'Data kost berhasil diubah',
                'data' => $data
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Data kost gagal diubah',
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function detailKost($id)
    {
        $data = Kost::find($id);

        if (!empty($data)) {
            return response()->json([
                'message' => 'Detail kost',
                'data' => $data
            ], Response::HTTP_OK);
        }
        return response()->json([
            'message' => 'Data kost tidak ditemukan'
        ], Response::HTTP_OK);
    }

    public function deleteKost($id)
    {
        $data = Kost::find($id);
        if ($data->delete()) {
            return response()->json([
                'message' => 'Data kost berhasil dihapus',
            ], Response::HTTP_OK);
        }
        return response()->json([
            'message' => 'Data kost gagal dihapus',
        ], Response::HTTP_BAD_REQUEST);
    }
}