<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all();

        $data = [];

        foreach ($settings as $s) {
            $value = $s->value;

            if ($s->type == 'json') {
                $value = json_decode($value, true);
            }

            $data[$s->key] = $value;
        }

        return response()->json($data);
    }

    // Obtener uno
    public function show($key)
    {
        $setting = Setting::where('key',$key)->firstOrFail();

        $value = $setting->type == 'json'
            ? json_decode($setting->value, true)
            : $setting->value;

        return response()->json([
            'key' => $key,
            'value' => $value
        ]);
    }

    // Guardar / actualizar
    public function store(Request $request)
    {
        $value = $request->value;

        if ($request->type == 'json') {
            $value = json_encode($value);
        }

        Setting::updateOrCreate(
            ['key' => $request->key],
            [
                'value' => $value,
                'type' => $request->type ?? 'text'
            ]
        );

        return response()->json(['message'=>'Guardado']);
    }

    // Subir imagen
    public function upload(Request $request)
    {
        $path = $request->file('file')
            ->store('settings','public');

        Setting::updateOrCreate(
            ['key'=>$request->key],
            [
                'value'=>$path,
                'type'=>'image'
            ]
        );

        return response()->json([
            'path'=>$path
        ]);
    }
}
