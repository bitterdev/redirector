<?php

namespace Concrete\Package\Redirector;

use Bitter\Redirector\Provider\ServiceProvider;
use Concrete\Core\Entity\Package as PackageEntity;
use Concrete\Core\Package\Package;

class Controller extends Package
{
    protected string $pkgHandle = 'redirector';
    protected string $pkgVersion = '0.0.2';
    protected $appVersionRequired = '9.0.0';
    protected $pkgAutoloaderRegistries = [
        'src/Bitter/Redirector' => 'Bitter\Redirector',
    ];

    public function getPackageDescription(): string
    {
        return t('Redirector lets you create and manage 301 redirects directly in Concrete CMS—no .htaccess needed. Ideal for SEO and site migrations, and compatible with any web server.');
    }

    public function getPackageName(): string
    {
        return t('Redirector');
    }

    public function on_start()
    {
        /** @var ServiceProvider $serviceProvider */
        /** @noinspection PhpUnhandledExceptionInspection */
        $serviceProvider = $this->app->make(ServiceProvider::class);
        $serviceProvider->register();
    }

    public function install(): PackageEntity
    {
        $pkg = parent::install();
        $this->installContentFile("data.xml");
        return $pkg;
    }

    public function upgrade()
    {
        parent::upgrade();
        $this->installContentFile("data.xml");
    }
}