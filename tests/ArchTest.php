<?php

arch('it will not use debugging functions')
    ->expect(['dd', 'dump', 'ray'])
    ->each->not->toBeUsed();

arch('exceptions extend base Exception')
    ->expect('Sashalenz\NovapayApi\Exceptions')
    ->toExtend('Exception');

arch('enums are backed enums')
    ->expect('Sashalenz\NovapayApi\Enums')
    ->toBeEnum();

arch('Types extend Spatie Data')
    ->expect('Sashalenz\NovapayApi\Types')
    ->toExtend('Spatie\LaravelData\Data');

arch('ApiModels extend BaseModel')
    ->expect('Sashalenz\NovapayApi\ApiModels')
    ->toExtend('Sashalenz\NovapayApi\ApiModels\BaseModel')
    ->ignoring('Sashalenz\NovapayApi\ApiModels\BaseModel');
