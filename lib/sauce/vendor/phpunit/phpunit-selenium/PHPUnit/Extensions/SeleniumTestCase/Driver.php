<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2010-2013, Sebastian Bergmann <sebastian@phpunit.de>.
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
 *   * Neither the name of Sebastian Bergmann nor the names of his
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
 * @package    PHPUnit_Selenium
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2010-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 1.0.0
 */

/**
 * Implementation of the Selenium RC client/server protocol.
 *
 * @package    PHPUnit_Selenium
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2010-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 1.0.0
 */
class PHPUnit_Extensions_SeleniumTestCase_Driver {
	/**
	 * @var    PHPUnit_Extensions_SeleniumTestCase
	 */
	protected $testCase;

	/**
	 * @var    string
	 */
	protected $testId;

	/**
	 * @var    string
	 */
	protected $name;

	/**
	 * @var    string
	 */
	protected $browser;

	/**
	 * @var    string
	 */
	protected $browserUrl;

	/**
	 * @var    boolean
	 */
	protected $collectCodeCoverageInformation = FALSE;

	/**
	 * @var    string
	 */
	protected $host = 'localhost';

	/**
	 * @var    integer
	 */
	protected $port = 4444;

	/**
	 * @var    integer
	 */
	protected $httpTimeout = 45;

	/**
	 * @var    integer
	 */
	protected $seleniumTimeout = 30;

	/**
	 * @var    string
	 */
	protected $sessionId;

	/**
	 * @var    integer
	 */
	protected $sleep = 0;

	/**
	 * @var    boolean
	 */
	protected $useWaitForPageToLoad = TRUE;

	/**
	 * @var    boolean
	 */
	protected $wait = 5;

	/**
	 * @var array
	 */
	protected static $autoGeneratedCommands = array();

	/**
	 * @var array
	 */
	protected $commands = array();

	/**
	 * @var array $userCommands A numerical array which holds custom user commands.
	 */
	protected $userCommands = array();

	/**
	 * @var array
	 */
	protected $verificationErrors = array();

	/**
	 * @var array
	 */
	private $webDriverCapabilities;

	public function __construct() {
		if ( empty( self::$autoGeneratedCommands ) ) {
			self::autoGenerateCommands();
		}
	}

	/**
	 * Only browserName is supported.
	 */
	public function setWebDriverCapabilities( array $capabilities ) {
		$this->webDriverCapabilities = $capabilities;
	}

	/**
	 * @return string
	 */
	public function start() {
		if ( $this->browserUrl == NULL ) {
			throw new PHPUnit_Framework_Exception(
					'setBrowserUrl() needs to be called before start().'
			);
		}

		if ( $this->webDriverCapabilities !== NULL ) {
			$seleniumServerUrl  = PHPUnit_Extensions_Selenium2TestCase_URL::fromHostAndPort( $this->host, $this->port );
			$driver             = new PHPUnit_Extensions_Selenium2TestCase_Driver( $seleniumServerUrl );
			$session            = $driver->startSession( $this->webDriverCapabilities, new PHPUnit_Extensions_Selenium2TestCase_URL( $this->browserUrl ) );
			$webDriverSessionId = $session->id();
			$this->sessionId    = $this->getString(
					'getNewBrowserSession',
					array( $this->browser, $this->browserUrl, '',
							"webdriver.remote.sessionid=$webDriverSessionId" )
			);

			$this->doCommand( 'setTimeout', array( $this->seleniumTimeout * 1000 ) );
		}

		if ( ! isset( $this->sessionId ) ) {
			$this->sessionId = $this->getString(
					'getNewBrowserSession',
					array( $this->browser, $this->browserUrl )
			);

			$this->doCommand( 'setTimeout', array( $this->seleniumTimeout * 1000 ) );
		}

		return $this->sessionId;
	}

	/**
	 * @return string
	 * @since  Method available since Release 1.1.0
	 */
	public function getSessionId() {
		return $this->sessionId;
	}

	/**
	 * @param string
	 *
	 * @since  Method available since Release 1.2.0
	 */
	public function setSessionId( $sessionId ) {
		$this->sessionId = $sessionId;
	}

	/**
	 */
	public function stop() {
		if ( ! isset( $this->sessionId ) ) {
			return;
		}

		$this->doCommand( 'testComplete' );

		$this->sessionId = NULL;
	}

	/**
	 * @param  boolean $flag
	 *
	 * @throws InvalidArgumentException
	 */
	public function setCollectCodeCoverageInformation( $flag ) {
		if ( ! is_bool( $flag ) ) {
			throw PHPUnit_Util_InvalidArgumentHelper::factory( 1, 'boolean' );
		}

		$this->collectCodeCoverageInformation = $flag;
	}

	/**
	 * @param  PHPUnit_Extensions_SeleniumTestCase $testCase
	 */
	public function setTestCase( PHPUnit_Extensions_SeleniumTestCase $testCase ) {
		$this->testCase = $testCase;
	}

	/**
	 * @param  integer $testId
	 */
	public function setTestId( $testId ) {
		$this->testId = $testId;
	}

	/**
	 * @param  string $name
	 *
	 * @throws InvalidArgumentException
	 */
	public function setName( $name ) {
		if ( ! is_string( $name ) ) {
			throw PHPUnit_Util_InvalidArgumentHelper::factory( 1, 'string' );
		}

		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param  string $browser
	 *
	 * @throws InvalidArgumentException
	 */
	public function setBrowser( $browser ) {
		if ( ! is_string( $browser ) ) {
			throw PHPUnit_Util_InvalidArgumentHelper::factory( 1, 'string' );
		}

		$this->browser = $browser;
	}

	/**
	 * @return string
	 */
	public function getBrowser() {
		return $this->browser;
	}

	/**
	 * @param  string $browserUrl
	 *
	 * @throws InvalidArgumentException
	 */
	public function setBrowserUrl( $browserUrl ) {
		if ( ! is_string( $browserUrl ) ) {
			throw PHPUnit_Util_InvalidArgumentHelper::factory( 1, 'string' );
		}

		$this->browserUrl = $browserUrl;
	}

	/**
	 * @param  string $host
	 *
	 * @throws InvalidArgumentException
	 */
	public function setHost( $host ) {
		if ( ! is_string( $host ) ) {
			throw PHPUnit_Util_InvalidArgumentHelper::factory( 1, 'string' );
		}

		$this->host = $host;
	}

	/**
	 * @return string
	 * @since  Method available since Release 1.1.0
	 */
	public function getHost() {
		return $this->host;
	}

	/**
	 * @param  integer $port
	 *
	 * @throws InvalidArgumentException
	 */
	public function setPort( $port ) {
		if ( ! is_int( $port ) ) {
			throw PHPUnit_Util_InvalidArgumentHelper::factory( 1, 'integer' );
		}

		$this->port = $port;
	}

	/**
	 * @return integer
	 * @since  Method available since Release 1.1.0
	 */
	public function getPort() {
		return $this->port;
	}

	/**
	 * @param  integer $timeout for Selenium RC in seconds
	 *
	 * @throws InvalidArgumentException
	 */
	public function setTimeout( $timeout ) {
		if ( ! is_int( $timeout ) ) {
			throw PHPUnit_Util_InvalidArgumentHelper::factory( 1, 'integer' );
		}

		$this->seleniumTimeout = $timeout;
	}

	/**
	 * @param  integer $timeout for HTTP connection to Selenium RC in seconds
	 *
	 * @throws InvalidArgumentException
	 */
	public function setHttpTimeout( $timeout ) {
		if ( ! is_int( $timeout ) ) {
			throw PHPUnit_Util_InvalidArgumentHelper::factory( 1, 'integer' );
		}

		$this->httpTimeout = $timeout;
	}

	/**
	 * @param  integer $seconds
	 *
	 * @throws InvalidArgumentException
	 */
	public function setSleep( $seconds ) {
		if ( ! is_int( $seconds ) ) {
			throw PHPUnit_Util_InvalidArgumentHelper::factory( 1, 'integer' );
		}

		$this->sleep = $seconds;
	}

	/**
	 * Sets the number of seconds to sleep() after *AndWait commands
	 * when setWaitForPageToLoad(FALSE) is used.
	 *
	 * @param  integer $seconds
	 *
	 * @throws InvalidArgumentException
	 */
	public function setWait( $seconds ) {
		if ( ! is_int( $seconds ) ) {
			throw PHPUnit_Util_InvalidArgumentHelper::factory( 1, 'integer' );
		}

		$this->wait = $seconds;
	}

	/**
	 * Sets whether waitForPageToLoad (TRUE) or sleep() (FALSE)
	 * is used after *AndWait commands.
	 *
	 * @param  boolean $flag
	 *
	 * @throws InvalidArgumentException
	 */
	public function setWaitForPageToLoad( $flag ) {
		if ( ! is_bool( $flag ) ) {
			throw PHPUnit_Util_InvalidArgumentHelper::factory( 1, 'boolean' );
		}

		$this->useWaitForPageToLoad = $flag;
	}

	/**
	 * Adds allowed user commands into {@link self::$userCommands}. See
	 * {@link self::__call()} (switch/case -> default) for usage.
	 *
	 * @param string $command A command.
	 *
	 * @return $this
	 * @see    self::__call()
	 */
	public function addUserCommand( $command ) {
		if ( ! is_string( $command ) ) {
			throw PHPUnit_Util_InvalidArgumentHelper::factory( 1, 'string' );
		}
		$this->userCommands[] = $command;
		return $this;
	}

	/**
	 * This method implements the Selenium RC protocol.
	 *
	 * @param  string $command
	 * @param  array  $arguments
	 *
	 * @return mixed
	 * @method unknown  addLocationStrategy()
	 * @method unknown  addLocationStrategyAndWait()
	 * @method unknown  addScript()
	 * @method unknown  addScriptAndWait()
	 * @method unknown  addSelection()
	 * @method unknown  addSelectionAndWait()
	 * @method unknown  allowNativeXpath()
	 * @method unknown  allowNativeXpathAndWait()
	 * @method unknown  altKeyDown()
	 * @method unknown  altKeyDownAndWait()
	 * @method unknown  altKeyUp()
	 * @method unknown  altKeyUpAndWait()
	 * @method unknown  answerOnNextPrompt()
	 * @method unknown  assignId()
	 * @method unknown  assignIdAndWait()
	 * @method unknown  attachFile()
	 * @method unknown  break()
	 * @method unknown  captureEntirePageScreenshot()
	 * @method unknown  captureEntirePageScreenshotAndWait()
	 * @method unknown  captureEntirePageScreenshotToStringAndWait()
	 * @method unknown  captureScreenshotAndWait()
	 * @method unknown  captureScreenshotToStringAndWait()
	 * @method unknown  check()
	 * @method unknown  checkAndWait()
	 * @method unknown  chooseCancelOnNextConfirmation()
	 * @method unknown  chooseCancelOnNextConfirmationAndWait()
	 * @method unknown  chooseOkOnNextConfirmation()
	 * @method unknown  chooseOkOnNextConfirmationAndWait()
	 * @method unknown  click()
	 * @method unknown  clickAndWait()
	 * @method unknown  clickAt()
	 * @method unknown  clickAtAndWait()
	 * @method unknown  close()
	 * @method unknown  contextMenu()
	 * @method unknown  contextMenuAndWait()
	 * @method unknown  contextMenuAt()
	 * @method unknown  contextMenuAtAndWait()
	 * @method unknown  controlKeyDown()
	 * @method unknown  controlKeyDownAndWait()
	 * @method unknown  controlKeyUp()
	 * @method unknown  controlKeyUpAndWait()
	 * @method unknown  createCookie()
	 * @method unknown  createCookieAndWait()
	 * @method unknown  deleteAllVisibleCookies()
	 * @method unknown  deleteAllVisibleCookiesAndWait()
	 * @method unknown  deleteCookie()
	 * @method unknown  deleteCookieAndWait()
	 * @method unknown  deselectPopUp()
	 * @method unknown  deselectPopUpAndWait()
	 * @method unknown  doubleClick()
	 * @method unknown  doubleClickAndWait()
	 * @method unknown  doubleClickAt()
	 * @method unknown  doubleClickAtAndWait()
	 * @method unknown  dragAndDrop()
	 * @method unknown  dragAndDropAndWait()
	 * @method unknown  dragAndDropToObject()
	 * @method unknown  dragAndDropToObjectAndWait()
	 * @method unknown  dragDrop()
	 * @method unknown  dragDropAndWait()
	 * @method unknown  echo ()
	 * @method unknown  fireEvent()
	 * @method unknown  fireEventAndWait()
	 * @method unknown  focus()
	 * @method unknown  focusAndWait()
	 * @method string   getAlert()
	 * @method array    getAllButtons()
	 * @method array    getAllFields()
	 * @method array    getAllLinks()
	 * @method array    getAllWindowIds()
	 * @method array    getAllWindowNames()
	 * @method array    getAllWindowTitles()
	 * @method string   getAttribute( string $attributeLocator )
	 * @method array    getAttributeFromAllWindows( string $attributeName )
	 * @method string   getBodyText()
	 * @method string   getConfirmation()
	 * @method string   getCookie()
	 * @method string   getCookieByName( string $name )
	 * @method integer  getCssCount( string $locator )
	 * @method integer  getCursorPosition( string $locator )
	 * @method integer  getElementHeight( string $locator )
	 * @method integer  getElementIndex( string $locator )
	 * @method integer  getElementPositionLeft( string $locator )
	 * @method integer  getElementPositionTop( string $locator )
	 * @method integer  getElementWidth( string $locator )
	 * @method string   getEval( string $script )
	 * @method string   getExpression( string $expression )
	 * @method string   getHtmlSource()
	 * @method string   getLocation()
	 * @method string   getLogMessages()
	 * @method integer  getMouseSpeed()
	 * @method string   getPrompt()
	 * @method array    getSelectOptions( string $selectLocator )
	 * @method string   getSelectedId( string $selectLocator )
	 * @method array    getSelectedIds( string $selectLocator )
	 * @method string   getSelectedIndex( string $selectLocator )
	 * @method array    getSelectedIndexes( string $selectLocator )
	 * @method string   getSelectedLabel( string $selectLocator )
	 * @method array    getSelectedLabels( string $selectLocator )
	 * @method string   getSelectedValue( string $selectLocator )
	 * @method array    getSelectedValues( string $selectLocator )
	 * @method unknown  getSpeed()
	 * @method unknown  getSpeedAndWait()
	 * @method string   getTable( string $tableCellAddress )
	 * @method string   getText( string $locator )
	 * @method string   getTitle()
	 * @method string   getValue( string $locator )
	 * @method boolean  getWhetherThisFrameMatchFrameExpression( string $currentFrameString, string $target )
	 * @method boolean  getWhetherThisWindowMatchWindowExpression( string $currentWindowString, string $target )
	 * @method integer  getXpathCount( string $xpath )
	 * @method unknown  goBack()
	 * @method unknown  goBackAndWait()
	 * @method unknown  highlight( string $locator )
	 * @method unknown  highlightAndWait( string $locator )
	 * @method unknown  ignoreAttributesWithoutValue( string $ignore )
	 * @method unknown  ignoreAttributesWithoutValueAndWait( string $ignore )
	 * @method boolean  isAlertPresent()
	 * @method boolean  isChecked( locator)
	 * @method boolean  isConfirmationPresent()
	 * @method boolean  isCookiePresent( string $name )
	 * @method boolean  isEditable( string $locator )
	 * @method boolean  isElementPresent( string $locator )
	 * @method boolean  isOrdered( string $locator1, string $locator2 )
	 * @method boolean  isPromptPresent()
	 * @method boolean  isSomethingSelected( string $selectLocator )
	 * @method boolean  isTextPresent( pattern)
	 * @method boolean  isVisible( locator)
	 * @method unknown  keyDown()
	 * @method unknown  keyDownAndWait()
	 * @method unknown  keyDownNative()
	 * @method unknown  keyDownNativeAndWait()
	 * @method unknown  keyPress()
	 * @method unknown  keyPressAndWait()
	 * @method unknown  keyPressNative()
	 * @method unknown  keyPressNativeAndWait()
	 * @method unknown  keyUp()
	 * @method unknown  keyUpAndWait()
	 * @method unknown  keyUpNative()
	 * @method unknown  keyUpNativeAndWait()
	 * @method unknown  metaKeyDown()
	 * @method unknown  metaKeyDownAndWait()
	 * @method unknown  metaKeyUp()
	 * @method unknown  metaKeyUpAndWait()
	 * @method unknown  mouseDown()
	 * @method unknown  mouseDownAndWait()
	 * @method unknown  mouseDownAt()
	 * @method unknown  mouseDownAtAndWait()
	 * @method unknown  mouseMove()
	 * @method unknown  mouseMoveAndWait()
	 * @method unknown  mouseMoveAt()
	 * @method unknown  mouseMoveAtAndWait()
	 * @method unknown  mouseOut()
	 * @method unknown  mouseOutAndWait()
	 * @method unknown  mouseOver()
	 * @method unknown  mouseOverAndWait()
	 * @method unknown  mouseUp()
	 * @method unknown  mouseUpAndWait()
	 * @method unknown  mouseUpAt()
	 * @method unknown  mouseUpAtAndWait()
	 * @method unknown  mouseUpRight()
	 * @method unknown  mouseUpRightAndWait()
	 * @method unknown  mouseUpRightAt()
	 * @method unknown  mouseUpRightAtAndWait()
	 * @method unknown  open()
	 * @method unknown  openWindow()
	 * @method unknown  openWindowAndWait()
	 * @method unknown  pause()
	 * @method unknown  refresh()
	 * @method unknown  refreshAndWait()
	 * @method unknown  removeAllSelections()
	 * @method unknown  removeAllSelectionsAndWait()
	 * @method unknown  removeScript()
	 * @method unknown  removeScriptAndWait()
	 * @method unknown  removeSelection()
	 * @method unknown  removeSelectionAndWait()
	 * @method unknown  retrieveLastRemoteControlLogs()
	 * @method unknown  rollup()
	 * @method unknown  rollupAndWait()
	 * @method unknown  runScript()
	 * @method unknown  runScriptAndWait()
	 * @method unknown  select()
	 * @method unknown  selectAndWait()
	 * @method unknown  selectFrame()
	 * @method unknown  selectPopUp()
	 * @method unknown  selectPopUpAndWait()
	 * @method unknown  selectWindow()
	 * @method unknown  setBrowserLogLevel()
	 * @method unknown  setBrowserLogLevelAndWait()
	 * @method unknown  setContext()
	 * @method unknown  setCursorPosition()
	 * @method unknown  setCursorPositionAndWait()
	 * @method unknown  setMouseSpeed()
	 * @method unknown  setMouseSpeedAndWait()
	 * @method unknown  setSpeed()
	 * @method unknown  setSpeedAndWait()
	 * @method unknown  shiftKeyDown()
	 * @method unknown  shiftKeyDownAndWait()
	 * @method unknown  shiftKeyUp()
	 * @method unknown  shiftKeyUpAndWait()
	 * @method unknown  shutDownSeleniumServer()
	 * @method unknown  store()
	 * @method unknown  submit()
	 * @method unknown  submitAndWait()
	 * @method unknown  type()
	 * @method unknown  typeAndWait()
	 * @method unknown  typeKeys()
	 * @method unknown  typeKeysAndWait()
	 * @method unknown  uncheck()
	 * @method unknown  uncheckAndWait()
	 * @method unknown  useXpathLibrary()
	 * @method unknown  useXpathLibraryAndWait()
	 * @method unknown  waitForCondition()
	 * @method unknown  waitForElementPresent()
	 * @method unknown  waitForElementNotPresent()
	 * @method unknown  waitForPageToLoad()
	 * @method unknown  waitForPopUp()
	 * @method unknown  windowFocus()
	 * @method unknown  windowMaximize()
	 */
	public function __call( $command, $arguments ) {
		$arguments = $this->preprocessParameters( $arguments );

		$wait = FALSE;

		if ( substr( $command, - 7, 7 ) == 'AndWait' ) {
			$command = substr( $command, 0, - 7 );
			$wait    = TRUE;
		}

		switch ( $command ) {
			case 'addLocationStrategy':
			case 'addScript':
			case 'addSelection':
			case 'allowNativeXpath':
			case 'altKeyDown':
			case 'altKeyUp':
			case 'answerOnNextPrompt':
			case 'assignId':
			case 'attachFile':
			case 'break':
			case 'captureEntirePageScreenshot':
			case 'captureScreenshot':
			case 'check':
			case 'chooseCancelOnNextConfirmation':
			case 'chooseOkOnNextConfirmation':
			case 'click':
			case 'clickAt':
			case 'close':
			case 'contextMenu':
			case 'contextMenuAt':
			case 'controlKeyDown':
			case 'controlKeyUp':
			case 'createCookie':
			case 'deleteAllVisibleCookies':
			case 'deleteCookie':
			case 'deselectPopUp':
			case 'doubleClick':
			case 'doubleClickAt':
			case 'dragAndDrop':
			case 'dragAndDropToObject':
			case 'dragDrop':
			case 'echo':
			case 'fireEvent':
			case 'focus':
			case 'goBack':
			case 'highlight':
			case 'ignoreAttributesWithoutValue':
			case 'keyDown':
			case 'keyDownNative':
			case 'keyPress':
			case 'keyPressNative':
			case 'keyUp':
			case 'keyUpNative':
			case 'metaKeyDown':
			case 'metaKeyUp':
			case 'mouseDown':
			case 'mouseDownAt':
			case 'mouseMove':
			case 'mouseMoveAt':
			case 'mouseOut':
			case 'mouseOver':
			case 'mouseUp':
			case 'mouseUpAt':
			case 'mouseUpRight':
			case 'mouseUpRightAt':
			case 'open':
			case 'openWindow':
			case 'pause':
			case 'refresh':
			case 'removeAllSelections':
			case 'removeScript':
			case 'removeSelection':
			case 'retrieveLastRemoteControlLogs':
			case 'rollup':
			case 'runScript':
			case 'select':
			case 'selectFrame':
			case 'selectPopUp':
			case 'selectWindow':
			case 'setBrowserLogLevel':
			case 'setContext':
			case 'setCursorPosition':
			case 'setMouseSpeed':
			case 'setSpeed':
			case 'shiftKeyDown':
			case 'shiftKeyUp':
			case 'shutDownSeleniumServer':
			case 'store':
			case 'submit':
			case 'type':
			case 'typeKeys':
			case 'uncheck':
			case 'useXpathLibrary':
			case 'windowFocus':
			case 'windowMaximize':
			case isset( self::$autoGeneratedCommands[$command] ):
			{
				// Pre-Command Actions
				switch ( $command ) {
					case 'open':
					case 'openWindow':
					{
						if ( $this->collectCodeCoverageInformation ) {
							$this->deleteCookie( 'PHPUNIT_SELENIUM_TEST_ID', 'path=/' );

							$this->createCookie(
									'PHPUNIT_SELENIUM_TEST_ID=' . $this->testId,
									'path=/'
							);
						}
					}
						break;
					case 'store':
						// store is a synonym of storeExpression
						// and RC only understands storeExpression
						$command = 'storeExpression';
						break;
				}

				if ( isset( self::$autoGeneratedCommands[$command] ) && self::$autoGeneratedCommands[$command]['functionHelper'] ) {
					$helperArguments = array( $command, $arguments, self::$autoGeneratedCommands[$command] );
					call_user_func_array( array( $this, self::$autoGeneratedCommands[$command]['functionHelper'] ), $helperArguments );
				}
				else {
					$this->doCommand( $command, $arguments );
				}

				// Post-Command Actions
				switch ( $command ) {
					case 'addLocationStrategy':
					case 'allowNativeXpath':
					case 'assignId':
					case 'captureEntirePageScreenshot':
					case 'captureScreenshot':
					{
						// intentionally empty
					}
						break;

					default:
						{
						if ( $wait ) {
							if ( $this->useWaitForPageToLoad ) {
								$this->waitForPageToLoad( $this->seleniumTimeout * 1000 );
							}
							else {
								sleep( $this->wait );
							}
						}

						if ( $this->sleep > 0 ) {
							sleep( $this->sleep );
						}

						$this->testCase->runDefaultAssertions( $command );
						}
				}
			}
				break;

			case 'getWhetherThisFrameMatchFrameExpression':
			case 'getWhetherThisWindowMatchWindowExpression':
			case 'isAlertPresent':
			case 'isChecked':
			case 'isConfirmationPresent':
			case 'isCookiePresent':
			case 'isEditable':
			case 'isElementPresent':
			case 'isOrdered':
			case 'isPromptPresent':
			case 'isSomethingSelected':
			case 'isTextPresent':
			case 'isVisible':
			{
				return $this->getBoolean( $command, $arguments );
			}
				break;

			case 'getCssCount':
			case 'getCursorPosition':
			case 'getElementHeight':
			case 'getElementIndex':
			case 'getElementPositionLeft':
			case 'getElementPositionTop':
			case 'getElementWidth':
			case 'getMouseSpeed':
			case 'getSpeed':
			case 'getXpathCount':
			{
				$result = $this->getNumber( $command, $arguments );

				if ( $wait ) {
					$this->waitForPageToLoad( $this->seleniumTimeout * 1000 );
				}

				return $result;
			}
				break;

			case 'getAlert':
			case 'getAttribute':
			case 'getBodyText':
			case 'getConfirmation':
			case 'getCookie':
			case 'getCookieByName':
			case 'getEval':
			case 'getExpression':
			case 'getHtmlSource':
			case 'getLocation':
			case 'getLogMessages':
			case 'getPrompt':
			case 'getSelectedId':
			case 'getSelectedIndex':
			case 'getSelectedLabel':
			case 'getSelectedValue':
			case 'getTable':
			case 'getText':
			case 'getTitle':
			case 'captureEntirePageScreenshotToString':
			case 'captureScreenshotToString':
			case 'getValue':
			{
				$result = $this->getString( $command, $arguments );

				if ( $wait ) {
					$this->waitForPageToLoad( $this->seleniumTimeout * 1000 );
				}

				return $result;
			}
				break;

			case 'getAllButtons':
			case 'getAllFields':
			case 'getAllLinks':
			case 'getAllWindowIds':
			case 'getAllWindowNames':
			case 'getAllWindowTitles':
			case 'getAttributeFromAllWindows':
			case 'getSelectedIds':
			case 'getSelectedIndexes':
			case 'getSelectedLabels':
			case 'getSelectedValues':
			case 'getSelectOptions':
			{
				$result = $this->getStringArray( $command, $arguments );

				if ( $wait ) {
					$this->waitForPageToLoad( $this->seleniumTimeout * 1000 );
				}

				return $result;
			}
				break;

			case 'waitForCondition':
			case 'waitForElementPresent':
			case 'waitForElementNotPresent':
			case 'waitForFrameToLoad':
			case 'waitForPopUp':
			{
				if ( count( $arguments ) == 1 ) {
					$arguments[] = $this->seleniumTimeout * 1000;
				}

				$this->doCommand( $command, $arguments );
				$this->testCase->runDefaultAssertions( $command );
			}
				break;

			case 'waitForPageToLoad':
			{
				if ( empty( $arguments ) ) {
					$arguments[] = $this->seleniumTimeout * 1000;
				}

				$this->doCommand( $command, $arguments );
				$this->testCase->runDefaultAssertions( $command );
			}
				break;

			default:
				{
				if ( ! in_array( $command, $this->userCommands ) ) {
					throw new BadMethodCallException(
							"Method $command not defined."
					);
				}
				$this->doCommand( $command, $arguments );
				}
		}
	}

	/**
	 * Send a command to the Selenium RC server.
	 *
	 * @param  string $command
	 * @param  array  $arguments
	 * @param  array  $namedArguments
	 *
	 * @return string
	 * @author Seth Casana <totallymeat@gmail.org>
	 */
	protected function doCommand( $command, array $arguments = array(), array $namedArguments = array() ) {
		$url = sprintf(
				'http://%s:%s/selenium-server/driver/',
				$this->host,
				$this->port
		);

		$numArguments = count( $arguments );
		$postData     = sprintf( 'cmd=%s', urlencode( $command ) );
		for ( $i = 0; $i < $numArguments; $i ++ ) {
			$argNum = strval( $i + 1 );

			if ( $arguments[$i] == ' ' ) {
				$postData .= sprintf( '&%s=%s', $argNum, urlencode( $arguments[$i] ) );
			}
			else {
				$postData .= sprintf( '&%s=%s', $argNum, urlencode( trim( $arguments[$i] ) ) );
			}
		}
		foreach ( $namedArguments as $key => $value ) {
			$postData .= sprintf( '&%s=%s', $key, urlencode( $value ) );
		}

		if ( isset( $this->sessionId ) ) {
			$postData .= sprintf( '&%s=%s', 'sessionId', $this->sessionId );
		}

		$curl = curl_init();
		curl_setopt( $curl, CURLOPT_URL, $url );
		curl_setopt( $curl, CURLOPT_HEADER, 0 );
		curl_setopt( $curl, CURLOPT_POST, TRUE );
		curl_setopt( $curl, CURLOPT_POSTFIELDS, $postData );
		curl_setopt( $curl, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/x-www-form-urlencoded; charset=utf-8'
		) );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, 60 );

		$response = curl_exec( $curl );
		$info     = curl_getinfo( $curl );

		if ( ! $response ) {
			throw new RuntimeException( "CURL error while accessing the Selenium Server at '$url': " . curl_error( $curl ) );
		}

		curl_close( $curl );

		if ( ! preg_match( '/^OK/', $response ) ) {
			throw new RuntimeException( "Invalid response while accessing the Selenium Server at '$url': " . $response );
		}

		if ( $info['http_code'] != 200 ) {
			throw new RuntimeException(
					'The response from the Selenium RC server is invalid: ' .
					$response
			);
		}

		return $response;
	}

	protected function preprocessParameters( $params ) {
		foreach ( $params as $key => $param ) {
			if ( is_string( $param ) && ( strlen( $param ) > 0 ) ) {
				$params[$key] = $this->getString( 'getExpression', array( $param ) );
			}
		}
		return $params;
	}

	/**
	 * Send a command to the Selenium RC server and treat the result
	 * as a boolean.
	 *
	 * @param  string $command
	 * @param  array  $arguments
	 *
	 * @return boolean
	 * @author Shin Ohno <ganchiku@gmail.com>
	 * @author Bjoern Schotte <schotte@mayflower.de>
	 */
	protected function getBoolean( $command, array $arguments ) {
		$result = $this->getString( $command, $arguments );

		switch ( $result ) {
			case 'true':
				return TRUE;

			case 'false':
				return FALSE;

			default:
				{
				throw new PHPUnit_Framework_Exception(
						'Result is neither "true" nor "false": ' . PHPUnit_Util_Type::export( $result )
				);
				}
		}
	}

	/**
	 * Send a command to the Selenium RC server and treat the result
	 * as a number.
	 *
	 * @param  string $command
	 * @param  array  $arguments
	 *
	 * @return numeric
	 * @author Shin Ohno <ganchiku@gmail.com>
	 * @author Bjoern Schotte <schotte@mayflower.de>
	 */
	protected function getNumber( $command, array $arguments ) {
		$result = $this->getString( $command, $arguments );

		if ( ! is_numeric( $result ) ) {
			throw new PHPUnit_Framework_Exception(
					'Result is not numeric: ' . PHPUnit_Util_Type::export( $result )
			);
		}

		return $result;
	}

	/**
	 * Send a command to the Selenium RC server and treat the result
	 * as a string.
	 *
	 * @param  string $command
	 * @param  array  $arguments
	 *
	 * @return string
	 * @author Shin Ohno <ganchiku@gmail.com>
	 * @author Bjoern Schotte <schotte@mayflower.de>
	 */
	protected function getString( $command, array $arguments ) {
		try {
			$result = $this->doCommand( $command, $arguments );
		} catch ( RuntimeException $e ) {
			throw $e;
		}

		return ( strlen( $result ) > 3 ) ? substr( $result, 3 ) : '';
	}

	/**
	 * Send a command to the Selenium RC server and treat the result
	 * as an array of strings.
	 *
	 * @param  string $command
	 * @param  array  $arguments
	 *
	 * @return array
	 * @author Shin Ohno <ganchiku@gmail.com>
	 * @author Bjoern Schotte <schotte@mayflower.de>
	 */
	protected function getStringArray( $command, array $arguments ) {
		$csv     = $this->getString( $command, $arguments );
		$token   = '';
		$tokens  = array();
		$letters = preg_split( '//', $csv, - 1, PREG_SPLIT_NO_EMPTY );
		$count   = count( $letters );

		for ( $i = 0; $i < $count; $i ++ ) {
			$letter = $letters[$i];

			switch ( $letter ) {
				case '\\':
				{
					$letter = $letters[++$i];
					$token .= $letter;
				}
					break;

				case ',':
				{
					$tokens[] = $token;
					$token    = '';
				}
					break;

				default:
					{
					$token .= $letter;
					}
			}
		}

		$tokens[] = $token;

		return $tokens;
	}

	public function getVerificationErrors() {
		return $this->verificationErrors;
	}

	public function clearVerificationErrors() {
		$this->verificationErrors = array();
	}

	protected function assertCommand( $command, $arguments, $info ) {
		$method         = $info['originalMethod'];
		$requiresTarget = $info['requiresTarget'];
		$result         = $this->__call( $method, $arguments );
		$message        = "Failed command: " . $command . "('"
				. ( array_key_exists( 0, $arguments ) ? $arguments[0] . "'" : '' )
				. ( array_key_exists( 1, $arguments ) ? ", '" . $arguments[1] . "'" : '' )
				. ")";

		if ( $info['isBoolean'] ) {
			if ( ! isset( $info['negative'] ) || ! $info['negative'] ) {
				PHPUnit_Framework_Assert::assertTrue( $result, $message );
			}
			else {
				PHPUnit_Framework_Assert::assertFalse( $result, $message );
			}
		}
		else {
			if ( $requiresTarget === TRUE ) {
				$expected = $arguments[1];
			}
			else {
				$expected = $arguments[0];
			}

			if ( strpos( $expected, 'exact:' ) === 0 ) {
				$expected = substr( $expected, strlen( 'exact:' ) );

				if ( ! isset( $info['negative'] ) || ! $info['negative'] ) {
					PHPUnit_Framework_Assert::assertEquals( $expected, $result, $message );
				}
				else {
					PHPUnit_Framework_Assert::assertNotEquals( $expected, $result, $message );
				}
			}
			else {
				$caseInsensitive = FALSE;

				if ( strpos( $expected, 'regexp:' ) === 0 ) {
					$expected = substr( $expected, strlen( 'regexp:' ) );
				}

				else if ( strpos( $expected, 'regexpi:' ) === 0 ) {
					$expected        = substr( $expected, strlen( 'regexpi:' ) );
					$caseInsensitive = TRUE;
				}

				else {
					if ( strpos( $expected, 'glob:' ) === 0 ) {
						$expected = substr( $expected, strlen( 'glob:' ) );
					}

					$expected = '^' . str_replace(
									array( '*', '?' ), array( '.*', '.?' ), $expected
							) . '$';
				}

				$expected = '/' . str_replace( '/', '\/', $expected ) . '/';

				if ( $caseInsensitive ) {
					$expected .= 'i';
				}

				if ( ! isset( $info['negative'] ) || ! $info['negative'] ) {
					PHPUnit_Framework_Assert::assertRegExp(
							$expected, $result, $message
					);
				}
				else {
					PHPUnit_Framework_Assert::assertNotRegExp(
							$expected, $result, $message
					);
				}
			}
		}
	}

	protected function verifyCommand( $command, $arguments, $info ) {
		try {
			$this->assertCommand( $command, $arguments, $info );
		} catch ( PHPUnit_Framework_AssertionFailedError $e ) {
			array_push( $this->verificationErrors, $e->toString() );
		}
	}

	protected function waitForCommand( $command, $arguments, $info ) {
		$lastExceptionMessage = '';
		for ( $second = 0; ; $second ++ ) {
			if ( $second > $this->httpTimeout ) {
				PHPUnit_Framework_Assert::fail(
						"WaitFor timeout. \n"
						. "Last exception message: \n" . $lastExceptionMessage
				);
			}

			try {
				$this->assertCommand( $command, $arguments, $info );
				return;
			} catch ( Exception $e ) {
				$lastExceptionMessage = $e->getMessage();
			}

			sleep( 1 );
		}
	}

	/**
	 * Parses the docblock of PHPUnit_Extensions_SeleniumTestCase_Driver::__call
	 * for get*(), is*(), assert*(), verify*(), assertNot*(), verifyNot*(),
	 * store*(), waitFor*(), and waitForNot*() methods.
	 */
	protected static function autoGenerateCommands() {
		$method     = new ReflectionMethod( __CLASS__, '__call' );
		$docComment = $method->getDocComment();

		if ( preg_match_all( '(@method\s+(\w+)\s+([\w]+)\((.*)\))', $docComment, $matches ) ) {
			foreach ( $matches[2] as $methodKey => $method ) {
				if ( preg_match( '/^(get|is)([A-Z].+)$/', $method, $methodMatches ) ) {
					$baseName       = $methodMatches[2];
					$isBoolean      = $methodMatches[1] == 'is';
					$requiresTarget = ( strlen( $matches[3][$methodKey] ) > 0 );

					if ( preg_match( '/^(.*)Present$/', $baseName, $methodMatches ) ) {
						$notBaseName = $methodMatches[1] . 'NotPresent';
					}
					else {
						$notBaseName = 'Not' . $baseName;
					}

					self::$autoGeneratedCommands['store' . $baseName] = array(
							'functionHelper' => FALSE
					);

					self::$autoGeneratedCommands['assert' . $baseName] = array(
							'originalMethod' => $method,
							'isBoolean'      => $isBoolean,
							'functionHelper' => 'assertCommand',
							'requiresTarget' => $requiresTarget
					);

					self::$autoGeneratedCommands['assert' . $notBaseName] = array(
							'originalMethod' => $method,
							'isBoolean'      => $isBoolean,
							'negative'       => TRUE,
							'functionHelper' => 'assertCommand',
							'requiresTarget' => $requiresTarget
					);

					self::$autoGeneratedCommands['verify' . $baseName] = array(
							'originalMethod' => $method,
							'isBoolean'      => $isBoolean,
							'functionHelper' => 'verifyCommand',
							'requiresTarget' => $requiresTarget
					);

					self::$autoGeneratedCommands['verify' . $notBaseName] = array(
							'originalMethod' => $method,
							'isBoolean'      => $isBoolean,
							'negative'       => TRUE,
							'functionHelper' => 'verifyCommand',
							'requiresTarget' => $requiresTarget
					);

					self::$autoGeneratedCommands['waitFor' . $baseName] = array(
							'originalMethod' => $method,
							'isBoolean'      => $isBoolean,
							'functionHelper' => 'waitForCommand',
							'requiresTarget' => $requiresTarget
					);

					self::$autoGeneratedCommands['waitFor' . $notBaseName] = array(
							'originalMethod' => $method,
							'isBoolean'      => $isBoolean,
							'negative'       => TRUE,
							'functionHelper' => 'waitForCommand',
							'requiresTarget' => $requiresTarget
					);
				}
			}
		}
	}
}
