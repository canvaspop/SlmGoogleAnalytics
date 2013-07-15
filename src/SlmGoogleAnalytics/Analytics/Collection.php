<?php
/**
 * Collection class
 * Collection of all Tracker objects
 *
 * @author Karl Clement <karl.clement@canvaspop.com>
 * User: karlclement
 * Date: 2013-07-12
 * Time: 3:24 PM
 */

namespace SlmGoogleAnalytics\Analytics;

use \SlmGoogleAnalytics\Analytics\Tracker;

class Collection
{
    /**
     * Enable Google Analytics tracking
     *
     * @var bool
     */
    protected $enableTracking = true;

    /**
     * Array of all tracker objects
     *
     * @var array
     */
    protected $trackers = array();

    function __construct () {}

    /**
     * @return bool
     */
    public function enabled ()
    {
        return $this->enableTracking;
    }

    /**
     * @param bool $enable_tracking
     */
    public function setEnableTracking ($enable_tracking = true)
    {
        $this->enableTracking = (bool) $enable_tracking;
    }

    /**
     * @param array $trackers
     */
    public function setTrackers ($tracker)
    {
        $this->trackers[] = $tracker;
    }

    /**
     * @return array
     */
    public function getTrackers ()
    {
        return $this->trackers;
    }

    /**
     * Add Google Analytics profile
     *
     * @param $title
     * @param $id
     * @return Tracker
     */
    public function addTracker ($title, $id)
    {
        $tracker = new Tracker($id);
        $tracker->setTitle($title);
        $this->setTrackers($tracker);
        return $tracker;
    }
}