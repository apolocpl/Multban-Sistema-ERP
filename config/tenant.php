<?php

return [
    /**
     * Request keys that should be validated against the authenticated empresa.
     * The middleware will look for these keys in query params, request data or route parameters.
     */
    'protected_parameters' => [
        'emp_id',
        'empresa_id',
        'empresa',
        'id_empresa',
    ],

    /**
     * When route model binding resolves a model, we can attempt to read these attributes
     * in order to validate the empresa automatically.
     */
    'model_tenant_attributes' => [
        'emp_id',
        'empresa_id',
        'company_id',
    ],
];

