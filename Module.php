<?php
/**
 * Copyright (c) 2012-2013 Jurian Sluiman.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the names of the copyright holders nor the names of the
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @author      Jurian Sluiman <jurian@juriansluiman.nl>
 * @copyright   2012-2013 Jurian Sluiman.
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://juriansluiman.nl
 */
namespace SlmGoogleAnalytics;

use Zend\EventManager\EventInterface;
use Zend\Http\Request as HttpRequest;
use Zend\ModuleManager\Feature;
use Zend\Mvc\MvcEvent;

use SlmGoogleAnalytics\Analytics;
use SlmGoogleAnalytics\View\Helper;
use SlmGoogleAnalytics\Analytics\Collection;

class Module implements
    Feature\AutoloaderProviderInterface,
    Feature\ConfigProviderInterface,
    Feature\ViewHelperProviderInterface,
    Feature\ServiceProviderInterface,
    Feature\BootstrapListenerInterface
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getViewHelperConfig()
    {
        return array(
            'factories' => array(
                'googleAnalytics' => function($sm) {
                    $trackers = $sm->getServiceLocator()->get('google-analytics');
                    $helper  = new Helper\GoogleAnalytics($trackers);

                    return $helper;
                },
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'aliases' => array(
                'google-analytics' => 'SlmGoogleAnalytics\Analytics\Tracker',
            ),
            'factories' => array(
                'SlmGoogleAnalytics\Analytics\Tracker' => function($sm) {
                    $config = $sm->get('config');
                    if (isset($config[ 'mothership' ][ 'settings' ]['google_analytics']))
                    {
                        $config = $config[ 'mothership' ][ 'settings' ]['google_analytics'];
                    }
                    else
                    {
                        $config = $config['google_analytics'];
                    }

                    $trackers = new Collection;

                    if(isset($config['title']) && isset($config['id']))
                    {
                        $tracker = $trackers->addTracker($config['title'], $config['id']);
                    }
                    else
                    {
                        $trackers->setEnableTracking(false);
                        return $trackers;
                    }

                    if (isset($config['domain_name'])) {
                        $tracker->setDomainName($config['domain_name']);
                    }

                    if (isset($config['allow_linker'])) {
                        $tracker->setAllowLinker($config['allow_linker']);
                    }

                    if (false == $config['enable'] || 'false' == $config['enable']) {
                        $trackers->setEnableTracking(false);
                    }

                    return $trackers;
                },
            ),
        );
    }

    /**
     * When the render event is triggered, we invoke the view helper to
     * render the javascript code.
     *
     * @param MvcEvent $e
     */
    public function onBootstrap(EventInterface $e)
    {
        $app = $e->getParam('application');
        $sm  = $app->getServiceManager();
        $em  = $app->getEventManager();

        if (!$app->getRequest() instanceof HttpRequest) {
            return;
        }

        $em->attach(MvcEvent::EVENT_RENDER, function(MvcEvent $e) use ($sm) {
            $view   = $sm->get('ViewHelperManager');
            $plugin = $view->get('googleAnalytics');
            $plugin();
        });
    }
}
