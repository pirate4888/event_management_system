<?php
///**
// * @author Christoph Bessei
// * @version
// */
//require_once 'PHPUnit/Extensions/Selenium2TestCase.php';
//require_once 'PHPUnit/Extensions/Selenium2TestCase/Driver.php';
//
//
//class Selenium2_Testcase extends PHPUnit_Extensions_Selenium2TestCase {
//	private $baseurl;
//
//	public function __construct() {
//		parent::__construct('Event_Management_System_Test');
//	}
//
//	protected function setUp() {
//
//
//		$wp = new WP_UnitTestCase();
//		$wp->setUp();
//
//		//Works with Sauce Connect or Selenium 2 Server on localhost
//		$this->setHost( "localhost" );
//		$this->setPort( 4445 );
//		$this->baseurl = get_site_url();
//		$this->setBrowser( 'firefox' );
//		$this->setBrowserUrl( $this->baseurl );
//		$caps = array(
//			'name' => get_called_class().'::'.$this->getName(),
//		);
//		$this->setDesiredCapabilities($caps);
//
//	}
//
//	public function testTitle() {
//		$this->url( $this->baseurl );
//		$this->assertContains( "DHV-Jugend", $this->title() );
//		echo "TITEL: ".$this->title();
//	}
//}


require_once( 'config.php' );

class Selenium2_Testcase extends SauceWrapper {
	public static $browsers = array(
		// run FF15 on Windows 8 on Sauce
			array(
					'browserName'         => 'firefox',
					'desiredCapabilities' => array(
							'version'  => '15',
							'platform' => 'Windows 2012',
					)
			)//,
		// run Chrome on Linux on Sauce
		//array(
		//'browserName' => 'chrome',
		//'desiredCapabilities' => array(
		//'platform' => 'Linux'
		//)
		//),
		// run Chrome locally
		//array(
		//'browserName' => 'chrome',
		//'local' => true,
		//'sessionStrategy' => 'shared'
		//)
	);

	public function setUpPage() {
		$this->url( get_site_url() );
	}

	public function testTitle() {
		$this->assertContains( "DHV-Jugend", $this->title() );
	}

//	public function testLink()
//	{
//		$link = $this->byId('i am a link');
//		$link->click();
//		$this->assertContains("I am another page title", $this->title());
//	}
//
//	public function testTextbox()
//	{
//		$test_text = "This is some text";
//		$textbox = $this->byId('i_am_a_textbox');
//		$textbox->click();
//		$this->keys($test_text);
//		$this->assertEquals($textbox->value(), $test_text);
//	}
//
//	public function testSubmitComments()
//	{
//		$comment = "This is a very insightful comment.";
//		$this->byId('comments')->value($comment);
//		$this->byId('submit')->submit();
//		$driver = $this;
//
//		$comment_test = function() use ($comment, $driver) {
//			$text = $driver->byId('your_comments')->text();
//			return $text == "Your comments: $comment";
//		};
//
//		$this->spinAssert("Comment never showed up!", $comment_test);
//
//	}
}

?>