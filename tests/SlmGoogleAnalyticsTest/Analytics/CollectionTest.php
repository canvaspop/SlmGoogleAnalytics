<?php
namespace SlmGoogleAnalytics\Analytics;

use PHPUnit_Framework_TestCase as TestCase;
use SlmGoogleAnalytics\Analytics\Tracker;
use SlmGoogleAnalytics\Analytics\Collection;

class CollectionTest extends TestCase
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var Collection > Tracker
     */
    protected $child_tracker;

    /**
     * @var Tracker
     */
    protected $tracker;

    protected function setUp()
    {
        $this->collection    = new Collection();
        $this->child_tracker = $this->collection->addTracker( 'Test', '555' );

        $this->tracker = new Tracker( '555' );
        $this->tracker->setTitle( 'Test' );
    }

    protected function tearDown()
    {
    }

    public function testIsEnabledByDefault()
    {
        $this->assertTrue( $this->collection->enabled() );
    }

    public function testCollectionContainsTrackerObjects()
    {
        $this->assertContains( $this->tracker, $this->collection->getTrackers() );
    }

    public function testAddTrackerReturnsTrackerObject()
    {
        $this->assertEquals( $this->tracker, $this->object->addTracker( 'Test', '555' ) );
    }
}
