<?php

namespace SlmGoogleAnalytics\Analytics;


use SlmGoogleAnalytics\Analytics\Tracker;
use SlmGoogleAnalytics\Exception\InvalidArgumentException;

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

    /**
     * @return bool
     */
    public function enabled()
    {
        return $this->enableTracking;
    }

    /**
     * @return boolean
     */
    public function getEnableTracking()
    {
        return (bool) $this->enableTracking;
    }

    /**
     * @param bool $enable_tracking
     */
    public function setEnableTracking( $enableTracking )
    {
        $this->enableTracking = (bool) $enableTracking;
    }

    /**
     * @param array $trackers
     */
    public function setTrackers( $tracker )
    {
        $this->trackers[ ] = $tracker;
    }

    /**
     * @return array
     */
    public function getTrackers()
    {
        return $this->trackers;
    }

    /**
     * Add Google Analytics profile
     *
     * @param $title
     * @param $id
     *
     * @return Tracker
     */
    public function addTracker( $title, $id )
    {
        $tracker = new Tracker( $id );
        $tracker->setTitle( $title );
        $this->setTrackers( $tracker );

        return $tracker;
    }

    public function getTrackerByTitle( $title )
    {
        foreach( $this->trackers as $tracker )
        {
            if( $title == $tracker->getTitle() )
            {
                return $tracker;
            }
        }

        throw new InvalidArgumentException( 'Title provided does not exist.' );
    }

    public function getTrackerById( $id )
    {
        foreach( $this->trackers as $tracker )
        {
            if( $id == $tracker->getId() )
            {
                return $tracker;
            }
        }

        throw new InvalidArgumentException( 'ID provided does not exist.' );
    }
}