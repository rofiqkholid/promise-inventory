<?php

namespace App\Traits;

trait DecodesHashInputs
{
    /**
     * Decode hash inputs based on a mapping of field names to Model classes.
     *
     * @param array $inputs The input data array
     * @param array $mapping Associative array ['field_name' => Model::class]
     * @return array The input array with decoded IDs
     */
    protected function decodeHashInputs(array $inputs, array $mapping)
    {
        foreach ($mapping as $key => $modelClass) {
            if (isset($inputs[$key]) && !is_numeric($inputs[$key])) {
                // Check if model has decodeHash method (via HasHashId trait)
                if (method_exists($modelClass, 'decodeHash')) {
                    $decoded = $modelClass::decodeHash($inputs[$key]);
                    if ($decoded) {
                        $inputs[$key] = $decoded;
                    }
                }
            }
        }
        return $inputs;
    }
}
