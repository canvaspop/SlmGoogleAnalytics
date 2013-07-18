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
namespace SlmGoogleAnalytics\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\View\Helper\HeadScript;
use SlmGoogleAnalytics\Analytics\Tracker;
use SlmGoogleAnalytics\Analytics\Collection;

use SlmGoogleAnalytics\Exception\RuntimeException;

class GoogleAnalytics extends AbstractHelper
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var string
     */
    protected $container = 'InlineScript';

    /**
     * @var bool
     */
    protected $rendered = false;

    public function __construct ($collection)
    {
        $this->collection = $collection;
    }

    public function getContainer ()
    {
        return $this->container;
    }

    public function setContainer ($container)
    {
        $this->container = $container;
    }

    public function __invoke ()
    {

        // Do not render the GA twice
        if  ($this->rendered) {
            return;
        }

        // Do not render when tracker is disabled
        if (!$this->collection->enabled()) {
            return;
        }

        // We need to be sure $container->appendScript() can be called
        $container = $this->view->plugin($this->getContainer());
        if (!$container instanceof HeadScript) {
            throw new RuntimeException(sprintf(
                'Container %s does not extend HeadScript view helper',
                 $this->getContainer()
            ));
        }

        $script  = "var _gaq = _gaq || [];\n";

        foreach ($this->collection->getTrackers() as $tracker) {
            $script .= sprintf("_gaq.push(['%s._setAccount', '%s']);\n",
                $tracker->getTitle(),
                $tracker->getId());

            if ($tracker->getDomainName()) {
                $script .= sprintf("_gaq.push(['%s._setDomainName', '%s']);\n",
                    $tracker->getTitle(),
                    $tracker->getDomainName());
            }

            if ($tracker->getAllowLinker()) {
                $script .= sprintf("_gaq.push(['%s._setAllowLinker', true]);\n",
                    $tracker->getTitle());
            }

            if ($tracker->getAnonymizeIp()) {
                $script .= sprintf("_gaq.push(['%s._gat._anonymizeIp']);\n",
                    $tracker->getTitle());
            }

            if (null !== ($customVariables = $tracker->customVariables())) {
                foreach ($customVariables as $variable) {
                    $script .= sprintf("_gaq.push(['%s._setCustomVar', %d, '%s', '%s', %d]);\n",
                        $tracker->getTitle(),
                        $variable->getIndex(),
                        $variable->getName(),
                        $variable->getValue(),
                        $variable->getScope());
                }
            }

            if ($tracker->enabledPageTracking()) {
                $script .= sprintf("_gaq.push(['%s._trackPageview']);\n",
                    $tracker->getTitle());
            }


            if (null !== ($events = $tracker->events())) {
                foreach ($events as $event) {
                    $script .= sprintf("_gaq.push(['%s._trackEvent', '%s', '%s', '%s', '%s']);\n",
                        $tracker->getTitle(),
                        $event->getCategory(),
                        $event->getAction(),
                        $event->getLabel() ?: '',
                        $event->getValue() ?: '');
                }
            }

            if (null !== ($transactions = $tracker->transactions())) {
                foreach ($transactions as $transaction) {
                    $script .= sprintf("_gaq.push(['%s._addTrans', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']);\n",
                        $tracker->getTitle(),
                        $transaction->getId(),
                        $transaction->getAffiliation() ?: '',
                        $transaction->getTotal(),
                        $transaction->getTax() ?: '',
                        $transaction->getShipping() ?: '',
                        $transaction->getCity() ?: '',
                        $transaction->getState() ?: '',
                        $transaction->getCountry() ?: '');

                    if (null !== ($items = $transaction->items())) {
                        foreach ($items as $item) {
                            $script .= sprintf("_gaq.push(['%s._addItem', '%s', '%s', '%s', '%s', '%s', '%s']);\n",
                                $tracker->getTitle(),
                                $transaction->getId(),
                                $item->getSku() ?: '',
                                $item->getProduct() ?: '',
                                $item->getCategory() ?: '',
                                $item->getPrice(),
                                $item->getQuantity());
                        }
                    }
                }

                $script .= sprintf("_gaq.push(['%s._trackTrans']);",
                    $tracker->getTitle());
            }
        }

        $script .= <<<SCRIPT
(function() {
  var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
  ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
  var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();\n
SCRIPT;

        $container->appendScript($script);

        // Mark this GA as rendered
        $this->rendered = true;
    }
}
