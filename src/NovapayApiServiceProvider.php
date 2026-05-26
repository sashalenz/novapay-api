<?php

namespace Sashalenz\NovapayApi;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class NovapayApiServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('novapay-api')
            ->hasConfigFile();
    }
}
