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
        \App\Facades\Log::debug('Validated data', [
            'data' => $data,
            'rules' => $rules,
        ]);

        $validator = resolve(\Illuminate\Contracts\Validation\Factory::class)->make($data, $rules);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }
    }
}

if (!function_exists('getHumanReadableNameFromViewFunction')) {
    /**
     * @param String $methodName name of view function
     *
     * @return String
     */
    function get_human_readable_name_from_view_function(String $methodName) : String
    {
        if (!starts_with($methodName, 'show')) {
            \App\Facades\Log::warning($methodName.' is not a valid name for a View-Function!');
        } else {
            $readableName = preg_replace(
                '/(?!^)[A-Z]{2,}(?=[A-Z][a-z])|[A-Z][a-z]|[0-9]{1,}/',
                ' $0',
                $methodName
            );
            $methodName = $readableName;
        }

        return $methodName;
    }
}
