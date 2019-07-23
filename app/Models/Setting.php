<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{

    protected $primaryKey = 'var';

    public $timestamps = false;

    public $incrementing = false;

    protected $fillable = [
        'var',
        'value'
    ];

    protected $casts = [
        'value' => 'array'
    ];

    protected static $caches = [];

    public static function get($var, $default = null)
    {
        if (isset(self::$caches[$var])) {
            // ada dicache, ga usah baca DB
            return self::$caches[$var];
        }
        
        // baca DB
        $setting = Setting::select('value')->whereVar($var)->first();
        // jika di DB belum diset, $value = default dari parameter
        $value = isset($setting->value) ? $setting->value : $default;
        
        // simpan dicache
        self::$caches[$var] = $value;
        
        // return
        return $value;
    }

    public static function set($var, $value)
    {
        // cek db sudah ada atau buat baru (jika belum ada)
        $setting = Setting::firstOrCreate([
            'var' => $var
        ]);
        
        // set value
        $setting->value = $value;
        // simpan di db
        $setting->save();
        // simpan di cache
        self::$caches[$var] = $value;
        
        return $value;
    }
}
