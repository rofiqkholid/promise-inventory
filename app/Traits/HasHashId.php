<?php

namespace App\Traits;

use Vinkla\Hashids\Facades\Hashids;

trait HasHashId
{
    /**
     * Get the Hashids instance for this model.
     * 
     * @return \Hashids\Hashids
     */
    protected static function getHashidsInstance()
    {
        // Use the config for length and alphabet, but append class name to salt
        $salt = config('app.key') . static::class; 
        $length = config('hashids.connections.main.length', 10);
        $alphabet = config('hashids.connections.main.alphabet', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890');
        
        return new \Hashids\Hashids($salt, $length, $alphabet);
    }

    public function getHashIdAttribute()
    {
        return self::encodeHash($this->attributes['id']);
    }

    /**
     * Encode an ID to a hash.
     *
     * @param int $id
     * @return string
     */
    public static function encodeHash($id)
    {
        return self::getHashidsInstance()->encode($id);
    }

    /**
     * Decode a hash to an ID.
     *
     * @param string $hash
     * @return int|null
     */
    public static function decodeHash($hash)
    {
        if (is_numeric($hash)) {
            return (int) $hash;
        }

        $decoded = self::getHashidsInstance()->decode($hash);
        return empty($decoded) ? null : $decoded[0];
    }

    /**
     * Find a model by its hash ID.
     *
     * @param string $hash
     * @return static|null
     */
    public static function findByHash($hash)
    {
        $id = self::decodeHash($hash);

        if (!$id) {
            return null;
        }

        return self::find($id);
    }

    /**
     * Find a model by its hash ID or throw an exception.
     *
     * @param string $hash
     * @return static
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public static function findByHashOrFail($hash)
    {
        $id = self::decodeHash($hash);

        if (!$id) {
            abort(404, 'Model not found (Invalid Hash).');
        }

        return self::findOrFail($id);
    }
}
