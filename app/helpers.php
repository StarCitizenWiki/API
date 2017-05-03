<?php

if (!function_exists('validate_array')) {
    /**
     * Validates an array with given rules
     *
     * @param array                    $data    The Data to validate
     * @param array                    $rules   The Rules to validate against
     * @param \Illuminate\Http\Request $request The Request, needed to throw a ValidationException
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return void
     */
    function validate_array(array $data, array $rules, \Illuminate\Http\Request $request)
    {
        \Illuminate\Support\Facades\App::make('Log')::debug('Validated data', [
            'data' => $data,
            'rules' => $rules,
        ]);

        $validator = resolve(\Illuminate\Contracts\Validation\Factory::class)->make($data, $rules);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }
    }
}
