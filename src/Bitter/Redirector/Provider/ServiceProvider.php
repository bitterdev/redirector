<?php

namespace Bitter\Redirector\Provider;

use Concrete\Core\Application\Application;
use Concrete\Core\Entity\Site\Site;
use Concrete\Core\Foundation\Service\Provider;
use Concrete\Core\Http\Request;
use Concrete\Core\Http\ResponseFactoryInterface;
use Concrete\Core\Page\Page;
use Concrete\Core\Routing\RouterInterface;
use Bitter\Redirector\Routing\RouteList;
use Concrete\Core\Site\Config\Liaison;
use Concrete\Core\Site\Service;
use Concrete\Core\Support\Facade\Url;

class ServiceProvider extends Provider
{
    protected RouterInterface $router;
    protected Service $siteService;
    protected Site $site;
    protected Liaison $siteConfig;
    protected ResponseFactoryInterface $responseFactory;

    public function __construct(
        Application              $app,
        RouterInterface          $router,
        Service                  $siteService,
        ResponseFactoryInterface $responseFactory
    )
    {
        parent::__construct($app);

        $this->router = $router;
        $this->siteService = $siteService;
        $this->site = $siteService->getActiveSiteForEditing();
        $this->siteConfig = $this->site->getConfigRepository();
        $this->responseFactory = $responseFactory;
    }

    public function register()
    {
        $this->registerRoutes();
        $this->applyRedirects();
    }

    private function applyRedirects()
    {
        $pageRedirects = $this->siteConfig->get("redirector.page_redirects", []);
        $r = Request::getInstance();

        foreach ($pageRedirects as $pageRedirect) {
            if (isset($pageRedirect["oldPath"]) &&
                isset($pageRedirect["cID"]) &&
                $r->getPath() === $pageRedirect["oldPath"] &&
                strlen($pageRedirect["oldPath"]) > 0) {

                $targetPage = Page::getByID($pageRedirect["cID"]);

                if ($targetPage instanceof Page && !$targetPage->isError()) {
                    $this->responseFactory->redirect(Url::to($targetPage))->send();
                    $this->app->shutdown();
                }
            }
        }
    }

    private function registerRoutes()
    {
        $this->router->loadRouteList(new RouteList());
    }
}